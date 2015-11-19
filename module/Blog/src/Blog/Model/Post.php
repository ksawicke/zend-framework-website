<?php

namespace Blog\Model;

class Post implements PostInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $bodytext;

    /**
     * {@inheritDoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
    * @param int $id
    */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
    * {@inheritDoc}
    */
    public function getTitle()
    {
        return $this->title;
    }

    /**
    * @param string $title
    */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
    * {@inheritDoc}
    */
    public function getBodytext()
    {
        return $this->bodytext;
    }

    /**
    * @param string $bodytext
    */
    public function setBodytext($bodytext)
    {
        $this->bodytext = $bodytext;
    }
}
