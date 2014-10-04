<?php

namespace App;

use App\Gtfs\Stops;
use App\Gtfs\StopTimes;
use App\Gtfs\Transfers;
use App\Helper\Helper;
use App\Entity\Diff;
use App\Output\Factory;
use App\Db\Table\Location as LocationDb;
use App\Db\Table\Links as LinksDb;
use App\Helper\Calculation;


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

    /**
     * @param Stops       $stops
     * @param StopTimes   $stopTimes
     * @param Transfers   $transfers
     * @param Calculation $calculation
     * @param Helper      $helper
     * @param Factory     $output
     */
    public function __construct(
        Stops $stops,
        StopTimes $stopTimes,
        Transfers $transfers,
        Calculation $calculation,
        Helper $helper,
        Factory $output
    ) {
        $this->stops         = $stops;
        $this->stopTimes     = $stopTimes;
        $this->transfers     = $transfers;
        $this->helper        = $helper;
        $this->calculation   = $calculation;
        $this->output        = $output;
    }

    private function saveLocations()
    {
        echo 'Get locations' . PHP_EOL;
        $locations = $this->stops->getLocations();

        echo 'Populate and Save Location Elevation' . PHP_EOL;
        $this->helper->saveLocationsElevation($locations);
    }

    private function saveTransferLinks()
    {
        echo 'Load Locations With Links from DB';
        $locations = $this->helper->loadLocationsWithLinks();

        echo 'Populate Linked Location Ids from Transfers' . PHP_EOL;
        $this->transfers->populateLinkedLocationIds($locations);

        echo 'Save Links' . PHP_EOL;
        $this->helper->saveLinks($locations, 0);
    }

    private function saveStopLinks()
    {
        echo 'Load Locations With Links from DB';
        $locations = $this->helper->loadLocationsWithLinks();

        echo 'Populate Linked Location Ids from Stop Times' . PHP_EOL;
        $locations = $this->helper->populateLocationIds($locations);
        $this->stopTimes->populateLinkedLocationIds($locations);

        echo 'Save Links' . PHP_EOL;
        $this->helper->saveLinks($locations, 1);
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
        $this->saveLocations();
        $this->saveTransferLinks();
        $this->saveStopLinks();

        $diffData  = $this->calculation->findTracks();

        return $this->getOutput($diffData, Factory::URL_HTML);
    }

}