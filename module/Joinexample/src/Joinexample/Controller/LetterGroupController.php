<?php
namespace Joinexample\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Joinexample\Model\LetterGroupModel;
use Joinexample\Form\LetterGroupSearchForm;

class LetterGroupController extends AbstractActionController
{
    /**
     *
     * @var LetterGroupModel
     */
    protected $letterGroupModel = null;

    /**
     *
     * @var LetterGroupSearchForm
     */
    protected $letterGroupSearchForm = null;

    /**
     * The default action - show the letter groups table contents
     */
    public function indexAction()
    {
        $data = $this->letterGroupModel->fetchAll();
        return new ViewModel([
            'letterGroups' => $data,
            'searchForm' => $this->letterGroupSearchForm,
        ]);
    }

    public function createAction()
    {
        return new ViewModel();
    }

    public function retrieveAction()
    {
        $data = [];
        $request = $this->getRequest();
        if ($request->isPost()) {
            $groupNumber = str_pad($request->getPost('groupnumber'), 5);
            $sectionNUmber = str_pad($request->getPost('sectionnumber'), 3);
//            $data = $this->letterGroupModel->select(array('LGGRP' => $filter))->join()->toArray();
            $data = $this->letterGroupModel->fetchLWithLetterMonitor($groupNumber, $sectionNUmber);
        }
        return new ViewModel([
            'letterGroups' => $data,
            'searchForm' => $this->letterGroupSearchForm,
        ]);
    }

    public function updateAction()
    {
        return new ViewModel();
    }

    public function deleteAction()
    {
        return new ViewModel();
    }

    public function letterDetailAction()
    {
        $group = '55038';
        $section = '000';

        $letterDetail = $this->letterGroupModel->fetchJoinLetterMonitor($group, $section);
        return new ViewModel([
            'letterDetail' => $letterDetail,
        ]);
    }

    public function setLetterGroupModel(LetterGroupModel $model)
    {
        $this->letterGroupModel = $model;
    }

    public function setLetterGroupForm(LetterGroupSearchForm $form)
    {
        $this->letterGroupSearchForm = $form;
    }
}
