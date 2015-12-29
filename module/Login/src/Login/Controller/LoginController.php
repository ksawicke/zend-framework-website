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
        $this->authenticationService = $authenticationService;
        $this->loginForm = $loginForm;
    }
    
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
                $session = new Container('User');
                $session->EMPLOYEE_NUMBER = strtolower(trim($result[0]->EMAIL_ADDRESS));
                $session->FIRST_NAME = ucwords(strtolower(trim($result[0]->FIRST_NAME)));
                $session->LAST_NAME = ucwords(strtolower(trim($result[0]->LAST_NAME)));
                $session->USERNAME = strtolower(trim($result[0]->USERNAME));
                $session->POSITION_TITLE = trim($result[0]->POSITION_TITLE);
                
                return $this->redirect()->toRoute('create2', array('controller' => 'request', 'action' => 'create'));
            } else {
                $this->flashMessenger()->addMessage('Login incorrect. Try again.');
                return $this->redirect()->toRoute('login', array('controller' => 'login', 'action' => 'index'));
            }
        }
        $view->setVariable('loginForm', $loginForm);
        return $view;
    }
    
    public function logoutAction()
    {
        unset($_SESSION['User']);
    }

}

?>