<?php

namespace App\Output;


/**
 * Class OutputAbstract
 *
 * @package App\Output
 */
class OutputAbstract
{
    /** @var \App\Diff[] */
    private $diffData;

    /** @var \App\Location[] */
    private $locations;

    /**
     * @param \App\Location[] $locations
     */
    public function setLocations(array $locations)
    {
        $this->locations = $locations;
    }

    /**
     * @return \App\Location[]
     */
    protected function getLocations()
    {
        return $this->locations;
    }

    /**
     * @param \App\Diff[] $diffData
     */
    public function setDiffData(array $diffData)
    {
        $this->diffData = $diffData;
    }

    /**
     * @return \App\Diff[]
     */
    protected function getDiffData()
    {
        return $this->diffData;
    }
}