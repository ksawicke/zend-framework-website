<?php
namespace Application\API;

use Zend\EventManager\EventManagerInterface;
use Zend\Mvc\Controller\AbstractRestfulController;

class ApiController extends AbstractRestfulController
{

    protected $allowedCollectionMethods = array(
        'POST',
        'GET'
    );

    protected $allowedResourceMethods = array(
        'POST',
        'GET'
    );

    public function setEventManager(EventManagerInterface $events)
    {
        parent::setEventManager($events);

        $this->events = $events;
        $events->attach('dispatch', array(
            $this,
            'checkOptions'
        ), 10);
    }

    public function checkOptions($e)
    {
        $matches = $e->getRouteMatch();
        $response = $e->getResponse();
        $request = $e->getRequest();
        $method = $request->getMethod();

        if ($matches->getParam('id', false)) {
            if (! in_array($method, $this->allowedResourceMethods)) {
                $response->setStatusCode(405);
                return $response;
            }
        }

        if (! in_array($method, $this->allowedCollectionMethods)) {
            $response->setStatusCode(405);
            return $response;
        }
        return;
    }

    public function options()
    {
        $response = $this->getResponse();

        $response->getHeaders()->addHeaderLine('Allow', implode(',', $this->_getOptions()));

        return $response;
    }

    public function _getOptions()
    {
        if ($this->params()->fromRoute('id', false)) {
            return $this->allowedResourceMethods;
        }

        return $this->allowedCollectionMethods;
    }

}