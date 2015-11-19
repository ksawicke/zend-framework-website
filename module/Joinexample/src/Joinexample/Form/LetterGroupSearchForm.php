<?php
namespace Joinexample\Form;
use Zend\Form\Form;

class LetterGroupSearchForm extends Form
{
    function __construct($name = null)
    {
        parent::__construct('lettergroupsearch');

        $this->add([
            'name' => 'groupnumber',
            'type' => 'text',
            'attributes' => array(
                'maxlength' => '5',
                'size' => '6',
            ),
            'options' => [
                'label' => 'Group Number',
            ],
        ]);

        $this->add([
            'name' => 'sectionnumber',
            'type' => 'text',
            'required' => false,
            'options' => [
                'label' => 'Section Number',
            ],

            'attributes' => array(
                'maxlength' => '3',
                'size' => '4',
            ),
        ]);

        $this->add([
            'name' => 'searchsubmit',
            'type' => 'submit',
            'attributes' => array(
                'value' => 'Search',
                'id' => 'submitButton',
            ),
        ]);

    }
}
