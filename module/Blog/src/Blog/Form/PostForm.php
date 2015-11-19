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

//        $this->add(array(
//            'name' => 'post-fieldset',
//            'type' => 'Blog\Form\PostFieldset',
//            'options' => array(
//                'use_as_base_fieldset' => true
//            )
//        ));
        $this->setHydrator(new ClassMethods(false));
        $this->setObject(new Post());
        $this->add(array(
            'type' => 'hidden',
            'name' => 'id'
        ));
        $this->add(array(
            'type' => 'text',
            'name' => 'title',
            'options' => array(
                'label' => 'Post Title'
            )
        ));
        $this->add(array(
            'type' => 'textarea',
            'name' => 'bodytext',
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
