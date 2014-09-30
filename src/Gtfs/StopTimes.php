<?php

namespace App\Gtfs;

use \Keboola\Csv\CsvFile;
use \App\Location;

/**
 * Class StopTimes
 *
 * [0] => trip_id
 * [1] => arrival_time
 * [2] => departure_time
 * [3] => stop_id
 * [4] => stop_sequence
 * [5] => stop_headsign
 * [6] => pickup_type
 * [7] => drop_off_type
 * [8] => shape_dist_traveled
 *
 * @package App\Gtfs
 */
class StopTimes
{
    const TRIP     = 0;
    const STOP     = 3;
    const SEQUENCE = 4;

    /** @var CsvFile */
    private $stopTimes;

    /** @var int */
    private $lastTripId;

    /** @var int */
    private $lastIterator;

    /**
     * @param CsvFile $stopTimes
     */
    public function __construct(CsvFile $stopTimes)
    {
        $this->stopTimes = $stopTimes;
    }

    /**
     * @param Location[] $locations
     */
    public function populateLinkedLocationIds(array &$locations)
    {
        //$count = $this->getTripCount();
        $count    = 204915;
        $batch    = round($count / 10);
        $tripData = [];
        $tripsProcessed = 0;

        foreach ($this->stopTimes as $iterator => $row) {
            if ($iterator == 0) {
                continue;
            }

            $tripId = $row[self::TRIP];

            if ($tripId != $this->lastTripId && $this->lastTripId !== null) {
                $this->lastTripId = $tripId;

                $this->addLinkedLocations($locations, $tripData);

                $tripData = [];

                if ($tripsProcessed && $tripsProcessed % $batch == 0) {
                    $percent = round($tripsProcessed / $count, 2) * 100;
                    echo $percent . '%' . PHP_EOL;
                }
                $tripsProcessed++;

                continue;
            }

            $this->lastTripId = $tripId;
            $tripData[$row[self::SEQUENCE]] = $row[self::STOP];
        }
    }

    /**
     * @param \App\Location[] $locations
     * @param array           $tripData
     */
    public function addLinkedLocations(array &$locations, array $tripData)
    {
        $parentStopId = null;
        foreach ($tripData as $stopId) {
            if ($parentStopId == null) {
                $parentStopId = $stopId;
                continue;
            }

            $locations[$stopId]->addLinkedLocation($parentStopId);
        }
    }

    /**
     * @return int
     */
    private function getTripCount()
    {
        $count      = 0;
        $lastTripId = null;

        foreach ($this->stopTimes as $iterator => $row) {
            if ($iterator == 0) {
                continue;
            }
            $tripId = $row[self::TRIP];

            if ($lastTripId != $tripId) {
                $count++;
                $lastTripId = $tripId;
            }
        }

        return $count;
    }
}