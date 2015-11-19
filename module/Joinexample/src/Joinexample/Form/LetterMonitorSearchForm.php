<?php
namespace Joinexample\Form;
use Zend\Form\Form;

class LetterMonitorSearchForm extends Form
{
    function __construct($name = null)
    {
        parent::__construct('lettermonitorsearch');

        $this->add([
            'name' => 'groupnumber',
            'type' => 'text',
            'attributes' => array(
                'maxlength' => '5',
                'size' => '6',
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
