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
     * @param \App\Location $locations
     *
     * @return \App\Diff
     */
    public function setLocations(array $locations);

    /**
     * @return string
     */
    public function __toString();
}