<?php
/**
 * Base database provider class, override and implement the abstract functions
 *
 * @author Eric
 * @package Slab
 * @subpackage Database
 * @class Base
 */
namespace Slab\Database\Providers;

abstract class BaseProvider implements \Slab\Components\Database\ProviderInterface
{
    /**
     * @param \Psr\Log\LoggerInterface $log
     * @return mixed
     */
    abstract public function setLog(\Psr\Log\LoggerInterface $log);

    /**
     * Perform a standard query
     *
     * @param string $queryString
     * @param string $suggestedClass
     * @return \Slab\Database\Providers\BaseResponse
     */
    abstract public function query($queryString, $suggestedClass);

    /**
     * Build an insert query
     *
     * @param string $table
     * @param string[] $data
     * @return string
     */
    abstract public function buildInsertQuery($table, $data);

    /**
     * Build a delete query
     *
     * @param string $table
     * @param string $whereKey
     * @param string $whereValue
     * @return string
     */
    abstract public function buildDeleteQuery($table, $whereKey, $whereValue);

    /**
     * Update a table
     *
     * @param string $table
     * @param array $data
     * @param string $whereValue
     * @param integer $limit
     *
     * @return string
     */
    abstract public function buildUpdateQuery($table, $data, $where, $limit);

    /**
     * Escape a string specific for this provider
     *
     * @param mixed $item
     * @return mixed
     */
    abstract public function escapeItem($item, $tableItem = false);
}