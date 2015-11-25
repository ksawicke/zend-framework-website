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
        RequestInterface $postPrototype
    ) {
        $this->dbAdapter      = $dbAdapter;
        $this->hydrator       = $hydrator;
        $this->requestPrototype  = $postPrototype;

        // 'alias' => 'FIELDNAME'
        $this->employeeColumns = [
            'employeeId' => 'PREN',
            'employeeFirstName' => 'PRFNM',
            'employeeMiddleName' => 'PRMNM',
            'employeeLastName' => 'PRLNM',
            'employeePosition' => 'PRPOS',
            'employeeEmail' => 'PREML1',
            'employeeHireDate' => 'PRDOHE',
            'employeeTitle' => 'PRTITL',
            'grandfatheredBalance' => 'PRAC5E',
            'grandfatheredTaken' => 'PRAC5T',
            'ptoBalance' => 'PRVAC',
            'ptoTaken' => 'PRVAT',
            'floatBalance' => 'PRSHA',
            'floatTaken' => 'PRSHT',
            'sickBalance' => 'PRSDA',
            'sickTaken' => 'PRSDT',
            'companyMandatedBalance' => 'PRAC4E',
            'companyMandatedTaken' => 'PRAC4T',
            'driverSickBalance' => 'PRAC6E',
            'driverSickTaken' => 'PRAC6T'
        ];
        $this->employeeSupervisorColumns = [
            'managerEmployeeId' => 'SPSPEN',
            'managerFirstName' => 'SPSPFNM',
            'managerMiddleName' => 'SPSPMI',
            'managerLastName' => 'SPSPLNM'
        ];
        $this->supervisorAddonColumns = [
            'supervisorEmail' => 'PREML1'
        ];

        // Now tell the Hydrator to array_flip the keys on save.
        // Advantage: This allows us to refer to easier to understand field names on the
        // front end, but let the application deal with the real names on the back end
        // as in when doing an update.
        // Can pass in multiple arrays here.
        $this->hydrator->setNamingStrategy(new ArrayMapNamingStrategy($this->employeeColumns));
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

        $stmt   = $sql->prepareStatementForSqlObject($select);
        $result = $stmt->execute();

        $resultSet = new ResultSet;
        $resultSet->initialize($result);

        return $resultSet->current();
    }

}
