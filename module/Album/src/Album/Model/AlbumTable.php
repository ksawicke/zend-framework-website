<?php

namespace Album\Model;

use Zend\Db\TableGateway\TableGateway;

class AlbumTable
{
    protected $tableGateway;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function fetchAll()
    {
        $resultSet = $this->tableGateway->select();
        return $resultSet;
    }

    public function getAlbum($IDENTITY_ID)
    {
        $IDENTITY_ID  = (int) $IDENTITY_ID;
        $rowset = $this->tableGateway->select(array('IDENTITY_ID' => $IDENTITY_ID));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $IDENTITY_ID");
        }
        return $row;
    }

    public function saveAlbum(Album $album)
    {
        $data = array(
            'artist' => $album->ARTIST,
            'title'  => $album->TITLE,
        );

        $IDENTITY_ID = (int) $album->IDENTITY_ID;
        if ($IDENTITY_ID == 0) {
            $this->tableGateway->insert($data);
        } else {
            if ($this->getAlbum($IDENTITY_ID)) {
                $this->tableGateway->update($data, array('IDENTITY_ID' => $IDENTITY_ID));
            } else {
                throw new \Exception('Album identity_id does not exist');
            }
        }
    }

    public function deleteAlbum($IDENTITY_ID)
    {
        $this->tableGateway->delete(array('IDENTITY_ID' => (int) $IDENTITY_ID));
    }
}
