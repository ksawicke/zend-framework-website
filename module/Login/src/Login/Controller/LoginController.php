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
            
//             echo '<pre>POST:';
//             print_r($data);
//             echo '</pre><br />';
            
            $result = $this->authenticationService->authenticateUser($data->email, $data->password);
            
            if($result->COUNT_USERS_FOUND==1) {
                $session = new Container('User');
//                 unset($_SESSION['User']['email']);
                $session->email = $data->email;
                
                //         echo '<pre>';
                //         print_r($session);
                //         echo '</pre>';
                
                //         die("@@");
                
//                 if ($session->offsetExists ( 'email' )) {
//                     echo "USER EMAIL CHECK OK";
//                 } else {
//                     echo "UESR EMAIL CHECK NEGATIVE";
//                 }
                
//                 die("@@");
                
//                 $authService = $this->getServiceLocator()->get('AuthService');
//                 $authService->getAdapter()
//                     ->setIdentity($data->email)
//                     ->setCredential($data->password);
//                 $result = $authService->authenticate();
//                 var_dump($result);
//                 var_dump($authService);
//                 die("@");
//                 die("WOO HOO YOU ARE REAL.");
            } else {
                $this->flashMessenger()->addMessage('Login incorrect. Try again.');
                return $this->redirect()->toRoute('login', array('controller' => 'login', 'action' => 'index'));
            }
            
//             echo '<pre>DATA';
//             print_r($data);
//             echo '</pre>';
            
//             die("@@@@");
            
//             if($loginForm->isValid()) {
//                 $data = $loginForm->getData();
//                 echo "is Valid!";

//                 $authService = $this->getServiceLocator()->get('AuthService');
                
//                 echo '<pre>POST:';
//                 print_r($authService);
//                 echo '</pre><br />';

//                 $authService->getAdapter()
//                     ->setIdentity($data['email'])
//                     ->setCredential($data['password']);

//                 $result = $authService->authenticate();
//                 echo "now here";
//                 var_dump($authService); die();

//             }

        }
        $view->setVariable('loginForm', $loginForm);
        return $view;
    }

}

?>