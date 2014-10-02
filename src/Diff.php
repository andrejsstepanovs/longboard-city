<?php

namespace App;

/**
 * Class Diff
 *
 * @package App
 */
class Diff
{
    /** @var int */
    private $startLocationId;

    /** @var int */
    private $stopLocationId;

    /** @var Location */
    private $startLocation;

    /** @var Location */
    private $stopLocation;

    /** @var float */
    private $distance;

    /** @var float */
    private $elevation;

    /** @var float */
    private $angle;

    /**
     * @param int   $startLocationId
     * @param int   $stopLocationId
     * @param float $distance
     * @param float $elevation
     */
    public function __construct($startLocationId, $stopLocationId, $distance)
    {
        $this->startLocationId = $startLocationId;
        $this->stopLocationId  = $stopLocationId;
        $this->distance        = $distance;
    }

    /**
     * Set up location objects variables and calculate other parameters.
     * This is making current class heavier. To free this data call ::free()
     *
     * @param Location[] $locations
     */
    public function setUp(array $locations)
    {
        $this->startLocation = $locations[$this->startLocationId];
        $this->stopLocation  = $locations[$this->stopLocationId];

        $elevation = $this->calculateElevation();
        $this->reorderLocations($elevation);

        $this->elevation  = abs($elevation);
        $this->angle      = $this->calculateAngle();
    }

    /**
     * Free up unnecessary variables so class is not so heavy in memory
     */
    public function free()
    {
        $this->startLocation = null;
        $this->stopLocation  = null;
        $this->elevation     = null;
        $this->angle         = null;
    }

    /**
     * @param float $elevation
     */
    private function reorderLocations($elevation)
    {
        if ($elevation < 0) {
            $stopLocation        = $this->stopLocation;
            $this->stopLocation  = $this->startLocation;
            $this->startLocation = $stopLocation;
        }
    }

    /**
     * @return float
     */
    private function calculateElevation()
    {
        return $this->startLocation->getElevation() - $this->stopLocation->getElevation();
    }

    /**
     * @return float
     */
    private function calculateAngle()
    {
        $height   = $this->getElevation(); // km
        $distance = $this->getDistance();  // m
        $distance *= 1000;                 // km

        if ($distance == 0) {
            return 0;
        }

        return atan($height / $distance) * 180 / pi();
    }

    /**
     * @return float
     */
    public function getAngle()
    {
        return $this->angle;
    }

    /**
     * @return Location
     */
    public function getStartLocation()
    {
        return $this->startLocation;
    }

    /**
     * @return Location
     */
    public function getStopLocation()
    {
        return $this->stopLocation;
    }

    /**
     * @return float
     */
    public function getDistance()
    {
        return $this->distance;
    }

    /**
     * @return float
     */
    public function getElevation()
    {
        return $this->elevation;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->getStartLocation()->getName() . ' => ' . $this->getStopLocation()->getName();
    }

    /**
     * @return array
     */
    public function __sleep()
    {
        return [
            'startLocationId',
            'stopLocationId',
            'distance'
        ];
    }
}
