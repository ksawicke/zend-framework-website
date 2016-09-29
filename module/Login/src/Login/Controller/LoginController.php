<?php
namespace Login\Controller;

use Login\Service\AuthenticationServiceInterface;
use Zend\Form\FormInterface;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Login\Form\LoginForm;
use Login\Form\Filter\LoginFilter;
use Login\Model\Login;
use Zend\Http\Request;

class LoginController extends AbstractActionController
{
    protected $authenticationService;
    protected $loginForm;
    public function __construct(AuthenticationServiceInterface $authenticationService, FormInterface $loginForm)
    {
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

        if (!$request->isPost()) {
            $view->setVariable('loginForm', $loginForm);
            return $view;
        }

        $data = $request->getPost();
        $loginForm->setData($data);

        $result = $this->authenticationService->authenticateUser($data->username, $data->password);

        if( !is_array( $result ) && $result == 0 ) {
            $this->flashMessenger()->addMessage('User ID or Password incorrect. Please try again.');
            return $this->redirect()->toUrl( $this->getRequest()->getBaseUrl() . '/login/index' );
        }

        $this->setSession($result);
        return $this->redirect()->toUrl( $this->getRequest()->getBaseUrl() . '/request/view-my-requests' );
    }

    protected function setSession($result)
    {
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
    }

    /**
     * Logs user out of application.
     */
    public function logoutAction()
    {
        \Login\Helper\UserSession::endUserSession();
        return $this->redirect()->toUrl( $this->getRequest()->getBaseUrl() . '/login/index' );
    }

    /**
     * handle SSO login
     *
     * @return \Zend\Http\Response
     */
    public function ssoAction()
    {

        /* set Passphrase and IV */
        $passphrase = 'I am so rich I wish I ha';
        $iv = 'Phoenix1';

        /* get encrypted data from query string */
        $encryptedDataBase64 = $this->params()->fromQuery('q');

        /* fill blanks in string with "+" */
        if (strpos($encryptedDataBase64, ' ') !== false) {
            $encryptedDataBase64 = str_replace(' ', '+', $encryptedDataBase64);
        }

        /* base64 decode encrypted string */
        $encryptedDataBase64 = base64_decode($encryptedDataBase64);

        /* decrypt string */
        $decryptedData = mcrypt_decrypt(MCRYPT_3DES, $passphrase, $encryptedDataBase64, MCRYPT_MODE_CBC, $iv);

        /* string seems to have hex 8 (backspace) characters attached, remove */
        $decryptedData = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $decryptedData);

        /* convert json to object */
        $jsonData = json_decode(html_entity_decode($decryptedData, ENT_QUOTES));

        /* extract data */
        $employeeId = $jsonData->zcid;
        $timestamp = $jsonData->t;

        /* reroute to login screen if decrypt not possible */
        if ($employeeId === false) {
            return $this->redirect()->toUrl( $this->getRequest()->getBaseUrl() . '/login/index' );
        }

        /* modified authentication */
        $result = $this->authenticationService->authenticateUserSSO($employeeId, $timestamp);

        /* SSO Key might be to old, send message */
        if(count($result) != 1) {
            $this->flashMessenger()->addMessage('Your SSO Key has expired. Please login with your User ID and Password.');
            return $this->redirect()->toUrl( $this->getRequest()->getBaseUrl() . '/login/index' );

        }

        /* set session */
        $this->setSession($result);

        /* reroute to request view */
        return $this->redirect()->toUrl( $this->getRequest()->getBaseUrl() . '/request/view-my-requests' );

    }

}