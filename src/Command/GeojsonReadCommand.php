<?php

declare(strict_types=1);

namespace App\Command;

use GeoJson\Feature\FeatureCollection;
use GeoJson\Feature\Feature;
use GeoJson\GeoJson;
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

        if (!file_exists($file)) {
            throw new \RuntimeException("File $file does not exists.");
        }

        if (false === $data = file_get_contents($file)) {
            throw new \RuntimeException('Unable to retrieve content of given file');
        }
        $decodedData = json_decode($data, true);
        if (!is_array($decodedData)) {
            throw new \RuntimeException('Given file contains unsupported content.');
        }

        $featureCollection = Geojson::jsonUnserialize($decodedData);

        if (!$featureCollection instanceof FeatureCollection) {
            throw new \UnexpectedValueException("Expected a \"GeoJson\Feature\FeatureCollection\", got \"{$featureCollection->getType()}\".");
        }

        $rows = [];
        /** @var Feature $feature */
        foreach ($featureCollection as $feature) {
            $properties = $feature->getProperties() ?? [];
            if (!\array_key_exists('name', $properties)) {
                throw new \UnexpectedValueException('Given Feature must contains a name property!');
            }

            $rows[] = [$properties['name'], $properties['description'] ?: ''];
        }

        $io->table(['Name', 'Description'], $rows);

        return Command::SUCCESS;
    }
}
