<?php

namespace App\Api;

use \App\Entity\Location;
use \Requests;


/**
 * Class Api
 *
 * @package App\Api
 */
class Elevation
{
    /** @var string */
    private $url;

    /** @var string */
    private $key;

    /**
     * @param string $key
     * @param string $url
     */
    public function __construct($key, $url)
    {
        $this->key = $key;
        $this->url = $url;
    }

    /**
     * @param Location[] &$locations
     */
    public function getLocationsElevationData(array &$locations)
    {
        $json    = $this->getElevationResponse($locations);
        $results = $json->results;

        $data = [];
        foreach ($results as $result) {
            $data[] = [
                'elevation'  => $result->elevation, // meters
                'resolution' => $result->resolution,
                'latitude'   => $result->location->lat,
                'longitude'  => $result->location->lng
            ];
        }

        return $data;
    }

    /**
     * @param array $locations
     *
     * @return \stdClass
     * @throws \RuntimeException
     */
    private function getElevationResponse(array $locations)
    {
        $params = ['locations' => $this->getLocationsRequestParam($locations)];
        $url = $this->getUrl($params);

        /** @var \Requests_Response $response */
        $response = Requests::get($url);

        if (!$response->success) {
            throw new \RuntimeException('Request failed');
        }

        $json = json_decode($response->body);

        return $json;
    }

    /**
     * @param array $params
     *
     * @return string
     */
    private function getUrl(array $params)
    {
        $params = array_merge($params, ['key' => $this->key]);

        $url = $this->url . '?' . http_build_query($params);

        return $url;
    }

    /**
     * @param Location[] $locations
     *
     * @return array
     */
    private function getLocationsRequestParam(array $locations)
    {
        $response = [];
        /** @var Location $location */
        foreach ($locations as $location) {
            $response[] = $location->getLocation();
        }

        return implode('|', $response);
    }
}