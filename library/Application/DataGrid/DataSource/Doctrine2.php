<?php

use Doctrine\ORM\Query;

class Application_DataGrid_DataSource_Doctrine2 implements Application_DataGrid_DataSource_Interface
{

    /**
     *
     * @var Query
     */
    protected $query;
    protected $page = 0;
    protected $resultsPerPage = 20;
    protected $sortField;
    protected $sortOrder;
    protected $defaultSortField = 'id';

    public function __construct($source)
    {
        switch ($source)
        {
            case ($source instanceof Doctrine\ORM\QueryBuilder) :
                $this->query = $source->getQuery();
                break;
            case ($source instanceof Doctrine\ORM\Query) :
                $this->query = $query;
                break;
            case ($source instanceof Doctrine\ORM\Entity\EntityRepository) :
                $this->query = $source->createQueryBuilder('al')->getQuery();
                break;
            default:
                throw new Application_DataGrid_Exception('Unknown source given, source must either be an entity, query, or querybuilder object.');
                break;
        }

        //$this->setColumns();
    }

    public function getPage()
    {
        return $this->page;
    }

    public function setPage($page)
    {
        $this->page = $page;
        return $this;
    }

    public function getResultsPerPage()
    {
        return $this->resultsPerPage;
    }

    public function setResultsPerPage($resultsPerPage)
    {
        $this->resultsPerPage = $resultsPerPage;
        return $this;
    }

    public function getSortField()
    {
        if (is_null($this->sortField))
        {
            return $this->getDefaultSortField();
        }
        return $this->sortField;
    }

    public function setSortField($sortField)
    {
        $this->sortField = $sortField;
        return $this;
    }

    public function getSortOrder()
    {
        return $this->sortOrder;
    }

    public function setSortOrder($sortOrder)
    {
        $this->sortOrder = $sortOrder;
        return $this;
    }

    public function getDefaultSortField()
    {
        return $this->defaultSortField;
    }

    public function setDefaultSortField($defaultSortField)
    {
        $this->defaultSortField = $defaultSortField;
        return $this;
    }

    private function setColumns()
    {
        //$this->columns = new Application_DataGrid_Column();

        $selectClause = $this->query->getAST()->selectClause;
        if (count($selectClause->selectExpressions) == 0)
        {
            throw new \Exception('The grid query should contain at least one column, none found.');
        }


        /* @var $selExpr Doctrine\ORM\Query\AST\SelectExpression */
        foreach ($selectClause->selectExpressions as $selExpr)
        {
            /**
             * Some magic needs to happen here.  If $selExpr isn't an instanceof
             * Doctrine\ORM\Query\AST\PathExpression then it might be a custom
             * expression like a vendor specific function.  We could use the
             * $selExpr->fieldIdentificationVariable member which is the alias
             * given to the special expression but sorting at this field could
             * cause string effects.
             * 
             * For instance:  SELEECT DATE_FORMAT(datefield, "%Y") AS alias FROM sometable
             * 
             * When you say:  ORDER BY alias DESC
             * 
             * You could get other results when you do:
             * 
             * ORDER BY sometable.datefield DESC
             * 
             * The idea is to rely on the alias field by default because it would
             * suite for most queries.  If someone would like to retrieve this expression
             * specific into they can add a field filter.  $ds->addFieldFilter(new DateFormat_Field_Filter(), 'expression classname');
             * 
             * And info of the field would be extracted from this class, something like that. 
             */
            $expr = $selExpr->expression;
            var_dump($selExpr);

            /* @var $expr Doctrine\ORM\Query\AST\PathExpression */
            if ($expr instanceof Doctrine\ORM\Query\AST\PathExpression)
            {
                $alias = $expr->identificationVariable;
                $name = ($selExpr->fieldIdentificationVariable === null) ? $expr->field : $selExpr->fieldIdentificationVariable;
                $label = ($selExpr->fieldIdentificationVariable === null) ? $name : $selExpr->fieldIdentificationVariable;
                $index = (strlen($alias) > 0 ? ($alias . '.') : '') . $name;
            }
            else
            {
                $name = $selExpr->fieldIdentificationVariable;
                $label = $name;
                $index = null;
            }


            echo "Name:  $name, Label:  $label, Index: $index<br>";
            //$this->columns->add($name, $label, $index);
        }
    }

    /**
     * Look to see if there is default sorting defined in the original query by
     * asking the AST.  Defining default sorting is done outside the datasource
     * where query or querybuilder object is defined.
     * 
     * @return array 
     */
    public function getDefaultSorting()
    {
        if (null !== $this->query->getAST()->orderByClause)
        {
            // support for 1 field only
            $orderByClause = $this->query->getAST()->orderByClause;

            /* @var $orderByItem Doctrine\ORM\Query\AST\OrderByItem */
            if ($orderByItem->expression instanceof \Doctrine\ORM\Query\AST\PathExpression)
            {
                $alias = $orderByItem->expression->identificationVariable;
                $field = $orderByItem->expression->field;

                $data['index'] = (strlen($alias) > 0 ? $alias . '.' : '') . $field;
                $data['direction'] = $orderByItem->type;

                return $data;
            }
        }

        return null;
    }

    public function getResults()
    {
        $hints = array();

        $page = $this->getPage();
        $limitPerPage = $this->getResultsPerPage();
        $offset = $limitPerPage * ($page - 1);

        $sortField = $this->getSortField();
        $sortOrder = (in_array($this->getSortOrder(), array('ASC', 'DESC')) ? strtoupper($this->getSortOrder()) : 'ASC');


        $hints[\Doctrine\ORM\Query::HINT_CUSTOM_TREE_WALKERS] = array('Application_DataGrid_DataSource_Doctrine2_OrderByWalker');
        $hints['sidx'] = $sortField;
        $hints['sord'] = $sortOrder;


        /* @var $paginateQuery \Doctrine\ORM\Query */
        $paginateQuery = Application_DataGrid_DataSource_Doctrine2_Paginate::getPaginateQuery($this->query, $offset, $limitPerPage, $hints);
        $results = $paginateQuery->getResult(Doctrine\ORM\Query::HYDRATE_ARRAY);
        return $results;
    }

    public function getCount()
    {
        return Application_DataGrid_DataSource_Doctrine2_Paginate::count($this->query);
    }

}

