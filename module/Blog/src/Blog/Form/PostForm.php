<?php

namespace Blog\Form;

use Zend\Form\Form;
use Zend\Form\Element;
use Blog\Model\Post;
use Zend\Form\Fieldset;
use Zend\Stdlib\Hydrator\ClassMethods;

class PostForm extends Form
{
    public function __construct($name = null, $options = array())
    {
        parent::__construct($name, $options);

        $this->setHydrator(new ClassMethods(false));
        $this->setObject(new Post());
        $this->add(array(
            'type' => 'hidden',
            'name' => 'id'
        ));
        $this->add(array(
            'name' => 'title',
            'type' => 'text',
            'options' => array(
                'label' => 'Post Title'
            )
        ));
        $this->add(array(
            'name' => 'bodytext',
            'type' => 'textarea',
            'options' => array(
                'label' => 'Text'
            ),
            'attributes' => [
                'rows' => '4',
                'cols' => '70',
                'wrap' => 'hard',
                'maxlength' => '700',
                'autofocus' => 'true',
                'data-role' => 'none',
                'style' => 'background-color: #bbb;'
            ]
        ));
        $this->add(array(
            'type' => 'submit',
            'name' => 'submit',
            'attributes' => array(
                'value' => 'Insert new Post',
                'class' => 'btn btn-form-primary'
            )
        ));
    }

}
