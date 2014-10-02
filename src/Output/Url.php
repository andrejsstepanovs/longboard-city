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
    const KEY_DISTANCE = 'distance';
    const KEY_ANGLE    = 'angle';
    const KEY_NAME     = 'name';
    const KEY_URL      = 'url';

    /** @var string */
    private $url = "https://www.google.de/maps/dir/'%s,%s'/'%s,%s'";

    /**
     * @return string
     */
    public function __toString()
    {
        $return = [];

        foreach ($this->getDiffData() as $diffData) {
            $diffData->setUp($this->getLocations());
            $return[] = $this->getOutputString($diffData);
            $diffData->free();
        }

        return implode(PHP_EOL, $return);
    }

    /**
     * @param Diff $diffData
     */
    protected function getOutputString(Diff $diffData)
    {
        $data = $this->getOutputData($diffData);

        return implode(' | ', $data);
    }

    /**
     * @param Diff $diffData
     *
     * @return array
     */
    protected function getOutputData(Diff $diffData)
    {
        return [
            self::KEY_DISTANCE => $this->getDistance($diffData),
            self::KEY_ANGLE    => $this->getAngle($diffData),
            self::KEY_NAME     => $diffData->getName(),
            self::KEY_URL      => $this->getUrl($diffData)
        ];
    }

    /**
     * @param Diff $diffData
     *
     * @return string
     */
    private function getAngle(Diff $diffData)
    {
        return round($diffData->getAngle(), 2) . 'º';
    }

    /**
     * @param Diff $diffData
     *
     * @return string
     */
    private function getDistance(Diff $diffData)
    {
        $distance = round($diffData->getDistance(), 2);

        return $distance . 'km';
    }

    /**
     * @param Diff $diffData
     *
     * @return string
     */
    private function getUrl(Diff $diffData)
    {
        $startLocation = $diffData->getStartLocation();
        $stopLocation  = $diffData->getStopLocation();

        return sprintf(
            $this->url,
            $startLocation->getLatitude(),
            $startLocation->getLongitude(),
            $stopLocation->getLatitude(),
            $stopLocation->getLongitude()
        );
    }
}