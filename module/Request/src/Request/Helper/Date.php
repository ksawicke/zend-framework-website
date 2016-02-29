<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Request\Helper;

/**
 * Description of Date
 *
 * @author sawik
 */
class Date {
    
    function convertToHYD( $dateString )
    {
        $inputDate = new \DateTime( $dateString );
        $year = (int)$inputDate->format( "Y" );
        $month = (int)$inputDate->format( "m" );
        $day = (int)$inputDate->format( "d" );
        $julianDate = gregoriantojd( $month, $day, $year);
        $startDate = gregoriantojd( 1, 1, 1900 );
        
        return ( $julianDate - $startDate );
    }
    
}
