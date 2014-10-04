<?php

namespace App;

use \Pimple\Container as Pimple;


/**
 * Class Container
 *
 * @package App
 */
class Container extends Pimple
{
    /**
     * @param array $configData
     */
    public function __construct(array $configData)
    {
        $this['config'] = $configData;

        $this->initCalculation();
        $this->initHelper();
        $this->initDb();
        $this->initApp();
    }

    private function initDb()
    {
        $this['db'] = function ($self) {
            $config = $self['config']['db'];
            return new \App\Db\Db($config['path'], $config['file']);
        };

        $this['db-location'] = function ($self) {
            return new \App\Db\Table\Location($self['db']);
        };

        $this['db-links'] = function ($self) {
            return new \App\Db\Table\Links($self['db']);
        };
    }

    private function initApp()
    {
        $this['app'] = function ($self) {
            /** @var \App\Gtfs\Stops $stops */
            $stops = $self['stops'];

            /** @var \App\Gtfs\StopTimes $stopTimes */
            $stopTimes = $self['stop-times'];

            /** @var \App\Gtfs\Transfers $stops */
            $transfers = $self['transfers'];

            /** @var \App\Helper\Helper $helper */
            $helper = $self['helper'];

            /** @var \App\Helper\Calculation $calculation */
            $calculation = $self['calculation'];

            /** @var \App\Output\Factory $output */
            $output = $self['output'];

            return new \App\App(
                $stops,
                $stopTimes,
                $transfers,
                $calculation,
                $helper,
                $output
            );
        };
    }

    private function initCalculation()
    {
        $this['home'] = function ($self) {
            $config = $self['config']['filter'];
            $home = empty($config['home']) ? false : $config['home'];
            if ($home) {
                return new \App\Entity\Location($home['latitude'], $home['longitude'], 0, 'Home');
            }
            return null;
        };

        $this['calculation'] = function ($self) {
            /** @var \App\Helper\Distance $distance */
            $distance = $self['distance'];
            $stops    = $self['config']['filter']['stops'];

            $config = $self['config']['filter'];
            $home   = $self['home'];

            /** @var \App\Db\Table\Location $locationTable */
            $locationTable = $self['db-location'];

            /** @var \App\Db\Table\Links $linksTable */
            $linksTable = $self['db-links'];

            $filterCity          = empty($config['city']) ? null : $config['city'];
            $minDistance         = $config['min-distance'];
            $maxDistance         = $config['max-distance'];
            $maxDistanceFromHome = $config['max-distance-from-home'];

            /** @var int $limit */
            $limit = $self['config']['filter']['limit'];

            return new \App\Helper\Calculation(
                $limit,
                $distance,
                $stops,
                $home,
                $maxDistanceFromHome,
                $locationTable,
                $linksTable,
                $filterCity,
                $minDistance,
                $maxDistance
            );
        };

        $this['stops'] = function ($self) {
            $config = $self['config']['gtfs'];
            $file   = $config['path'] . $config['city'] . DIRECTORY_SEPARATOR . 'stops.txt';
            $stops  = new \Keboola\Csv\CsvFile($file);

            return new \App\Gtfs\Stops($stops);
        };

        $this['stop-times'] = function ($self) {
            $config = $self['config']['gtfs'];
            $file   = $config['path'] . $config['city'] . DIRECTORY_SEPARATOR . 'stop_times.txt';
            $stopTimes = new \Keboola\Csv\CsvFile($file);

            return new \App\Gtfs\StopTimes($stopTimes);
        };

        $this['output'] = function ($self) {
            $url = new \App\Output\Url();
            $urlHtml = new \App\Output\UrlHtml();

            return new \App\Output\Factory($url, $urlHtml);
        };

        $this['transfers'] = function ($self) {
            $config = $self['config']['gtfs'];
            $file   = $config['path'] . $config['city'] . DIRECTORY_SEPARATOR . 'transfers.txt';
            $stops  = new \Keboola\Csv\CsvFile($file);

            return new \App\Gtfs\Transfers($stops);
        };
    }

    private function initHelper()
    {
        $this['api'] = function ($self) {
            $googleApi = $self['config']['google-api'];
            $key = $googleApi['key'];
            $url = $googleApi['url'];

            return new \App\Api\Elevation($key, $url);
        };

        $this['helper'] = function ($self) {
            /** @var \App\Api\Elevation $api */
            $api = $self['api'];

            $config = $self['config']['google-api'];
            $locationsInRequest       = $config['locations-in-request'];
            $allowedRequestsPerSecond = $config['allowed-requests-per-second'];

            /** @var \App\Db\Table\Location $locationDb */
            $locationDb = $self['db-location'];

            /** @var \App\Db\Table\Links $linksDb */
            $linksDb = $self['db-links'];

            /** @var \App\Helper\Distance $url */
            $distance = $self['distance'];

            return new \App\Helper\Helper($api, $distance, $locationDb, $linksDb, $locationsInRequest, $allowedRequestsPerSecond);
        };

        $this['distance'] = function ($self) {
            $geoLocation = new \AnthonyMartin\GeoLocation\GeoLocation();

            return new \App\Helper\Distance($geoLocation);
        };
    }
}