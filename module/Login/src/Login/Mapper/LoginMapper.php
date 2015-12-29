<?php
namespace Login\Mapper;

use Login\Model\LoginInterface;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\ResultSet\HydratingResultSet;
use Zend\Db\Sql\Delete;
use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Update;
use Zend\Db\Sql\Expression;
use Zend\Db\ResultSet\ResultSet;
use Zend\Stdlib\Hydrator\HydratorInterface;
use Zend\Stdlib\Hydrator\NamingStrategy\ArrayMapNamingStrategy;

class LoginMapper implements LoginMapperInterface
{

    /**
     *
     * @var \Zend\Db\Adapter\AdapterInterface
     */
    protected $dbAdapter;

    /**
     *
     * @var \Zend\Stdlib\Hydrator\HydratorInterface
     */
    protected $hydrator;

    /**
     *
     * @var \Login\Model\LoginInterface
     */
    protected $loginPrototype;

    /**
     *
     * @param AdapterInterface $dbAdapter
     * @param HydratorInterface $hydrator
     * @param PostInterface $postPrototype
     */
    public function __construct(AdapterInterface $dbAdapter, HydratorInterface $hydrator, LoginInterface $loginPrototype)
    {
        $this->dbAdapter = $dbAdapter;
        $this->hydrator = $hydrator;
        $this->loginPrototype = $loginPrototype;
        
//         $this->employeeColumns = [
//             'EMPLOYER_NUMBER' => 'PRER',
//             'EMPLOYEE_NUMBER' => 'PREN',
//             'LEVEL_1' => 'PRL01',
//             'LEVEL_2' => 'PRL02',
//             'LEVEL_3' => 'PRL03',
//             'LEVEL_4' => 'PRL04',
//             'FIRST_NAME' => 'PRFNM',
//             'MIDDLE_INITIAL' => 'PRMNM',
//             'LAST_NAME' => 'PRLNM',
//             'POSITION' => 'PRPOS',
//             'EMAIL_ADDRESS' => 'PREML1',
//             'EMPLOYEE_HIRE_DATE' => 'PRDOHE',
//             'POSITION_TITLE' => 'PRTITL'
//         ];
        
        // Now tell the Hydrator to array_flip the keys on save.
        // Advantage: This allows us to refer to easier to understand field names on the
        // front end, but let the application deal with the real names on the back end
        // as in when doing an update.
        // Can pass in multiple arrays here.
//         $this->hydrator->setNamingStrategy(new ArrayMapNamingStrategy($this->employeeColumns));
        // $this->employeeSupervisorColumns
    }

    public function authenticateUser($username = null, $password = null)
    {
        $sql = new Sql($this->dbAdapter);
        
        /**
         * We can validate differently in development or production here.
         */
        switch(ENVIRONMENT) {
            case 'development':
            case 'production':
            default:
                $select = $sql->select(['employee' => 'PRPMS'])
                    ->columns([
                               'EMPLOYER_NUMBER' => 'PRER',
                               'EMPLOYEE_NUMBER' => 'PREN',
                               'LEVEL_1' => 'PRL01',
                               'LEVEL_2' => 'PRL02',
                               'LEVEL_3' => 'PRL03',
                               'LEVEL_4' => 'PRL04',
                               'FIRST_NAME' => 'PRFNM',
                               'MIDDLE_INITIAL' => 'PRMNM',
                               'LAST_NAME' => 'PRLNM',
                               'POSITION' => 'PRPOS',
                               'EMAIL_ADDRESS' => 'PREML1',
                               'USERNAME' => 'PRURL1',
                               'EMPLOYEE_HIRE_DATE' => 'PRDOHE',
                               'POSITION_TITLE' => 'PRTITL'
                             ])
//                     ->where(['trim(employee.PREML1)' => trim($username)]);
                    ->where(['trim(employee.PRURL1)' => strtoupper(trim($username))]);
                break;
        }
        
        $return = \Request\Helper\ResultSetOutput::getResultArray($sql, $select);
        
        return $return;
    }
}
