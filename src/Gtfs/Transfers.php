<?php

namespace App\Gtfs;

use \Keboola\Csv\CsvFile;
use \App\Entity\Location;


/**
 * Class Transfers
 *
 * [0] => from_stop_id
 * [1] => to_stop_id
 * [2] => transfer_type
 * [3] => min_transfer_time
 * [4] => from_trip_id
 * [5] => to_trip_id
 *
 * @package App\Gtfs
 */
class Transfers
{
    const FROM_ID = 0;
    const TO_ID   = 1;

    /** @var CsvFile */
    private $transfers;

    /**
     * @param CsvFile $transfers
     */
    public function __construct(CsvFile $transfers)
    {
        $this->transfers = $transfers;
    }

    /**
     * @return array
     */
    private function filterValidTransfers()
    {
        $rows = [];
        $iterator = 0;
        foreach ($this->transfers as $row) {
            if ($iterator == 0) {
                $iterator = 1;
                continue;
            }

            if ($row[self::FROM_ID] == $row[self::TO_ID]) {
                continue;
            }

            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * @param Location[] $locations
     */
    public function populateLinkedLocationIds(array &$locations)
    {
        $rows  = $this->filterValidTransfers();
        $count = count($locations);
        $batch = round($count / 10);

        $iterator = 0;
        foreach ($locations as &$location) {
            $this->addLink($location, $rows);

            if ($iterator++ && $iterator % $batch == 0) {
                $percent = round($iterator / $count, 2) * 100;
                echo $percent . '%' . PHP_EOL;
            }
        }
    }

    /**
     * @param Location $location
     * @param array    $rows
     */
    public function addLink(Location &$location, array $rows)
    {
        $locationId = $location->getId();
        foreach ($rows as $row) {
            $fromId = $row[self::FROM_ID];
            $toId   = $row[self::TO_ID];

            if ($locationId == $fromId) {
                $location->addLinkedLocation($toId);
            }

            if ($locationId == $toId) {
                $location->addLinkedLocation($fromId);
            }
        }
    }
}