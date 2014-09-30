<?php

namespace App\Output;

use \App\Diff;


/**
 * Class Url
 *
 * @package App\Output
 */
class Url extends OutputAbstract implements OutputInterface
{
    /** @var string */
    private $url = "https://www.google.de/maps/dir/'%s,%s'/'%s,%s'";

    /**
     * @return string
     */
    public function __toString()
    {
        $return = [];

        foreach ($this->getDiffData() as $elevationDiff) {
            $return[] = $this->getOutput($elevationDiff);
        }

        return implode(PHP_EOL, $return);
    }

    /**
     * @param Diff $diffData
     */
    private function getOutput(Diff $diffData)
    {
        $startLocation = $diffData->getStartLocation();
        $stopLocation  = $diffData->getStopLocation();

        $url = sprintf(
            $this->url,
            $startLocation->getLatitude(),
            $startLocation->getLongitude(),
            $stopLocation->getLatitude(),
            $stopLocation->getLongitude()
        );

        $distance = round($diffData->getDistance(), 2);

        $distance = $distance . 'km';
        $angle    = round($diffData->getAngle(), 2);
        $name     = $startLocation->getName() . ' => ' . $stopLocation->getName();

        return $distance . ' | ' . $angle . ' | ' . $name . ' | ' . $url;
    }
}