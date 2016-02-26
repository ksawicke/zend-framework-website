<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Request\Model;

/**
 * Description of RequestEntry
 *
 * @author sawik
 */
class RequestEntry {
    
    public static function getRequestBlocks( $requestId )
    {
        $request = ['id' => '123495',
                    'for' => ['employee_number' => '49499', 'level1' => '1234', 'level2' => '2424', 'level3' => '3224', 'level4' => '3434'],
                    'dates' => [ ['hours' => '8.00', 'type' => 'P', 'date' => '2016-02-14'], /** Record 1 (14 days) **/
                                 ['hours' => '8.00', 'type' => 'P', 'date' => '2016-02-13'],
//                                 ['hours' => '8.00', 'type' => 'P', 'date' => '2016-02-03'],
                                 ['hours' => '8.00', 'type' => 'P', 'date' => '2016-02-04'],
                                 ['hours' => '8.00', 'type' => 'K', 'date' => '2016-02-05'],
                                 ['hours' => '8.00', 'type' => 'P', 'date' => '2016-02-06'],
//                                 ['hours' => '8.00', 'type' => 'P', 'date' => '2016-02-07'],
                                 ['hours' => '8.00', 'type' => 'P', 'date' => '2016-02-08'],
                                 ['hours' => '8.00', 'type' => 'P', 'date' => '2016-02-09'],
                                 ['hours' => '8.00', 'type' => 'P', 'date' => '2016-02-10'],
                                 ['hours' => '8.00', 'type' => 'P', 'date' => '2016-02-11'],
                                 ['hours' => '8.00', 'type' => 'P', 'date' => '2016-02-12'],
                                 ['hours' => '8.00', 'type' => 'P', 'date' => '2016-02-02'],
//                                 ['hours' => '4.00', 'type' => 'P', 'date' => '2016-02-01'],
                                 ['hours' => '4.00', 'type' => 'S', 'date' => '2016-02-01'],
                                 ['hours' => '8.00', 'type' => 'P', 'date' => '2016-02-15'], /** Record 2 (6 days) **/
                                 ['hours' => '8.00', 'type' => 'P', 'date' => '2016-02-16'],
                                 ['hours' => '8.00', 'type' => 'P', 'date' => '2016-02-17'],
                                 ['hours' => '8.00', 'type' => 'P', 'date' => '2016-02-18'],
                                 ['hours' => '8.00', 'type' => 'P', 'date' => '2016-02-19'],
                                 ['hours' => '8.00', 'type' => 'P', 'date' => '2016-02-20'],
                                 ['hours' => '8.00', 'type' => 'P', 'date' => '2016-03-01'], /** Record 3 (3 days) **/
                                 ['hours' => '8.00', 'type' => 'P', 'date' => '2016-03-02'],
                                 ['hours' => '8.00', 'type' => 'P', 'date' => '2016-03-03'],
                                 ['hours' => '8.00', 'type' => 'P', 'date' => '2016-04-01'], /** Record 4 (3 days) **/
                                 ['hours' => '8.00', 'type' => 'P', 'date' => '2016-04-02'],
                                 ['hours' => '8.00', 'type' => 'P', 'date' => '2016-04-03'],
                                 ['hours' => '8.00', 'type' => 'P', 'date' => '2016-05-01'], /** Record 5 (1 day) **/
                                 ['hours' => '8.00', 'type' => 'P', 'date' => '2016-06-01']  /** Record 6 (1 day) **/
                              ]
               ];
		
        $request = self::sortRequestDates( $request );
        
//        $test = self::buildDateMatrix( $request );
//        
//        echo '<pre>';
//        print_r( $test );
//        echo '</pre>';
////        
//        die("STOP");
//        
//        $requestBlocks = self::parseRequestBlocks( $request );
        
        $requestBlocks = self::buildDateMatrix( $request );

        return $requestBlocks;
    }
    
    private static function parseRequestBlocks( $request )
    {
        $requestBlocks = [];
        $numDaysRequested = count( $request['dates'] );
        $requestSpan = 0;
        $recordCounter = 0;
        $key = 0;
        
        $startDate = $request['dates'][0]['date'];
        
        $startDateObject = new \DateTime( $startDate );
        $endDate = $startDateObject->add(new \DateInterval('P13D'));
        $endDate = $endDate->format( "Y-m-d" );
        
        echo "<pre>";
        print_r( $request );
        echo "</pre>";
//        exit();
//        
        for( $i = 1; $i = 14; $i++ ) {
            if( $i === 0 ) {
                $requestBlocks[ $recordCounter ][ $key ] = $request['dates'][ $count - 1 ];
            }
        }
//        exit();
        
        for( $count = 1; $count <= $numDaysRequested; $count++ ) {
            $requestBlocks[ $recordCounter ][ $key ] = $request['dates'][ $count - 1 ];

            if( $count < $numDaysRequested ) {
                    $thisDate = strtotime( $request['dates'][ $count - 1 ]['date'] );
                    $nextDate = strtotime( $request['dates'][ $count ]['date'] );
                    $dateDiff = $nextDate - $thisDate;
                    $requestSpan = $requestSpan + $dateDiff;
                    $daysFromLast = floor( $requestSpan / ( 60*60*24 ) );
                    $key++;

                    if( $daysFromLast >= 14 ) {
                        $requestSpan = 0;
                        $key = 0;
                        $recordCounter++;
                    }
            }
        }
        
        echo "<pre>";
        print_r( $requestBlocks );
        echo "</pre>";
        exit();

        return $requestBlocks;
    }
    
    public static function buildDateMatrix( $request ) {
        $startDate = $request['dates'][0]['date'];
        $startDateObject = new \DateTime( $startDate );
        $endDate = $request['dates'][count($request['dates'])-1]['date'];
        $endDateObject = new \DateTime( $endDate );
        
        /**
         * instantiate DateInterval object
         */
        $interval = \DateInterval::createFromDateString('14 days');

        /**
         * instantiate DatePeriod object
         */
        $period = new \DatePeriod( $startDateObject, $interval, $endDateObject );
        
        $someData = [];
        
        foreach( $period as $chunk ) {
            
            // Check if any have hours, if so:
            $hoursTotal = 0;
            
            $testData = self::buildDateMatrixSub( $request, $chunk );
            foreach( $testData as $tdKey => $tdValues ) {
                $hoursTotal += $tdValues['hours'];
            }
            if( $hoursTotal > 0 ) {
                $someData[] = $testData;
            }
        }
        
        return $someData;
    }
    
    /**
     * Build guest count array matrix based on hotel data
     *
     * @param array $data
     * @param \DateTime $dateFrom
     * @param \DateTime $dateTo
     * @return array
     */
    public static function buildDateMatrixSub( $request, $chunk )
    {
//        $startDate = $chunk;
        $startDateObject = $chunk;
        $endDateObject = clone $startDateObject;
        $endDateObject->add(new \DateInterval('P14D'));
        
        /**
         * pre define empty array
         */
        $dataMatrix = [];

        /**
         * instantiate DateInterval object
         */
        $interval = \DateInterval::createFromDateString('1 day');

        /**
         * instantiate DatePeriod object
         */
        $period = new \DatePeriod( $startDateObject, $interval, $endDateObject );

        /**
         * loop thru passed in data
         */
        foreach ( $request['dates'] as $requestData ) {
            /**
             * instantiate two DateTime objects from hoteldata
             */
            $offDate = new \DateTime( date('Y-m-d', strtotime( $requestData['date'] ) ));
            
            /**
             * initialize array key
             */
            $dayOfPeriod = 0;
            
            /**
             * loop thru date period
             */
            foreach ( $period as $dt ) {

                /**
                 * pre set array index in not yet set
                 */
                if ( !isset( $dataMatrix[$dayOfPeriod] ) ) {
                    // do a blank one
                    $dataMatrix[$dayOfPeriod] = [ 'hours' => '0.00', 'type' => '', 'date' => $dt->format('Y-m-d') ];
                }

                /**
                 * if day of date perios matches data
                 */
                if ( $dt == $offDate ) {
                    $dataMatrix[$dayOfPeriod] = $requestData;
                }

                /**
                 * increase array key counter
                 */
                $dayOfPeriod++;
            }
        }

        /**
         * return data matrix
         */
        return $dataMatrix;
    }
    
    private static function sortRequestDates( $request )
    {
        usort($request['dates'], [__CLASS__, 'sortRequestDatesAscending']);
        return $request;
    }

    public static function sortRequestDatesDescending( $a, $b )
    {
        $t1 = strtotime($a['date']);
        $t2 = strtotime($b['date']);
        return $t2 - $t1;
    }

    public static function sortRequestDatesAscending( $a, $b )
    {
        $t1 = strtotime($a['date']);
        $t2 = strtotime($b['date']);
        return $t1 - $t2;
    }
    
}
