<?php
namespace Login\Form;

use Zend\Form\Form;
use Zend\Form\Element;
use Zend\Form\Element\Csrf;

class LoginForm extends Form
{
    public function __construct($name = null)
    {
        parent::__construct($name);
        $this->setAttribute('method', 'post');

        $this->add(array(
            'name' => 'username',
            'type' => 'text',
            'attributes' => array(
                'id' => 'username',
                'class' => 'form-control',
                'placeholder' => 'User ID'
            ),
            'options' => array(
                'label' => 'User ID'
            )
        ));

        $this->add(array(
            'name' => 'password',
            'type' => 'password',
            'attributes' => array(
                'id' => 'password',
                'class' => 'form-control',
                'placeholder' => 'Password'
            ),
            'options' => array(
                'label' => 'Password',
            )
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Csrf',
            'name' => 'loginCsrf',
            'options' => array(
                'csrf_options' => array(
                    'timeout' => 3600
                )
            )
        ));

        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Login',
                'class' => 'btn btn-form-primary'
            )
        ));
    }
}
