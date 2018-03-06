<?php
/**
 * Mock Provider
 *
 * @package Slab
 * @subpackage Tests
 * @author Eric
 */
namespace Slab\Tests\Database\Mocks;

class Provider implements \Slab\Components\Database\ProviderInterface
{
    /**
     * Perform a standard query
     *
     * @param string $queryString
     * @param string $suggestedClass
     * @return \Slab\Components\Database\ResponseInterface
     */
    public function query($queryString, $suggestedClass)
    {
        $response = new Response();
        $response->initializeFromQueryResults($queryString, 1, 'none', get_called_class());

        return $response;
    }

    /**
     * Build an insert query
     *
     * @param string $table
     * @param string[] $data
     * @return string
     */
    public function buildInsertQuery($table, $data)
    {
        return 'insert:' . $table . ':' . implode(',', $data);
    }

    /**
     * Build a delete query
     *
     * @param string $table
     * @param string $whereKey
     * @param string $whereValue
     * @return string
     */
    public function buildDeleteQuery($table, $whereKey, $whereValue)
    {
        return 'delete:' . $table . ':' . $whereKey . ':' . $whereValue;
    }

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
    public function buildUpdateQuery($table, $data, $where, $limit)
    {
        return 'update:' . $table . ':' . implode(',', $data) . ':' . $where . ':' . $limit;
    }

    /**
     * Escape a string specific for this provider
     *
     * @param mixed $item
     * @return mixed
     */
    public function escapeItem($item, $tableItem = false)
    {
        return '|' . strrev($item) . '|';
    }

    /**
     * @param \Psr\Log\LoggerInterface $log
     * @return mixed|void
     */
    public function setLog(\Psr\Log\LoggerInterface $log)
    {
        //hi!
    }
}