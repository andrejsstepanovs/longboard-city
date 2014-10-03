<?php

namespace App\Output;

use \App\Entity\Diff;


/**
 * Class OutputAbstract
 *
 * @package App\Output
 */
class OutputAbstract
{
    /** @var Diff[] */
    private $diffData;

    /**
     * @param Diff[] $diffData
     */
    public function setDiffData(array $diffData)
    {
        $this->diffData = $diffData;
    }

    /**
     * @return Diff[]
     */
    protected function getDiffData()
    {
        return $this->diffData;
    }
}