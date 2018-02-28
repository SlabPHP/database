<?php
/**
 * Database Provider Interface
 *
 * @package Slab
 * @subpackage Database
 * @author Eric
 */
namespace Slab\Database\Providers;

interface ProviderInterface
{
    /**
     * @param \Psr\Log\LoggerInterface $log
     * @return mixed
     */
    public function setLog(\Psr\Log\LoggerInterface $log);

    /**
     * Perform a standard query
     *
     * @param string $queryString
     * @param string $suggestedClass
     * @return \Slab\Database\Providers\ResponseInterface
     */
    public function query($queryString, $suggestedClass);

    /**
     * Build an insert query
     *
     * @param string $table
     * @param string[] $data
     * @return string
     */
    public function buildInsertQuery($table, $data);

    /**
     * Build a delete query
     *
     * @param string $table
     * @param string $whereKey
     * @param string $whereValue
     * @return string
     */
    public function buildDeleteQuery($table, $whereKey, $whereValue);

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
    public function buildUpdateQuery($table, $data, $where, $limit);

    /**
     * Escape a string specific for this provider
     *
     * @param mixed $item
     * @return mixed
     */
    public function escapeItem($item, $tableItem = false);
}