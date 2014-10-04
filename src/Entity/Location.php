<?php

namespace App\Entity;

/**
 * Class Location
 *
 * @package App\Entity
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
     * @param float  $elevation
     */
    public function __construct($latitude, $longitude, $id, $name = null, $elevation = null)
    {
        $this->latitude  = $latitude;
        $this->longitude = $longitude;
        $this->id        = $id;
        $this->name      = $name;
        $this->elevation = $elevation;
    }

    /**
     * @param int $locationId
     *
     * @return $this
     */
    public function addLinkedLocation($locationId)
    {
        if ($locationId && !in_array($locationId, $this->linkedLocationIds)) {
            $this->linkedLocationIds[] = $locationId;
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