<?php

namespace Simpler\Controller;

use Zend\Mvc\Controller\AbstractActionController;

class SomeController extends AbstractActionController
{

    public function indexAction()
    {
        $this->getServiceLocator()->get('Simpler\Model\Example')->exampleMethod();
    }

}
