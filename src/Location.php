<?php

namespace App;

/**
 * Class Location
 *
 * @package App
 */
class Location
{
    /** @var float */
    private $latitude;

    /** @var float */
    private $longitude;

    /** @var float */
    private $elevation;

    /** @var string */
    private $name;

    /** @var int */
    private $id;

    /** @var int[] */
    private $linkedLocationIds = [];

    /**
     * @param float  $latitude
     * @param float  $longitude
     * @param int    $id
     * @param string $name
     */
    public function __construct($latitude, $longitude, $id, $name = null)
    {
        $this->latitude  = $latitude;
        $this->longitude = $longitude;
        $this->id        = $id;
        $this->name      = $name;
    }

    /**
     * @param int $id
     *
     * @return $this
     */
    public function addLinkedLocation($id)
    {
        if (!in_array($id, $this->linkedLocationIds)) {
            $this->linkedLocationIds[] = $id;
        }

        return $this;
    }

    /**
     * @return \int[]
     */
    public function getLinkedLocationIds()
    {
        return $this->linkedLocationIds;
    }

    /**
     * @param float $elevation
     *
     * @return $this
     */
    public function setElevation($elevation)
    {
        $this->elevation = $elevation;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return float
     */
    public function getElevation()
    {
        return $this->elevation;
    }

    /**
     * @return float
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * @return float
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * @return string
     */
    public function getLocation()
    {
        return $this->latitude . ',' . $this->longitude;
    }

    /**
     * @return array
     */
    public function __sleep()
    {
        return [
            'id',
            'name',
            'latitude',
            'longitude',
            'elevation',
            'linkedLocationIds'
        ];
    }
}