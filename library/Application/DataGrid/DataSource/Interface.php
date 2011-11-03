<?php

interface Application_DataGrid_DataSource_Interface
{

    public function getPage();

    public function setPage($page);

    public function getResultsPerPage();

    public function setResultsPerPage($resultsPerPage);

    public function getSortField();

    public function setSortField($sortField);

    public function getSortOrder();

    public function setSortOrder($sortOrder);

    public function getDefaultSortField();

    public function setDefaultSortField($defaultSortField);

    public function getResults();

    public function getCount();
}
