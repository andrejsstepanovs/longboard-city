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
            foreach ($linkedIds as $stopId) {
                if (!array_key_exists($stopId, $locations)) {
                    continue;
                }

                /** @var Location $stopLocation */
                $stopLocation = $locations[$stopId];

                $distance = $this->distance->getDistance($startLocation, $stopLocation);
                $diff = new Diff(
                    $startLocation,
                    $stopLocation,
                    $distance,
                    $stops
                );

                $this->linksTable->save($diff);
            }

            if ($iterator++ && $iterator % $batch == 0) {
                $percent = round($iterator / $count, 2) * 100;
                echo $percent . '%' . PHP_EOL;
            }
        }
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