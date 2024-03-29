<?php

use Doctrine\ORM\Query;

class Application_DataGrid_DataSource_Doctrine2_PaginationAdapter implements \Zend_Paginator_Adapter_Interface
{

    /**
     * The SELECT query to paginate
     * 
     * @var Query 
     */
    protected $query = null;

    /**
     * Total Item Count
     * 
     * @var type 
     */
    protected $rowCount = null;

    /**
     * Return results as an array
     * @var boolean 
     */
    protected $arrayResult = false;

    /**
     * Namespace to use for bound parameters
     * If you use :pgid_# as a parameter, then
     * you must change this.
     * 
     * @var string 
     */
    protected $namespace = 'pgid';
    protected $whereInHints = array();

    /**
     * Constructor
     * 
     * @param Query $query
     * @param type $ns Namespace to prevent named parameter conflicts
     */
    public function __construct(Query $query, $ns = 'pgid')
    {
        $this->query = $query;
        $this->namespace = $ns;
    }

    /**
     * Set use array result flag
     * 
     * @param boolean $flag 
     */
    public function useArrayResult($flag = true)
    {
        $this->arrayResult = $flag;
    }

    /**
     * Sets the total row count for this paginator
     * 
     * Can be either an integer, or a Doctrine\ORM\Query object
     * which returns a count.
     * 
     * @param Query|integer $rowCount
     * @throws \InvalidArgumentException 
     */
    public function setRowCount($rowCount)
    {
        if ($rowCount instanceof Query)
        {
            $this->rowCount = $rowCount->getSingleScalarResult();
        }
        else if (is_integer($rowCount))
        {
            $this->rowCount = $rowCount;
        }
        else
        {
            throw new \InvalidArgumentException("Invalid row count");
        }
    }

    /**
     * Sets the namespace to be used for named parameters
     * 
     * Parameters will be in the format 'namespace_1' ... 'namespace_N'
     * 
     * @param string $ns 
     */
    public function setNamespace($ns)
    {
        $this->namespace = $ns;
    }

    public function setWhereInHints(array $hints = array())
    {
        $this->whereInHints = $hints;
    }

    /**
     *
     * @return int 
     */
    public function count()
    {
        if (is_null($this->rowCount))
        {
            $this->setRowCount($this->createCountQuery());
        }
        return $this->rowCount;
    }

    /**
     * Gets the current page of items
     * 
     * @param iny $offset
     * @param int $itemCountPerPage
     * @return void 
     */
    public function getItems($offset, $itemCountPerPage)
    {
        $ids = $this->createLimitSubquery($offset, $itemCountPerPage)->getScalarResult();
        $ids = array_map(function($e)
                {
                    return current($e);
                }, $ids);

        if ($this->arrayResult)
        {
            return $this->createWhereInQuery($ids)->getArrayResult();
        }
        else
        {
            return $this->createWhereInQuery($ids)->getResults();
        }
    }

    /**
     *
     * @return Query
     */
    protected function createCountQuery()
    {
        return Application_DataGrid_DataSource_Doctrine2_Paginate::createCountQuery($this->query);
    }

    /**
     *
     * @param int $offset
     * @param int $itemCountPerPage
     * @return Query 
     */
    protected function createLimitSubquery($offset, $itemCountPerPage)
    {
        return Application_DataGrid_DataSource_Doctrine2_Paginate::createLimitSubquery($this->query, $offset, $itemCountPerPage);
    }

    /**
     *
     * @param type $ids
     * @return Query 
     */
    protected function createWhereInQuery($ids)
    {
        return Application_DataGrid_DataSource_Doctrine2_Paginate::createWhereInQuery($this->query, $ids, $this->namespace, $this->whereInHints);
    }

}

