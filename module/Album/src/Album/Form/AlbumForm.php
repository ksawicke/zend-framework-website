<?php

namespace Album\Form;

use Zend\Form\Form;

class AlbumForm extends Form
{
    public function __construct($name = null)
    {
     // we want to ignore the name passed
     parent::__construct('ALBUM');

     $this->add(array(
         'name' => 'IDENTITY_ID',
         'type' => 'Hidden',
     ));
     $this->add(array(
         'name' => 'TITLE',
         'type' => 'Text',
         'options' => array(
             'label' => 'Title',
         ),
     ));
     $this->add(array(
         'name' => 'ARTIST',
         'type' => 'Text',
         'options' => array(
             'label' => 'Artist',
         ),
     ));
     $this->add(array(
         'name' => 'submit',
         'type' => 'Submit',
         'attributes' => array(
             'value' => 'ADD',
             'id' => 'submitbutton',
         ),
     ));
    }
}
