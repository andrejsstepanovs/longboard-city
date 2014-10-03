<?php

namespace App\Db;


/**
 * Class Db
 *
 * @package App\Db
 */
class Db
{
    /** @var string */
    private $path;

    /** @var string */
    private $file;

    /** @var \SQLite3 */
    private $db;

    /**
     * @param string $path
     * @param string $file
     */
    public function __construct($path, $file)
    {
        $this->path = $path;
        $this->file = $file;
    }

    /**
     * @return string
     */
    private function getFileName()
    {
        return rtrim($this->path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $this->file;
    }

    /**
     * @return \SQLite3
     * @throws \RuntimeException
     */
    private function getDb()
    {
        if ($this->db === null) {
            $this->db = new \SQLite3($this->getFileName());
            if ($this->db === false) {
                throw new \RuntimeException('Db file cannot be opened.');
            }
        }

        return $this->db;
    }

    public function __destruct()
    {
        $this->getDb()->close();
    }

    /**
     * @return int
     */
    public function getLastId()
    {
        return $this->getDb()->lastInsertRowID();
    }

    /**
     * @param $query
     *
     * @return \SQLite3Result
     */
    public function query($query)
    {
        $result = $this->getDb()->query($query);

        return $result;
    }

    /**
     * @return int
     */
    public function getAffectedRows()
    {
        return $this->getDb()->changes();
    }

    /**
     * @param array  $data
     * @param string $table
     * @param bool   $replace
     *
     * @return string
     */
    public function getInsertQuery(array $data, $table, $replace = true)
    {
        $sql = [];
        $sql[] = $replace ? 'INSERT OR REPLACE' : 'INSERT';
        $sql[] = 'INTO';
        $sql[] = '`' . $table . '`';
        $sql[] = '(`' . implode('`, `', array_keys($data)) . '`)';
        $sql[] = 'VALUES';
        $sql[] = '("' . implode('", "', array_values($data)) . '");';

        return implode(' ', $sql);
    }

    /**
     * @param array  $data
     * @param string $table
     *
     * @return bool|int
     */
    public function save(array $data, $table)
    {
        $sql = $this->getInsertQuery($data, $table);

        $this->query($sql);
        $affected = $this->getAffectedRows();
        if ($affected) {
            return $this->getLastId();
        }

        return false;
    }
}