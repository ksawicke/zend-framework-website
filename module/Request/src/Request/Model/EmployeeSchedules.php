<?php

namespace Request\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\ResultSet\ResultSet;

class EmployeeSchedules extends BaseDB {

//    public function getAll($data) {
//        $libraryLists = [
//            'development' => 'SAWIK HRTEST HRDBFA TRDBFA HRCUST IESR7FILE HR2000 TR2000 AM2000 KADIRI IESR7CSTM MLCTRL MLLIBR IESR7', // SAWIK HRTEST HRDBFA TRDBFA HRCUST IESR7FILE HR2000 TR2000 AM2000 KADIRI IESR7CSTM MLCTRL MLLIBR IESR7
//            'production'  => 'HRDBFA TRDBFA HRCUST IESR7FILE HR2000 TR2000 AM2000 KADIRI IESR7CSTM MLCTRL MLLIBR IESR7'
//        ];
//        $driverConfig = array(
//            'driver' => 'IbmDb2',
//            'database' => '*LOCAL',
//            'username' => 'PHPUSER',
//            'password' => 'SWIFT123',
//            'driver_options' => [
//                'i5_naming' => DB2_I5_NAMING_ON,
//                'i5_libl' => $libraryLists[ENVIRONMENT]
//            ],
//            'platform_options' => ['quote_identifiers' => false]
//        );
//        
//        $configPath = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/config/autoload/global.php';
//        $config = new \Zend\Config\Config( include $configPath );
//        echo '<pre>';
//        print_r($config->database);
//        
//        echo '</pre>';
//        
//        
//        
//        die("#");
//        // )
//
//        $adapter = new Adapter($driverConfig);
//
//        $sql = new Sql($adapter);
//        $select = $sql->select();
//        $select->from('HOTEL_MANAGER_ROOMS');
//        $select->join('HOTEL_MANAGER_HOTELS', 'HOTEL_MANAGER_HOTELS.IDENTITY_ID = HOTEL_IDENTITY_ID', array('HOTEL_SHORT_NAME'));
//
//        $select->where(array('ROOM_ACTIVE' => 'A'));
//
//        $columns = array(
//            'roomHotel' => 'HOTEL_SHORT_NAME',
//            'roomNumber' => 'ROOM_NUMBER',
//            'roomCapacity' => 'ROOM_CAPACITY',
//            'roomSmoking' => 'ROOM_SMOKING',
//        );
//
//        $sortColumn = $data['order'][0]['column'];
//        $sortColumnName = $data['columns'][$sortColumn]['data'];
//
//        $select->order($columns[$sortColumnName] . ' ' . $data['order'][0]['dir']);
//
//        $select->limit($data['length']);
//        $select->offset($data['start']);
//
//        $statement = $sql->prepareStatementForSqlObject($select);
//        //var_dump($statement->getSql());
//
//        $result = $statement->execute();
//
//        if ($result instanceof ResultInterface && $result->isQueryResult()) {
//            $resultSet = new ResultSet();
//            $resultSet->initialize($result);
//            //var_dump($resultSet->toArray());
//            return $resultSet->toArray();
//        }
//
//        return array();
//        // var_dump($results);
//    }
    
    public function updateEmployeeSchedule( $post = null )
    {
        $rawSql = "UPDATE timeoff_request_employee_schedules SET " .
                  "SCHEDULE_SUN = '" . $post->request['forEmployee']['SCHEDULE_SUN'] . "', " .
                  "SCHEDULE_MON = '" . $post->request['forEmployee']['SCHEDULE_MON'] . "', " .
                  "SCHEDULE_TUE = '" . $post->request['forEmployee']['SCHEDULE_TUE'] . "', " .
                  "SCHEDULE_WED = '" . $post->request['forEmployee']['SCHEDULE_WED'] . "', " .
                  "SCHEDULE_THU = '" . $post->request['forEmployee']['SCHEDULE_THU'] . "', " .
                  "SCHEDULE_FRI = '" . $post->request['forEmployee']['SCHEDULE_FRI'] . "', " .
                  "SCHEDULE_SAT = '" . $post->request['forEmployee']['SCHEDULE_SUN'] . "' " .
                  "WHERE TRIM(EMPLOYEE_NUMBER) = '" . $post->request['byEmployee'] . "'";
        $employeeData = \Request\Helper\ResultSetOutput::executeRawSql( $this->adapter, $rawSql );   
    }

}
