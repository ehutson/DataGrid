<?php

class Application_DataGrid_Column_HyperLink extends Application_DataGrid_Column
{
    /**
     * The list of possible targets 
     */

    const TARGET_LIST = array('_blank', '_parent', '_self', '_top');

    /**
     * The field that this data is mapped to
     * 
     * @var string
     */
    protected $dataUrlField;

    /**
     * The formatting string used to compute how the navigation url of hyperlink will be displayed
     * @var string
     */
    protected $dataUrlFormat;

    /**
     * A statis URL to display
     * 
     * @var string
     */
    protected $staticUrl;

    /**
     * The statis text to diplay
     * 
     * @var string
     */
    protected $staticText;

    /**
     * The target of the url
     * 
     * @var type 
     */
    protected $target;

    public function getDataUrlField()
    {
        return $this->dataUrlField;
    }

    public function setDataUrlField($dataUrlField)
    {
        $this->dataUrlField = $dataUrlField;
    }

    public function getDataUrlFormat()
    {
        return $this->dataUrlFormat;
    }

    public function setDataUrlFormat($dataUrlFormat)
    {
        $this->dataUrlFormat = $dataUrlFormat;
    }

    public function getStaticUrl()
    {
        return $this->staticUrl;
    }

    public function setStaticUrl($staticUrl)
    {
        $this->staticUrl = $staticUrl;
    }

    public function getStaticText()
    {
        return $this->staticText;
    }

    public function setStaticText($staticText)
    {
        $this->staticText = $staticText;
    }

    public function getTarget()
    {
        return $this->target;
    }

    public function setTarget($target)
    {
        $this->target = $target;
    }

}
