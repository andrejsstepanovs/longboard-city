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
    'db' => [
        'path' => __DIR__ . '/../data/db/',
    ],
    'filter' => [
        'home' => [
            'latitude'  => null,
            'longitude' => null
        ],
        'stops'                  => null,
        'limit'                  => null,
        'city'                   => null,
        'max-distance-from-home' => null,
        'max-distance'           => null,
        'min-distance'           => null,
    ],
];