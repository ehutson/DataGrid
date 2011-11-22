<?php

class Application_DataGrid_Column_HyperLink extends Application_DataGrid_Column
{
    /**
     * The list of possible targets 
     */
    const TARGET_LIST = array('_blank', '_parent', '_self', '_top');

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

    public function getDataUrlFormat()
    {
        return $this->dataUrlFormat;
    }

    public function setDataUrlFormat($dataUrlFormat)
    {
        $this->dataUrlFormat = $dataUrlFormat;
        return $this;
    }

    public function getStaticUrl()
    {
        return $this->staticUrl;
    }

    public function setStaticUrl($staticUrl)
    {
        $this->staticUrl = $staticUrl;
        return $this;
    }

    public function getStaticText()
    {
        return $this->staticText;
    }

    public function setStaticText($staticText)
    {
        $this->staticText = $staticText;
        return $this;
    }

    public function getTarget()
    {
        return $this->target;
    }

    public function setTarget($target)
    {
        $this->target = $target;
        return $this;
    }

    public function render($data)
    {
        $fieldData = '';
        if (isset($dsata[$this->getDataField]))
        {
            $fieldData = $data[$column->getDataField()];
        }
        
        if (isset($this->staticUrl))
        {
            $url = $this->staticUrl;
        }
        else
        {
            $url = str_replace('%%DATA_FIELD%%', $fieldData, $this->dataUrlFormat);
        }
        
        $text = $this->staticText;
        
        if (in_array(strtolower(($this->getTarget())), self::TARGET_LIST))
        {
            $target = strtolower($this->getTarget());
        }
        else
        {
            $target = '_self';
        }
        
        return '<a href="' . $url . '" target="' . $target . '">' . $text . '</a>';
    }
}
