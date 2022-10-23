<?php

declare(strict_types=1);

namespace App\Helper;

use GeoJson\Feature\FeatureCollection;
use GeoJson\GeoJson;

final class GeoJsonFileManipulator
{
    public function read(string $file): FeatureCollection
    {
        $this->ensureFileExists($file);

        if (false === $data = file_get_contents($file)) {
            throw new \RuntimeException('Unable to retrieve content of given file');
        }
        $decodedData = json_decode($data, true);
        if (!\is_array($decodedData)) {
            throw new \RuntimeException('Given file contains unsupported content.');
        }

        $featureCollection = Geojson::jsonUnserialize($decodedData);

        if (!$featureCollection instanceof FeatureCollection) {
            throw new \UnexpectedValueException("Expected a \"GeoJson\Feature\FeatureCollection\", got \"{$featureCollection->getType()}\".");
        }

        return $featureCollection;
    }

    public function write(string $file, FeatureCollection $featureCollection): void
    {
        $this->ensureFileExists($file);

        $json = $featureCollection->jsonSerialize();
        if (false === $json = json_encode($json)) {
            throw new \RuntimeException('Unable to json encode the given FeatureCollection.');
        }

        $json = str_replace('\\\n', '\n', $json);

        file_put_contents($file, $json);
    }

    private function ensureFileExists(string $file): void
    {
        if (file_exists($file)) {
            return;
        }

        throw new \RuntimeException("File $file does not exists.");
    }
}
