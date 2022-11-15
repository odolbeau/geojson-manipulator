<?php

declare(strict_types=1);

namespace App\Command;

use App\Helper\GeoJsonFileManipulator;
use Geocoder\Provider\Provider;
use Geocoder\Query\GeocodeQuery;
use GeoJson\Feature\Feature;
use GeoJson\Feature\FeatureCollection;
use GeoJson\Geometry\Point;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'geojson:create-from-contacts-csv',
    description: 'Generate a geojson file from a Google CSV file containing contacts.',
)]
/**
 * List of all columns in a Google Contacts CSV
 * 0 => "Name"
 * 1 => "Given Name"
 * 2 => "Additional Name"
 * 3 => "Family Name"
 * 4 => "Yomi Name"
 * 5 => "Given Name Yomi"
 * 6 => "Additional Name Yomi"
 * 7 => "Family Name Yomi"
 * 8 => "Name Prefix"
 * 9 => "Name Suffix"
 * 10 => "Initials"
 * 11 => "Nickname"
 * 12 => "Short Name"
 * 13 => "Maiden Name"
 * 14 => "Birthday"
 * 15 => "Gender"
 * 16 => "Location"
 * 17 => "Billing Information"
 * 18 => "Directory Server"
 * 19 => "Mileage"
 * 20 => "Occupation"
 * 21 => "Hobby"
 * 22 => "Sensitivity"
 * 23 => "Priority"
 * 24 => "Subject"
 * 25 => "Notes"
 * 26 => "Language"
 * 27 => "Photo"
 * 28 => "Group Membership"
 * 29 => "E-mail 1 - Type"
 * 30 => "E-mail 1 - Value"
 * 31 => "E-mail 2 - Type"
 * 32 => "E-mail 2 - Value"
 * 33 => "E-mail 3 - Type"
 * 34 => "E-mail 3 - Value"
 * 35 => "IM 1 - Type"
 * 36 => "IM 1 - Service"
 * 37 => "IM 1 - Value"
 * 38 => "Phone 1 - Type"
 * 39 => "Phone 1 - Value"
 * 40 => "Phone 2 - Type"
 * 41 => "Phone 2 - Value"
 * 42 => "Address 1 - Type"
 * 43 => "Address 1 - Formatted"
 * 44 => "Address 1 - Street"
 * 45 => "Address 1 - City"
 * 46 => "Address 1 - PO Box"
 * 47 => "Address 1 - Region"
 * 48 => "Address 1 - Postal Code"
 * 49 => "Address 1 - Country"
 * 50 => "Address 1 - Extended Address"
 * 51 => "Organization 1 - Type"
 * 52 => "Organization 1 - Name"
 * 53 => "Organization 1 - Yomi Name"
 * 54 => "Organization 1 - Title"
 * 55 => "Organization 1 - Department"
 * 56 => "Organization 1 - Symbol"
 * 57 => "Organization 1 - Location"
 * 58 => "Organization 1 - Job Description"
 * 59 => "Website 1 - Type"
 * 60 => "Website 1 - Value"
 * 61 => "Website 2 - Type"
 * 62 => "Website 2 - Value".
 **/
class GeojsonCreateFromContactsCsvCommand extends Command
{
    public function __construct(
        private Provider $contactsGeocoder,
        private GeoJsonFileManipulator $geoJsonFileManipulator,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('input', InputArgument::OPTIONAL, 'The file to read', __DIR__.'/../../var/contacts.csv')
            ->addArgument('output', InputArgument::OPTIONAL, 'The file to write', __DIR__.'/../../var/contacts.geojson')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        /** @phpstan-ignore-next-line */
        $inputFile = (string) $input->getArgument('input');
        /** @phpstan-ignore-next-line */
        $outputFile = (string) $input->getArgument('output');

        if (!file_exists($inputFile)) {
            throw new \RuntimeException("File $inputFile does not exists.");
        }

        $lines = [];
        if (false !== ($handle = fopen($inputFile, 'r'))) {
            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                $lines[] = $data;
            }
            fclose($handle);
        }

        // Remove headers line
        array_shift($lines);

        $features = [];
        foreach ($lines as $line) {
            if (empty($line[43])) {
                continue;
            }

            $name = $line[0];
            if (!empty($line[11])) {
                $name .= " ({$line[11]})";
            }

            if (null === $coordinates = $this->contactsGeocoder->geocodeQuery(GeocodeQuery::create($line[43]))->first()->getCoordinates()) {
                continue;
            }

            $features[] = new Feature(new Point([$coordinates->getLongitude(), $coordinates->getLatitude()]), [
                'name' => $name,
            ]);
        }

        $this->geoJsonFileManipulator->write($outputFile, new FeatureCollection($features));

        return Command::SUCCESS;
    }
}
