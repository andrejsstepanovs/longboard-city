<?php

namespace App;

use \App\Location;
use \App\Diff;
use \Fhaculty\Graph\Graph;
use \Fhaculty\Graph\Vertex;


/**
 * Class Calculation
 *
 * @package App
 */
class Calculation
{
    /** @var Distance */
    private $distance;

    /**
     * @param Distance $distance
     */
    public function __construct(Distance $distance)
    {
        $this->distance = $distance;
    }

    /**
     * @param Location[] $locations
     * @param Graph      $graph
     *
     * @return array
     */
    public function getDiffData(array $locations, Graph $graph)
    {
        $data = [];

        foreach ($locations as $location) {
            $firstLocationId = $location->getId();
            /** @var \Fhaculty\Graph\Edge\Directed $directed */
            foreach ($graph->getVertex($firstLocationId)->getEdgesOut() as $directed) {
                $vertex     = $directed->getVertexEnd();
                $locationId = $vertex->getId();
                if (!array_key_exists($locationId, $locations)) {
                    continue;
                }

                /** @var Location $currentLocation */
                $currentLocation = $locations[$locationId];

                $key        = $this->getKey($firstLocationId, $locationId);
                $distance   = $this->distance->getDistance($location, $currentLocation);
                $data[$key] = new Diff($firstLocationId, $locationId, $distance);
            }
        }

        return $data;
    }

    /**
     * @param int $startLocationId
     * @param int $stopLocationId
     *
     * @return string
     */
    private function getKey($startLocationId, $stopLocationId)
    {
        return min($startLocationId, $stopLocationId) . '|' . max($startLocationId, $stopLocationId);
    }
}