<?php
/**
 * Database query response object
 *
 * @author Eric
 * @package Slab
 * @subpackage Database
 * @class Response
 */
namespace Slab\Database\Providers;

abstract class BaseResponse implements ResponseInterface
{
    /**
     * Results, can be anything
     *
     * @var mixed
     */
    protected $results = NULL;

    /**
     * Number of results
     *
     * @var integer
     */
    protected $numberOfResults = 0;

    /**
     * Error result
     *
     * @var string
     */
    protected $error;

    /**
     * Initialize the result object from data
     *
     * @param mixed $queryResult
     * @param mixed $linkId
     * @param string $suggestedClass
     */
    abstract public function initializeFromQueryResults($queryResult, $rowsAffected, $error, $suggestedClass);

    /**
     * Number of results in the result set or number of rows affected if a value response
     *
     * @return integer
     */
    public function count()
    {
        return $this->numberOfResults;
    }

    /**
     * Return results as an array
     *
     * @return \stdClass[]
     */
    public function result()
    {
        if (!empty($this->results)) {
            reset($this->results);
            return $this->results;
        }

        return array();
    }

    /**
     * Return the next item in the list
     *
     * @return mixed
     */
    public function row()
    {
        $output = current($this->results);

        next($this->results);

        return $output;
    }
}