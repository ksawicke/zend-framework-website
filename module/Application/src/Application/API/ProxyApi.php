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
use \Request\Model\Employee;
use \Request\Model\EmployeeSchedules;
use \Request\Model\TimeOffRequestLog;
use \Request\Model\TimeOffRequests;
use \Request\Model\RequestEntry;
use \Request\Model\Papaatmp;
use \Request\Helper\OutlookHelper;
use \Request\Helper\ValidationHelper;
use \Login\Helper\UserSession;
use \Application\Factory\EmailFactory;

/**
 * Handles API requests for the Time Off application.
 * 
 * @author sawik
 *
 */
class ProxyApi extends ApiController {
    
    /**
     * Submits new proxy request for an employee.
     */
    public function submitProxyRequestAction()
    {
        /** Clean up / append data to the Request **/
        $post = $this->getRequest()->getPost();
        
        $result = new JsonModel([
            'success' => true,
            'post' => $post
        ]);
        
        return $result;
    }
    
}