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
use Zend\Db\Sql\Where;

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

    public function getRequestEntry( $entryId )
    {
        $sql = new Sql( $this->adapter );
        $select = $sql->select(['entry' => 'TIMEOFF_REQUEST_ENTRIES'])
            ->columns(['ENTRY_ID' => 'ENTRY_ID', 'REQUEST_ID' => 'REQUEST_ID',
                       'REQUESTED_HOURS' => 'REQUESTED_HOURS', 'REQUEST_DATE' => 'REQUEST_DATE',
                       'REQUEST_CODE' => 'REQUEST_CODE' ])
            ->where(['entry.ENTRY_ID' => $entryId]);
        $requestEntry = \Request\Helper\ResultSetOutput::getResultRecord($sql, $select);

        return $requestEntry;
    }

    public function getRequestObject( $requestId )
    {
        $request = [ 'id' => $requestId, 'reason' => '', 'for' => [], 'dates' => [] ];

        $sql = new Sql( $this->adapter );
        $select = $sql->select(['entry' => 'TIMEOFF_REQUEST_ENTRIES'])
                ->columns(['ENTRY_ID' => 'ENTRY_ID', 'REQUEST_ID' => 'REQUEST_ID', 'REQUESTED_HOURS' => 'REQUESTED_HOURS', 'REQUEST_DATE' => 'REQUEST_DATE', 'REQUEST_CODE' => 'REQUEST_CODE' ])
                ->join(['request' => 'TIMEOFF_REQUESTS'], 'request.REQUEST_ID = entry.REQUEST_ID',
                       ['EMPLOYEE_NUMBER' => 'EMPLOYEE_NUMBER', 'REQUEST_REASON' => 'REQUEST_REASON'])
                ->where(['request.REQUEST_ID' => $requestId, 'entry.IS_DELETED' => 0])
                ->order(['entry.REQUEST_DATE ASC']);
        $requestEntries = \Request\Helper\ResultSetOutput::getResultArray($sql, $select);

        foreach( $requestEntries as $ctr => $entry ) {
            $request['dates'][] = [ 'entry_id' => $entry['ENTRY_ID'], 'hours' => $entry['REQUESTED_HOURS'], 'type' => $entry['REQUEST_CODE'], 'date' => $entry['REQUEST_DATE'] ];
        }

        $request['reason'] = ( count( $requestEntries ) >= 0 ? trim( $requestEntries[0]['REQUEST_REASON'] ) : '' );
        $request['for']['employee_number'] = ( count( $requestEntries ) >= 0 ? trim( $requestEntries[0]['EMPLOYEE_NUMBER'] ) : '' );

        $request['dates'] = $this->multisort( $request['dates'], 'entry_id' );

        $requestObject = $this->buildDateMatrix( $request );

        return $requestObject;
    }

    /**
     * Bubble sort a 3-dimensional array
     *
     * @param unknown $array
     * @param unknown $key
     * @param string $sort_flags
     * @see http://stackoverflow.com/questions/13722865/sort-multidimensional-array-with-an-index
     */
    protected function multisort($array, $key, $sort_flags = SORT_REGULAR) {
        if (is_array($array) && count($array) > 0) {
            if (!empty($key)) {
                $mapping = array();
                foreach ($array as $k => $v) {
                    $sort_key = '';
                    if (!is_array($key)) {
                        $sort_key = $v[$key];
                    } else {
                        foreach ($key as $key_key) {
                            $sort_key .= $v[$key_key];
                        }
                        $sort_flags = SORT_STRING;
                    }
                    $mapping[$k] = $sort_key;
                }
                asort($mapping, $sort_flags);
                $sorted = array();
                foreach ($mapping as $k => $v) {
                    $sorted[] = $array[$k];
                }
                return $sorted;
            }
        }
        return $array;
    }

    public function buildDateMatrix( $request ) {
        if( count( $request['dates'] ) > 0 ) {
            $endCounter = ( (count($request['dates'])==1) ? 0 : (count($request['dates']) - 1) );
            $startDate = $request['dates'][0]['date'];
            $startDateObject = new \DateTime( $startDate );
            $endDate = $request['dates'][$endCounter]['date'];
            $endDateObject = new \DateTime( $endDate );

            /**
             * instantiate DateInterval object
             */
            $interval = \DateInterval::createFromDateString('14 days');

            /**
             * instantiate DatePeriod object
             */
            $period = new \DatePeriod( $startDateObject, $interval, $endDateObject->modify('+1 day') );

            $data = [];

            foreach( $period as $chunk ) {
                /**
                 * Check if any have hours, if so:
                 */
                $hoursTotal = 0;

                $childData = $this->buildChildDateMatrix( $request, $chunk );
                foreach( $childData as $childDataCounter => $childDataObject ) {
                    foreach( $childDataObject as $childDataObjectCounter => $cdata ) {
                        foreach( $cdata as $cdKey => $cdValues ) {
                            $hoursTotal += $cdValues['hours'];
                        }
                    }
                }
                if( $hoursTotal > 0 ) {
                    $data[] = $childData;
                }
            }
            $request['dates'] = $data;
        }

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

        $newDatesArray = [];
        $currentDate = '';
        foreach ( $request['dates'] as $requestDataObjectCounter => $requestData ) {
            if( !array_key_exists( $requestData['date'], $newDatesArray ) ) {
                $currentDate = $requestData['date'];
            }
            if( !array_key_exists( $currentDate, $newDatesArray ) ) {
                $counter = 0;
            } else {
                $counter = 1;
            }
            /**
             * Let's format our $requestData this way:
             * [ '2016-09-30' => [ 0 => [ 'hours' => 4.00, 'type' => 'P' ], '1' => [ 'hours' => 8.00, 'type' => 'K' ] ],
             *   '2016-10-01' => [ 0 => [ 'hours' => 4.00, 'type' => 'P' ], '1' => [ 'hours' => 8.00, 'type' => 'K' ] ]
             * ]
             */
            $thisDateObject = new \DateTime( $currentDate );
            $newDatesArray[$currentDate][$counter] = [ 'entry_id' => $requestData['entry_id'], 'hours' => $requestData['hours'], 'type' => $requestData['type'],
                'dow' => strtoupper( $thisDateObject->format( "D" ) ),
                'mdY' => strtoupper( $thisDateObject->format( "mdY" ) )
            ];
        }

        foreach ( $newDatesArray as $requestDataDate => $requestData ) {
            /**
             * instantiate two DateTime objects
             */
            $offDate = new \DateTime( date('Y-m-d', strtotime( $requestDataDate ) ));

            /**
             * initialize array key
             */
            $dayOfPeriod = 0;

            /**
             * loop thru date period
             */
            foreach ( $period as $dt ) {

                $currentDate = $dt->format('Y-m-d');
                $thisDateObject = new \DateTime( $currentDate );

                /**
                 * pre set array index in not yet set
                 */
                if ( !isset( $dataMatrix[$dayOfPeriod] ) ) {
                    /**
                     * Does an entry exist for $newDatesArray[$currentDate]?
                     */
                    if( array_key_exists( $currentDate, $newDatesArray ) ) {
                        if( count( $newDatesArray[$currentDate] )==1 ) {
                            $newDatesArray[$currentDate][1] = [ 'entry_id' => null, 'hours' => '0.00', 'type' => '', 'dow' => strtoupper( $thisDateObject->format( "D" ) ), 'mdY' => strtoupper( $thisDateObject->format( "mdY" ) ) ];
                        } else {
                            $newDatesArray[$currentDate][0]['dow'] = strtoupper( $dt->format( "D" ) );
                            $newDatesArray[$currentDate][0]['mdY'] = strtoupper( $dt->format( "mdY" ) );
                            $newDatesArray[$currentDate][1]['dow'] = strtoupper( $dt->format( "D" ) );
                            $newDatesArray[$currentDate][1]['mdY'] = strtoupper( $dt->format( "mdY" ) );
                        }
                        $dataMatrix[$dayOfPeriod][$currentDate] = $newDatesArray[$currentDate];
                    } else {
                        $dataMatrix[$dayOfPeriod][$currentDate] = [ 0 => [ 'entry_id' => null, 'hours' => '0.00', 'type' => '', 'dow' => strtoupper( $dt->format( "D" ) ), 'mdY' => strtoupper( $dt->format( "mdY" ) ) ],
                                                                    1 => [ 'entry_id' => null, 'hours' => '0.00', 'type' => '', 'dow' => strtoupper( $dt->format( "D" ) ), 'mdY' => strtoupper( $dt->format( "mdY" ) ) ] ];
                    }
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

    private function sortRequestDates( $dates )
    {
        usort($dates, [__CLASS__, 'sortRequestDatesAscending']);
        return $dates;
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

    public function setRequestsToCompleted()
    {
        $sql = new Sql($this->adapter);

        /* sub select for PAPAATMP */
        $subSelectPapaatmp = $sql->select();
        $subSelectPapaatmp->from('PAPAATMP');
        $subSelectPapaatmp->columns([new Expression('COUNT(*) AS RCOUNT')]);
        $subSelectPapaatmp->where('PAPAATMP.TIMEOFF_REQUEST_ID = TIMEOFF_REQUESTS.REQUEST_ID');

        /* sub select for HPAPAATMP */
        $subSelectHPapaatmp = $sql->select();
        $subSelectHPapaatmp->from('HPAPAATMP');
        $subSelectHPapaatmp->columns([new Expression('COUNT(*) AS RCOUNT')]);
        $subSelectHPapaatmp->where('HPAPAATMP.TIMEOFF_REQUEST_ID = TIMEOFF_REQUESTS.REQUEST_ID');

        /* define select for TIMEOFF_REQUESTS */
        $select = $sql->select();
        $select->from('TIMEOFF_REQUESTS');
        $select->columns(['REQUEST_ID']);

        $where = new Where();

        $where->equalTo('REQUEST_STATUS', 'S')
              ->and->expression('?', new \Zend\Db\Sql\Predicate\Expression('(' . $subSelectPapaatmp->getSqlString($this->adapter->platform) . ') = 0'))
              ->and->expression('?', new \Zend\Db\Sql\Predicate\Expression('(' . $subSelectHPapaatmp->getSqlString($this->adapter->platform) . ') = 0'));

        $select->where($where);

        $statement = $sql->prepareStatementForSqlObject($select);

        $result = $statement->execute();

        $selectedRecords = [];
        if ($result instanceof ResultInterface && $result->isQueryResult()) {
            $resultSet = new ResultSet();
            $resultSet->initialize($result);
            $selectedRecords = $resultSet->toArray();
        }

        if (count($selectedRecords) == 0) {
            return;
        }
        /* define the update */
        $update = $sql->update();

        $update->table('TIMEOFF_REQUESTS');

        $update->set(['REQUEST_STATUS' => 'F']);

        $update->where($where);

        $statement = $sql->prepareStatementForSqlObject($update);

        $result = $statement->execute();

        /* write log entries */
        $timeOffRequestLog = new TimeoffRequestLog();
        foreach ($selectedRecords as $record) {
            $timeOffRequestLog->logEntry($record['REQUEST_ID'], null, 'Status changed to Completed PAFs');
        }

    }

}
