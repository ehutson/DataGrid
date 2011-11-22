<?php

class Application_DataGrid_Formatter_HtmlFormatter extends Application_DataGrid_Formatter_AbstractFormatter implements Application_DataGrid_Formatter_Interface
{

    /**
     * This formatter supports pagination
     * @return boolean
     */
    public function allowPagination()
    {
        return true;
    }

    /**
     * Renders the DataGrid in HTML format
     * @param Application_DataGrid $datagrid
     * @return string
     */
    public function render(Application_DataGrid $datagrid)
    {
        $columns = $datagrid->getColumns();
        $html = '<div class="grid-container"><table class="common-table zebra-striped"';

        if (!is_null($datagrid->getWidth()))
        {
            $html .= ' style="width:' . $datagrid->getWidth() . ';" ';
        }
        $html .= '>';
        /* Render the header... */
        $html .= '<thead><tr>';
        /* @var $column Application_DataGrid_Column */
        foreach ($columns as $column)
        {
            if ($column->getIsVisible())
            {
                $html .= $this->_buildHeaderField($column, $datagrid->getDataSource()->getSortField(), $datagrid->getDataSource()->getSortOrder());
            }
        }
        $html .= '</tr></thead><tbody>';


        /* Render the body of the table */
        foreach ($datagrid->getDataSource()->getResults() as $row)
        {
            $html .= '<tr>';
            /* @var $column Application_DataGrid_Column */
            foreach ($columns as $column)
            {
                if ($column->getIsVisible())
                {
                    $callback = $column->getFormatCallback();

                    if (!is_null($callback) && is_callable($callback))
                    {
                        $fieldData = $callback($row, $row[$column->getDataField()]);
                    }
                    else
                    {
                        $fieldData = $column->render($row);
                    }

                    $html .= '<td>' . $fieldData . '</td>';
                }
            }
            $html .= '</tr>';
        }

        $html .= '</tbody></table></div>';

        /* Add the pagination controls to the html */
        $html .= '<div class="pagination-wrapper">';
        $html .= $datagrid->getView()->paginationControl($datagrid->getDataSource()->getPaginator(), 'Sliding', 'pagination_control.phtml');
        $html .= '</div>';
        return $html;
    }

    /**
     *
     * @param Application_DataGrid_Column $column
     * @param string $sortField
     * @param string $sortOrder 
     */
    protected function _buildHeaderField($column, $sortField, $sortOrder)
    {
        if ($column->getAllowSorting())
        {
            $html = '<th';
            if (!is_null($column->getWidth()))
            {
                $html .= ' style="width:' . $column->getWidth() . ';"';
            }
            $html .= ' class="header ';
            if ($sortField == $column->getDataField())
            {
                // we're sorting this field
                if (strtolower($sortOrder) == Application_DataGrid::SORT_DIRECTION_ASC)
                {
                    $sortDir = Application_DataGrid::SORT_DIRECTION_DESC;
                    $sortClass = 'headerSortDown';
                }
                else
                {
                    $sortDir = Application_DataGrid::SORT_DIRECTION_ASC;
                    $sortClass = 'headerSortUp';
                }
            }
            else
            {
                $sortDir = Application_DataGrid::SORT_DIRECTION_ASC;
                $sortClass = '';
            }

            $html .= $sortClass . '"><a href="' . $this->buildSortUrl($column, $sortDir) . '">' . $column->getHeaderText() . '</a></th>';
        }
        else
        {
            $html = '<th>' . $column->getHeaderText() . '</th>';
        }
        return $html;
    }

    /**
     *
     * @param Application_DataGrid_Column $column
     * @param string $sortOrder 
     */
    protected function buildSortUrl($column, $sortOrder)
    {
        $params = Zend_Controller_Front::getInstance()->getRequest()->getParams();
        $params['sidx'] = $column->getDataField();
        $params['sord'] = $sortOrder;

        $router = Zend_Controller_Front::getInstance()->getRouter();
        return $router->assemble($params, null, true, true);
    }

}