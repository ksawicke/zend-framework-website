<?php

namespace Request\Helper;

/**
 * Description of PapaaHelper
 *
 * @author sawik
 */
class PapaaHelper {
    
    public function __construct()
    {
        //echo "requestController<br />";
    }
    
    public static function build()
    {
        $request = ['id' => '123495',
                    'for' => ['employee_number' => '49499', 'level1' => '1234', 'level2' => '2424', 'level3' => '3224', 'level4' => '3434'],
                    'dates' => [ ['hours' => '8.00', 'type' => 'P', 'date' => '2016-02-14'], /** Record 1 (14 days) **/
                                 ['hours' => '8.00', 'type' => 'P', 'date' => '2016-02-13'],
                                 ['hours' => '8.00', 'type' => 'P', 'date' => '2016-02-03'],
                                 ['hours' => '8.00', 'type' => 'P', 'date' => '2016-02-04'],
                                 ['hours' => '8.00', 'type' => 'K', 'date' => '2016-02-05'],
                                 ['hours' => '8.00', 'type' => 'P', 'date' => '2016-02-06'],
                                 ['hours' => '8.00', 'type' => 'P', 'date' => '2016-02-07'],
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
		
        $request = self::sortDates( $request );
        $requestBlocks = self::getRequestBlocks( $request );

        return $requestBlocks;
    }
    
    public static function getPapaaVars()
    {
        $PapaaObject = new \Request\Model\PapaaObject();
        $class_vars = get_class_vars( get_class( $PapaaObject ) );
        
        foreach( $class_vars as $name => $value ) {
            echo "$name: $value<br />";
        }
    }
    
    public static function papaa()
    {
        $timeOff = [ [ 0 => '2015-11-19',
                         1 => '10.00',
                         2 => 'P'
                     ],
                     [ 0 => '2015-11-20',
                         1 => '10.00',
                         2 => 'P'
                     ],
                     [ 0 => '2015-12-07',
                         1 => '10.00',
                         2 => 'P'
                     ],
                     [ 0 => '2015-12-08',
                         1 => '10.00',
                         2 => 'P'
                     ],
                     [ 0 => '2015-12-14',
                         1 => '10.00',
                         2 => 'P'
                     ],
                     [ 0 => '2015-12-15',
                         1 => '10.00',
                         2 => 'P'
                     ],
                     [ 0 => '2015-12-16',
                         1 => '10.00',
                         2 => 'P'
                     ]
        ];
        
        $timeOffTmp = self::_loadEntriesByDate( $timeOff );
        
        $iMax = count( $timeOffTmp );

        $employer = '002';
        $employeeId = '49499';
        $level1 = 'ASDF';
        $level2 = 'BCNV';
        $level3 = 'FHFH';
        $level4 = 'DDSA';
        $reason = 'Go to Hawaii';
        
        if ( $iMax > 0 ) {
            //Setup array with header info
            $requestInfo = [
                "employer" => $employer,
                "employeeId" => $employeeId,
                "level1" => $level1,
                "level2" => $level2,
                "level3" => $level3,
                "level4" => $level4,
                "reason" => $reason ];

            self::_loadFortnight( $timeOffTmp, $requestInfo );
        } else {
            echo "No entries found";
        }
        
        echo '<pre>timeOff';
        print_r( $timeOff );
        echo '</pre>';
        
        echo '<pre>timeOffTmp';
        print_r( $timeOffTmp );
        echo '</pre>';
        
        die("STOP");
    }
    
    private static function _loadEntriesByDate( $timeOffEntries ) {
        $timeOffTmp = [];
        $holdDate = "";
        $i = 0;

        foreach ( $timeOffEntries as $entry) {
            //Is the date same as last?
            if ( $entry[ 0 ] == $holdDate ) {
                //Same as previous value add hour/code to 2nd bucket
                $timeOffTmp[ $i - 1 ][ 3 ] = $entry[ 1 ];
                $timeOffTmp[ $i - 1 ][ 4 ] = $entry[ 2 ];
            } else {
                //Setup record
                $timeOffTmp[] = [ $entry[ 0 ] , $entry[ 1 ], $entry[ 2 ], "0", "" ];

                //Set hold value and increment array index
                $holdDate = $entry[ 0 ];
                $i++;
            }
        }

        return $timeOffTmp;
    }
    
    private static function _loadFortnight( $timeOffTmp , $requestInfo ) {
        $iMax = count($timeOffTmp)-1;
        $firstDate = $timeOffTmp[ 0 ][ 0 ];
        $lastDate = $timeOffTmp[ $iMax ][ 0 ];
        $oneDay = new \DateInterval("P1D");
        $i = 0;

        //Get the first date to process
        echo "<pre>";
        print_r( $timeOffTmp );
        echo "</pre>";
        echo "<br>firstDate: $firstDate";
        $endDate = $timeOffTmp[ count($timeOffTmp)-1 ][ 0 ];
        echo "<br>endDate: $endDate";
        //exit;

        //Get the date for the time entry being processed
        $entryDate = new \DateTime( $firstDate );
        $endDate = new \DateTime( $lastDate );

        //Continue until we have exhausted all the dates requested
        do {

            //Setup structure to mimic the structure of the file we are writing to
            $tmpRecord = [];

            //Get the date for the time entry being processed
            $entryDate = new \DateTime( $timeOffTmp[ $i ][ 0 ] );

            //Get the current time off date
            $tmpDate = clone $entryDate;

            //Need to build exactly 14 days for the record
            for ($j = 0; $j < 14; $j++) {

                $tmpRecord[] = [ strtoupper($entryDate->format( "D" )), $entryDate->format( "mdY") ];

                //Is there an entry for this date?
                if ( $entryDate == $tmpDate ) {
                    $tmpRecord[ $j ][ 2 ] = $timeOffTmp[ $i ][ 1 ];
                    $tmpRecord[ $j ][ 3 ] = $timeOffTmp[ $i ][ 2 ];
                    $tmpRecord[ $j ][ 4 ] = $timeOffTmp[ $i ][ 3 ];
                    $tmpRecord[ $j ][ 5 ] = $timeOffTmp[ $i ][ 4 ];

                    //Increment the request entry index if not at max size
                    if ($i < $iMax) {
                        //Get the current time off date
                        $tmpDate = new \DateTime( $timeOffTmp[ ++$i ][ 0 ] );
                    }

                } else {
                    //No entry for this date so build an empty one

                    $tmpRecord[ $j ][ 2 ] = "";
                    $tmpRecord[ $j ][ 3 ] = "";
                    $tmpRecord[ $j ][ 4 ] = "";
                    $tmpRecord[ $j ][ 5 ] = "";
                }

                //Increment date
                $entryDate->add( $oneDay );

            }
            //Now the 14 day record has been built; write it to file

            //Get the last time off date
            $lastDate = clone $entryDate;

            //subtract one to get back to last date
            $lastDate->sub( $oneDay );

            //Convert to hundred year date
            $dateHYD = (string) self::_convertToHYD( $lastDate->format( "m/d/Y" ) );

            $record = new TimeoffTmp(
                ["AAER" => $requestInfo[ "employer" ],
                 "AACLK#" => $requestInfo[ "employeeId" ],
                 "AALVL1" => $requestInfo[ "level1" ],
                 "AALVL2" => $requestInfo[ "level2" ],
                 "AALVL3" => $requestInfo[ "level3" ],
                 "AALVL4" => $requestInfo[ "level4" ],
                 "AAWEND" => $lastDate->format( "mdY" ),
                 "AAWEYR" => $lastDate->format( "Y" ),
                 "AAWEMO" => $lastDate->format( "m" ),
                 "AAWEDA" => $lastDate->format( "d" ),
                 "AAWENDH" => $dateHYD,
                 "AACOMM" => $requestInfo[ "reason" ] ]);

            //Load up the hours and insert
            self::_loadTimeoffFields( $record, $tmpRecord );
            //$record->insert();
            $record->altInsert();

        } while ( $endDate > $entryDate );

    }
    
    private static function _loadTimeoffFields( $timeOffRecord , $timeOffEntries ){
        $i = 0;

        //Populate timeoff structure in the record for each of the 14 days
        for ($week = 1; $week < 3 ; $week++) {
            for ($day = 1; $day < 8; $day++) {

                //Load the instance values
                $timeOffRecord->setValue( "AAWK{$week}DA{$day}", $timeOffEntries[ $i ][ 0 ] );
                $timeOffRecord->setValue( "AAWK{$week}DT{$day}", $timeOffEntries[ $i ][ 1 ] );
                $timeOffRecord->setValue( "AAWK{$week}HR{$day}A", $timeOffEntries[ $i ][ 2 ] );
                $timeOffRecord->setValue( "AAWK{$week}RC{$day}A", $timeOffEntries[ $i ][ 3 ] );
                $timeOffRecord->setValue( "AAWK{$week}HR{$day}B", $timeOffEntries[ $i ][ 4 ] );
                $timeOffRecord->setValue( "AAWK{$week}RC{$day}B", $timeOffEntries[ $i ][ 5 ] );

                //Increment counter
                $i++;
            }
        }

        echo '<hr><pre>';
        print_r( $timeOffRecord );
        echo '</pre>';
    }
    
    //Convert date from normal date format to 100 year date format
    private static function _convertToHYD( $dateString ) {
        $inputDate = new \DateTime( $dateString );
        $year = (int)$inputDate->format( "Y" );
        $month = (int)$inputDate->format( "m" );
        $day = (int)$inputDate->format( "d" );

        $julianDate = gregoriantojd( $month, $day, $year);
        $startDate = gregoriantojd( 1, 1, 1900 );
        return ( $julianDate - $startDate );
    }
	
    private static function getRequestBlocks( $request )
    {
        $requestBlocks = [];
        $numDaysRequested = count( $request['dates'] );
        $requestSpan = 0;
        $recordCounter = 0;
        $key = 0;

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

        return $requestBlocks;
    }

    private static function convertIntToWkDy( $int = 0 )
    {
        $wk = 1;
        for( $ctr = 1; $ctr >= $int; $ctr++ ) {
            if( $int%7===0 ) {
                $wk++;
            }
        }
        return $wk;
    }

    private static function buildPapaaSQL( $requestBlock )
    {
//        echo self::convertIntToWkDy( 1 ) . '<br />';
//        echo self::convertIntToWkDy( 2 ) . '<br />';
//        echo self::convertIntToWkDy( 3 ) . '<br />';
//        echo self::convertIntToWkDy( 4 ) . '<br />';
//        echo self::convertIntToWkDy( 5 ) . '<br />';
//        echo self::convertIntToWkDy( 6 ) . '<br />';
//        echo self::convertIntToWkDy( 7 ) . '<br />';
//        echo self::convertIntToWkDy( 8 ) . '<br />';
//        echo self::convertIntToWkDy( 9 ) . '<br />';
//        echo self::convertIntToWkDy( 10 ) . '<br />';
//        echo self::convertIntToWkDy( 11 ) . '<br />';
//        echo self::convertIntToWkDy( 12 ) . '<br />';
//        echo self::convertIntToWkDy( 13 ) . '<br />';
//        echo self::convertIntToWkDy( 14 ) . '<br />';
////        die( "STOP" );

//        echo '<pre>!!!';
//        print_r( $requestBlock );
//        echo '</pre>';
//        
//        echo count( $requestBlock );
        
        $sql = [];
        for( $ctr = 1; $ctr <= count( $requestBlock ) - 1; $ctr++ ) {
            foreach($requestBlock[$ctr-1] as $key => $data) {
                if( !array_key_exists( $ctr, $sql ) ) {
                    $week = 1;
                    $day = 1;
                    $sql[ $ctr ]['AAWK' . $week . 'DA' . $day] = 'start';
//                    $week++;
                    $day++;
                } else {
                    $sql[ $ctr ]['AAWK' . $week . 'DA' . $day] = 'start';
//                    $week++;
                    $day++;
                }
            }
        }
        
//        foreach( $requestBlock as $counter => $data ) {
//            $sql[ $counter ]['AAWKxDAy'] = $requestBlock[ $counter ]['date'];
//            $sql[ $counter ]['AAWKxHRyA'] = $data['type'];
//        }

        echo '<pre>';
        print_r( $requestBlock );
        echo '</pre>';

        echo '<pre>';
        print_r( $sql );
        echo '</pre>';

        die();
    }

    private static function sortDates( $request )
    {
        usort($request['dates'], [__CLASS__, 'sortDatesAscending']);
        return $request;
    }

    public static function sortDatesDescending( $a, $b )
    {
        $t1 = strtotime($a['date']);
        $t2 = strtotime($b['date']);
        return $t2 - $t1;
    }

    public static function sortDatesAscending( $a, $b )
    {
        $t1 = strtotime($a['date']);
        $t2 = strtotime($b['date']);
        return $t1 - $t2;
    }
	
    /** Guido's cool date thing **/
    protected function buildDateMatrix( $data, $dateFrom, $dateTo )
    {
        $dataMatrix = [];

        $begin = new \DateTime( date('Y-m-d', $dateFrom));
        $end = new \DateTime( date('Y-m-d', $dateTo));

        $interval = \DateInterval::createFromDateString('1 day');
        $period = new \DatePeriod($begin, $interval, $end);

        //var_dump($data);
        foreach ($data as $hotelData) {
            $checkIn = new \DateTime( date('Y-m-d', strtotime($hotelData['GUEST_CHECK_IN']) ));
            $checkOut = new \DateTime( date('Y-m-d', strtotime($hotelData['GUEST_CHECK_OUT']) ));
            $currentDay = 0;
            foreach ( $period as $dt ) {
//                var_dump($dt->getTimestamp());
                if ($dt >= $checkIn and ($dt <= $checkOut || $hotelData['GUEST_CHECK_OUT'] == '0001-01-01-00.00.00.000000')) {
                    $dataMatrix[$hotelData['IDENTITY_ID']][$dt->getTimestamp()] = $hotelData['GUEST_NUMBER_OF_GUESTS'];
                }
                $currentDay++;
            }
        }
        var_dump($dataMatrix);

        return $dataMatrix;
    }
    
}
