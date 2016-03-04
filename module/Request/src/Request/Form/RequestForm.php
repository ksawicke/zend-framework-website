<?php
namespace Request\Form;

use Zend\Form\Form;
use Zend\Form\Element;
use Request\Model\Request;
use Zend\Form\Fieldset;
use Zend\Stdlib\Hydrator\ClassMethods;

class RequestForm extends Form
{

    public function __construct($name = null, $options = array())
    {
        parent::__construct($name, $options);

        $this->setHydrator(new ClassMethods(false));
        $this->setObject(new Request());
        $this->add([
            'type' => 'hidden',
            'name' => 'id'
        ]);
        $this->add([
            'name' => 'title',
            'type' => 'text',
            'options' => [
                'label' => 'Request Title'
            ],
            'attributes' => [
                'style' => 'background-color: #F7F3D9;'
            ]
            // 'autofocus' => 'true'

        ]);
        $this->add([
            'name' => 'bodytext',
            'type' => 'textarea',
            'options' => [
                'label' => 'Text'
            ],
            'attributes' => [
                'rows' => '4',
                'cols' => '70',
                'wrap' => 'hard',
                'maxlength' => '700',
                'data-role' => 'none',
                'style' => 'background-color: #F7F3D9;'
            ]
        ]);
        $this->add([
            'type' => 'submit',
            'name' => 'submit',
            'attributes' => [
                'value' => 'Insert new Post',
                'class' => 'btn btn-form-primary'
            ]
        ]);
    }
}
