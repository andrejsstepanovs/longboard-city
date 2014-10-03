<?php

namespace App\Db\Table;

use App\Location as Entity;


/**
 * Class Location
 *
 * @package App\Db
 */
class Location extends AbstractTable
{
    /**
     * @return string
     */
    public function createTableQuery()
    {
        return 'CREATE TABLE IF NOT EXISTS "location" (
            "id" INTEGER PRIMARY KEY NOT NULL,
            "latitude" REAL NOT NULL,
            "longitude" REAL NOT NULL,
            "elevation" REAL NULL,
            "name" VARCHAR NOT NULL
        );';
    }

    /**
     * @param Entity $location
     *
     * @return bool|int
     */
    public function save(Entity $location)
    {
        $data = [
            'id'        => $location->getId(),
            'latitude'  => $location->getLatitude(),
            'longitude' => $location->getLongitude(),
            'elevation' => $location->getElevation(),
            'name'      => $location->getName(),
        ];

        return $this->getDb()->save($data, 'location');
    }
}