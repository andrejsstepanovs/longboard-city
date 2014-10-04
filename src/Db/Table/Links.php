<?php

namespace App\Db\Table;

use App\Entity\Diff as Entity;
use App\Entity\Location as LocationEntity;


/**
 * Class Links
 *
 * @package App\Db
 */
class Links extends AbstractTable
{
    /**
     * @return string
     */
    public function createTableQuery()
    {
        return [
            'CREATE TABLE IF NOT EXISTS "links" (
                "start" INTEGER NOT NULL,
                "stop" INTEGER NOT NULL,
                "stops" INTEGER NOT NULL,
                "angle" REAL NOT NULL,
                "distance" REAL NOT NULL,
                "elevation" REAL NOT NULL,
                "name" VARCHAR NOT NULL
            );',
            'CREATE UNIQUE INDEX IF NOT EXISTS "start-stop" ON "links" ("start", "stop");',
            'CREATE INDEX IF NOT EXISTS "angle" ON "links" ("angle");'
        ];
    }

    /**
     * @param Entity $diff
     *
     * @return bool|int
     */
    public function save(Entity $diff)
    {
        $startId = $diff->getStartLocation()->getId();
        $stopId  = $diff->getStopLocation()->getId();

        if ($startId == $stopId) {
            return false;
        }

        $data = [
            'start'     => $startId,
            'stop'      => $stopId,
            'stops'     => $diff->getStops(),
            'angle'     => $diff->getAngle(),
            'distance'  => $diff->getDistance(),
            'elevation' => $diff->getElevation(),
            'name'      => $diff->getName(),
        ];

        return $this->getDb()->save($data, 'links');
    }

    /**
     * @param LocationEntity $location
     *
     * @return int[]
     */
    public function fetchLinkedIds(LocationEntity $location)
    {
        $locationId = $location->getId();
        $sql = 'SELECT * FROM `links` WHERE start = %s OR stop = %s;';
        $result = $this->getDb()->query(sprintf($sql, $locationId, $locationId));

        $links = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $links[$row['start']] = null;
            $links[$row['stop']]  = null;
        };

        if (array_key_exists($locationId, $links)) {
            unset($links[$locationId]);
        }

        return !empty($links) ? array_keys($links) : [];
    }

    /**
     * @return \App\Db\Db
     */
    public function getDb()
    {
        return parent::getDb();
    }
}