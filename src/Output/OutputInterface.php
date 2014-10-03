<?php

namespace App\Output;

use \App\Entity\Diff;


/**
 * Interface OutputInterface
 *
 * @package App\Output
 */
interface OutputInterface
{
    /**
     * @param Diff $diffData
     *
     * @return Diff
     */
    public function setDiffData(array $diffData);

    /**
     * @return string
     */
    public function __toString();
}