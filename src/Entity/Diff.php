<?php

namespace App\Entity;


/**
 * Class Diff
 *
 * @package App\Entity
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
    private $elevation;

    /** @var float */
    private $angle;

    /** @var int */
    private $stops;

    /**
     * @param Location $startLocation
     * @param Location $stopLocation
     * @param float    $distance
     * @param int      $stops
     * @param float    $angle
     * @param float    $elevation
     */
    public function __construct(
        Location $startLocation,
        Location $stopLocation,
        $distance,
        $stops,
        $angle = null,
        $elevation = null
    ) {
        $this->startLocation = $startLocation;
        $this->stopLocation  = $stopLocation;
        $this->distance      = $distance;
        $this->stops         = $stops;
        $this->angle         = $angle;
        $this->elevation     = $elevation === null ? null : abs($elevation);

        $this->setUp();
    }

    private function setUp()
    {
        $elevation = $this->calculateElevation();
        $this->reorderLocations($elevation);

        if ($this->elevation === null) {
            $this->elevation = abs($elevation);
        }

        if ($this->angle === null) {
            $this->angle = $this->calculateAngle();
        }
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
     * @return int
     */
    public function getStops()
    {
        return $this->stops;
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
        $height   = $this->getElevation(); // m
        $distance = $this->getDistance();  // km
        $distance *= 1000;                 // m

        if ($distance == 0) {
            return 0;
        }

        $angle = atan($height / $distance) * 180 / pi();

        return $angle;
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
}
