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
use Zend\Filter\Decrypt;
use Zend\Crypt\Symmetric\Mcrypt;

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

    protected function pkcs5_unpad($text) {
        $pad = ord($text{strlen($text) - 1});
        if ($pad > strlen($text)) return false;
        if (strspn($text, $text{strlen($text) - 1}, strlen($text) - $pad) != $pad) {
            return false;
        }
        return substr($text, 0, -1 * $pad);
    }

    /**
     * handle SSO login
     *
     * @return \Zend\Http\Response
     */
    public function ssoAction()
    {
        echo "<pre>";
$ciphers = openssl_get_cipher_methods();
//         $sslKey = 'DES3';
//         $toEncrypt = 'zcid=000000&t=Mon, 26 Sep 2016 18:56:09 GMT';
//         $blockCipher2 = new BlockCipher(new Mcrypt(array('algo' => '3des')));//, 'mode' => 'ncfb')));

//         /* set decrypt key */
//         $blockCipher2->setKey('85e88d473301ed963019bf2225cec1ac');
//         $encryptedText = $blockCipher2->encrypt($toEncrypt);
//         var_dump($encryptedText);

//         $xxx = openssl_encrypt ($toEncrypt, $sslKey, '85e88d473301ed963019bf2225cec1ac');
//         var_dump($xxx);
//         var_dump(base64_encode($xxx));
// echo "openssl above" . PHP_EOL;

$passphrase = 'I am so rich I wish I had a dime for every dime I have';
$iv = 'Phoenix1';
$ivHex = bin2hex($iv);
var_dump($ivHex);
        /* get encrypted data from query string */
        $encryptedDataBase64 = $this->params()->fromQuery('q');
// $encryptedDataBase64 = urldecode($encryptedDataBase64);
//         $encryptedDataBase64 = 'U2FsdGVkX1+evgjEdO3FHR7tlhNVKZcrQ75emBT7v5uzw4808w3pExQbouQH5vahnQNc9k2vCxGKsQ2/KUqaPA==';
//         $encryptedDataBase64 = 'U2FsdGVkX18qUQQRslM5wPUM8mfv/dW2Cq/4TaSc7Lt49zq8xlJLbES8XSEmPftr2Bt4UH9sRzkD27gDOlK89g==';
//         $encryptedDataBase64 = '0ae66798187950a831d886b871a90651678a1cb691d788031a38143c4f20479fQhGj5RhRMfpHjR9mzTRQ/PYtX/g5OclIlyRv+ZqG9hCkF8I1oocPZf1r0U/CWwUFRsz0/GOTU5g=';
//         $encryptedDataBase64 = $encryptedText;
// $encryptedDataBase64 = urldecode($encryptedDataBase64);
//         $test = base64_encode($encryptedDataBase64);
// $test=$encryptedDataBase64;
//         var_dump($encryptedDataBase64);
//         echo "LALALA" . PHP_EOL;
//         var_dump(openssl_decrypt($xxx, $sslKey, '85e88d473301ed963019bf2225cec1ac'));

        $falseKeys = [];
        foreach ($ciphers as $cyper) {
            $result = openssl_decrypt($encryptedDataBase64, $cyper, $passphrase, 0, $ivHex);
            if ($result === false) {
                $falseKeys[] = $cyper;
            } else {
                echo '----------- '.$cyper.' --------------'.PHP_EOL;
                var_dump($result);
                var_dump($this->pkcs5_unpad($result));
            }
        }
        var_dump($falseKeys);
// die();

//         $result = mcrypt_decrypt(MCRYPT_3DES, '85e88d473301ed963019bf2225cec1ac', $encryptedDataBase64, MCRYPT_MODE_CBC);
//         var_dump($result);


//         $td = mcrypt_module_open('tripledes', '', 'cbc', '');
//         $key = substr($passphrase, 0, mcrypt_enc_get_key_size($td));
//         $iv = mcrypt_create_iv (mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
//         mcrypt_generic_init($td, $passphrase, $iv);
//         $decrypted_data = mdecrypt_generic($td, $encryptedDataBase64);
$decrypted_data = mcrypt_decrypt(MCRYPT_3DES, $passphrase, $encryptedDataBase64, MCRYPT_MODE_CBC);
        echo "Decrypt: ".$decrypted_data;
//         mcrypt_generic_deinit($td);
//         mcrypt_module_close($td);

//         die();
// //         $encryptedDataBase64 = 'rLyFBkWywG4CchrXvAiOWv/eGElTuwwHfP5d8kFgOVgVuSdkxyRqvRUNHkyAU+Xeo2Ytt9ecnkE=';

// //         $urlDecoded = urldecode($encryptedDataBase64);
// //         var_dump($urlDecoded);

// //         $base64Decoded = base64_decode($encryptedDataBase64);
// //         var_dump($base64Decoded);
// //         $base64UrlDecoded = base64_decode($urlDecoded);
// //         var_dump($base64UrlDecoded);

        $filter = new Decrypt();
        $filter->setKey($passphrase);
        var_dump($filter->filter($encryptedDataBase64));
//         die();

die();
// $encryptedDataBase64 = base64_encode($encryptedDataBase64);
var_dump($encryptedDataBase64);

// die();
        /* redirect to login screen if no encrypted data available */
//         if ($encryptedDataBase64 == null) {
//             var_dump($encryptedDataBase64); die();
//             return $this->redirect()->toUrl( $this->getRequest()->getBaseUrl() . '/login/index' );
//         }

        /* initialize block cipher */
//         $blockCipher = BlockCipher::factory('mcrypt', array('algo' => '3des', 'mode' => 'CBC'));
        $blockCipher = new BlockCipher(new Mcrypt(array('algo' => 'des', 'mode' => 'cbc')));

        /* set decrypt key */
        $blockCipher->setKey($passphrase);

//         $test = $blockCipher->encrypt('Hello, world!');
//         var_dump($test); die();
        /* decrypt data */
//         $encryptedData = base64_decode($encryptedDataBase64);
        $decryptedData = $blockCipher->decrypt($encryptedDataBase64);
        var_dump($decryptedData);

//         $decryptedData = $blockCipher->decrypt($urlDecoded);
//         var_dump($decryptedData);

//         $decryptedData = $blockCipher->decrypt($base64UrlDecoded);
//         var_dump($decryptedData);

//         $decryptedData = $blockCipher->decrypt($base64Decoded);
//         var_dump($decryptedData); die();
        die();
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
