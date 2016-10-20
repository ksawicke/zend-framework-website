<?php
namespace Request\Service;

use Zend\Mvc\Controller\AbstractActionController;
use Request\Model\TimeOffRequestSettings;
use Request\Model\RequestEntry;
use Request\Model\TimeOffRequests;
use Request\Model\Employee;
use Request\Model\EmployeeSchedules;

class CalendarInviteService extends AbstractActionController
{

    protected $serviceLocator;

    protected $timeOffRequestSettings;

    protected $overrideEmails;

    protected $emailOverrideList;

    protected $requestEntry;

    protected $timeOffRequests;

    protected $employee;

    protected $employeeSchedules;

    protected $requestId;

    protected $toEmail;

    protected $organizerName;

    protected $organizerEmail;

    protected $participantName1;

    protected $participantEmail1;

    protected $participantName2;

    protected $participantEmail2;

    protected $subject;

    protected $location;

    protected $description;

    protected $startDate;

    protected $endDate;

    protected $startTime;

    protected $endTime;

    public function __construct( TimeOffRequestSettings $timeOffRequestSettings, RequestEntry $requestEntry, TimeOffRequests $timeOffRequests, Employee $employee, EmployeeSchedules $employeeSchedules )
    {
        $this->timeOffRequestSettings = $timeOffRequestSettings;
        $this->requestEntry = $requestEntry;
        $this->timeOffRequests = $timeOffRequests;
        $this->employee = $employee;
        $this->employeeSchedules = $employeeSchedules;
    }

    public function setRequestId( $requestId )
    {
        $this->requestId = $requestId;
        return $this;
    }

    private function checkEmailOverride()
    {
        $emailOverrideList = $this->timeOffRequestSettings->getEmailOverrideList();
        $this->overrideEmails = $this->timeOffRequestSettings->getOverrideEmailsSetting();
        $this->emailOverrideList = ( ( ENVIRONMENT=='testing' || ENVIRONMENT=='development' ) ?
            $emailOverrideList : '' );

    }

    public function send()
    {
        $calendarInviteData = $this->timeOffRequests->findRequestCalendarInviteData( $this->requestId );
        $dateRequestBlocks = $this->requestEntry->getRequestObject( $this->requestId );
        $employeeData = $this->employee->findEmployeeTimeOffData( $dateRequestBlocks['for']['employee_number'], "Y", "EMPLOYER_NUMBER, EMPLOYEE_NUMBER, LEVEL_1, LEVEL_2, LEVEL_3, LEVEL_4, SALARY_TYPE" );
        $employeeProfile = $this->employeeSchedules->getEmployeeProfile( $dateRequestBlocks['for']['employee_number'] );

        echo '<pre>';
        var_dump( $calendarInviteData );
        var_dump( $dateRequestBlocks );
        var_dump( $employeeData );
        var_dump( $employeeProfile );
        echo '<pre>';
        die( "...." );
    }

}