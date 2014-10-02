<?php

namespace App;

use \App\Location;


/**
 * Class Order
 *
 * @package App
 */
class Order
{
    /** @var \App\Location */
    private $home;

    /** @var \App\Distance */
    private $distance;

    /**
     * @param Location $home
     * @param Distance $distance
     */
    public function __construct(Location $home, Distance $distance)
    {
        $this->home     = $home;
        $this->distance = $distance;
    }

    /**
     * @param Diff[]     $diffData
     * @param Location[] $locations
     *
     * @return Diff[]
     */
    public function orderClosestToHome(array $diffData, array $locations)
    {
        if (!$this->home) {
            return $diffData;
        }

        $home     = $this->home;
        $distance = $this->distance;

        usort(
            $diffData,
            function (Diff $diffA, Diff $diffB) use ($home, $distance, $locations) {
                $diffA->setUp($locations);
                $diffB->setUp($locations);

                $distanceA = min(
                    $this->distance->getDistance($home, $diffA->getStartLocation()),
                    $this->distance->getDistance($home, $diffA->getStopLocation())
                );
                $distanceB = min(
                    $this->distance->getDistance($home, $diffB->getStartLocation()),
                    $this->distance->getDistance($home, $diffB->getStopLocation())
                );

                $diffA->free();
                $diffB->free();

                return $distanceA < $distanceB ? -1 : 1;
            }
        );

        return $diffData;
    }

    /**
     * @param array      $diffData
     * @param Location[] $locations
     *
     * @return array
     */
    public function orderByAngle(array $diffData, array $locations)
    {
        usort(
            $diffData,
            function (Diff $diffA, Diff $diffB) use ($locations) {
                $diffA->setUp($locations);
                $diffB->setUp($locations);

                $angleA = $diffA->getAngle();
                $angleB = $diffB->getAngle();

                $diffA->free();
                $diffB->free();

                return $angleA > $angleB ? -1 : 1;
            }
        );

        return $diffData;
    }
}