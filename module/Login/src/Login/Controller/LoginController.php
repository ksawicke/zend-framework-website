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
            
//             echo '<pre>';
//             print_r($result);
//             echo '</pre>';
//             exit();
            
            if(count($result)==1) {
                $session = new Container('Timeoff_'.ENVIRONMENT);
                
                $session->offsetSet('EMPLOYEE_NUMBER', trim($result[0]->EMPLOYEE_NUMBER));
                $session->offsetSet('EMAIL_ADDRESS', strtolower(trim($result[0]->EMAIL_ADDRESS)));
                $session->offsetSet('COMMON_NAME', ucwords(strtolower(trim($result[0]->COMMON_NAME))));
                $session->offsetSet('FIRST_NAME', ucwords(strtolower(trim($result[0]->FIRST_NAME))));
                $session->offsetSet('LAST_NAME', ucwords(strtolower(trim($result[0]->LAST_NAME))));
                $session->offsetSet('USERNAME', strtolower(trim($result[0]->USERNAME)));
                $session->offsetSet('POSITION_TITLE', trim($result[0]->POSITION_TITLE));
                                
                $session->offsetSet('MANAGER_EMPLOYEE_NUMBER', trim($result[0]->MANAGER_EMPLOYEE_NUMBER));
                $session->offsetSet('MANAGER_FIRST_NAME', ucwords(strtolower(trim($result[0]->MANAGER_FIRST_NAME))));
                $session->offsetSet('MANAGER_LAST_NAME', ucwords(strtolower(trim($result[0]->MANAGER_LAST_NAME))));
                $session->offsetSet('MANAGER_EMAIL_ADDRESS', strtolower(trim($result[0]->MANAGER_EMAIL_ADDRESS)));
                
//                 echo '<pre>';
//                 print_r($_SESSION['Timeoff']);
//                 echo '</pre>';
//                 exit();
//                 echo '<pre>@!';
//                 print_r($session);
//                 echo '</pre>';
//                 exit();
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
        unset($_SESSION['Timeoff_'.ENVIRONMENT]);
    }

}

?>