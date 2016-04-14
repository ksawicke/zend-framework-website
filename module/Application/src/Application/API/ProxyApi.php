<?php

/**
 * ProxyApi.php
 *
 * Proxy API
 *
 * API Handler for proxy submissions and actions
 *
 * PHP version 5
 *
 * @package    Application\API\ProxyApi
 * @author     Kevin Sawicke <kevin_sawicke@swifttrans.com>
 * @copyright  2016 Swift Transportation
 * @version    GIT: $Id$ In development
 */

namespace Application\API;

use Zend\View\Model\JsonModel;
use \Request\Model\EmployeeProxies;
use \Login\Helper\UserSession;
use \Application\Factory\EmailFactory;

/**
 * Handles API requests for the Time Off application.
 * 
 * @author sawik
 *
 */
class ProxyApi extends ApiController {
    
    public function loadProxiesAction()
    {
        try {
            $post = $this->getRequest()->getPost();
            $EmployeeProxies = new EmployeeProxies();
            $proxyData = $EmployeeProxies->getProxies( $post );
        
            $result = new JsonModel([
                'success' => true,
                'employeeNumber' => $post->EMPLOYEE_NUMBER,
                'proxyData' => $proxyData
            ]);
        } catch ( Exception $ex ) {
            $result = new JsonModel([
                'success' => false,
                'message' => 'There was an error adding a proxy for this employee number. Please try again.'
            ]);
        }        
        
        return $result;
    }
    
    /**
     * Submits new proxy request for an employee.
     */
    public function submitProxyRequestAction()
    {
        $post = $this->getRequest()->getPost();
        $EmployeeProxies = new EmployeeProxies();
        
        try {
            $EmployeeProxies->addProxy( $post );
        
            $result = new JsonModel([
                'success' => true,
                'employeeNumber' => $post->EMPLOYEE_NUMBER
            ]);
        } catch ( Exception $ex ) {
            $result = new JsonModel([
                'success' => false,
                'message' => 'There was an error adding a proxy for this employee number. Please try again.'
            ]);
        }        
        
        return $result;
    }
    
}