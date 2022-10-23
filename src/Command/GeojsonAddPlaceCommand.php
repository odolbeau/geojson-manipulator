<?php

declare(strict_types=1);

namespace App\Command;

use App\Helper\GeoJsonFileManipulator;
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
    name: 'geojson:add-place',
    description: 'Add a new place in the existing geojson file.',
)]
class GeojsonAddPlaceCommand extends Command
{
    public function __construct(
        private GeoJsonFileManipulator $geoJsonFileManipulator,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('file', InputArgument::OPTIONAL, 'The file to write', __DIR__.'/../../var/data.geojson')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        /** @phpstan-ignore-next-line */
        $file = (string) $input->getArgument('file');

        /** @phpstan-ignore-next-line */
        $name = (string) $io->ask('Type the name of the new alternative', null, static function ($answer): mixed {
            if (!\is_string($answer)) {
                throw new \InvalidArgumentException('The name is mandatory!');
            }

            return $answer;
        });
        /** @phpstan-ignore-next-line */
        $address = (string) $io->ask('Its address', null, static function ($answer): mixed {
            if (!\is_string($answer)) {
                throw new \InvalidArgumentException('A valid address is mandatory!');
            }

            return $answer;
        });
        /** @phpstan-ignore-next-line */
        $link = (string) $io->ask('A link to a website, wwooff, ...');
        /** @phpstan-ignore-next-line */
        $description = (string) $io->ask('A description is warmly welcomed');
        $latitude = 0.966797;
        $longitude = 44.386692;

        $featureCollection = $this->geoJsonFileManipulator->read($file);

        $features = $featureCollection->getFeatures();
        $features[] = new Feature(new Point([$latitude, $longitude]), [
            'name' => $name,
            'description' => $description,
            'address' => $address,
            'link' => $link,
        ]);

        $this->geoJsonFileManipulator->write($file, new FeatureCollection($features));

        return Command::SUCCESS;
    }
}
