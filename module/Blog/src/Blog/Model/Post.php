<?php

namespace Blog\Model;

class Post implements PostInterface
{
    /**
     * @var int
     */
    protected $ID;

    /**
     * @var string
     */
    protected $TITLE;

    /**
     * @var string
     */
    protected $TEXT;

    /**
     * {@inheritDoc}
     */
    public function getId()
    {
        return $this->ID;
    }

    /**
    * @param int $id
    */
    public function setId($ID)
    {
        $this->ID = $ID;
    }

    /**
    * {@inheritDoc}
    */
    public function getTitle()
    {
        return $this->TITLE;
    }

    /**
    * @param string $title
    */
    public function setTitle($TITLE)
    {
        $this->TITLE = $TITLE;
    }

    /**
    * {@inheritDoc}
    */
    public function getText()
    {
        return $this->TEXT;
    }

    /**
    * @param string $text
    */
    public function setText($TEXT)
    {
        $this->TEXT = $TEXT;
    }
}
