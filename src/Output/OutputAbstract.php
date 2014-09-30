<?php

namespace App\Output;


/**
 * Class OutputAbstract
 *
 * @package App\Output
 */
class OutputAbstract
{
    /** @var \App\Diff */
    private $diffData;

    /**
     * @param \App\Diff $diffData
     */
    public function setDiffData(array $diffData)
    {
        $this->diffData = $diffData;
    }

    /**
     * @return \App\Diff
     */
    protected function getDiffData()
    {
        return $this->diffData;
    }
}