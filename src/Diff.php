<?php

namespace App;

/**
 * Class Diff
 *
 * @package App
 */
class Diff
{
    /** @var Location */
    private $startLocation;

    /** @var Location */
    private $stopLocation;

    /** @var float */
    private $distance;

    /** @var float */
    private $angle;

    /**
     * @param Location $startLocation
     * @param Location $stopLocation
     * @param float    $distance
     * @param float    $elevation
     */
    public function __construct(Location $startLocation, Location $stopLocation, $distance)
    {
        $this->startLocation = $startLocation;
        $this->stopLocation  = $stopLocation;
        $this->distance      = $distance;
        $elevation           = $this->calculateElevation();
        $this->reorderLocations($elevation);

        $this->elevation     = abs($elevation);
        $this->angle         = $this->calculateAngle();
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
            'startLocation',
            'stopLocation',
            'distance',
            'angle'
        ];
    }
}
