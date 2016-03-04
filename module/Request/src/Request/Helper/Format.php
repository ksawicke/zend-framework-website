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
        if(!empty($object)) {
            array_walk_recursive($object, function( &$value, $key ) {
                /**
                 * Value is of type string
                 */
                if ( is_string( $value ) ) {
                    $value = trim( $value );
                }
            });
        }

        return $object;
    }
    
    public static function rightPadEmployeeNumber( $employeeNumber )
    {
        return str_pad( $employeeNumber, 9, " ", STR_PAD_LEFT );
    }
    
    public static function rightPad($string)
    {
        return str_pad($string, 9, " ", STR_PAD_LEFT);
    }
    
    public static function p($array)
    {
        echo '<pre>';
        print_r($array);
        echo '</pre>';
    }
}