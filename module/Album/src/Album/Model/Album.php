<?php

namespace Album\Model;

use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class Album implements InputFilterAwareInterface
{
    /**
     * Table field names should be uppercase.
     **/
    public $IDENTITY_ID;
    public $ARTIST;
    public $TITLE;

    protected $inputFilter;

    public function exchangeArray($data)
    {
        $this->IDENTITY_ID      = (!empty($data['IDENTITY_ID'])) ? trim($data['IDENTITY_ID']) : null;
        $this->ARTIST           = (!empty($data['ARTIST'])) ? trim($data['ARTIST']) : null;
        $this->TITLE            = (!empty($data['TITLE'])) ? trim($data['TITLE']) : null;
    }

    public function getArrayCopy()
    {
        return get_object_vars($this);
    }

    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new \Exception("Not used");
    }

    public function getInputFilter()
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();

            $inputFilter->add(array(
                'name'     => 'IDENTITY_ID',
                'required' => true,
                'filters'  => array(
                    array('name' => 'Int'),
                ),
            ));

            $inputFilter->add(array(
                'name'     => 'ARTIST',
                'required' => true,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 1,
                            'max'      => 100,
                        ),
                    ),
                ),
            ));

            $inputFilter->add(array(
             'name'     => 'TITLE',
             'required' => true,
             'filters'  => array(
                 array('name' => 'StripTags'),
                 array('name' => 'StringTrim'),
             ),
             'validators' => array(
                 array(
                     'name'    => 'StringLength',
                     'options' => array(
                         'encoding' => 'UTF-8',
                         'min'      => 1,
                         'max'      => 100,
                     ),
                 ),
             ),
            ));

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }

}
