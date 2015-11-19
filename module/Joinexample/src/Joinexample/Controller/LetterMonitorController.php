<?php
namespace Joinexample\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Joinexample\Model\LetterMonitorModel;
use Joinexample\Form\LetterMonitorSearchForm;

class LetterMonitorController extends AbstractActionController
{
    /**
     *
     * @var LetterMonitorModel
     */
    protected $letterMonitorModel = null;

    /**
     *
     * @var LetterMonitorSearchForm
     */
    protected $letterMonitorSearchForm = null;


    /**
     * The default action - show the home page
     */
    public function indexAction()
    {
//        var_dump($this->params()->fromQuery()); exit;
        $paginator = $this->letterMonitorModel->fetchAll();
        $paginator->setCurrentPageNumber((int) $this->params()->fromRoute('page', 1));
//        $paginator->setCurrentPageNumber(10);
        return new ViewModel([
            'paginator' => $paginator,
            'searchForm' => $this->letterMonitorSearchForm,
        ]);
    }

    public function createAction()
    {
        var_dump($this->params()->fromQuery()); exit;
        return new ViewModel();
    }

    public function retrieveAction()
    {
        $data = [];
        $request = $this->getRequest();
        if ($request->isPost()) {
            $filter = str_pad($request->getPost('groupnumber'), 5);
            $data = $this->letterMonitorModel->select(array('LMGRP' => $filter))->toArray();
        }
        return new ViewModel([
            'letterMonitor' => $data,
            'searchForm' => $this->letterMonitorSearchForm,
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

    public function setLetterMonitorModel(LetterMonitorModel $model)
    {
        $this->letterMonitorModel = $model;
    }

    public function setLetterMonitorForm(LetterMonitorSearchForm $form)
    {
        $this->letterMonitorSearchForm = $form;
    }

}
