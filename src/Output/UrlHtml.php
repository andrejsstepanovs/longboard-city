<?php

namespace App\Output;

use \App\Diff;


/**
 * Class UrlHtml
 *
 * @package App\Output
 */
class UrlHtml extends Url
{
    /**
     * @return string
     */
    public function __toString()
    {
        return str_replace(PHP_EOL, '<br/>', parent::__toString());
    }

    /**
     * @param Diff $diffData
     */
    protected function getOutputString(Diff $diffData)
    {
        $data = $this->getOutputData($diffData);

        $name = [];
        $name[] = '(' . $data[self::KEY_ELEVATION] . ')';
        $name[] = '(' . $data[self::KEY_ANGLE] . ')';
        $name[] = $data[self::KEY_NAME];
        $name[] = '(' . $data[self::KEY_DISTANCE] . ')';

        return '<a href="' . $data[self::KEY_URL] . '" >' . implode(' ', $name) . '</a>';
    }
}