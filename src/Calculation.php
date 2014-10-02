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

    /** @var int */
    private $stops;

    /** @var Diff[] */
    private $data = [];

    /**
     * @param Distance $distance
     * @param int      $stops
     */
    public function __construct(Distance $distance, $stops)
    {
        $this->distance = $distance;
        $this->stops    = $stops;
    }

    /**
     * @param Location[] $locations
     * @param Graph      $graph
     *
     * @return array
     */
    public function getDiffData(array $locations, Graph $graph)
    {
        foreach ($locations as $location) {
            $firstLocationId = $location->getId();
            $vertex = $graph->getVertex($firstLocationId);

            $this->iterateVertex($vertex, $locations);
        }

        return $this->data;
    }

    /**
     * @param Vertex $startVertex
     * @param array  $locations
     * @param Vertex $parentVertex
     * @param int    $level
     */
    private function iterateVertex(Vertex $startVertex, array $locations, Vertex $parentVertex = null, $level = 1)
    {
        $parentVertex    = $parentVertex ? $parentVertex : $startVertex;
        $firstLocationId = $parentVertex->getId();
        $startLocation   = $locations[$parentVertex->getId()];
        $level++;

        /** @var \Fhaculty\Graph\Edge\Directed $directed */
        foreach ($startVertex->getEdgesOut() as $directed) {
            $stopVertex = $directed->getVertexEnd();
            $locationId = $stopVertex->getId();
            if (!array_key_exists($locationId, $locations)) {
                continue;
            }

            /** @var Location $currentLocation */
            $currentLocation = $locations[$locationId];

            $key      = $this->getKey($firstLocationId, $locationId);
            $distance = $this->distance->getDistance($startLocation, $currentLocation);
            $this->data[$key] = new Diff($firstLocationId, $locationId, $distance);

            if ($level <= $this->stops) {
                $this->iterateVertex($stopVertex, $locations, $parentVertex, $level);
            }
        }
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