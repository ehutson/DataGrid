<?php

class Application_DataGrid
{

    const SORT_DIRECTION_ASC = 'asc';
    const SORT_DIRECTION_DESC = 'desc';

    /**
     * The datasource
     * 
     * @var Application_DataGrid_DataSource_Interface 
     */
    protected $dataSource;

    /**
     * The paginator to use
     * 
     * @var Zend_Paginator_Adapter_Interface
     */
    protected $pager;

    /**
     * The number of records to display on each page.
     * 
     * @var integer
     */
    protected $recordsPerPage = 20;

    /**
     * The columns
     * 
     * @var array
     */
    protected $columns = array();
    
    /**
     *
     * @var string
     */
    protected $defaultSortField = 'id';
    

    public function setRecordsPerPage($recordsPerPage)
    {
        $this->recordsPerPage = $recordsPerPage;
        return $this;
    }

    public function getRecordsPerPage()
    {
        return $this->recordsPerPage;
    }

    public function setDataSource(Application_DataGrid_DataSource_Interface $dataSource)
    {
        $this->dataSource = $dataSource;
        return $this;
    }

    public function getDataSource()
    {
        return $this->dataSource;
    }

    public function getPager()
    {
        return $this->pager;
    }

    public function setPager(Zend_Paginator_Adapter_Interface $pager)
    {
        $this->pager = $pager;
    }

    public function getColumns()
    {
        return $this->columns;
    }

    public function setColumns($columns)
    {
        $this->columns = $columns;
    }

    public function addColumn(Application_DataGrid_Column $column)
    {
        $this->columns[] = $column;
    }
    
    public function getDefaultSortField()
    {
        return $this->defaultSortField;
    }

    public function setDefaultSortField($defaultSortField)
    {
        $this->defaultSortField = $defaultSortField;
    }

    
    public function render()
    {
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $this->dataSource->setPage($request->getParam('page', 1));
        $this->dataSource->setSortField($request->getParam('sidx'));
        $this->dataSource->setSortOrder($request->getParam('sord'));
        $this->dataSource->setDefaultSortField($this->getDefaultSortField());
        
        
        $html = '<table>';
        
        
        /* Render the header... */
        
        $html .= '<tr>';
        /* @var $column Application_DataGrid_Column */
        foreach ($this->columns as $column)
        {
            $html .= '<th>' . $column->getHeaderText() . '</th>';
        }
        $html .= '</tr>';
        
        
        
        foreach ($this->dataSource->getResults() as $row)
        {
            $html .= '<tr>';
            foreach ($this->columns as $column)
            {
                if (array_key_exists($column->getDataField(), $row))
                {
                    $html .= '<td>' . $row[$column->getDataField()] . '</td>';
                }
            }
            $html .= '</tr>';
        }
        
        
        
        $html .= '</table>';
        
        return $html;
    }
    
    public function __toString()
    {
        $this->render();
    }
}