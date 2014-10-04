<?php

namespace App\Gtfs;

use \Keboola\Csv\CsvFile;
use \App\Entity\Location;


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
        $count          = $this->getTripCount();
        $batch          = round($count / 10);
        $tripData       = [];
        $tripsProcessed = 0;
        $iterator       = 0;
        $lastTripId     = null;

        foreach ($this->stopTimes as $row) {
            if ($iterator == 0) {
                $iterator = 1;
                continue;
            }

            $tripId = $row[self::TRIP];
            if ($iterator == 1) {
                $iterator = 2;
                $lastTripId = $tripId;
            }

            $tripData[$tripId][$row[self::SEQUENCE]] = $row[self::STOP];

            if (!empty($tripData) && !is_null($lastTripId) && $tripId != $lastTripId) {
                $this->addLinkedLocations($locations, $tripData[$lastTripId]);
                unset($tripData[$lastTripId]);
                $lastTripId = $tripId;

                $tripsProcessed++;
                if ($tripsProcessed && $tripsProcessed % $batch == 0) {
                    $percent = round($tripsProcessed / $count, 2) * 100;
                    echo $percent . '%' . PHP_EOL;
                }

                continue;
            }
        }
    }

    /**
     * @param Location[] $locations
     * @param array      $tripData
     */
    public function addLinkedLocations(array &$locations, array $tripData)
    {
        $parentStopId = null;
        foreach ($tripData as $sequence => $stopId) {
            $parent = array_key_exists($sequence - 1, $tripData) ? $tripData[$sequence - 1] : null;
            $next   = array_key_exists($sequence + 1, $tripData) ? $tripData[$sequence + 1] : null;

            $location = $locations[$stopId];
            $location->addLinkedLocation($parent);
            $location->addLinkedLocation($next);
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