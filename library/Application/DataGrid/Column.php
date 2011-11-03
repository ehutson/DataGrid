<?php

class Application_DataGrid_Column
{

    protected $allowSorting = true;
    protected $autoFillValue;
    protected $dataField;
    protected $formatCallback;
    protected $headerText;
    protected $visible = true;

    public function __construct($dataField = '', $headerText = '', $formatCallback = null)
    {
        $this->dataField = $dataField;
        $this->headerText = $headerText;
        $this->formatCallback = $formatCallback;
    }

    /**
     *
     * @return boolean 
     */
    public function getAllowSorting()
    {
        return $this->allowSorting;
    }

    /**
     *
     * @param boolean $allowSorting 
     */
    public function setAllowSorting($allowSorting)
    {
        $this->allowSorting = $allowSorting;
    }

    /**
     *
     * @return string 
     */
    public function getAutoFillValue()
    {
        return $this->autoFillValue;
    }

    /**
     *
     * @param string $autoFillValue 
     */
    public function setAutoFillValue($autoFillValue)
    {
        $this->autoFillValue = $autoFillValue;
    }

    /**
     *
     * @return string 
     */
    public function getDataField()
    {
        return $this->dataField;
    }

    /**
     *
     * @param string $dataField 
     */
    public function setDataField($dataField)
    {
        $this->dataField = $dataField;
    }

    /**
     *
     * @return Closure
     */
    public function getFormatCallback()
    {
        return $this->formatCallback;
    }

    /**
     *
     * @param Closure $formatCallback 
     */
    public function setFormatCallback($formatCallback)
    {
        $this->formatCallback = $formatCallback;
    }

    /**
     *
     * @return string 
     */
    public function getHeaderText()
    {
        return $this->headerText;
    }

    /**
     *
     * @param string $headerText 
     */
    public function setHeaderText($headerText)
    {
        $this->headerText = $headerText;
    }

    /**
     *
     * @return boolean
     */
    public function getIsVisible()
    {
        return $this->visible;
    }

    /**
     *
     * @param boolean $visible 
     */
    public function setIsVisible($visible)
    {
        $this->visible = $visible;
    }

}
