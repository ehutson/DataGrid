<?php

abstract class Application_DataGrid_Formatter_AbstractFormatter
{

    protected $view;

    public function getView()
    {
        return $this->view;
    }

    public function setView($view)
    {
        $this->view = $view;
    }

}