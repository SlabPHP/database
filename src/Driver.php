<?php
/**
 * Database driver, masquerades as a database provider and performs query binding
 *
 * @author Eric
 * @package Slab
 * @subpackage Database
 * @class Driver
 */
namespace Slab\Database;

class Driver implements \Slab\Components\DataSourceDriverInterface
{
    const DATABASE_BINDING_TOKEN = '?';

    /**
     * Database provider
     *
     * @var \Slab\Database\Providers\ProviderInterface
     */
    private $provider;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $log;

    /**
     * @param Providers\ProviderInterface $provider
     * @return $this
     */
    public function setProvider(\Slab\Database\Providers\ProviderInterface $provider)
    {
        $this->provider = $provider;

        if (!empty($this->log))
        {
            $this->provider->setLog($this->log);
        }

        return $this;
    }

    /**
     * @return Providers\ProviderInterface
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * @param \Psr\Log\LoggerInterface $log
     * @return $this
     */
    public function setLog(\Psr\Log\LoggerInterface $log)
    {
        $this->log = $log;

        return $this;
    }

    /**
     * Run an SQL query
     *
     * @param string $sql
     * @param string[] $binders
     * @param string $suggestedClass
     *
     * @return \Slab\Database\Providers\ResponseInterface
     */
    public function query($sql, $binders = array(), $suggestedClass = null, $debug = false)
    {
        //By keeping track of the last binding location, we can skip
        //binders that are within bound data
        $minBinderLocation = 0;

        if (!is_array($binders)) $binders = array($binders);

        if (!empty($binders)) {
            foreach ($binders as &$bound) {
                $bound = $this->provider->escapeItem($bound);

                $bindingLocation = strpos($sql, static::DATABASE_BINDING_TOKEN, $minBinderLocation);

                if ($bindingLocation !== false) {
                    $sql = substr_replace($sql, $bound, $bindingLocation, 1);

                    $minBinderLocation = $bindingLocation + mb_strlen($bound) + 1;
                }
            }
        }

        $data = $this->provider->query($sql, $suggestedClass);

        if (!empty($debug)) {
            $output = new \stdClass();
            $output->sql = $sql;
            $output->response = $data;
            $output->inputs = $binders;

            if (!empty($this->log))
            {
                $this->log->notice('Debug Output', [$output]);
            }
        }

        return $data;
    }

    /**
     * Insert data
     *
     * @param string $table
     * @param string[] $data
     *
     * @return \Slab\Database\Providers\ResponseInterface
     */
    public function insert($table, $data)
    {
        $query = $this->provider->buildInsertQuery($table, $data);

        return $this->provider->query($query, null);
    }

    /**
     * Delete data
     *
     * @param string $table
     * @param string $where
     * @param string $whereValue
     *
     * @return \Slab\Database\Providers\ResponseInterface
     */
    public function delete($table, $where, $whereValue)
    {
        $query = $this->provider->buildDeleteQuery($table, $where, $whereValue);

        return $this->provider->query($query, null);
    }

    /**
     * Update data
     *
     * @param string $table
     * @param array $data
     * @param string $where
     *
     * @return \Slab\Database\Providers\ResponseInterface
     */
    public function update($table, $data, $where, $limit = null)
    {
        $query = $this->provider->buildUpdateQuery($table, $data, $where, $limit);

        return $this->provider->query($query, null);
    }

    /**
     * Escape a string
     *
     * @param string $input
     * @param boolean $tableComponent
     *
     * @return string
     */
    public function escape($input, $tableComponent = false)
    {
        return $this->provider->escapeItem($input, $tableComponent);
    }
}