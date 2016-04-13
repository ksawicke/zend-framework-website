<?php
namespace Request\Helper;

class Format
{
    public static $fieldsAsFloat;
    
    public static function setFieldsAsFloat( $fieldsAsFloat )
    {
        self::$fieldsAsFloat = $fieldsAsFloat;
    }
    
    public static function setStringDefaultToZero($string)
    {
        return (empty($string) ? number_format(0, 2) : $string);
    }
    
    public static function trimData( $object )
    {
//        echo '<pre>';
//        var_dump( $object );
//        echo '</pre>';
//        exit();
        
        if(!empty($object)) {
            array_walk_recursive($object, function( &$value, $key ) {
                switch( gettype( $value ) ) {
                    case 'string':
                        $value = trim( $value );
                        break;
                }
//                if( in_array( $key, self::$fieldsAsFloat ) ) {
//                    $value = $value * 1.00;
//                }
            });
        }

//        echo '<pre>';
//        var_dump( $object );
//        echo '</pre>';
//        exit();
        
        return $object;
    }
    
    public static function rightPadEmployeeNumber( $employeeNumber )
    {
        return str_pad( trim( $employeeNumber ), 9, " ", STR_PAD_LEFT );
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