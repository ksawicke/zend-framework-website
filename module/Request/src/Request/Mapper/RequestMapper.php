<?php

namespace Request\Mapper;

use Request\Model\RequestInterface;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\ResultSet\HydratingResultSet;
use Zend\Db\Sql\Delete;
use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Update;
use Zend\Db\ResultSet\ResultSet;
use Zend\Stdlib\Hydrator\HydratorInterface;
use Zend\Stdlib\Hydrator\NamingStrategy\ArrayMapNamingStrategy;

class RequestMapper implements RequestMapperInterface
{
    /**
     * @var \Zend\Db\Adapter\AdapterInterface
     */
    protected $dbAdapter;

    /**
     * @var \Zend\Stdlib\Hydrator\HydratorInterface
     */
    protected $hydrator;

    /**
     * @var \Request\Model\RequestInterface
     */
    protected $requestPrototype;

    public $requestColumns = [];
    public $authUserColumns = [];
    public $docTypeColumns = [];
    public $emailRecipientColumns = [];

    /**
     * @param AdapterInterface  $dbAdapter
     * @param HydratorInterface $hydrator
     * @param PostInterface    $postPrototype
     */
    public function __construct(
        AdapterInterface $dbAdapter,
        HydratorInterface $hydrator,
        RequestInterface $requestPrototype
    ) {
        $this->dbAdapter      = $dbAdapter;
        $this->hydrator       = $hydrator;
        $this->requestPrototype  = $requestPrototype;

        // 'alias' => 'FIELDNAME'
        $this->employeeColumns = [
            'EMPLOYEE_ID' => 'PREN',
            'EMPLOYEE_FIRST_NAME' => 'PRFNM',
            'EMPLOYEE_MIDDLE_NAME' => 'PRMNM',
            'EMPLOYEE_LAST_NAME' => 'PRLNM',
            'EMPLOYEE_POSITION' => 'PRPOS',
            'EMPLOYEE_EMAIL' => 'PREML1',
            'EMPLOYEE_HIRE_DATE' => 'PRDOHE',
            'EMPLOYEE_TITLE' => 'PRTITL',
            'GRANDFATHERED_BALANCE' => 'PRAC5E',
            'GRANDFATHERED_TAKEN' => 'PRAC5T',
            'PTO_BALANCE' => 'PRVAC',
            'PTO_TAKEN' => 'PRVAT',
            'FLOAT_BALANCE' => 'PRSHA',
            'FLOAT_TAKEN' => 'PRSHT',
            'SICK_BALANCE' => 'PRSDA',
            'SICK_TAKEN' => 'PRSDT',
            'COMPANY_MANDATED_BALANCE' => 'PRAC4E',
            'COMPANY_MANDATED_TAKEN' => 'PRAC4T',
            'DRIVER_SICK_BALANCE' => 'PRAC6E',
            'DRIVER_SICK_TAKEN' => 'PRAC6T'
        ];
        $this->employeeSupervisorColumns = [
            'MANAGER_EMPLOYEE_ID' => 'SPSPEN',
            'MANAGER_FIRST_NAME' => 'SPSPFNM',
            'MANAGER_MIDDLE_NAME' => 'SPSPMI',
            'MANAGER_LAST_NAME' => 'SPSPLNM'
        ];
        $this->supervisorAddonColumns = [
            'SUPERVISOR_EMAIL' => 'PREML1'
        ];

        // Now tell the Hydrator to array_flip the keys on save.
        // Advantage: This allows us to refer to easier to understand field names on the
        // front end, but let the application deal with the real names on the back end
        // as in when doing an update.
        // Can pass in multiple arrays here.
        $this->hydrator->setNamingStrategy(new ArrayMapNamingStrategy(
            $this->employeeColumns,
            $this->employeeSupervisorColumns,
            $this->supervisorAddonColumns
        ));
    }

    public function findTimeOffBalances($employeeId = null)
    {
        $sql    = new Sql($this->dbAdapter);
        $select =
            $sql->select(['employee' => 'PRPMS'])
                ->columns($this->employeeColumns)
                ->join(['supervisor' => 'PRPSP'], 'employee.PREN = supervisor.SPEN', $this->employeeSupervisorColumns)
                ->join(['z' => 'PRPMS'], 'z.PREN = supervisor.SPSPEN', $this->supervisorAddonColumns)
                ->where(['trim(employee.PREN)' => trim($employeeId)]);

        return \Request\Helper\ResultSetOutput::getResultObject($sql, $select);
    }

}
