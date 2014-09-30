<?php

namespace App;

use App\Location;
use AnthonyMartin\GeoLocation\GeoLocation;

/**
 * Class Distance
 *
 * @package App
 */
class Distance
{
    /** @var string */
    private $unitOfMeasurement = 'kilometers';

    /**
     * @param Location $start
     * @param Location $stop
     *
     * @return float
     */
    public function getDistance(Location $start, Location $stop)
    {
        $locationStart = GeoLocation::fromDegrees($start->getLatitude(), $start->getLongitude());
        $locationStop  = GeoLocation::fromDegrees($stop->getLatitude(), $stop->getLongitude());

        return $locationStart->distanceTo($locationStop, $this->unitOfMeasurement);
    }
}