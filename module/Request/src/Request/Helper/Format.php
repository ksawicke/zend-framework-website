<?php
namespace Request\Helper;

class Format
{
    public static function setStringDefaultToZero($string)
    {
        return (empty($string) ? number_format(0, 2) : $string);
    }
}