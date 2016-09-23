<?php
namespace Login\Controller;

use Login\Service\AuthenticationServiceInterface;
use Zend\Form\FormInterface;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Login\Form\LoginForm;
use Login\Form\Filter\LoginFilter;
use Zend\Crypt\BlockCipher;
use Login\Mapper\LoginMapper;
use Login\Model\Login;
use Zend\Hydrator\ClassMethods;

class LoginController extends AbstractActionController
{
    protected $authenticationService;

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
        if(count($result) != 1) {
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
        echo "kjhkhkjhkjh"; die();
        /* get encrypted data from query string */
        $encryptedData = $this->params()->fromQuery('sso');

        /* redirect to login screen if no encrypted data available */
        if ($encryptedData == null) {
            var_dump($encryptedData); die();
            return $this->redirect()->toUrl( $this->getRequest()->getBaseUrl() . '/login/index' );
        }

        var_dump('lalala'); die();
        /* initialize block cipher */
        $blockCipher = BlockCipher::factory('mcrypt', array('algo' => '3des'));

        /* set decrypt key */
        $blockCipher->setKey('ssssssss');

        /* decrypt data */
        $decryptedData = $blockCipher->decrypt($encryptedData);

        /* reroute to login screen if decrypt not possible */
        if ($decryptedData === false) {
            return $this->redirect()->toUrl( $this->getRequest()->getBaseUrl() . '/login/index' );
        } 

        /* unfortunately the login mapper is not cortrect created, we have to instantiate it here again to get to the method */
        $loginMapper = new LoginMapper($this->serviceLocator->get('Zend\Db\Adapter\Adapter'), new ClassMethods(false), new Login());

        /* read user data */
        $result = $loginMapper->getUserDataByUsername($decryptedData['username']);

        /* set the session */
        $this->setSession($result);

        /* reroute to view-my-request */
        return $this->redirect()->toUrl( $this->getRequest()->getBaseUrl() . '/request/view-my-requests' );

    }

}
