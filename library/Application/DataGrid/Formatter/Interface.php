<?php

interface Application_DataGrid_Formatter_Interface
{

    /**
     * Renders the data in a custom format
     * @var Application_DataGrid $datagrid
     */
    public function render(Application_DataGrid $datagrid);
    
    /**
     * Does this formatter support pagination?
     */
    public function allowPagination();
}
