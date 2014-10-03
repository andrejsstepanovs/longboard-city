<?php

namespace App\Db\Table;

use App\Entity\Diff as Entity;


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
     * @return \App\Db\Db
     */
    public function getDb()
    {
        return parent::getDb();
    }
}