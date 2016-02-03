<?php
namespace Request\Helper;

class Format
{
    public static function setStringDefaultToZero($string)
    {
        return (empty($string) ? number_format(0, 2) : $string);
    }
    
    public static function trimData($object)
    {
        array_walk_recursive($object, function( &$value, $key ) {
            /**
             * Value is of type string
             */
            if ( is_string( $value ) ) {
                $value = trim( $value );
            }
        });

        return $object;
    }
}