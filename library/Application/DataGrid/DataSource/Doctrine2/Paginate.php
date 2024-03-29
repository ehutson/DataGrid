<?php

/**
 * Copyright (C) 2011 by Pieter Vogelaar (Platina Designs) and Kees Schepers (SkyConcepts)
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 */
use Doctrine\ORM\Query;

class Application_DataGrid_DataSource_Doctrine2_Paginate
{

    /**
     *
     * @param Query $query
     * @return Query
     */
    static protected function cloneQuery(Query $query)
    {
        $reflector = new ReflectionClass($query);
        $attribute = $reflector->getProperty('_paramTypes');
        $attribute->setAccessible(true);
        $paramTypes = $attribute->getValue($query);

        /* @var $countQuery Query */
        $countQuery = clone $query;
        $params = $query->getParameters();

        $countQuery->setParameters($params, $paramTypes);
        return $countQuery;
    }

    /**
     *
     * @param Query $query
     * @return int
     */
    public static function count(Query $query)
    {
        return self::createCountQuery($query)->getSingleScalarResult();
    }

    /**
     *
     * @param Query $query
     * @return int
     */
    public static function getTotalQueryResults(Query $query)
    {
        $q = self::createCountQuery($query);
        return $q->getSingleScalarResult();
    }

    /**
     * Given the Query it resturns a new query that is a paginatable query using a modified subselect.
     * 
     * @param Query $query
     * @param int $offset
     * @param int $itemCountPerPage
     * @param array $hints
     * @return Query 
     */
    public static function getPaginateQuery(Query $query, $offset, $itemCountPerPage, array $hints = array())
    {
        $ids = array_map('current', self::createLimitSubQuery($query, $offset, $itemCountPerPage)->getScalarResult());
        return self::createWhereInQuery($query, $ids, 'pgid', $hints);
    }

    /**
     *
     * @param Query $query
     * @return Query 
     */
    public static function createCountQuery(Query $query)
    {
        /* @var $countQuery Query */
        $countQuery = self::cloneQuery($query);

        $countQuery->setHint(Query::HINT_CUSTOM_TREE_WALKERS, array('Application_DataGrid_DataSource_Doctrine2_CountWalker'));
        $countQuery->setFirstResult(null)->setMaxResults(null);

        $countQuery->setParameters($query->getParameters());
        return $countQuery;
    }

    /**
     *
     * @param Query $query
     * @param int $offset
     * @param int $itemCountPerPage 
     * @return Query
     */
    public static function createLimitSubQuery(Query $query, $offset, $itemCountPerPage)
    {
        /* @var $subQuery Query */
        $subQuery = self::cloneQuery($query);

        $subQuery->setHint(Query::HINT_CUSTOM_TREE_WALKERS, array('Application_DataGrid_DataSource_Doctrine2_LimitSubqueryWalker'));
        $subQuery->setFirstResult($offset)->setMaxResults($itemCountPerPage);

        $subQuery->setParameters($query->getParameters());
        return $subQuery;
    }

    /**
     *
     * @param Query $query
     * @param array $ids
     * @param string $namespace
     * @param array $phints
     * @return Query 
     */
    public static function createWhereInQuery(Query $query, array $ids, $namespace = 'pgid', array $phints = array())
    {
        // don't do this for an empty id array
        if (count($ids) > 0)
        {
            $whereInQuery = clone $query;

            $whereInQuery->setParameters($query->getParameters());

            $hints = array();
            $hints[Query::HINT_CUSTOM_TREE_WALKERS] = array('Application_DataGrid_DataSource_Doctrine2_WhereInWalker');
            $hints['id.count'] = count($ids);
            $hints['pg.ns'] = $namespace;

            foreach ($phints as $name => $hint)
            {
                if ($name == Query::HINT_CUSTOM_TREE_WALKERS)
                {
                    $hints[Query::HINT_CUSTOM_TREE_WALKERS] = array_merge($hints[Query::HINT_CUSTOM_TREE_WALKERS], $hint);
                }
                else
                {
                    $hints[$name] = $hint;
                }
            }

            foreach ($hints as $name => $hint)
            {
                $whereInQuery->setHint($name, $hint);
            }

            $whereInQuery->setFirstResult(null)->setMaxResults(null);
            foreach ($ids as $i => $id)
            {
                $i = $i + 1;
                $whereInQuery->setParameter("{$namespace}_{$i}", $id);
            }

            return $whereInQuery;
        }
        else
        {
            return $query;
        }
    }

}

