<?php

namespace App\Helper;

use \App\Api\Elevation as Api;
use \App\Entity\Location;
use \App\Entity\Diff;
use \App\Db\Table\Location as LocationTable;
use \App\Db\Table\Links as LinksTable;
use \App\Helper\Calculation;
use \App\Helper\Distance;

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

    /** @var LocationTable */
    private $locationTable;

    /** @var Distance */
    private $distance;

    /**
     * @param Api           $api
     * @param LocationTable $locationTable
     * @param LinksTable    $linksTable
     * @param int           $locationsInRequest
     * @param int           $allowedRequestsPerSecond
     */
    public function __construct(Api $api, Distance $distance, LocationTable $locationTable, LinksTable $linksTable, $locationsInRequest, $allowedRequestsPerSecond)
    {
        $this->api                      = $api;
        $this->distance                 = $distance;
        $this->locationTable            = $locationTable;
        $this->linksTable               = $linksTable;
        $this->locationsInRequest       = $locationsInRequest;
        $this->allowedRequestsPerSecond = $allowedRequestsPerSecond;
    }

    /**
     * @param Location &$location
     */
    private function populateLocationLinks(Location &$location)
    {
        $linkedIds = $this->linksTable->fetchLinkedIds($location);
        if (!empty($linkedIds)) {
            foreach ($linkedIds as $locationId) {
                $location->addLinkedLocation($locationId);
            }
        }
    }

    /**
     * @return Location[]
     */
    public function loadLocationsWithLinks()
    {
        $locations = $this->locationTable->fetchAll();

        $iterator = 0;
        $count = count($locations);
        $batch = round($count / 10);

        /** @var Location $location */
        foreach ($locations as $location) {
            $this->populateLocationLinks($location);

            if ($iterator++ && $iterator % $batch == 0) {
                $percent = round($iterator / $count, 2) * 100;
                echo $percent . '%' . PHP_EOL;
            }
        }

        return $locations;
    }

    /**
     * @param int $locationId
     *
     * @return Location|bool
     */
    public function loadLocationWithLinks($locationId)
    {
        if (empty($locationId)) {
            return false;
        }

        $location = $this->locationTable->fetch($locationId);
        if ($location) {
            $this->populateLocationLinks($location);
        }

        return $location;
    }

    /**
     * @param Location[] $locations
     * @param int        $stops
     */
    public function saveLinks(array $locations, $stops)
    {
        $iterator = 0;
        $count = count($locations);
        $batch = round($count / 10);
        foreach ($locations as $startLocation) {
            $linkedIds = $startLocation->getLinkedLocationIds();
            $this->saveLinkedIds($linkedIds, $startLocation, $stops);

            if ($stops > 0) {
                foreach ($linkedIds as $locationId) {
                    if (array_key_exists($locationId, $locations)) {
                        continue;
                    }

                    /** @var Location $linkedLocation */
                    $linkedLocation = $locations[$locationId];

                    if ($linkedLocation) {
                        $nextLinkedIds = $linkedLocation->getLinkedLocationIds();

                        $key = array_search($startLocation->getId(), $nextLinkedIds);
                        if ($key !== false) {
                            unset($nextLinkedIds[$key]);
                        }

                        $nextStops = $stops + 1;
                        $this->saveLinkedIds($nextLinkedIds, $startLocation, $nextStops);
                    }
                }
            }

            if ($iterator++ && $iterator % $batch == 0) {
                $percent = round($iterator / $count, 2) * 100;
                echo $percent . '%' . PHP_EOL;
            }
        }
    }

    /**
     * @param array    $linkedIds
     * @param Location $startLocation
     * @param int      $stops
     */
    private function saveLinkedIds(array $linkedIds, Location $startLocation, $stops)
    {
        foreach ($linkedIds as $stopId) {
            $stopLocation = $this->loadLocationWithLinks($stopId);
            if (!$stopLocation) {
                continue;
            }

            $this->saveDiff($startLocation, $stopLocation, $stops);
        }
    }

    /**
     * @param Location $startLocation
     * @param Location $stopLocation
     * @param int      $stops
     */
    private function saveDiff(Location $startLocation, Location $stopLocation, $stops)
    {
        $distance = $this->distance->getDistance($startLocation, $stopLocation);
        $diff = new Diff(
            $startLocation,
            $stopLocation,
            $distance,
            $stops
        );

        $this->linksTable->save($diff);
    }

    /**
     * @param Location[] $locations
     */
    public function saveLocations(array $locations)
    {
        $count = count($locations);
        $batch = round($count / 10);

        foreach ($locations as $iterator => $location) {
            $this->locationTable->save($location);

            if ($iterator && $iterator % $batch == 0) {
                $percent = round($iterator / $count, 2) * 100;
                echo $percent . '%' . PHP_EOL;
            }
        }
    }

    /**
     * @param Location[] $locations
     */
    public function saveLocationsElevation(array $locations)
    {
        /** @var Location[] $chunk */
        $chunks = array_chunk($locations, $this->locationsInRequest);
        $count  = count($chunks);
        $batch  = round($count / 10);
        $batch  = $batch > 1 ? $batch : 2;
        $sleep  = round(abs($this->allowedRequestsPerSecond / $count - 1), 1);

        foreach ($chunks as $iterator => $chunk) {
            $data = $this->api->getLocationsElevationData($chunk);
            if (count($data) != count($chunk)) {
                throw new \RuntimeException('Something went wrong.');
            }

            foreach ($chunk as $id => $location) {
                $responseData = $data[$id];
                $location->setElevation($responseData['elevation']);

                $this->locationTable->save($location);
            }

            if ($iterator && $iterator % $batch == 0) {
                $percent = round($iterator / $count, 2) * 100;
                echo $percent . '%' . PHP_EOL;
            }

            sleep($sleep);
        }
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

}