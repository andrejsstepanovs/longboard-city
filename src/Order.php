<?php

namespace App;

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
     * @param array $elevationDiff
     *
     * @return \App\Location
     */
    private function getDiffStartLocation(array $elevationDiff)
    {
        return $elevationDiff[Calculation::START];
    }

    /**
     * @param array $elevationDiff
     *
     * @return \App\Location
     */
    private function getDiffStopLocation(array $elevationDiff)
    {
        return $elevationDiff[Calculation::STOP];
    }

    /**
     * @param array $diffData
     *
     * @return array
     */
    public function orderClosestToHome(array $diffData)
    {
        if (!$this->home) {
            return $diffData;
        }

        $home     = $this->home;
        $distance = $this->distance;

        usort(
            $diffData,
            function (Diff $diffA, Diff $diffB) use ($home, $distance) {
                $distanceA = min(
                    $this->distance->getDistance($home, $diffA->getStartLocation()),
                    $this->distance->getDistance($home, $diffA->getStopLocation())
                );
                $distanceB = min(
                    $this->distance->getDistance($home, $diffB->getStartLocation()),
                    $this->distance->getDistance($home, $diffB->getStopLocation())
                );

                return $distanceA < $distanceB ? -1 : 1;
            }
        );

        return $diffData;
    }

    /**
     * @param array $diffData
     *
     * @return array
     */
    public function orderByAngle(array $diffData)
    {
        usort(
            $diffData,
            function (Diff $diffA, Diff $diffB) {
                $angleA = $diffA->getAngle();
                $angleB = $diffB->getAngle();

                return $angleA < $angleB ? -1 : 1;
            }
        );

        return $diffData;
    }
}