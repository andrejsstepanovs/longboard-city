<?php

namespace App\Output;

use App\Distance;


/**
 * Interface OutputInterface
 *
 * @package App\Output
 */
interface OutputInterface
{
    /**
     * @param \App\Diff $diffData
     *
     * @return \App\Diff
     */
    public function setDiffData(array $diffData);

    /**
     * @return string
     */
    public function __toString();
}