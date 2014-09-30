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
    const START = 'from';
    const STOP  = 'to';
    const DIFF  = 'diff';

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
            /** @var Vertex $vertex */
            foreach ($graph->getVertex($location->getId())->getVerticesEdgeFrom() as $vertex) {

                $locationId = $vertex->getId();
                if (!array_key_exists($locationId, $locations)) {
                    continue;
                }

                /** @var Location $currentLocation */
                $currentLocation = $locations[$locationId];

                $distance = $this->distance->getDistance($location, $currentLocation);
                $data[]   = new Diff($location, $currentLocation, $distance);
            }
        }

        return $data;
    }
}