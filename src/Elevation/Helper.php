<?php

namespace App\Elevation;

use \App\Google\Elevation\Api;
use \App\Location;
use \Fhaculty\Graph\Graph;
use \Fhaculty\Graph\Exception\OutOfBoundsException;


/**
 * Class Helper
 *
 * @package App
 */
class Helper
{
    /** @var Api */
    private $api;

    /** @var int */
    private $locationsInRequest;

    /** @var int */
    private $allowedRequestsPerSecond;

    /**
     * @param Api $api
     * @param int $locationsInRequest
     */
    public function __construct(Api $api, $locationsInRequest, $allowedRequestsPerSecond)
    {
        $this->api                      = $api;
        $this->locationsInRequest       = $locationsInRequest;
        $this->allowedRequestsPerSecond = $allowedRequestsPerSecond;
    }

    /**
     * @param \App\Location $locations
     *
     * @return \App\Location
     */
    public function populateLocationsElevation(array $locations)
    {
        $response = [];

        /** @var Location[] $chunk */
        $chunks = array_chunk($locations, $this->locationsInRequest);

        $count = count($chunks);
        $batch = round($count / 10);
        $batch = $batch > 1 ? $batch : 2;
        $sleep = round(abs($this->allowedRequestsPerSecond / $count - 1), 1);

        foreach ($chunks as $iterator => $chunk) {
            $data = $this->api->getLocationsElevationData($chunk);

            foreach ($chunk as $i => $location) {
                $responseData = $data[$i];
                $location->setElevation($responseData['elevation']);

                $response[] = $location;
            }

            if ($iterator && $iterator % $batch == 0) {
                $percent = round($iterator / $count, 2) * 100;
                echo $percent . '%' . PHP_EOL;
            }

            sleep($sleep);
        }

        return $response;
    }

    /**
     * @param Location[] $locations
     */
    public function filterLocationsWithLinks(array $locations)
    {
        foreach ($locations as $key => $location) {
            $linkedLocationIds = $location->getLinkedLocationIds();

            if (empty($linkedLocationIds)) {
                unset($locations[$key]);
            }
        }

        return $locations;
    }

    /**
     * @param Location[] $locations
     *
     * @return Location[]
     */
    public function populateLocationIds(array $locations)
    {
        $response = [];
        foreach ($locations as $location) {
            $response[$location->getId()] = $location;
        }

        return $response;
    }

    /**
     * @param Location[] $locations
     *
     * @return Graph
     */
    public function getGraph(array $locations)
    {
        $graph = new Graph();
        $this->createVertex($locations, $graph);
        $this->addLinkedLocations($locations, $graph);

        return $graph;
    }

    /**
     * @param array $locations
     * @param Graph $graph
     */
    private function addLinkedLocations(array $locations, Graph $graph)
    {
        $count    = count($locations);
        $iterator = 0;
        $batch    = round($count / 10);

        /** @var Location $location */
        foreach ($locations as $location) {
            $iterator++;

            $currentVertex = $graph->getVertex($location->getId());
            foreach ($location->getLinkedLocationIds() as $linkedLocationId) {
                try {
                    $currentVertex->createEdgeTo($graph->getVertex($linkedLocationId));
                } catch (OutOfBoundsException $exc) { }
            }

            if ($iterator && $iterator % $batch == 0) {
                $percent = round($iterator / $count, 2) * 100;
                echo $percent . '%' . PHP_EOL;
            }
        }
    }

    /**
     * @param array $locations
     * @param Graph $graph
     */
    private function createVertex(array $locations, Graph $graph)
    {
        foreach ($locations as $locationId => $location) {
            $graph->createVertex($locationId);
        }
    }

    /**
     * @param Location $location
     * @param array    $locations
     * @param array    $route
     */
    private function addRoute(Location $location, array $locations, array &$route)
    {
        foreach ($location->getLinkedLocationIds() as $linkedId) {
            $route[$linkedId] = [];

            $this->addRoute($locations[$linkedId], $locations, $route[$linkedId]);
        }
    }
}