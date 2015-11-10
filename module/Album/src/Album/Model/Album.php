<?php

namespace Album\Model;

class Album
{
    public $identity_id;
    public $artist;
    public $title;

    public function exchangeArray($data)
    {
        $this->identity_id      = (!empty($data['IDENTITY_ID'])) ? $data['IDENTITY_ID'] : null;
        $this->artist           = (!empty($data['ARTIST'])) ? $data['ARTIST'] : null;
        $this->title            = (!empty($data['TITLE'])) ? $data['TITLE'] : null;
    }
}
