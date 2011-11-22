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
     *
     * @var Application_DataGrid_Formatter_Interface
     */
    protected $formatter;

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

    /**
     *
     * @var int
     */
    protected $defaultSortOrder = self::SORT_DIRECTION_ASC;

    /**
     * Column Width
     * 
     * @var string
     */
    protected $width;

    /**
     * An index that contains the order in which to iterate columns
     *
     * @var array
     */
    protected $index = array();

    /**
     * Whether index is dirty and needs to be re-arranged
     *
     * @var bool
     */
    protected $dirtyIndex = false;

    /**
     *
     * @var Zend_View
     */
    protected $view;

    public function __construct(Zend_View $view, Application_DataGrid_DataSource_Interface $dataSource = null)
    {
        $this->view = $view;

        // Add a local path for the paginator view partial
        $path = realpath(APPLICATION_PATH . '/../library/App/DataGrid/View/Partial/');
        $this->view->addScriptPath($path);

        if (!is_null($dataSource))
        {
            $this->dataSource = $dataSource;
        }
    }

    /**
     * Sets the number of records to show per page
     * @param integer $recordsPerPage
     * @return Application_DataGrid 
     */
    public function setRecordsPerPage($recordsPerPage)
    {
        $this->recordsPerPage = $recordsPerPage;
        return $this;
    }

    /**
     * Gets the number of records to show per page
     * @return integer
     */
    public function getRecordsPerPage()
    {
        return $this->recordsPerPage;
    }

    /**
     * Gets with total width of the grid
     * @return string
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * Sets the total width of the grid
     * @param string $width
     * @return Application_DataGrid_Column 
     */
    public function setWidth($width)
    {
        $this->width = $width;
        return $this;
    }

    /**
     * Sets the datasource
     * @param Application_DataGrid_DataSource_Interface $dataSource
     * @return Application_DataGrid 
     */
    public function setDataSource(Application_DataGrid_DataSource_Interface $dataSource)
    {
        $this->dataSource = $dataSource;
        return $this;
    }

    /**
     * Returns the DataSource used to fetch the data
     * @return Application_DataGrid_DataSource_Interface
     */
    public function getDataSource()
    {
        return $this->dataSource;
    }

    /**
     * Gets the output formatter (eg. HTML, CSV, etc.)
     * @return Application_DataGrid_Formatter_Interface
     */
    public function getFormatter()
    {
        if (!isset($this->formatter))
        {
            $this->formatter = new Application_DataGrid_Formatter_HtmlFormatter();
        }
        return $this->formatter;
    }

    /**
     * Sets the output formatter (eg. HTML, CSV, etc.)
     * @param Application_DataGrid_Formatter_Interface $formatter
     * @return Application_DataGrid 
     */
    public function setFormatter(Application_DataGrid_Formatter_Interface $formatter)
    {
        $this->formatter = $formatter;
        return $this;
    }

    /**
     * Autogenerate the columns based off the SELECT data in the QueryBuilder
     */
    public function autoGenerateColumns()
    {
        if (!is_null($this->dataSource))
        {
            $this->setColumns($this->dataSource->generateColumns());
        }
    }

    /**
     * Returns an ordered list of columns in the DataGrid
     * @see Application_DataGrid_Column
     * @return array
     */
    public function getColumns()
    {
        $columns = array();
        $this->dirtyIndex = true;
        $this->sort();
        $indexes = array_keys($this->index);
        foreach ($indexes as $hash)
        {
            $columns[] = $this->columns[$hash];
        }
        return $columns;
    }

    /**
     * Sets the list of columns used in the DataGrid
     * @see Application_DataGrid_Column
     * @param array $columns 
     */
    public function setColumns(array $columns)
    {
        foreach ($columns as $column)
        {
            $this->addColumn($column);
        }
        return $this;
    }

    /**
     * Add a single column to the list of columns used in the DataGrid
     * @see Application_DataGrid_Column
     * @param Application_DataGrid_Column $column 
     */
    public function addColumn(Application_DataGrid_Column $column)
    {
        $column->setView($this->view);
        $hash = $column->hashCode();

        if (array_key_exists($hash, $this->index))
        {
            // page is already in container
            return $this;
        }

        // adds column to container and sets dirty flag
        $this->columns[$hash] = $column;
        $this->index[$hash] = $column->getOrder();
        $this->dirtyIndex = true;
    }

    /**
     * Gets the column corresponding to the supplied field name
     * @see Application_DataGrid_Column
     * @param string $fieldName
     * @return Application_DataGrid_Column
     */
    public function getColumn($fieldName)
    {
        foreach ($this->columns as $column)
        {
            if ($column->getDataField() == $fieldName)
            {
                return $column;
            }
        }
        return null;
    }

    /**
     * Returns the default field that the datagrid will be sorted on
     * @return string
     */
    public function getDefaultSortField()
    {
        return $this->defaultSortField;
    }

    /**
     * Sets the default field that the datagrid wil be sorted on
     * @param string $defaultSortField 
     */
    public function setDefaultSortField($defaultSortField)
    {
        $this->defaultSortField = $defaultSortField;
    }

    /**
     * Returns the default sort order (asc, desc)
     * @return string
     */
    public function getDefaultSortOrder()
    {
        return $this->defaultSortOrder;
    }

    /**
     * Sets the default sort order (asc, desc)
     * @param string $defaultSortOrder 
     */
    public function setDefaultSortOrder($defaultSortOrder)
    {
        $this->defaultSortOrder = $defaultSortOrder;
    }

    /**
     * Get the view instance
     * @return Zend_View
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * Set the view instance
     * @param Zend_View $view
     * @return Application_DataGrid 
     */
    public function setView($view)
    {
        $this->view = $view;
        return $this;
    }

    /**
     * Returns the rendered grid paginated and in the specified format
     * @see Application_DataGrid_Formatter_Interface
     * @return string 
     */
    public function render()
    {
        $formatter = $this->getFormatter();
        $formatter->setView($this->view);

        if ($formatter->allowPagination())
        {
            $request = Zend_Controller_Front::getInstance()->getRequest();
            $sortField = $request->getParam('sidx', $this->getDefaultSortField());
            $sortOrder = $request->getParam('sord', $this->getDefaultSortOrder());

            $this->dataSource->setPage($request->getParam('page', 1));
            $this->dataSource->setSortField($sortField);
            $this->dataSource->setSortOrder($sortOrder);
            $this->dataSource->setResultsPerPage($this->recordsPerPage);
        }
        else
        {
            $this->dataSource->setPage(1);
            $this->dataSource->setSortField($this->getDefaultSortField());
            $this->dataSource->setSortOrder($this->getDefaultSortOrder());
            $this->dataSource->setResultsPerPage(-1);
        }

        return $formatter->render($this);
    }

    /**
     * Calls Application_DataGrid::render()
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }

    /**
     * Create the column index used to order the columns in the datagrid
     */
    protected function sort()
    {
        if ($this->dirtyIndex)
        {
            $newIndex = array();
            $index = 0;

            foreach ($this->columns as $hash => $column)
            {
                $order = $column->getOrder();
                if ($order === null)
                {
                    $newIndex[$hash] = $index;
                    $index++;
                }
                else
                {
                    $newIndex[$hash] = $order;
                }
            }

            asort($newIndex);
            $this->index = $newIndex;
            $this->dirtyIndex = false;
        }
    }

}