<?php

declare(strict_types=1);

namespace App\Command;

use App\Helper\GeoJsonFileManipulator;
use GeoJson\Feature\Feature;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'geojson:read',
    description: 'Display an array with all features contained in a geojson file',
)]
class GeojsonReadCommand extends Command
{
    public function __construct(
        private GeoJsonFileManipulator $geoJsonFileManipulator,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('file', InputArgument::OPTIONAL, 'The file to read', __DIR__.'/../../var/data.geojson')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        /** @phpstan-ignore-next-line */
        $file = (string) $input->getArgument('file');

        $featureCollection = $this->geoJsonFileManipulator->read($file);

        $rows = [];
        /** @var Feature $feature */
        foreach ($featureCollection as $feature) {
            $properties = $feature->getProperties() ?? [];
            if (!\array_key_exists('name', $properties)) {
                throw new \UnexpectedValueException('Given Feature must contains a name property!');
            }

            $rows[] = [
                $properties['name'],
                $properties['description'] ?? '',
                $properties['address'] ?? '',
                $properties['link'] ?? '',
            ];
        }

        $io->table(['Name', 'Description', 'Address', 'Link'], $rows);

        return Command::SUCCESS;
    }
}
