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
    const URL_HTML = 'url_html';

    /** @var array */
    private $class = [];

    /**
     * @param Url     $url
     * @param UrlHtml $urlHtml
     */
    public function __construct(Url $url, UrlHtml $urlHtml)
    {
        $this->class[self::URL]      = $url;
        $this->class[self::URL_HTML] = $urlHtml;
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