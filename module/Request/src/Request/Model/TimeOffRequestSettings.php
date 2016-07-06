<?php

namespace Request\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\ResultSet\ResultSet;

class TimeOffRequestSettings extends BaseDB {
    
    /**
     * Returns an array of emails to override in certain application areas.
     * 
     * @return type
     */
    public function getEmailOverrides()
    {
        $sql = new Sql( $this->adapter );
        $select = $sql->select( [ 'settings' => 'TIMEOFF_REQUEST_SETTINGS' ] )
                ->columns( [ 'SYSTEM_VALUE' => 'SYSTEM_VALUE' ] )
                ->where( [ 'settings.SYSTEM_KEY' => 'emailOverrideList' ] );

        try {
            $request = \Request\Helper\ResultSetOutput::getResultRecord( $sql, $select );
            $emailOverrideList = json_decode( $request->SYSTEM_VALUE );            
        } catch ( Exception $e ) {
            var_dump( $e );
        }
        
        return $emailOverrideList;
    }
    
    /**
     * Returns a list of company holidays.
     * 
     * @return type
     */
    public function getCompanyHolidays()
    {
        $sql = new Sql( $this->adapter );
        $select = $sql->select( [ 'settings' => 'TIMEOFF_REQUEST_SETTINGS' ] )
                ->columns( [ 'SYSTEM_VALUE' => 'SYSTEM_VALUE' ] )
                ->where( [ 'settings.SYSTEM_KEY' => 'companyHolidays' ] );

        try {
            $request = \Request\Helper\ResultSetOutput::getResultRecord( $sql, $select );
            $companyHolidays = json_decode( $request->SYSTEM_VALUE );
        } catch ( Exception $e ) {
            var_dump( $e );
        }
        
        return $companyHolidays;
    }
    
    /**
     * Verify if date is already set as a Company Holiday.
     * 
     * @param array $data   $data = [ 'date' => 'mm/dd/yyyy' ];
     * @return integer
     */
    public function isSavedCompanyHoliday( $date )
    {
        $companyHolidays = $this->getCompanyHolidays();
        foreach( $companyHolidays as $ctr => $recordedHoliday ) {
            if( $date === $recordedHoliday ) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Adds a Company Holiday.
     * 
     * @param type $data
     */
    public function addCompanyHoliday( $data = [] )
    {        
        $return = false;
        if( !$this->isSavedCompanyHoliday( $data['date'] ) ) {
            $companyHolidays = $this->getCompanyHolidays();
            
            $companyHolidays[] = $data['date'];
            sort( $companyHolidays );
            $rawSql = "UPDATE TIMEOFF_REQUEST_SETTINGS SET SYSTEM_VALUE = '" . json_encode( $companyHolidays, JSON_UNESCAPED_SLASHES ) .
                      "' WHERE SYSTEM_KEY = 'companyHolidays'";
            
            try {
                \Request\Helper\ResultSetOutput::executeRawSql( $this->adapter, $rawSql );
                $return = true;
            } catch ( Exception $e ) {
                throw new \Exception( "Error when trying to add Company Holiday entry: " . $e->getMessage() );
            }
        } else {
            $return = true;
        }
        
        return $return;
    }
    
    /**
     * Deletes a Company Holiday.
     * 
     * @param type $data
     */
    public function deleteCompanyHoliday( $data = [] )
    {        
        $return = false;
        if( $this->isSavedCompanyHoliday( $data['date'] ) ) {
            $companyHolidays = $this->getCompanyHolidays();
            foreach($companyHolidays as $ctr => $companyHoliday) {
                if( $data['date']==$companyHoliday ) {
                    unset( $companyHolidays[$ctr] );
                }
            }
            sort( $companyHolidays );
            
            $rawSql = "UPDATE TIMEOFF_REQUEST_SETTINGS SET SYSTEM_VALUE = '" . json_encode( $companyHolidays, JSON_UNESCAPED_SLASHES ) .
                      "' WHERE SYSTEM_KEY = 'companyHolidays'";
            
            try {
                \Request\Helper\ResultSetOutput::executeRawSql( $this->adapter, $rawSql );
                $return = true;
            } catch ( Exception $e ) {
                throw new \Exception( "Error when trying to delete Company Holiday entry: " . $e->getMessage() );
            }
        } else {
            $return = true;
        }
        
        return $return;
    }
    
}