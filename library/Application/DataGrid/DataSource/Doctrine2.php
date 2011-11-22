<?php

use Doctrine\ORM\Query;
use Doctrine\ORM\Query\AST;

class Application_DataGrid_DataSource_Doctrine2 implements Application_DataGrid_DataSource_Interface
{

    /**
     * The Doctrine2 Query Builder
     * @var Query
     */
    protected $queryBuilder;

    /**
     * The Doctrine2 Entity Manager
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;

    /**
     *
     * @var Zend_Paginator
     */
    protected $paginator;

    /**
     * The page number we're currently on
     * @var integer
     */
    protected $page = 0;

    /**
     * The number of results to show per page
     * @var integer
     */
    protected $resultsPerPage = 10;

    /**
     * The column name that we're sorting on
     * @var string
     */
    protected $sortField;

    /**
     * The order that we're sorting (asc or desc)
     * @var string
     */
    protected $sortOrder;

    /**
     * An internal container for information about the fields being returned
     * by the SELECT clause in the query builder
     * @var array
     */
    protected $fields = array();

    /**
     *
     * @param Doctrine\ORM\EntityManager $entityManager
     * @param Doctrine\ORM\QueryBuilder $source 
     */
    public function __construct(Doctrine\ORM\EntityManager $entityManager, Doctrine\ORM\QueryBuilder $source)
    {
        $this->entityManager = $entityManager;
        $this->queryBuilder = $source;
        $this->setFields();
    }

    /**
     * Returns the Zend Paginator instance
     * @return Zend_Paginator
     */
    public function getPaginator()
    {
        return $this->paginator;
    }

    /**
     * Returns the current page number
     * @return type 
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * Sets the current page number
     * @param integer $page
     * @return Application_DataGrid_DataSource_Doctrine2 
     */
    public function setPage($page)
    {
        $this->page = $page;
        return $this;
    }

    /**
     * Gets the number of results shown per page
     * @return integer
     */
    public function getResultsPerPage()
    {
        return $this->resultsPerPage;
    }

    /**
     * Sets the number of results shown per page
     * @param integer $resultsPerPage
     * @return Application_DataGrid_DataSource_Doctrine2 
     */
    public function setResultsPerPage($resultsPerPage)
    {
        $this->resultsPerPage = $resultsPerPage;
        return $this;
    }

    /**
     * Gets the field name that we're sorting on.
     * This should have the same name as the dataField in the corresponding Column
     * @see Application_DataGrid_Column
     * @return string 
     */
    public function getSortField()
    {
        return $this->sortField;
    }

    /**
     * Sets the field name that we're sorting on.
     * This should have the same name as the dataField in the corresponding Column
     * @see Application_DataGrid_Column
     * @param string $sortField
     * @return Application_DataGrid_DataSource_Doctrine2 
     */
    public function setSortField($sortField)
    {
        $this->sortField = $sortField;
        return $this;
    }

    /**
     * Gets the sort order (asc or desc)
     * @see Application_DataGrid
     * @return string
     */
    public function getSortOrder()
    {
        return $this->sortOrder;
    }

    /**
     * Sets the sort order (asc or desc)
     * @see Application_DataGrid
     * @param string $sortOrder
     * @return Application_DataGrid_DataSource_Doctrine2 
     */
    public function setSortOrder($sortOrder)
    {
        $this->sortOrder = $sortOrder;
        return $this;
    }

    public function generateColumns()
    {
        $columns = array();
        $fields = $this->getFields();
        foreach ($fields as $key => $field)
        {
            $columns[] = new Application_DataGrid_Column($key, $field['title']);
        }
        return $columns;
    }

    /**
     * Returns the internal fields container
     * @return array
     */
    public function getFields()
    {
        if (empty($this->fields))
        {
            $this->setFields();
        }
        return $this->fields;
    }

    /**
     * Generates the list of fields used internally to map the columns back to
     * the SELECT fields in the QueryBuilder
     */
    public function setFields()
    {
        if (empty($this->fields))
        {
            $ast = $this->queryBuilder->getQuery()->getAST();

            $fieldNumber = 1;
            $returnFields = array();
            foreach ($ast->selectClause->selectExpressions as $selectExpression)
            {
                $expression = $selectExpression->expression;

                // if the expression is a string, there is an alias used to get all fields
                // to get all fields from the alias, we fetch the entity class and retrieve
                // the metadata from it, so we can set the fields correctly
                if (is_string($expression))
                {
                    // the expression itself is a pathexpression, where we can directly
                    // fetch the title and field

                    $alias = $expression;
                    $tableName = $this->_getModelFromAlias($alias);
                    $metadata = $this->entityManager->getClassMetadata($tableName);

                    foreach ($metadata->fieldMappings as $key => $detail)
                    {
                        $returnFields[$key]['title'] = $this->formatTitle($key);
                        $returnFields[$key]['field'] = $alias . '.' . $key;
                        $returnFields[$key]['type'] = $details['type'];
                    }
                }
                elseif ($expression instanceof AST\PathExpression)
                {
                    $field = ($selectExpression->fieldIdentificationVariable != null) ? $selectExpression->fieldIdentificationVariable : $expression->field;
                    $returnFields[$field]['title'] = $this->formatTitle($field);
                    $returnFields[$field]['field'] = $expression->identificationVariable . '.' . $expression->field;
                    $returnFields[$field]['type'] = '';
                }
                elseif ($expression instanceof AST\Subselect)
                {
                    //handle subselects. we only need the identification variable for the field
                    $field = $selectExpression->fieldIdentificationVariable;
                    $returnFields[$field]['title'] = $this->formatTitle($field);
                    $returnFields[$field]['field'] = $field;
                    $returnFields[$field]['type'] = '';
                }
                else
                {
                    $field = $selectExpression->fieldIdentificationVariable;

                    //doctrine uses numeric keys for expressions which got no
                    //identification variable, so the key will be set to the
                    //current counter $i
                    if ($field === null)
                    {
                        $field = $this->_getNameForExpression($expression);
                        $key = $fieldNumber;
                        $fieldNumber++;
                    }
                    else
                    {
                        $key = $field;
                    }

                    $returnFields[$field]['title'] = $this->formatTitle($field);
                    $returnFields[$key]['field'] = $field;
                    $returnFields[$key]['type'] = '';
                }
            }
            $this->fields = $returnFields;
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
        if (null !== $this->queryBuilder->getQuery()->getAST()->orderByClause)
        {
            // support for 1 field only
            $orderByClause = $this->queryBuilder->getQuery()->getAST()->orderByClause;

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

    /**
     * Returns a Zend_Paginator instance containing the results from the QueryBuilder
     * @see Zend_Paginator
     * @return Zend_Paginator
     */
    public function getResults()
    {

        $page = $this->getPage();

        $sortField = $this->getFieldForColumn($this->sortField);
        $sortOrder = (in_array(strtoupper($this->getSortOrder()), array('ASC', 'DESC')) ? strtoupper($this->getSortOrder()) : 'ASC');
        $this->queryBuilder->addOrderBy($sortField, $sortOrder);

        $adapter = new Application_DataGrid_DataSource_Doctrine2_PaginationAdapter($this->queryBuilder->getQuery());
        $adapter->useArrayResult();
        $this->paginator = new Zend_Paginator($adapter);
        $this->paginator->setCurrentPageNumber($page);
        $this->paginator->setItemCountPerPage($this->getResultsPerPage());

        return $this->paginator;
    }

    /**
     * Returns the total number of items
     * @return int
     */
    public function getCount()
    {
        return $this->paginator->getTotalItemCount();
    }

    /**
     * Generates the expression used in the select expression
     *
     * @param FunctionNode $expression
     * @return string
     */
    private function _getNameForExpression($expression)
    {
        $str = '';

        foreach ($expression as $key => $sub)
        {
            if ($sub instanceof AST\PathExpression)
            {
                $str .= $sub->identificationVariable . '.' . $sub->field;
                if ($expression instanceof AST\Functions\FunctionNode)
                {
                    $str = $expression->name . '(' . $str . ')';
                }
                elseif ($expression instanceof AST\AggregateExpression)
                {
                    $str = $expression->functionName . '(' . $str . ')';
                }
                //when we got another array, we will call the method recursive and add
                //brackets for readability.
            }
            elseif (is_array($sub))
            {
                $str .= '(' . $this->_getNameForExpression($sub) . ')';
                //call the method recursive to get all names.
            }
            elseif (is_object($sub))
            {
                $str .= $this->_getNameForExpression($sub);
                //key is numeric and value is a string, we probably got an
                //arithmetic identifier (like "-" or "/")
            }
            elseif (is_numeric($key) && is_string($sub))
            {
                $str .= ' ' . $sub . ' ';
                //we got a string value for example in an arithmetic expression
                //(a.value - 1) the "1" here is the value we append to the string here
            }
            elseif ($key == 'value')
            {
                $str .= $sub;
            }
        }

        return $str;
    }

    /**
     * Finds a model for which an alias belongs.
     *
     * @param string $alias
     * @return string The name of the entity.
     */
    private function _getModelFromAlias($alias)
    {
        $qb = $this->queryBuilder;
        $fromParts = $qb->getDQLPart('from');

        //first try to get the model from the from part
        foreach ($fromParts as $fromPart)
        {
            if ($fromPart->getAlias() == $alias)
            {
                return $fromPart->getFrom();
            }
        }

        //when the from part doesnt have it, we first find the join field defined
        //by the alias
        $AST = $qb->getQuery()->getAST();

        $field = null;
        foreach ($AST->fromClause->identificationVariableDeclarations[0]->joinVariableDeclarations as $joinVariable)
        {
            if ($alias == $joinVariable->join->aliasIdentificationVariable)
            {
                $field = $joinVariable->join->joinAssociationPathExpression->associationField;
                break;
            }
        }
        if (is_null($field))
        {
            throw new Application_DataGrid_Exception("No field found.");
        }

        //iterate over the fromparts, get the metadata from it and
        //iterate then over the association mappings to find the specific
        //model for the alias
        foreach ($fromParts as $fromPart)
        {
            $metadata = $this->entityManager->getClassMetadata($fromPart->getFrom());
            foreach ($metadata->associationMappings as $mapping)
            {
                if ($mapping['fieldName'] == $field)
                {
                    return $mapping['targetEntity'];
                }
            }
        }

        throw new Application_DataGrid_Exception("No model found.");
    }

    /**
     * Returns the field name used by the QueryBuilder for the supplied column name
     * @see Application_DataGrid_Column
     * @param string $name
     * @return string 
     */
    protected function getFieldForColumn($name)
    {
        if (array_key_exists($name, $this->fields))
        {
            return $this->fields[$name]['field'];
        }
        return null;
    }

    /**
     * Returns the field title used by the QueryBuilder for the supplied column name
     * @see Application_DataGrid_Column
     * @param string $name
     * @return string 
     */
    protected function getTitleForColumn($name)
    {
        if (array_key_exists($name, $this->fields))
        {
            return $this->fields[$name]['title'];
        }
        return null;
    }

    /**
     * Returns the field type used by the QueryBuilder for the supplied column name
     * @see Application_DataGrid_Column
     * @param string $name
     * @return string 
     */
    protected function getTypeForColumn($name)
    {
        if (array_key_exists($name, $this->fields))
        {
            return $this->fields[$name]['type'];
        }
        return null;
    }

    protected function formatTitle($title)
    {
        // First split camelcase into words
        $title = implode(' ', preg_split('/([[:upper:]][[:lower:]]+)/', $title, null, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY));
        // then replace underscores with spaces
        return ucwords(str_replace('_', ' ', $title));
    }

}

