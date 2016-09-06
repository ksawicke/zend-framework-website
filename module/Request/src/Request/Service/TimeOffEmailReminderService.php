<?php
namespace Request\Service;

use Zend\Mvc\Controller\AbstractActionController;
use Request\Model\TimeOffRequests;
use Request\Model\TimeOffEmailReminder;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Resolver\TemplateMapResolver;
use Request\Model\Employee;

class TimeOffEmailReminderService extends AbstractActionController
{

    protected $serviceLocator;

    protected $timeOffRequests;

    protected $timeOffEmailReminder;

    protected $emailService;

    protected $employeeModel;

    protected $emailReminderList;

    /**
     * Constructor
     *
     * @param TimeOffRequests $timeOffRequests
     * @param TimeOffEmailReminder $timeOffEmailReminder
     * @param EmailService $emailService
     */
    public function __construct(TimeOffRequests $timeOffRequests, TimeOffEmailReminder $timeOffEmailReminder, EmailService $emailService, Employee $employeeModel)
    {
        $this->timeOffRequests = $timeOffRequests;
        $this->timeOffEmailReminder = $timeOffEmailReminder;
        $this->emailService = $emailService;
        $this->employeeModel = $employeeModel;
    }

    /**
     * Main
     */
    public function sendThreeDayReminderEmailToSupervisor()
    {
        /* get URL view helper to resolve routes */
        $getRouteUrl = $this->serviceLocator->get('viewhelpermanager')->get('url');

        /* resolve route 'home' with server name */
        $route = $getRouteUrl('home');
//         $route = $getRouteUrl('home',array(),array('force_canonical' => true));
        var_dump($route);die();

        /* retrieve all unapproved records */
        $timeOffRequestsResult = $this->timeOffRequests->getRequestsOverThreeDaysUnapproved();
        if (count($timeOffRequestsResult) == 0 ) {
            return;
        }

        /* insert unapproved records where apllicable */
        $insertResult = $this->timeOffEmailReminder->insertReminderRecords($timeOffRequestsResult);

        /* retrieve unsend email reminders */
        $timeOffEmailReminderResult = $this->timeOffEmailReminder->getAllUnsendRecordData();

        /* group reminders by supervisor */
        $this->prepareEmailArray($timeOffEmailReminderResult);

        foreach ($this->emailReminderList as $emailReminder) {

            /* build list for email body */
            $employeeList = '<ul>';
            foreach ($emailReminder as $reminder) {
                $employeeList .= '<li>' . $reminder['EMPLOYEE_NAME'] . '</li>';
            }
            $employeeList .= '</ul>';

            /* add URL link for email body */
            $employeeList .= '<a href="' . $route . '">Time-Off</a>';

            /* render email from view */
            $renderedEmail = $this->renderEmail($employeeList);

            /* retrieve supervisor email address */
            $supervisorEmail = $this->employeeModel->getEmployeeEmailAddress( key($emailReminder) );

            /* prepare and send email */
            $this->emailService->setTo($supervisorEmail)
                               ->setFrom('timeoffrequests-donotreply@swifttrans.com')
                               ->setSubject('SWIFT - Time Off Requests')
                               ->setBody($renderedEmail)
                               ->send();
        }

        /* mark records as send */
        $this->markRequestsAsSend($timeOffEmailReminderResult);

    }

    protected function prepareEmailArray($timeOffReminders)
    {
        if (!is_array($timeOffReminders)) {
            return false;
        }

        if (count($timeOffReminders) == 0) {
            return false;
        }

        $this->emailReminderList = [];

        foreach ($timeOffReminders as $timeOffReminder) {
            $this->emailReminderList[trim($timeOffReminder['SPSPEN'])][$timeOffReminder['EMPLOYEE_NUMBER']] = $timeOffReminder;
        }
    }

    protected function renderEmail( $content )
    {
        $view = new PhpRenderer();
        $resolver = new TemplateMapResolver();
        $resolver->setMap([
            'mailLayout' => __DIR__ . '/../../../view/email/TimeOffEmailReminderLayout.phtml',
            'mailTemplate' => __DIR__ . '/../../../view/email/TimeOffEmailReminderTemplate.phtml',
        ]);

        $view->setResolver($resolver);

        $viewModel = new ViewModel();
        $viewModel->setTemplate('mailTemplate')
             ->setVariables([
                 'emailBody' => $content
             ]);

        $content = $view->render($viewModel);

        $viewLayout = new ViewModel();
        $viewLayout->setTemplate('mailLayout')
                   ->setVariables([
                       'content' => $content
        ]);

        return $view->render($viewLayout);
    }

    protected function markRequestsAsSend( array $requests )
    {
        foreach ($requests as $request) {
            $this->timeOffEmailReminder->markRecordAsSend( $request['IDENTITY_ID'] );
        }
    }

}

