<?php

declare(strict_types=1);

namespace App\Helper;

use GeoJson\Feature\FeatureCollection;
use GeoJson\GeoJson;

final class GeoJsonFileManipulator
{
    public function read(string $file): FeatureCollection
    {
        if (!file_exists($file)) {
            throw new \RuntimeException("File $file does not exists.");
        }

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
}
