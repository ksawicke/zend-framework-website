<?php

namespace Request\Model;

use Zend\Db\Sql\Delete;
use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Update;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\ResultSet\ResultSet;
use Request\Model\BaseDB;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Expression;
use Application\Model\DataTableInquiry;

/**
 * All Database functions for employee proxies
 *
 * @author sawik
 *
 */
class EmployeeProxies extends BaseDB {

    public function __construct() {
        parent::__construct();
    }

    /**
     * Get count of Manager Queue data
     *
     * @param array $data   $data = [ 'employeeData' => 'xxxxxxxxx' ];
     * @return int
     */
    public function countProxyItems( $data = null, $isFiltered = false )
    {
        $rawSql = "SELECT COUNT(*) AS RCOUNT
        FROM TIMEOFF_REQUEST_EMPLOYEE_PROXIES p
        WHERE trim(p.EMPLOYEE_NUMBER) = '" . $data['employeeNumber'] . "'";

        $employeeData = \Request\Helper\ResultSetOutput::getResultRecordFromRawSql( $this->adapter, $rawSql );

        return (int) $employeeData['RCOUNT'];
    }

    public function countAllSupervisorProxies(DataTableInquiry $datatableInquiry, $isFiltered = false )
    {
        $sql = new Sql($this->adapter);

        $select = $sql->select();

        $select->from('TIMEOFF_REQUEST_EMPLOYEE_PROXIES');

        $select->columns(['RCOUNT' => new Expression("COUNT(*)")]);

        if ($isFiltered === true) {
            if (trim($datatableInquiry->getSearch()->value) !== '') {
                $select->join(['PROXY_PRPMS' => 'PRPMS'], new Expression("TRIM(PROXY_PRPMS.PREN) = trim(PROXY_EMPLOYEE_NUMBER) and TRIM(PROXY_PRPMS.PRER) = '002'"), []);
                $select->join(['EMPLOYEE_PRPMS' => 'PRPMS'], new Expression("TRIM(EMPLOYEE_PRPMS.PREN) = trim(EMPLOYEE_NUMBER) and TRIM(EMPLOYEE_PRPMS.PRER) = '002'"), []);

                $select->offset($datatableInquiry->getStart());
                $select->limit($datatableInquiry->getLength());

                $where = new Where();
                $where->like('PROXY_PRPMS.PRFNM', "%".strtoupper($datatableInquiry->getSearch()->value)."%")
                ->or->like('PROXY_PRPMS.PRLNM', "%".strtoupper($datatableInquiry->getSearch()->value)."%")
                ->or->like('EMPLOYEE_PRPMS.PRFNM', "%".strtoupper($datatableInquiry->getSearch()->value)."%")
                ->or->like('EMPLOYEE_PRPMS.PRLNM', "%".strtoupper($datatableInquiry->getSearch()->value)."%")
                ->or->like('EMPLOYEE_NUMBER', "%".strtoupper($datatableInquiry->getSearch()->value)."%")
                ->or->like('PROXY_EMPLOYEE_NUMBER', "%".strtoupper($datatableInquiry->getSearch()->value)."%");
                $select->where($where);
            }
        }

        $statement = $sql->prepareStatementForSqlObject($select);

        $result = $statement->execute();

        if ($result instanceof ResultInterface && $result->isQueryResult()) {
            $resultSet = new ResultSet();
            $resultSet->initialize($result);
            $recordCount = $resultSet->toArray();
            foreach ($recordCount as $key => $value) {
                return $value["RCOUNT"];
            }
        }

        return 0;
    }

    public function getProxies( $post )
    {
        $proxyData = [];
//        echo '<pre>';
//        var_dump( $post );
//        echo '</pre>';
//        exit();
        $rawSql = "SELECT trim(p.PROXY_EMPLOYEE_NUMBER) as PROXY_EMPLOYEE_NUMBER,
            trim(p.EMPLOYEE_NUMBER) as EMPLOYEE_NUMBER,
            TRIM(employee.PRLNM) CONCAT ', ' CONCAT TRIM(employee.PRCOMN) CONCAT ' (' CONCAT TRIM(employee.PREN) CONCAT ')' as EMPLOYEE_DESCRIPTION,
            p.STATUS
            FROM TIMEOFF_REQUEST_EMPLOYEE_PROXIES p
            LEFT JOIN PRPMS employee ON TRIM(employee.PREN) = trim(p.PROXY_EMPLOYEE_NUMBER) and TRIM(employee.PRER) = '002'
            WHERE
               trim(p.EMPLOYEE_NUMBER) = '" . $post['employeeNumber'] . "'
            ORDER BY employee.PRLNM ASC";

        $statement = $this->adapter->query( $rawSql );
        $result = $statement->execute();

        if ( $result instanceof ResultInterface && $result->isQueryResult() ) {
            $resultSet = new ResultSet;
            $resultSet->initialize( $result );

            foreach( $resultSet as $field => $proxyEmployeeNumber ) {
                $proxyData[] = $proxyEmployeeNumber;
            }
        }

        return $proxyData;
    }

    public function getAllSupervisorProxies(DataTableInquiry $datatableInquiry)
    {
        $sql = new Sql($this->adapter);

        $select = $sql->select();

        $select->from('TIMEOFF_REQUEST_EMPLOYEE_PROXIES');

        $select->join(['PROXY_PRPMS' => 'PRPMS'], new Expression("TRIM(PROXY_PRPMS.PREN) = trim(PROXY_EMPLOYEE_NUMBER) and TRIM(PROXY_PRPMS.PRER) = '002'"), ['PROXY_PRLNM' => 'PRLNM', 'PROXY_PRCOMN' => 'PRCOMN']);
        $select->join(['EMPLOYEE_PRPMS' => 'PRPMS'], new Expression("TRIM(EMPLOYEE_PRPMS.PREN) = trim(EMPLOYEE_NUMBER) and TRIM(EMPLOYEE_PRPMS.PRER) = '002'"), ['EMPLOYEE_PRLNM' => 'PRLNM', 'EMPLOYEE_PRCOMN' => 'PRCOMN']);

        $select->offset($datatableInquiry->getStart());
        $select->limit($datatableInquiry->getLength());

        if (trim($datatableInquiry->getSearch()->value) !== '') {
            $where = new Where();
            $where->like('PROXY_PRPMS.PRFNM', "%".strtoupper($datatableInquiry->getSearch()->value)."%")
                  ->or->like('PROXY_PRPMS.PRLNM', "%".strtoupper($datatableInquiry->getSearch()->value)."%")
                  ->or->like('EMPLOYEE_PRPMS.PRFNM', "%".strtoupper($datatableInquiry->getSearch()->value)."%")
                  ->or->like('EMPLOYEE_PRPMS.PRLNM', "%".strtoupper($datatableInquiry->getSearch()->value)."%")
                  ->or->like('EMPLOYEE_NUMBER', "%".strtoupper($datatableInquiry->getSearch()->value)."%")
                  ->or->like('PROXY_EMPLOYEE_NUMBER', "%".strtoupper($datatableInquiry->getSearch()->value)."%");
                  $select->where($where);
        }

        $statement = $sql->prepareStatementForSqlObject($select);

        $result = $statement->execute();

        if ( $result instanceof ResultInterface && $result->isQueryResult() ) {
            $resultSet = new ResultSet;
            $resultSet->initialize( $result );
            return $resultSet->toArray();
        }

        return [];
    }

    /**
     * Adds a proxy to be able to submit time off requests for a designated employee.
     *
     * @param type $post
     * @throws \Exception
     */
    public function addProxy( $post ) {

        $employeeNumber = $post->EMPLOYEE_NUMBER;
        $proxyEmployeeNumber = $post->PROXY_EMPLOYEE_NUMBER;

        $existingProxyCount = $this->checkDuplicateProxy($employeeNumber, $proxyEmployeeNumber);
        if ($existingProxyCount != 0) {
            return;
        }

        $sql = new Sql($this->adapter);

        $employeeProxy = $sql->insert();
        $employeeProxy->into( 'timeoff_request_employee_proxies' );

        $employeeProxy->values( [
            'EMPLOYEE_NUMBER' => \Request\Helper\Format::rightPadEmployeeNumber( $post->EMPLOYEE_NUMBER ),
            'PROXY_EMPLOYEE_NUMBER' => \Request\Helper\Format::rightPadEmployeeNumber( $post->PROXY_EMPLOYEE_NUMBER ),
            'STATUS' => '1'
        ] );

        $stmt = $sql->prepareStatementForSqlObject( $employeeProxy );
        try {
            $result = $stmt->execute();
        } catch ( \Exception $e ) {
            throw new \Exception( "Can't execute statement: " . $e->getMessage() );
        }
    }

    public function checkDuplicateProxy( $employeeNumber, $proxyEmployeeNumber)
    {
        $sql = new Sql($this->adapter);

        $select = $sql->select();

        $select->from( 'timeoff_request_employee_proxies' );

        $select->columns(['RCOUNT' => new Expression('count(*)')]);

        $where = new Where();
        $where->equalTo('EMPLOYEE_NUMBER', str_pad(trim($employeeNumber), 9 ,' ', STR_PAD_LEFT))
              ->and->equalTo('PROXY_EMPLOYEE_NUMBER', str_pad(trim($proxyEmployeeNumber), 9, ' ', STR_PAD_LEFT));

        $select->where($where);

        $statement = $sql->prepareStatementForSqlObject($select);

        $result = $statement->execute();

        if ($result instanceof ResultInterface && $result->isQueryResult()) {
            $resultSet = new ResultSet();
            $resultSet->initialize($result);
            return $resultSet->toArray()[0]['RCOUNT'];
        }

        return 0;

    }

    /**
     * Deletes a proxy for a designated employee.
     *
     * @param type $post
     * @return type
     */
    public function deleteProxy( $post ) {
        /**
         * instantiate new SQL adapter
         */
        $sql = new Sql($this->adapter);

        /**
         * prepare new sql DELETE
         */
        $delete = $sql->delete();

        /**
         * define sql FROM
         */
        $delete->from('TIMEOFF_REQUEST_EMPLOYEE_PROXIES');

        /**
         * define sql WHERE
         */
        $delete->where(array(
            'EMPLOYEE_NUMBER' => \Request\Helper\Format::rightPadEmployeeNumber( $post->EMPLOYEE_NUMBER ),
            'PROXY_EMPLOYEE_NUMBER' => \Request\Helper\Format::rightPadEmployeeNumber( $post->PROXY_EMPLOYEE_NUMBER )
        ));

        /**
         * prepare SQL execution
         */
        $statement = $sql->prepareStatementForSqlObject($delete);

        /**
         * execute SQL
         */
        $result = $statement->execute();

        /**
         * analyze result set
         */
        if ($result instanceof ResultInterface && $result->isQueryResult()) {
            $resultSet = new ResultSet();
            $resultSet->initialize($result);
            return $resultSet->toArray();
        }

        return array();
    }

    public function toggleProxy( $post )
    {
        $rawSql = "UPDATE TIMEOFF_REQUEST_EMPLOYEE_PROXIES SET STATUS = '" . $post->STATUS . "' WHERE " .
                  "EMPLOYEE_NUMBER = '" . str_pad(trim($post->EMPLOYEE_NUMBER), 9, ' ', STR_PAD_LEFT) . "' AND " .
                  "PROXY_EMPLOYEE_NUMBER = '" . str_pad(trim($post->PROXY_EMPLOYEE_NUMBER), 9, ' ', STR_PAD_LEFT) . "'";

        $proxyData = \Request\Helper\ResultSetOutput::executeRawSql($this->adapter, $rawSql);

        return $proxyData;
    }

}
