<?php
namespace Application\Model;

/**
 *
 * @author faecg
 *
 */
class DataTableInquiry
{
    protected $draw;

    protected $order;

    protected $search;

    protected $start;

    protected $length;

    protected $columns;

    protected $searchFromDate;

    protected $searchToDate;

    public function initialize( $inputData )
    {
        $this->setDraw($inputData->draw);
        $this->setOrder($inputData->order);
        $this->setSearch($inputData->search);
        $this->setStart($inputData->start);
        $this->setLength($inputData->length);
        $this->setColumns($inputData->columns);
        $this->setSearchFromDate($inputData->searchFromDate);
        $this->setSearchToDate($inputData->searchToDate);
    }

    public function increaseDraw()
    {
        $this->setDraw($this->getDraw() + 1);
        return $this;
    }

    /**
     * @return the $draw
     */
    public function getDraw()
    {
        return $this->draw;
    }

    /**
     * @return the $order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @return the $search
     */
    public function getSearch()
    {
        return $this->search;
    }

    /**
     * @return the $start
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * @return the $length
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * @return the $columns
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * @return the $searchFromDate
     */
    public function getSearchFromDate()
    {
        return $this->searchFromDate;
    }

    /**
     * @return the $searchToDate
     */
    public function getSearchToDate()
    {
        return $this->searchToDate;
    }

    /**
     * @param field_type $draw
     */
    public function setDraw($draw)
    {
        $this->draw = $draw;

        return $this;
    }

    /**
     * @param field_type $order
     */
    public function setOrder($order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * @param field_type $search
     */
    public function setSearch($search)
    {
        $this->search = $search;

        return $this;
    }

    /**
     * @param field_type $start
     */
    public function setStart($start)
    {
        $this->start = $start;

        return $this;
    }

    /**
     * @param field_type $length
     */
    public function setLength($length)
    {
        $this->length = $length;

        return $this;
    }

    /**
     * @param field_type $columns
     */
    public function setColumns($columns)
    {
        $this->columns = $columns;

        return $this;
    }

    /**
     * @param field_type $searchFromDate
     */
    public function setSearchFromDate($searchFromDate)
    {
        $this->searchFromDate = $searchFromDate;

        return $this;
    }

    /**
     * @param field_type $searchToDate
     */
    public function setSearchToDate($searchToDate)
    {
        $this->searchToDate = $searchToDate;

        return $this;
    }

}
