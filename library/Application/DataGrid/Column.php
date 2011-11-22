<?php

class Application_DataGrid_Column
{

    /**
     * Can this column be sorted?
     * @var boolean
     */
    protected $allowSorting = true;

    /**
     * The value to put in the field if the datasource value is empty
     * @var string
     */
    protected $autoFillValue = '';

    /**
     * The field name
     * @var string
     */
    protected $dataField;

    /**
     * A Closure that can be used to do custom formatting on the column data
     * @var Closure
     */
    protected $formatCallback;

    /**
     * The text to show in the grid header for this field.  i.e. The column title.
     * @var string 
     */
    protected $headerText;

    /**
     * Is this column visible in the datagrid
     * @var boolean
     */
    protected $visible = true;

    /**
     * The width of the field.  Must specify as px or em, etc. 
     * @var string
     */
    protected $width;

    /**
     * The order that the column should appear in the grid
     * @var int
     */
    protected $order;

    /**
     *
     * @var Zend_View
     */
    protected $view;
    
    /**
     *
     * @param string $dataField
     * @param string $headerText
     * @param Closure $formatCallback 
     */
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
        return $this;
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
        return $this;
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
        return $this;
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
        return $this;
    }

    public function getOrder()
    {
        return $this->order;
    }

    public function setOrder($order)
    {
        $this->order = $order;
        return $this;
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
        return $this;
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
        return $this;
    }

    /**
     *
     * @return string
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     *
     * @param string $width
     * @return Application_DataGrid_Column 
     */
    public function setWidth($width)
    {
        $this->width = $width;
        return $this;
    }

    public function getView()
    {
        return $this->view;
    }

    public function setView($view)
    {
        $this->view = $view;
    }

        
    /**
     * Render the column. In this case, we only render the data for this column,
     * but the entire row is provided in case a future custom column type needs
     * the extra information
     * @param array $data
     * @return string
     */
    public function render($data)
    {
        if (isset($data[$this->getDataField()]))
        {
            return $data[$this->getDataField()];
        }
        return $this->autoFillValue;
    }

    /**
     * Returns a hash code value for the page
     *
     * @return string  a hash code value for this page
     */
    public final function hashCode()
    {
        return spl_object_hash($this);
    }

}
