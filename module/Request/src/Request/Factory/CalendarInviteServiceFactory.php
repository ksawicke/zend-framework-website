<?php
namespace Request\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Request\Service\CalendarInviteService;
use Request\Model\TimeOffRequestSettings;
use Request\Model\RequestEntry;
use Request\Model\TimeOffRequests;
use Request\Model\Employee;
use Request\Model\EmployeeSchedules;

class CalendarInviteServiceFactory implements FactoryInterface
{
    public function createService( ServiceLocatorInterface $serviceLocator )
    {
        $timeOffRequestSettings = new TimeOffRequestSettings();
        $requestEntry = new RequestEntry();
        $timeOffRequests = new TimeOffRequests();
        $employee = new Employee();
        $employeeSchedules = new EmployeeSchedules();
        $emailService = $serviceLocator->get('EmailService');

        $calendarInviteService = new CalendarInviteService( $timeOffRequestSettings, $requestEntry, $timeOffRequests, $employee, $employeeSchedules, $emailService );

        return $calendarInviteService;
    }
}
