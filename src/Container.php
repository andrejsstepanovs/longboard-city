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

        $this['api'] = function ($self) {
            $googleApi = $self['config']['google-api'];
            $key = $googleApi['key'];
            $url = $googleApi['url'];

            return new \App\Google\Elevation\Api($key, $url);
        };

        $this['helper'] = function ($self) {
            /** @var \App\Google\Elevation\Api $api */
            $api = $self['api'];

            $config = $self['config']['google-api'];
            $locationsInRequest       = $config['locations-in-request'];
            $allowedRequestsPerSecond = $config['allowed-requests-per-second'];

            return new \App\Elevation\Helper($api, $locationsInRequest, $allowedRequestsPerSecond);
        };

        $this['home'] = function ($self) {
            $config = $self['config']['filter'];
            $home = empty($config['home']) ? false : $config['home'];
            if ($home) {
                return new \App\Location($home['latitude'], $home['longitude'], 0, 'Home');
            }

            return null;
        };

        $this['distance'] = function ($self) {
            return new \App\Distance();
        };

        $this['order'] = function ($self) {
            /** @var \App\Location $home */
            $home = $self['home'];

            /** @var \App\Distance $url */
            $distance = $self['distance'];

            return new \App\Order($home, $distance);
        };

        $this['filter'] = function ($self) {
            $config             = $self['config']['filter'];
            $city               = $config['city'];
            $minDistance        = $config['min-distance'];
            $maxDistance        = $config['max-distance'];

            /** @var \App\Location $home */
            $home = $self['home'];

            /** @var \App\Distance $url */
            $distance = $self['distance'];

            return new \App\Filter($distance, $city, $minDistance, $maxDistance, $home);
        };

        $this['calculation'] = function ($self) {

            /** @var Distance $distance */
            $distance = $self['distance'];

            return new \App\Calculation($distance);
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

            return new \App\Output\Factory($url);
        };

        $this['transfers'] = function ($self) {
            $config = $self['config']['gtfs'];
            $file   = $config['path'] . $config['city'] . DIRECTORY_SEPARATOR . 'transfers.txt';
            $stops  = new \Keboola\Csv\CsvFile($file);

            return new \App\Gtfs\Transfers($stops);
        };

        $this['app'] = function ($self) {
            /** @var \App\Gtfs\Stops $stops */
            $stops = $self['stops'];

            /** @var \App\Gtfs\StopTimes $stopTimes */
            $stopTimes = $self['stop-times'];

            /** @var \App\Gtfs\Transfers $stops */
            $transfers = $self['transfers'];

            /** @var \App\Elevation\Helper $helper */
            $helper = $self['helper'];

            /** @var \App\Calculation $calculation */
            $calculation = $self['calculation'];

            /** @var \App\Output\Factory $output */
            $output = $self['output'];

            /** @var \App\Filter $filter */
            $filter = $self['filter'];

            /** @var \App\Order $order */
            $order = $self['order'];

            /** @var int $limit */
            $limit = $self['config']['filter']['limit'];

            return new \App\App(
                $stops,
                $stopTimes,
                $transfers,
                $calculation,
                $helper,
                $output,
                $filter,
                $order,
                $limit
            );
        };
    }

}