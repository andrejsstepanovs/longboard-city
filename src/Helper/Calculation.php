<?php

namespace App\Helper;

use \App\Entity\Location;
use \App\Entity\Diff;
use \App\Db\Table\Location as LocationTable;
use \App\Db\Table\Links as LinksTable;

/**
 * Class Calculation
 *
 * @package App
 */
class Calculation
{
    /** @var Distance */
    private $distance;

    /** @var int */
    private $stops;

    /** @var Location */
    private $home;

    /** @var LocationTable */
    private $locationTable;

    /** @var LinksTable */
    private $linksTable;

    /** @var float */
    private $maxDistance;

    /** @var float */
    private $minDistance;

    /** @var float */
    private $maxDistanceFromHome;

    /** @var string */
    private $filterCity;

    /** @var int */
    private $limit;


    public function __construct(
        $limit,
        Distance $distance,
        $stops,
        Location $home = null,
        $maxDistanceFromHome,
        $locationTable,
        $linksTable,
        $filterCity,
        $minDistance,
        $maxDistance
    ) {
        $this->limit               = $limit;
        $this->distance            = $distance;
        $this->stops               = $stops;
        $this->home                = $home;
        $this->linksTable          = $linksTable;
        $this->locationTable       = $locationTable;
        $this->maxDistanceFromHome = $maxDistanceFromHome;
        $this->filterCity          = $filterCity;
        $this->minDistance         = $minDistance;
        $this->maxDistance         = $maxDistance;
    }

    /**
     * @param Diff[]     $diffData
     * @param Location[] $locations
     *
     * @return Diff[]
     */
    public function filterClosestToHome(array $diffData, array $locations)
    {
        if (empty($this->maxDistanceFromHome)) {
            return $diffData;
        }

        $maxDistanceFromHome = $this->maxDistanceFromHome;
        $home                = $this->home;

        $diffData = array_filter(
            $diffData,
            function(Diff $diff) use($maxDistanceFromHome, $home, $locations) {
                $distance = min(
                    $this->distance->getDistance($home, $diff->getStartLocation()),
                    $this->distance->getDistance($home, $diff->getStopLocation())
                );

                if ($distance > $maxDistanceFromHome) {
                    return false;
                }

                return true;
            }
        );

        return $diffData;
    }

    /**
     * @param Diff[] $diffData
     */
    public function limitDiff(array $diffData, $limit)
    {
        if ($limit) {
            $diffData = array_slice($diffData, 0, $limit);
        }

        return $diffData;
    }

    /**
     * @return string
     */
    private function getQuery()
    {
        $sql = [];
        $sql[] = 'SELECT * FROM links WHERE %s ORDER BY angle DESC';

        $where = [];
        if (!empty($this->filterCity)) {
            $where[] = 'name LIKE "%' . $this->filterCity . '%"';
        }
        if (!empty($this->minDistance)) {
            $where[] = 'distance >= ' . $this->minDistance;
        }
        if (!empty($this->maxDistance)) {
            $where[] = 'distance <= ' . $this->maxDistance;
        }

        $query = implode(' ', $sql);
        return sprintf($query, implode(' AND ', $where));
    }

    /**
     * @return Diff[]
     */
    public function findTracks()
    {
        $query  = $this->getQuery();
        $result = $this->linksTable->getDb()->query($query);

        $diffs = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $diff = $this->getDiffFromRow($row);

            if ($this->home) {
                $distance = min(
                    $this->distance->getDistance($this->home, $diff->getStartLocation()),
                    $this->distance->getDistance($this->home, $diff->getStopLocation())
                );
                if ($distance > $this->maxDistanceFromHome) {
                    continue;
                }
            }
            $diffs[] = $diff;
        }

        $diffs = array_slice($diffs, 0, $this->limit);

        return $diffs;
    }

    /**
     * @param array $row
     *
     * @return Diff
     */
    private function getDiffFromRow(array $row)
    {
        $startLocation = $this->locationTable->fetch($row['start']);
        $stopLocation  = $this->locationTable->fetch($row['stop']);

        return new Diff(
            $startLocation,
            $stopLocation,
            $row['distance'],
            $row['stops'],
            $row['angle']
        );
    }
}