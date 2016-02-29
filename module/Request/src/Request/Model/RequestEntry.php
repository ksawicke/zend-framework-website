<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Request\Model;

use Zend\Db\Sql\Delete;
use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Update;
use Zend\Db\Sql\Expression;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\ResultSet\ResultSet;
use Request\Model\BaseDB;

/**
 * Description of RequestEntry
 *
 * @author sawik
 */
class RequestEntry extends BaseDB {
    
    public function __construct()
    {
        parent::__construct();
    }
    
    public function getRequestObject( $requestId )
    {
        $request = [ 'id' => $requestId, 'reason' => '', 'for' => [], 'dates' => [] ];
        
        $sql = new Sql( $this->adapter );
        $select = $sql->select(['entry' => 'TIMEOFF_REQUEST_ENTRIES'])
                ->columns(['REQUEST_ID' => 'REQUEST_ID', 'REQUESTED_HOURS' => 'REQUESTED_HOURS', 'REQUEST_DATE' => 'REQUEST_DATE', 'REQUEST_CODE' => 'REQUEST_CODE' ])
                ->join(['request' => 'TIMEOFF_REQUESTS'], 'request.REQUEST_ID = entry.REQUEST_ID',
                       ['EMPLOYEE_NUMBER' => 'EMPLOYEE_NUMBER', 'REQUEST_REASON' => 'REQUEST_REASON'])
                ->where(['request.REQUEST_ID' => $requestId])
                ->order(['entry.REQUEST_DATE ASC']);
        $requestEntries = \Request\Helper\ResultSetOutput::getResultArray($sql, $select);
        
        foreach( $requestEntries as $ctr => $entry ) {
            $request['dates'][] = [ 'hours' => $entry['REQUESTED_HOURS'], 'type' => $entry['REQUEST_CODE'], 'date' => $entry['REQUEST_DATE'] ];
        }
        
        $request['reason'] = trim( $requestEntries[0]['REQUEST_REASON'] );
        $request['for']['employee_number'] = trim( $requestEntries[0]['EMPLOYEE_NUMBER'] );
        
        $request = $this->sortRequestDates( $request );        
        $requestObject = $this->buildDateMatrix( $request );
        
        return $requestObject;
    }
    
//    private function parseRequestBlocks( $request )
//    {
//        $requestBlocks = [];
//        $numDaysRequested = count( $request['dates'] );
//        $requestSpan = 0;
//        $recordCounter = 0;
//        $key = 0;
//        
//        $startDate = $request['dates'][0]['date'];
//        
//        $startDateObject = new \DateTime( $startDate );
//        $endDate = $startDateObject->add(new \DateInterval('P13D'));
//        $endDate = $endDate->format( "Y-m-d" );
//        
//        echo "<pre>";
//        print_r( $request );
//        echo "</pre>";
////        exit();
////        
//        for( $i = 1; $i = 14; $i++ ) {
//            if( $i === 0 ) {
//                $requestBlocks[ $recordCounter ][ $key ] = $request['dates'][ $count - 1 ];
//            }
//        }
////        exit();
//        
//        for( $count = 1; $count <= $numDaysRequested; $count++ ) {
//            $requestBlocks[ $recordCounter ][ $key ] = $request['dates'][ $count - 1 ];
//
//            if( $count < $numDaysRequested ) {
//                    $thisDate = strtotime( $request['dates'][ $count - 1 ]['date'] );
//                    $nextDate = strtotime( $request['dates'][ $count ]['date'] );
//                    $dateDiff = $nextDate - $thisDate;
//                    $requestSpan = $requestSpan + $dateDiff;
//                    $daysFromLast = floor( $requestSpan / ( 60*60*24 ) );
//                    $key++;
//
//                    if( $daysFromLast >= 14 ) {
//                        $requestSpan = 0;
//                        $key = 0;
//                        $recordCounter++;
//                    }
//            }
//        }
//        
//        echo "<pre>";
//        print_r( $requestBlocks );
//        echo "</pre>";
//        exit();
//
//        return $requestBlocks;
//    }
    
    public function buildDateMatrix( $request ) {
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
        
        $data = [];
        
        foreach( $period as $chunk ) {
            // Check if any have hours, if so:
            $hoursTotal = 0;
            
            $childData = $this->buildChildDateMatrix( $request, $chunk );
            foreach( $childData as $cdKey => $cdValues ) {
                $hoursTotal += $cdValues['hours'];
            }
            if( $hoursTotal > 0 ) {
                $data[] = $childData;
            }
        }
        
        $request['dates'] = $data;
        
        return $request;
    }
    
    /**
     * Build guest count array matrix based on hotel data
     *
     * @param array $data
     * @param \DateTime $dateFrom
     * @param \DateTime $dateTo
     * @return array
     */
    public function buildChildDateMatrix( $request, $chunk )
    {
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
    
    private function sortRequestDates( $request )
    {
        usort($request['dates'], [__CLASS__, 'sortRequestDatesAscending']);
        return $request;
    }

    public function sortRequestDatesDescending( $a, $b )
    {
        $t1 = strtotime($a['date']);
        $t2 = strtotime($b['date']);
        return $t2 - $t1;
    }

    public function sortRequestDatesAscending( $a, $b )
    {
        $t1 = strtotime($a['date']);
        $t2 = strtotime($b['date']);
        return $t1 - $t2;
    }
    
}
