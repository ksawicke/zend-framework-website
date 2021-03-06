<?php

/*
 * Output Papaa data object
 */

namespace Request\Model;

use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Expression;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\ResultSet\ResultSet;
use Request\Model\BaseDB;
use Application\Factory\Logger;

/**
 * Build and save bjects for tables: PAPAATMP, HPAPAATMP
 *
 * @author sawik
 */
class Papaatmp extends BaseDB {

    public $collection;
    public $table;

    public function __construct()
    {
        parent::__construct();
        $this->collection = [];
        $this->table = "PAPAATMP";
        $this->request_id = null;
    }

    /**
     * Prepare the data to write records to HPAPAATMP/PAPAATMP.
     *
     * @param array $employeeData
     * @param array $dateRequestBlocks
     */
    public function prepareToWritePapaatmpRecords( $employeeData, $dateRequestBlocks, $request_id )
    {
        $this->request_id = $request_id;
        $dateRequestBlocks['for']['employer_number'] = $employeeData['EMPLOYER_NUMBER'];
        $dateRequestBlocks['for']['level1'] = $employeeData['LEVEL_1'];
        $dateRequestBlocks['for']['level2'] = $employeeData['LEVEL_2'];
        $dateRequestBlocks['for']['level3'] = $employeeData['LEVEL_3'];
        $dateRequestBlocks['for']['level4'] = $employeeData['LEVEL_4'];
        $dateRequestBlocks['for']['salary_type'] = $employeeData['SALARY_TYPE'];

        foreach ( $dateRequestBlocks['dates'] as $ctr => $dateCollection ) {
            $this->SaveDates( $dateRequestBlocks['for'], $dateRequestBlocks['reason'], $dateCollection, $request_id );
        }
    }

    /**
     * Build the HPAPAATMP/PAPAATMP object.
     *
     * @param type $employeeData
     * @param type $reason
     * @param type $dateCollection
     */
    public function SaveDates( $employeeData = [], $reason = '', $dateCollection = [], $request_id = null )
    {
        $this->table = ( ( $employeeData['salary_type']==="H" ? "HPAPAATMP" : "PAPAATMP" ) );

        call_user_func_array( [ __NAMESPACE__ ."\Papaatmp", "EmployeeData" ], [ $employeeData ] );
        call_user_func_array( [ __NAMESPACE__ ."\Papaatmp", "WeekEndingData" ], [ $dateCollection ] );

        for( $i = 1; $i <= count( $dateCollection ) ; $i++ ) {
            foreach( $dateCollection[$i-1] as $date => $data ) {
                call_user_func_array(
                    [ __NAMESPACE__ . "\Papaatmp", "Day$i" ],
                    [ $data[0]['dow'], $data[0]['mdY'], $data[0]['hours'], $data[0]['type'], $data[1]['hours'], $data[1]['type'] ]
                );
            }
        }

        call_user_func_array( [ __NAMESPACE__ ."\Papaatmp", "Reason" ], [ $reason ] );
        call_user_func_array( [ __NAMESPACE__ ."\Papaatmp", "RequestId" ], [ $request_id ] );

        $this->insertPapaatmpRecord();
    }

    /**
     * Write the HPAPAATMP/PAPAATMP object to the appropriate table.
     *
     * @throws \Exception
     */
    protected function insertPapaatmpRecord()
    {
        $logger = new Logger();

        try {
            $action = new Insert( $this->table );
            $action->values( $this->collection );
            $sql = new Sql( $this->adapter );
            $rawSql = $sql->getSqlStringForSqlObject( $action );

            $logger->logEntry( "Attempted to write entry to table " . $this->table . " for Request ID " . $this->request_id . ": " . $rawSql );

            \Request\Helper\ResultSetOutput::executeRawSql( $this->adapter, $rawSql );
        } catch ( \Exception $e ) {
            $logger->logEntry( __CLASS__ .'->'.__FUNCTION__.' ERROR: [LINE: ' . $e->getLine() . '] ' . "Can't execute statement: " . $e->getMessage() );
        }
    }

    /**
     * Append employee data to the PAPAA object.
     *
     * @param type $employeeData
     */
    public function EmployeeData( $employeeData = [] )
    {
        $this->collection['AAER'] = $employeeData['employer_number'];
        $this->collection['AACLK#'] = \Request\Helper\Format::rightPadEmployeeNumber( $employeeData['employee_number'] );
        $this->collection['AALVL1'] = $employeeData['level1'];
        $this->collection['AALVL2'] = $employeeData['level2'];
        $this->collection['AALVL3'] = $employeeData['level3'];
        $this->collection['AALVL4'] = $employeeData['level4'];
    }

    /**
     * Append week ending data to the PAPAA object.
     *
     * @param type $dateCollection
     */
    public function WeekEndingData( $dateCollection = [] )
    {
        $Date = new \Request\Helper\Date();

        $lastDate = '';
        foreach( $dateCollection[ count( $dateCollection ) - 1 ] as $objectDate => $objectData ) {
            $lastDate = $objectDate;
        }

        $dateEnding  = new \DateTime( $lastDate );
        $weekEndingHY = $Date->convertToHYD( $lastDate );

        $this->collection['AAWEND'] = $dateEnding->format( "mdY" );
        $this->collection['AAWEYR'] = $dateEnding->format( "Y" );
        $this->collection['AAWEMO'] =  $dateEnding->format( "m" );
        $this->collection['AAWEDA'] = $dateEnding->format( "d" );
        $this->collection['AAWENDH'] = $weekEndingHY;
    }

    /**
     * Append Day 1 to the PAPAA object.
     *
     * @param string $weekdayAbbr       Abbreviation of day of week (three characters)
     * @param string $dateFormat        Date formated as YYYY-MM-DD
     * @param decimal $numHoursSplitA   Hours requested with 2 decimals
     * @param string $typeSplitA        Abbreviation of request type (one character)
     * @param type $numHoursSplitB      Hours requested with 2 decimals
     * @param type $typeSplitB          Abbreviation of request type (one character)
     */
    public function Day1( $weekdayAbbr, $dateFormat, $numHoursSplitA, $typeSplitA, $numHoursSplitB, $typeSplitB )
    {
        $this->collection['AAWK1DA1'] = $weekdayAbbr;
        $this->collection['AAWK1DT1'] = $dateFormat;
        $this->collection['AAWK1HR1A'] = $numHoursSplitA;
        $this->collection['AAWK1RC1A'] = $typeSplitA;
        $this->collection['AAWK1HR1B'] = $numHoursSplitB;
        $this->collection['AAWK1RC1B'] = $typeSplitB;
    }

    /**
     * Append Day 2 to the PAPAA object.
     *
     * @param string $weekdayAbbr       Abbreviation of day of week (three characters)
     * @param string $dateFormat        Date formated as YYYY-MM-DD
     * @param decimal $numHoursSplitA   Hours requested with 2 decimals
     * @param string $typeSplitA        Abbreviation of request type (one character)
     * @param type $numHoursSplitB      Hours requested with 2 decimals
     * @param type $typeSplitB          Abbreviation of request type (one character)
     */
    public function Day2( $weekdayAbbr, $dateFormat, $numHoursSplitA, $typeSplitA, $numHoursSplitB, $typeSplitB )
    {
        $this->collection['AAWK1DA2'] = $weekdayAbbr;
        $this->collection['AAWK1DT2'] = $dateFormat;
        $this->collection['AAWK1HR2A'] = $numHoursSplitA;
        $this->collection['AAWK1RC2A'] = $typeSplitA;
        $this->collection['AAWK1HR2B'] = $numHoursSplitB;
        $this->collection['AAWK1RC2B'] = $typeSplitB;
    }

    /**
     * Append Day 3 to the PAPAA object.
     *
     * @param string $weekdayAbbr       Abbreviation of day of week (three characters)
     * @param string $dateFormat        Date formated as YYYY-MM-DD
     * @param decimal $numHoursSplitA   Hours requested with 2 decimals
     * @param string $typeSplitA        Abbreviation of request type (one character)
     * @param type $numHoursSplitB      Hours requested with 2 decimals
     * @param type $typeSplitB          Abbreviation of request type (one character)
     */
    public function Day3( $weekdayAbbr, $dateFormat, $numHoursSplitA, $typeSplitA, $numHoursSplitB, $typeSplitB )
    {
        $this->collection['AAWK1DA3'] = $weekdayAbbr;
        $this->collection['AAWK1DT3'] = $dateFormat;
        $this->collection['AAWK1HR3A'] = $numHoursSplitA;
        $this->collection['AAWK1RC3A'] = $typeSplitA;
        $this->collection['AAWK1HR3B'] = $numHoursSplitB;
        $this->collection['AAWK1RC3B'] = $typeSplitB;
    }

    /**
     * Append Day 4 to the PAPAA object.
     *
     * @param string $weekdayAbbr       Abbreviation of day of week (three characters)
     * @param string $dateFormat        Date formated as YYYY-MM-DD
     * @param decimal $numHoursSplitA   Hours requested with 2 decimals
     * @param string $typeSplitA        Abbreviation of request type (one character)
     * @param type $numHoursSplitB      Hours requested with 2 decimals
     * @param type $typeSplitB          Abbreviation of request type (one character)
     */
    public function Day4( $weekdayAbbr, $dateFormat, $numHoursSplitA, $typeSplitA, $numHoursSplitB, $typeSplitB )
    {
        $this->collection['AAWK1DA4'] = $weekdayAbbr;
        $this->collection['AAWK1DT4'] = $dateFormat;
        $this->collection['AAWK1HR4A'] = $numHoursSplitA;
        $this->collection['AAWK1RC4A'] = $typeSplitA;
        $this->collection['AAWK1HR4B'] = $numHoursSplitB;
        $this->collection['AAWK1RC4B'] = $typeSplitB;
    }

    /**
     * Append Day 5 to the PAPAA object.
     *
     * @param string $weekdayAbbr       Abbreviation of day of week (three characters)
     * @param string $dateFormat        Date formated as YYYY-MM-DD
     * @param decimal $numHoursSplitA   Hours requested with 2 decimals
     * @param string $typeSplitA        Abbreviation of request type (one character)
     * @param type $numHoursSplitB      Hours requested with 2 decimals
     * @param type $typeSplitB          Abbreviation of request type (one character)
     */
    public function Day5( $weekdayAbbr, $dateFormat, $numHoursSplitA, $typeSplitA, $numHoursSplitB, $typeSplitB )
    {
        $this->collection['AAWK1DA5'] = $weekdayAbbr;
        $this->collection['AAWK1DT5'] = $dateFormat;
        $this->collection['AAWK1HR5A'] = $numHoursSplitA;
        $this->collection['AAWK1RC5A'] = $typeSplitA;
        $this->collection['AAWK1HR5B'] = $numHoursSplitB;
        $this->collection['AAWK1RC5B'] = $typeSplitB;
    }

    /**
     * Append Day 6 to the PAPAA object.
     *
     * @param string $weekdayAbbr       Abbreviation of day of week (three characters)
     * @param string $dateFormat        Date formated as YYYY-MM-DD
     * @param decimal $numHoursSplitA   Hours requested with 2 decimals
     * @param string $typeSplitA        Abbreviation of request type (one character)
     * @param type $numHoursSplitB      Hours requested with 2 decimals
     * @param type $typeSplitB          Abbreviation of request type (one character)
     */
    public function Day6( $weekdayAbbr, $dateFormat, $numHoursSplitA, $typeSplitA, $numHoursSplitB, $typeSplitB )
    {
        $this->collection['AAWK1DA6'] = $weekdayAbbr;
        $this->collection['AAWK1DT6'] = $dateFormat;
        $this->collection['AAWK1HR6A'] = $numHoursSplitA;
        $this->collection['AAWK1RC6A'] = $typeSplitA;
        $this->collection['AAWK1HR6B'] = $numHoursSplitB;
        $this->collection['AAWK1RC6B'] = $typeSplitB;
    }

    /**
     * Append Day 7 to the PAPAA object.
     *
     * @param string $weekdayAbbr       Abbreviation of day of week (three characters)
     * @param string $dateFormat        Date formated as YYYY-MM-DD
     * @param decimal $numHoursSplitA   Hours requested with 2 decimals
     * @param string $typeSplitA        Abbreviation of request type (one character)
     * @param type $numHoursSplitB      Hours requested with 2 decimals
     * @param type $typeSplitB          Abbreviation of request type (one character)
     */
    public function Day7( $weekdayAbbr, $dateFormat, $numHoursSplitA, $typeSplitA, $numHoursSplitB, $typeSplitB )
    {
        $this->collection['AAWK1DA7'] = $weekdayAbbr;
        $this->collection['AAWK1DT7'] = $dateFormat;
        $this->collection['AAWK1HR7A'] = $numHoursSplitA;
        $this->collection['AAWK1RC7A'] = $typeSplitA;
        $this->collection['AAWK1HR7B'] = $numHoursSplitB;
        $this->collection['AAWK1RC7B'] = $typeSplitB;
    }

    /**
     * Append Day 8 to the PAPAA object.
     *
     * @param string $weekdayAbbr       Abbreviation of day of week (three characters)
     * @param string $dateFormat        Date formated as YYYY-MM-DD
     * @param decimal $numHoursSplitA   Hours requested with 2 decimals
     * @param string $typeSplitA        Abbreviation of request type (one character)
     * @param type $numHoursSplitB      Hours requested with 2 decimals
     * @param type $typeSplitB          Abbreviation of request type (one character)
     */
    public function Day8( $weekdayAbbr, $dateFormat, $numHoursSplitA, $typeSplitA, $numHoursSplitB, $typeSplitB )
    {
        $this->collection['AAWK2DA1'] = $weekdayAbbr;
        $this->collection['AAWK2DT1'] = $dateFormat;
        $this->collection['AAWK2HR1A'] = $numHoursSplitA;
        $this->collection['AAWK2RC1A'] = $typeSplitA;
        $this->collection['AAWK2HR1B'] = $numHoursSplitB;
        $this->collection['AAWK2RC1B'] = $typeSplitB;
    }

    /**
     * Append Day 9 to the PAPAA object.
     *
     * @param string $weekdayAbbr       Abbreviation of day of week (three characters)
     * @param string $dateFormat        Date formated as YYYY-MM-DD
     * @param decimal $numHoursSplitA   Hours requested with 2 decimals
     * @param string $typeSplitA        Abbreviation of request type (one character)
     * @param type $numHoursSplitB      Hours requested with 2 decimals
     * @param type $typeSplitB          Abbreviation of request type (one character)
     */
    public function Day9( $weekdayAbbr, $dateFormat, $numHoursSplitA, $typeSplitA, $numHoursSplitB, $typeSplitB )
    {
        $this->collection['AAWK2DA2'] = $weekdayAbbr;
        $this->collection['AAWK2DT2'] = $dateFormat;
        $this->collection['AAWK2HR2A'] = $numHoursSplitA;
        $this->collection['AAWK2RC2A'] = $typeSplitA;
        $this->collection['AAWK2HR2B'] = $numHoursSplitB;
        $this->collection['AAWK2RC2B'] = $typeSplitB;
    }

    /**
     * Append Day 10 to the PAPAA object.
     *
     * @param string $weekdayAbbr       Abbreviation of day of week (three characters)
     * @param string $dateFormat        Date formated as YYYY-MM-DD
     * @param decimal $numHoursSplitA   Hours requested with 2 decimals
     * @param string $typeSplitA        Abbreviation of request type (one character)
     * @param type $numHoursSplitB      Hours requested with 2 decimals
     * @param type $typeSplitB          Abbreviation of request type (one character)
     */
    public function Day10( $weekdayAbbr, $dateFormat, $numHoursSplitA, $typeSplitA, $numHoursSplitB, $typeSplitB )
    {
        $this->collection['AAWK2DA3'] = $weekdayAbbr;
        $this->collection['AAWK2DT3'] = $dateFormat;
        $this->collection['AAWK2HR3A'] = $numHoursSplitA;
        $this->collection['AAWK2RC3A'] = $typeSplitA;
        $this->collection['AAWK2HR3B'] = $numHoursSplitB;
        $this->collection['AAWK2RC3B'] = $typeSplitB;
    }

    /**
     * Append Day 11 to the PAPAA object.
     *
     * @param string $weekdayAbbr       Abbreviation of day of week (three characters)
     * @param string $dateFormat        Date formated as YYYY-MM-DD
     * @param decimal $numHoursSplitA   Hours requested with 2 decimals
     * @param string $typeSplitA        Abbreviation of request type (one character)
     * @param type $numHoursSplitB      Hours requested with 2 decimals
     * @param type $typeSplitB          Abbreviation of request type (one character)
     */
    public function Day11( $weekdayAbbr, $dateFormat, $numHoursSplitA, $typeSplitA, $numHoursSplitB, $typeSplitB )
    {
        $this->collection['AAWK2DA4'] = $weekdayAbbr;
        $this->collection['AAWK2DT4'] = $dateFormat;
        $this->collection['AAWK2HR4A'] = $numHoursSplitA;
        $this->collection['AAWK2RC4A'] = $typeSplitA;
        $this->collection['AAWK2HR4B'] = $numHoursSplitB;
        $this->collection['AAWK2RC4B'] = $typeSplitB;
    }

    /**
     * Append Day 12 to the PAPAA object.
     *
     * @param string $weekdayAbbr       Abbreviation of day of week (three characters)
     * @param string $dateFormat        Date formated as YYYY-MM-DD
     * @param decimal $numHoursSplitA   Hours requested with 2 decimals
     * @param string $typeSplitA        Abbreviation of request type (one character)
     * @param type $numHoursSplitB      Hours requested with 2 decimals
     * @param type $typeSplitB          Abbreviation of request type (one character)
     */
    public function Day12( $weekdayAbbr, $dateFormat, $numHoursSplitA, $typeSplitA, $numHoursSplitB, $typeSplitB )
    {
        $this->collection['AAWK2DA5'] = $weekdayAbbr;
        $this->collection['AAWK2DT5'] = $dateFormat;
        $this->collection['AAWK2HR5A'] = $numHoursSplitA;
        $this->collection['AAWK2RC5A'] = $typeSplitA;
        $this->collection['AAWK2HR5B'] = $numHoursSplitB;
        $this->collection['AAWK2RC5B'] = $typeSplitB;
    }

    /**
     * Append Day 13 to the PAPAA object.
     *
     * @param string $weekdayAbbr       Abbreviation of day of week (three characters)
     * @param string $dateFormat        Date formated as YYYY-MM-DD
     * @param decimal $numHoursSplitA   Hours requested with 2 decimals
     * @param string $typeSplitA        Abbreviation of request type (one character)
     * @param type $numHoursSplitB      Hours requested with 2 decimals
     * @param type $typeSplitB          Abbreviation of request type (one character)
     */
    public function Day13( $weekdayAbbr, $dateFormat, $numHoursSplitA, $typeSplitA, $numHoursSplitB, $typeSplitB )
    {
        $this->collection['AAWK2DA6'] = $weekdayAbbr;
        $this->collection['AAWK2DT6'] = $dateFormat;
        $this->collection['AAWK2HR6A'] = $numHoursSplitA;
        $this->collection['AAWK2RC6A'] = $typeSplitA;
        $this->collection['AAWK2HR6B'] = $numHoursSplitB;
        $this->collection['AAWK2RC6B'] = $typeSplitB;
    }

    /**
     * Append Day 14 to the PAPAA object.
     *
     * @param string $weekdayAbbr       Abbreviation of day of week (three characters)
     * @param string $dateFormat        Date formated as YYYY-MM-DD
     * @param decimal $numHoursSplitA   Hours requested with 2 decimals
     * @param string $typeSplitA        Abbreviation of request type (one character)
     * @param type $numHoursSplitB      Hours requested with 2 decimals
     * @param type $typeSplitB          Abbreviation of request type (one character)
     */
    public function Day14( $weekdayAbbr, $dateFormat, $numHoursSplitA, $typeSplitA, $numHoursSplitB, $typeSplitB )
    {
        $this->collection['AAWK2DA7'] = $weekdayAbbr;
        $this->collection['AAWK2DT7'] = $dateFormat;
        $this->collection['AAWK2HR7A'] = $numHoursSplitA;
        $this->collection['AAWK2RC7A'] = $typeSplitA;
        $this->collection['AAWK2HR7B'] = $numHoursSplitB;
        $this->collection['AAWK2RC7B'] = $typeSplitB;
    }

    /**
     * Append Reason to the pappa object.
     * NOTE: 11/01/2016 Per Payroll, changed this function to simply add nothing to the AACOMM field.
     * Reason: Not needed by Payroll, they can use the Time Off Requests system to look
     * at the comment if needed. Also avoids having to truncate the reason after 75 characters.
     *
     * @param string $reason
     */
    public function Reason( $reason )
    {
        $this->collection['AACOMM'] = '';
    }

    /**
     * Append Request ID to the pappa object.
     *
     * @param string $reason
     */
    public function RequestId( $request_id )
    {
        $this->collection['TIMEOFF_REQUEST_ID'] = $request_id;
    }

}
