<?php

namespace App;


/**
 * Class Filter
 *
 * @package App
 */
class Filter
{
    /** @var Distance */
    private $distance;

    /** @var string */
    private $filterCity;

    /** @var float */
    private $minDistance;

    /** @var float */
    private $maxDistance;

    /** @var float */
    private $maxDistanceFromHome;

    /** @var Location */
    private $home;

    /**
     * @param Distance $distance
     * @param string   $filterCity
     * @param float    $minDistance
     * @param float    $maxDistance
     * @param float    $maxDistanceFromHome
     * @param Location $home
     */
    public function __construct(
        Distance $distance,
        $filterCity,
        $minDistance,
        $maxDistance,
        $maxDistanceFromHome,
        Location $home
    ) {
        $this->distance            = $distance;
        $this->filterCity          = $filterCity;
        $this->minDistance         = $minDistance;
        $this->maxDistance         = $maxDistance;
        $this->home                = $home;
        $this->maxDistanceFromHome = $maxDistanceFromHome;
    }

    /**
     * @param Location[] $locations
     *
     * @return Location[]
     */
    public function filterCity(array $locations)
    {
        return $this->filterName($locations, $this->filterCity);
    }

    /**
     * @param Location[] $locations
     * @param string     $name
     *
     * @return array
     */
    private function filterName(array $locations, $name)
    {
        if (empty($name)) {
            return $locations;
        }

        $locations = array_filter(
            $locations,
            function (Location $location) use ($name) {
                if (stripos($location->getName(), $name) === false) {
                    return false;
                }
                return true;
            }
        );

        return $locations;
    }

    /**
     * @param Diff[] $diffData
     *
     * @return Diff[]
     */
    public function filterDistance(array $diffData)
    {
        if (empty($this->maxDistance) && empty($this->minDistance)) {
            return $diffData;
        }

        $maxDistance = $this->maxDistance;
        $minDistance = $this->minDistance;

        $diffData = array_filter(
            $diffData,
            function(Diff $diff) use($maxDistance, $minDistance) {
                $distance = $diff->getDistance();
                if ($maxDistance > 0 && $distance > $maxDistance) {
                    return false;
                }

                if ($minDistance > 0 && $distance < $minDistance) {
                    return false;
                }

                return true;
            }
        );

        return $diffData;
    }

    /**
     * @param Diff[]     $diffData
     * @param Location[] $locations
     *
     * @return Diff[]
     */
    public function filterClosestToHome(array $diffData, array $locations)
    {
        if (empty($this->maxDistanceFromHome)) {
            return $diffData;
        }

        $maxDistanceFromHome = $this->maxDistanceFromHome;
        $home                = $this->home;

        $diffData = array_filter(
            $diffData,
            function(Diff $diff) use($maxDistanceFromHome, $home, $locations) {
                $diff->setUp($locations);
                $distance = min(
                    $this->distance->getDistance($home, $diff->getStartLocation()),
                    $this->distance->getDistance($home, $diff->getStopLocation())
                );
                $diff->free();

                if ($distance > $maxDistanceFromHome) {
                    return false;
                }

                return true;
            }
        );

        return $diffData;
    }

    /**
     * @param Diff[] $diffData
     */
    public function limitDiff(array $diffData, $limit)
    {
        if ($limit) {
            $diffData = array_slice($diffData, 0, $limit);
        }

        return $diffData;
    }
}