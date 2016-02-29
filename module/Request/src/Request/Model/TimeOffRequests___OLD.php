<?php
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
 * All Database functions for employees
 *
 * @author sawik
 *
 */
class TimeOffRequests extends BaseDB
{

    /**
     * @var array
     */
    public $employeeData = [];
    public $employerNumber = '';
    public $includeApproved = '';
    public $timeoffRequestColumns;
    public $timeoffRequestEntryColumns;
    public $timeoffRequestCodeColumns;
    
    public static $requestStatuses = [
        'draft' => 'D',
        'approved' => 'A',
        'cancelled' => 'C',
        'pendingApproval' => 'P',
        'beingReviewed' => 'R'
    ];
    
    public static $requestStatusText = [
        'D' => 'draft',
        'A' => 'approved',
        'C' => 'cancelled',
        'P' => 'pendingApproval',
        'R' => 'beingReviewed'
    ];
    
    protected static $typesToCodes = [
        'timeOffPTO' => 'P',
        'timeOffFloat' => 'K',
        'timeOffSick' => 'S',
        'timeOffUnexcusedAbsence' => 'X',
        'timeOffBereavement' => 'B',
        'timeOffCivicDuty' => 'J',
        'timeOffGrandfathered' => 'R',
        'timeOffApprovedNoPay' => 'A'
    ];
    
    protected static $categoryToClass = [
        'PTO' => 'timeOffPTO',
        'Float' => 'timeOffFloat',
        'Sick' => 'timeOffSick',
        'UnexcusedAbsence' => 'timeOffUnexcusedAbsence',
        'Bereavement' => 'timeOffBereavement',
        'CivicDuty' => 'timeOffCivicDuty',
        'Grandfathered' => 'timeOffGrandfathered',
        'ApprovedNoPay' => 'timeOffApprovedNoPay'
    ];
    
    protected static $codesToKronos = [
        'P' => 'PTO',
        'R' => 'GFVAC',
        'B' => 'BR',
        'K' => 'FHP',
        'S' => 'SK',
        'V' => 'VA'
    ];