<?php

namespace App\Output;

/**
 * Class Factory
 *
 * @package App\Output
 */
class Factory
{
    const URL = 'url';

    /** @var array */
    private $class = [];

    /**
     * @param Url $url
     */
    public function __construct(Url $url)
    {
        $this->class[self::URL] = $url;
    }

    /**
     * @param $name
     *
     * @return OutputInterface
     */
    public function getOutput($name)
    {
        return $this->class[$name];
    }
}