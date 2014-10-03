<?php

namespace App\Gtfs;

use \Keboola\Csv\CsvFile;
use \App\Entity\Location;

/**
 * Class Stops
 *
 * [0] => stop_id
 * [1] => stop_code
 * [2] => stop_name
 * [3] => stop_desc
 * [4] => stop_lat
 * [5] => stop_lon
 * [6] => zone_id
 * [7] => stop_url
 * [8] => location_type
 * [9] => parent_station
 *
 * @package App\Gtfs
 */
class Stops
{
    const ID     = 0;
    const NAME   = 2;
    const LAT    = 4;
    const LON    = 5;
    const PARENT = 9;

    /** @var CsvFile */
    private $stops;

    /** @var Location[] */
    private $locations;

    /**
     * @param CsvFile $stops
     */
    public function __construct(CsvFile $stops)
    {
        $this->stops = $stops;
    }

    /**
     * @return Location[]
     */
    public function getLocations()
    {
        if ($this->locations === null) {
            $this->locations = [];

            foreach ($this->stops as $iterator => $row) {
                if ($iterator == 0) {
                    continue;
                }

                $location = new Location(
                    $row[self::LAT],
                    $row[self::LON],
                    $row[self::ID],
                    $row[self::NAME]
                );

                $this->locations[] = $location;
            }
        }

        return $this->locations;
    }
}