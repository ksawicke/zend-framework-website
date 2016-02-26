<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Request\Model;

/**
 * Description of Papaa
 *
 * @author sawik
 */
class Papaa {
    
    public $collection;
    
    public function __construct()
    {
        $this->collection = [];
    }
    
    public function SaveDates( $dateCollection = [] )
    {
//        $startDate = $dateCollection[0]['date'];
//        
//        $startDateObject = new \DateTime( $dateCollection[0]['date'] );
//        $endDate = $startDateObject->add(new \DateInterval('P13D'));
//        $endDate = $endDate->format( "Y-m-d" );
        
//        for( $i = 1; $i <= 14; $i++ ) {
//            
//        }
//        
//        for( $i = 1; $i <= count( $dateCollection ); $i++ ) {
//            
//        }
//        
//        echo "$startDate to $endDate<br /><br />";
//        echo '<pre>';
//        print_r( $dateCollection );
//        echo '</pre>';
//        exit();
        
        call_user_func_array( array( __NAMESPACE__ ."\Papaa", "EmployeeData" ), array( '002', '   229589', '34100', 'IT', 'DV00X', '92510', '09252016', '2016', '09', '25', '42636' ) );
                
        for( $i = 1; $i <= count( $dateCollection ); $i++ ) {
            $date = new \DateTime( $dateCollection[$i-1]['date'] );
            $weekdayAbbr = strtoupper( $date->format( "D" ) );
            $dateFormat = $date->format( "mdY" );
           
            call_user_func_array( array( __NAMESPACE__ ."\Papaa", "Day$i" ), array(
                $weekdayAbbr, $dateFormat, $dateCollection[$i-1]['hours'], $dateCollection[$i-1]['type'], '0.00', ''
            ) );
        }
    }
    
    public function EmployeeData( $employerNumber, $employeeNumber, $level1, $level2, $level3, $level4,
        $weekEndingDate, $weekEndingYear, $weekEndingMonth, $weekEndingDay, $weekEndingHY ) {
        $this->collection['AAER'] = $employerNumber;
        $this->collection['AACLK#'] = $employeeNumber;
        $this->collection['AALVL1'] = $level1;
        $this->collection['AALVL2'] = $level2;
        $this->collection['AALVL3'] = $level3;
        $this->collection['AALVL4'] = $level4;
        $this->collection['AAWEND'] = $weekEndingDate;
        $this->collection['AAWEYR'] = $weekEndingYear;
        $this->collection['AAWEMO'] = $weekEndingMonth;
        $this->collection['AAWEDA'] = $weekEndingDay;
        $this->collection['AAWENDH'] = $weekEndingHY;
    }
    
    public function Day1( $weekdayAbbr, $dateFormat, $numHoursSplitA, $typeSplitA, $numHoursSplitB, $typeSplitB )
    {
        $this->collection['AAWK1DA1'] = $weekdayAbbr;
        $this->collection['AAWK1DT1'] = $dateFormat;
        $this->collection['AAWK1HR1A'] = $numHoursSplitA;
        $this->collection['AAWK1RC1A'] = $typeSplitA;
        $this->collection['AAWK1HR1B'] = $numHoursSplitB;
        $this->collection['AAWK1RC1B'] = $typeSplitB;
    }
    
    public function Day2( $weekdayAbbr, $dateFormat, $numHoursSplitA, $typeSplitA, $numHoursSplitB, $typeSplitB )
    {
        $this->collection['AAWK1DA2'] = $weekdayAbbr;
        $this->collection['AAWK1DT2'] = $dateFormat;
        $this->collection['AAWK1HR2A'] = $numHoursSplitA;
        $this->collection['AAWK1RC2A'] = $typeSplitA;
        $this->collection['AAWK1HR2B'] = $numHoursSplitB;
        $this->collection['AAWK1RC2B'] = $typeSplitB;
    }
    
    public function Day3( $weekdayAbbr, $dateFormat, $numHoursSplitA, $typeSplitA, $numHoursSplitB, $typeSplitB )
    {
        $this->collection['AAWK1DA3'] = $weekdayAbbr;
        $this->collection['AAWK1DT3'] = $dateFormat;
        $this->collection['AAWK1HR3A'] = $numHoursSplitA;
        $this->collection['AAWK1RC3A'] = $typeSplitA;
        $this->collection['AAWK1HR3B'] = $numHoursSplitB;
        $this->collection['AAWK1RC3B'] = $typeSplitB;
    }
    
    public function Day4( $weekdayAbbr, $dateFormat, $numHoursSplitA, $typeSplitA, $numHoursSplitB, $typeSplitB )
    {
        $this->collection['AAWK1DA4'] = $weekdayAbbr;
        $this->collection['AAWK1DT4'] = $dateFormat;
        $this->collection['AAWK1HR4A'] = $numHoursSplitA;
        $this->collection['AAWK1RC4A'] = $typeSplitA;
        $this->collection['AAWK1HR4B'] = $numHoursSplitB;
        $this->collection['AAWK1RC4B'] = $typeSplitB;
    }
    
    public function Day5( $weekdayAbbr, $dateFormat, $numHoursSplitA, $typeSplitA, $numHoursSplitB, $typeSplitB )
    {
        $this->collection['AAWK1DA5'] = $weekdayAbbr;
        $this->collection['AAWK1DT5'] = $dateFormat;
        $this->collection['AAWK1HR5A'] = $numHoursSplitA;
        $this->collection['AAWK1RC5A'] = $typeSplitA;
        $this->collection['AAWK1HR5B'] = $numHoursSplitB;
        $this->collection['AAWK1RC5B'] = $typeSplitB;
    }
    
    public function Day6( $weekdayAbbr, $dateFormat, $numHoursSplitA, $typeSplitA, $numHoursSplitB, $typeSplitB )
    {
        $this->collection['AAWK1DA6'] = $weekdayAbbr;
        $this->collection['AAWK1DT6'] = $dateFormat;
        $this->collection['AAWK1HR6A'] = $numHoursSplitA;
        $this->collection['AAWK1RC6A'] = $typeSplitA;
        $this->collection['AAWK1HR6B'] = $numHoursSplitB;
        $this->collection['AAWK1RC6B'] = $typeSplitB;
    }
    
    public function Day7( $weekdayAbbr, $dateFormat, $numHoursSplitA, $typeSplitA, $numHoursSplitB, $typeSplitB )
    {
        $this->collection['AAWK1DA7'] = $weekdayAbbr;
        $this->collection['AAWK1DT7'] = $dateFormat;
        $this->collection['AAWK1HR7A'] = $numHoursSplitA;
        $this->collection['AAWK1RC7A'] = $typeSplitA;
        $this->collection['AAWK1HR7B'] = $numHoursSplitB;
        $this->collection['AAWK1RC7B'] = $typeSplitB;
    }
    
    public function Day8( $weekdayAbbr, $dateFormat, $numHoursSplitA, $typeSplitA, $numHoursSplitB, $typeSplitB )
    {
        $this->collection['AAWK2DA1'] = $weekdayAbbr;
        $this->collection['AAWK2DT1'] = $dateFormat;
        $this->collection['AAWK2HR1A'] = $numHoursSplitA;
        $this->collection['AAWK2RC1A'] = $typeSplitA;
        $this->collection['AAWK2HR1B'] = $numHoursSplitB;
        $this->collection['AAWK2RC1B'] = $typeSplitB;
    }
    
    public function Day9( $weekdayAbbr, $dateFormat, $numHoursSplitA, $typeSplitA, $numHoursSplitB, $typeSplitB )
    {
        $this->collection['AAWK2DA2'] = $weekdayAbbr;
        $this->collection['AAWK2DT2'] = $dateFormat;
        $this->collection['AAWK2HR2A'] = $numHoursSplitA;
        $this->collection['AAWK2RC2A'] = $typeSplitA;
        $this->collection['AAWK2HR2B'] = $numHoursSplitB;
        $this->collection['AAWK2RC2B'] = $typeSplitB;
    }
    
    public function Day10( $weekdayAbbr, $dateFormat, $numHoursSplitA, $typeSplitA, $numHoursSplitB, $typeSplitB )
    {
        $this->collection['AAWK2DA3'] = $weekdayAbbr;
        $this->collection['AAWK2DT3'] = $dateFormat;
        $this->collection['AAWK2HR3A'] = $numHoursSplitA;
        $this->collection['AAWK2RC3A'] = $typeSplitA;
        $this->collection['AAWK2HR3B'] = $numHoursSplitB;
        $this->collection['AAWK2RC3B'] = $typeSplitB;
    }
    
    public function Day11( $weekdayAbbr, $dateFormat, $numHoursSplitA, $typeSplitA, $numHoursSplitB, $typeSplitB )
    {
        $this->collection['AAWK2DA4'] = $weekdayAbbr;
        $this->collection['AAWK2DT4'] = $dateFormat;
        $this->collection['AAWK2HR4A'] = $numHoursSplitA;
        $this->collection['AAWK2RC4A'] = $typeSplitA;
        $this->collection['AAWK2HR4B'] = $numHoursSplitB;
        $this->collection['AAWK2RC4B'] = $typeSplitB;
    }
    
    public function Day12( $weekdayAbbr, $dateFormat, $numHoursSplitA, $typeSplitA, $numHoursSplitB, $typeSplitB )
    {
        $this->collection['AAWK2DA5'] = $weekdayAbbr;
        $this->collection['AAWK2DT5'] = $dateFormat;
        $this->collection['AAWK2HR5A'] = $numHoursSplitA;
        $this->collection['AAWK2RC5A'] = $typeSplitA;
        $this->collection['AAWK2HR5B'] = $numHoursSplitB;
        $this->collection['AAWK2RC5B'] = $typeSplitB;
    }
    
    public function Day13( $weekdayAbbr, $dateFormat, $numHoursSplitA, $typeSplitA, $numHoursSplitB, $typeSplitB )
    {
        $this->collection['AAWK2DA6'] = $weekdayAbbr;
        $this->collection['AAWK2DT6'] = $dateFormat;
        $this->collection['AAWK2HR6A'] = $numHoursSplitA;
        $this->collection['AAWK2RC6A'] = $typeSplitA;
        $this->collection['AAWK2HR6B'] = $numHoursSplitB;
        $this->collection['AAWK2RC6B'] = $typeSplitB;
    }
    
    public function Day14( $weekdayAbbr, $dateFormat, $numHoursSplitA, $typeSplitA, $numHoursSplitB, $typeSplitB )
    {
        $this->collection['AAWK2DA7'] = $weekdayAbbr;
        $this->collection['AAWK2DT7'] = $dateFormat;
        $this->collection['AAWK2HR7A'] = $numHoursSplitA;
        $this->collection['AAWK2RC7A'] = $typeSplitA;
        $this->collection['AAWK2HR7B'] = $numHoursSplitB;
        $this->collection['AAWK2RC7B'] = $typeSplitB;
    }
    
}
