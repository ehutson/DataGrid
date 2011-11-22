<?php

class Application_DataGrid_Formatter_CsvFormatter extends Application_DataGrid_Formatter_AbstractFormatter implements Application_DataGrid_Formatter_Interface
{

    /**
     * This formatter doesn't support pagination
     * We want CSV files to return all of the data...
     * @return boolean
     */
    public function allowPagination()
    {
        return false;
    }

    /**
     * Renders the data in CSV format
     * @param Application_DataGrid $datagrid
     * @return string
     */
    public function render(Application_DataGrid $datagrid)
    {

        $fp = fopen('php://output', 'w');

        $header = array();
        foreach ($datagrid->getColumns() as $column)
        {
            if ($column->getIsVisible())
            {
                $header[] = $column->getHeaderText();
            }
        }
        fputcsv($fp, $header);

        foreach ($datagrid->getDataSource()->getResults() as $row)
        {
            $data = array();
            foreach ($datagrid->getColumns() as $column)
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

                    $data[] = $fieldData;
                }
            }
            fputcsv($fp, $data);
        }

        $csv = fgets($fp);
        fclose($fp);

        return $csv;
    }

}