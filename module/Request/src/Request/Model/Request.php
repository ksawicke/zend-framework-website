<?php
namespace Request\Model;

use Request\Model\BaseDB;

/**
 * Common Request arrays
 *
 * @author sawik
 *
 */
class Request extends BaseDB
{
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
    
}