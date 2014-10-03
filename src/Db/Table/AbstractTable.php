<?php

namespace App\Db\Table;

use App\Db\Db;

/**
 * Class AbstractTable
 *
 * @package App\Db\Table
 */
abstract class AbstractTable
{
    /** @var Db */
    private $db;

    /**
     * @param Db $db
     */
    public function __construct(Db $db)
    {
        $this->db = $db;

        if (method_exists($this, 'createTableQuery')) {
            $sqlQueries = $this->createTableQuery();
            foreach ($sqlQueries as $sql) {
                $this->getDb()->query($sql);
            }
        }
    }

    /**
     * @return Db
     */
    protected function getDb()
    {
        return $this->db;
    }
}