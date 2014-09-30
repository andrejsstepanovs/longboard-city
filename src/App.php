<?php

namespace App;

use App\Gtfs\Stops;
use App\Gtfs\StopTimes;
use App\Gtfs\Transfers;
use App\Elevation\Helper;
use App\Output\Factory;

/**
 * Class App
 *
 * @package App
 */
class App
{
    /** @var Stops */
    private $stops;

    /** @var StopTimes */
    private $stopTimes;

    /** @var Transfers */
    private $transfers;

    /** @var Calculation */
    private $calculation;

    /** @var Factory */
    private $output;

    /** @var Filter */
    private $filter;

    /** @var Order */
    private $order;

    /** @var int */
    private $limit;

    /**
     * @param Stops       $stops
     * @param StopTimes   $stopTimes
     * @param Transfers   $transfers
     * @param Calculation $calculation
     * @param Helper      $helper
     * @param Factory     $output
     * @param Order       $order
     * @param int         $limit
     */
    public function __construct(
        Stops $stops,
        StopTimes $stopTimes,
        Transfers $transfers,
        Calculation $calculation,
        Helper $helper,
        Factory $output,
        Filter $filter,
        Order $order,
        $limit
    ) {
        $this->stops       = $stops;
        $this->stopTimes   = $stopTimes;
        $this->transfers   = $transfers;
        $this->helper      = $helper;
        $this->calculation = $calculation;
        $this->output      = $output;
        $this->filter      = $filter;
        $this->order       = $order;
        $this->limit       = $limit;
    }

    /**
     * @return Location[]
     */
    private function getLocations()
    {
        echo 'Get locations' . PHP_EOL;
        $locations = $this->stops->getLocations();
        $locations = $this->helper->populateLocationIds($locations);
        echo 'Populate Linked Location Ids from Transfers' . PHP_EOL;
        $this->transfers->populateLinkedLocationIds($locations);
        echo 'Populate Linked Location Ids from Stop Times' . PHP_EOL;
        $this->stopTimes->populateLinkedLocationIds($locations);
        $locations = $this->helper->populateLocationIds($locations);
        echo 'Populate Location Elevation' . PHP_EOL;
        $locations = $this->helper->populateLocationsElevation($locations);

        return $locations;
    }

    /**
     * @param Location[] $locations
     *
     * @return Diff[]
     */
    private function getDiff(array $locations)
    {
        echo 'Populate Location Ids' . PHP_EOL;
        $locations = $this->helper->populateLocationIds($locations);
        echo 'Filter City' . PHP_EOL;
        $locations = $this->filter->filterCity($locations);
        echo 'Generate Graph' . PHP_EOL;
        $graph = $this->helper->getGraph($locations);
        echo 'Calculate Diff Data' . PHP_EOL;
        $diffData = $this->calculation->getDiffData($locations, $graph);

        return $diffData;
    }

    /**
     * @param Diff[] $diffData
     *
     * @return Diff[]
     */
    private function findBestMatch(array $diffData)
    {
        $diffData = $this->filter->filterDistance($diffData);

        $diffData = $this->order->orderClosestToHome($diffData);
        $diffData = $this->filter->limitDiff($diffData, $this->limit);

        $diffData = $this->order->orderByAngle($diffData);
        $diffData = $this->filter->limitDiff($diffData, $this->limit);

        return $diffData;
    }

    /**
     * @param Diff[] $diffData
     * @param string $type
     *
     * @return string
     */
    private function getOutput(array $diffData, $type)
    {
        $output = $this->output->getOutput($type);
        $output->setDiffData($diffData);

        return $output->__toString();
    }

    public function getResponse()
    {
        $locations = $this->getLocations();

        $diffData = $this->getDiff($locations);

        $diffData = $this->findBestMatch($diffData);

        return $this->getOutput($diffData, Factory::URL);
    }
}