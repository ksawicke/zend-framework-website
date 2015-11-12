<?php

namespace Album\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Album\Model\Album;
use Album\Form\AlbumForm;

class AlbumController extends AbstractActionController
{
    protected $albumTable;

    public function indexAction()
    {
        return new ViewModel(array(
            'albums' => $this->getAlbumTable()->fetchAll(),
        ));
    }

    public function addAction()
    {
        $form = new AlbumForm();
        $form->get('submit')->setValue('Add');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $album = new Album();
            $form->setInputFilter($album->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $album->exchangeArray($form->getData());
                $this->getAlbumTable()->saveAlbum($album);

                // Redirect to list of albums
                return $this->redirect()->toRoute('album');
            }
        }
        return array('form' => $form);
    }

    public function editAction()
    {
        $IDENTITY_ID = (int) $this->params()->fromRoute('IDENTITY_ID', 0);
        if (!$IDENTITY_ID) {
            return $this->redirect()->toRoute('album', array(
                'action' => 'add'
            ));
        }

        // Get the Album with the specified id.  An exception is thrown
        // if it cannot be found, in which case go to the index page.
        try {
            $album = $this->getAlbumTable()->getAlbum($IDENTITY_ID);
        }
        catch (\Exception $ex) {
            return $this->redirect()->toRoute('album', array(
                'action' => 'index'
            ));
        }

        // var_dump($album);die("NO!");
        // object(Album\Model\Album)#282 (4) { ["IDENTITY_ID"]=> string(5) "10017" ["ARTIST"]=> string(100) "asdfasdfsdfsdf " ["TITLE"]=> string(100) "sdaf " ["inputFilter":protected]=> NULL } NO!

        $form  = new AlbumForm();
        $form->bind($album);
        $form->get('submit')->setAttribute('value', 'Edit');

        $request = $this->getRequest();
        if ($request->isPost()) {
            var_dump($_POST);die();
            $form->setInputFilter($album->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $this->getAlbumTable()->saveAlbum($album);

                // Redirect to list of albums
                return $this->redirect()->toRoute('album');
            }
        }

        return array(
            'identity_id' => $IDENTITY_ID,
            'form' => $form
        );
    }

    public function deleteAction()
    {
        $IDENTITY_ID = (int) $this->params()->fromRoute('IDENTITY_ID', 0);
        if (!$IDENTITY_ID) {
            return $this->redirect()->toRoute('album');
        }

        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('del', 'No');

            if ($del == 'Yes') {
                $IDENTITY_ID = (int) $request->getPost('IDENTITY_ID');
                $this->getAlbumTable()->deleteAlbum($IDENTITY_ID);
            }

            // Redirect to list of albums
            return $this->redirect()->toRoute('album');
        }

        return array(
            'IDENTITY_ID' => $IDENTITY_ID,
            'album' => $this->getAlbumTable()->getAlbum($IDENTITY_ID)
        );
    }

    public function getAlbumTable()
    {
        if (!$this->albumTable) {
            $sm = $this->getServiceLocator();
            $this->albumTable = $sm->get('Album\Model\AlbumTable');
        }

        return $this->albumTable;
    }

}
