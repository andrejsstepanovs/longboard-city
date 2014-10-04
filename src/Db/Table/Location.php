<?php

namespace App\Db\Table;

use App\Entity\Location as Entity;


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
        return [
            'CREATE TABLE IF NOT EXISTS "location" (
                "id" INTEGER PRIMARY KEY NOT NULL,
                "name" VARCHAR NOT NULL,
                "elevation" REAL NULL,
                "latitude" REAL NOT NULL,
                "longitude" REAL NOT NULL
            );',
            'CREATE INDEX IF NOT EXISTS "name" ON "location" ("name");'
        ];
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

    /**
     * @param $id
     *
     * @return Entity|bool
     */
    public function fetch($id)
    {
        $sql = 'SELECT * FROM `location` WHERE id = ' . $id;
        $result = $this->getDb()->query($sql);

        return $this->populateEntity($result->fetchArray(SQLITE3_ASSOC));
    }

    public function fetchAll()
    {
        $result = $this->getDb()->query('SELECT * FROM `location`');

        $locations = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $locations[$row['id']] = $this->populateEntity($row);
        };

        return $locations;
    }

    /**
     * @param array $row
     *
     * @return Entity|null
     */
    private function populateEntity(array $row)
    {
        if (empty($row)) {
            return false;
        }

        return new Entity(
            $row['latitude'],
            $row['longitude'],
            $row['id'],
            $row['name'],
            $row['elevation']
        );
    }
}