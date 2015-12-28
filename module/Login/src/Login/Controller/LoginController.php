<?php
namespace Login\Controller;


use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Login\Form\LoginForm;
use Login\Form\Filter\LoginFilter;
// class LoginController extends AppController
class LoginController extends AbstractActionController
{
    public function indexAction()
    {
        $request = $this->getRequest();

        $view = new ViewModel();
        $loginForm = new LoginForm('loginForm');
        //$loginForm->setInputFilter(new LoginFilter());

        if ($request->isPost()) {
            $data = $request->getPost();
            $loginForm->setData($data);
echo "got POST";
            if($loginForm->isValid()) {
                $data = $loginForm->getData();
                echo "is Valid!";

                $authService = $this->getServiceLocator()->get('AuthService');

                $authService->getAdapter()
                    ->setIdentity($data['email'])
                    ->setCredential($data['password']);

                $result = $authService->authenticate();
                echo "now here";
                var_dump($authService); die();

            }

        }
        $view->setVariable('loginForm', $loginForm);
        return $view;
    }

}

?>