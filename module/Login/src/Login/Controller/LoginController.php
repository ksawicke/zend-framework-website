<?php
namespace Login\Controller;

use Login\Service\AuthenticationServiceInterface;
use Zend\Form\FormInterface;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Login\Form\LoginForm;
use Login\Form\Filter\LoginFilter;
use Zend\Session\Container;

class LoginController extends AbstractActionController
{
    public function __construct(AuthenticationServiceInterface $authenticationService, FormInterface $loginForm)
    {
//        $helper = $this->getServiceLocator()->get('ViewHelperManager')->get('ServerUrl');
//        $url = $helper();
//
//        die( $url );

//        die("STOP");
        $this->authenticationService = $authenticationService;
        $this->loginForm = $loginForm;
    }

    /**
     * Login check, redirect if user not logged in.
     * @return \Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        $request = $this->getRequest();

        $view = new ViewModel();
        $loginForm = new LoginForm('loginForm');
        $loginForm->setInputFilter(new LoginFilter());

        if ($request->isPost()) {
            $data = $request->getPost();
            $loginForm->setData($data);

            $result = $this->authenticationService->authenticateUser($data->username, $data->password);
            if(count($result)==1) {
                $session = \Login\Helper\UserSession::createUserSession($result[0]);

                $employeeNumber = \Login\Helper\UserSession::getUserSessionVariable('EMPLOYEE_NUMBER');
                $isManager = $this->authenticationService->isManager($employeeNumber);
                $isSupervisor = $this->authenticationService->isSupervisor($employeeNumber);
                $isPayroll = $this->authenticationService->isPayroll($employeeNumber);
                $isPayrollAdmin = $this->authenticationService->isPayrollAdmin($employeeNumber);
                $isPayrollAssistant = $this->authenticationService->isPayrollAssistant($employeeNumber);
                $isProxy = $this->authenticationService->isProxy($employeeNumber);
                $isProxyForManager = $this->authenticationService->isProxyForManager($employeeNumber);
                \Login\Helper\UserSession::setUserSessionVariable('IS_MANAGER', $isManager);
                \Login\Helper\UserSession::setUserSessionVariable('IS_SUPERVISOR', $isSupervisor);
                \Login\Helper\UserSession::setUserSessionVariable('IS_PAYROLL', $isPayroll);
                \Login\Helper\UserSession::setUserSessionVariable('IS_PAYROLL_ADMIN', $isPayrollAdmin);
                \Login\Helper\UserSession::setUserSessionVariable('IS_PAYROLL_ASSISTANT', $isPayrollAssistant);
                \Login\Helper\UserSession::setUserSessionVariable('IS_PROXY', $isProxy);
                \Login\Helper\UserSession::setUserSessionVariable('IS_PROXY_FOR_MANAGER', $isProxyForManager);

                return $this->redirect()->toUrl( $this->getRequest()->getBaseUrl() . '/request/view-my-requests' );
            } else {
                $this->flashMessenger()->addMessage('User ID or Password incorrect. Please try again.');
                return $this->redirect()->toUrl( $this->getRequest()->getBaseUrl() . '/login/index' );
            }
        }
        $view->setVariable('loginForm', $loginForm);
        return $view;
    }

    /**
     * Logs user out of application.
     */
    public function logoutAction()
    {
        \Login\Helper\UserSession::endUserSession();
        return $this->redirect()->toUrl( $this->getRequest()->getBaseUrl() . '/login/index' );
    }

}

?>