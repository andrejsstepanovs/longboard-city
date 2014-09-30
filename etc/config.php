<?php

return [
    'google-api' => [
        'locations-in-request'        => 50,
        'allowed-requests-per-second' => 10,
        'url'                         => 'https://maps.googleapis.com/maps/api/elevation/json',
        'key'                         => null
    ],
    'gtfs' => [
        'path' => __DIR__ . '/../data/gtfs/',
        'city' => null
    ],
    'filter' => [
        'home' => [
            'latitude'  => null,
            'longitude' => null
        ],
        'limit'        => null,
        'city'         => null,
        'max-distance' => null,
        'min-distance' => null,
    ],
];