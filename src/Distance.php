<?php

namespace App;

use \App\Location;
use \AnthonyMartin\GeoLocation\GeoLocation;

/**
 * Class Distance
 *
 * @package App
 */
class Distance
{
    /** @var string */
    private $unitOfMeasurement = 'kilometers';

    /** @var GeoLocation */
    private $geoLocation;

    /**
     * @param GeoLocation $geoLocation
     */
    public function __construct(GeoLocation $geoLocation)
    {
        $this->geoLocation = $geoLocation;
    }

    /**
     * @param Location $start
     * @param Location $stop
     *
     * @return float
     */
    public function getDistance(Location $start, Location $stop)
    {
        $locationStart = $this->geoLocation->fromDegrees($start->getLatitude(), $start->getLongitude());
        $locationStop  = $this->geoLocation->fromDegrees($stop->getLatitude(), $stop->getLongitude());

        $distance = $locationStart->distanceTo($locationStop, $this->unitOfMeasurement);

        unset($locationStart, $locationStop);

        return $distance;
    }
}