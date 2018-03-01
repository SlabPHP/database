<?php
/**
 * MySQLi database provider class
 *
 * @author Eric
 * @package Slab
 * @subpackage Database
 * @class MySQL
 */
namespace Slab\Database\Providers\MySQL;

class Provider extends \Slab\Database\Providers\BaseProvider
{
    /**
     * Database object
     *
     * @var \Mysqli
     */
    private $mysqli;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $log;

    /**
     * @param \Mysqli $mysqli
     * @return $this
     */
    public function setMySQL(\Mysqli $mysqli)
    {
        $this->mysqli = $mysqli;

        return $this;
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
     * Perform a standard query
     *
     * @param string $queryString
     * @param string $suggestedClass
     * @throws \Slab\Database\Exceptions\Query
     * @return \Slab\Database\Providers\BaseResponse
     */
    public function query($queryString, $suggestedClass = NULL)
    {
        if (!empty($this->log)) {
            $this->log->debug("Performing SQL: " . $queryString . '...', 'DATABASE');
        }

        $result = $this->mysqli->query($queryString);

        if (!empty($this->mysqli->error)) {
            throw new \Slab\Database\Exceptions\Query("Query error: " . $queryString . " with error " . $this->mysqli->error);
        }

        if (empty($result)) {
            throw new \Slab\Database\Exceptions\Query("Query Failed: " . $queryString . " with no error.");
        }

        $affectedRows = 0;
        $error = '';

        try
        {
            $affectedRows = $this->mysqli->affected_rows;
            $error = $this->mysqli->error;
        }
        catch (\Throwable $exception)
        {
            //This means we're in a test environment
        }

        $response = new Response();
        $response->initializeFromQueryResults(
            $result,
            $affectedRows,
            $error,
            $suggestedClass
        );

        return $response;
    }

    /**
     * Real escape of a string
     *
     * @param string $input
     * @return string
     */
    private function real_escape($input)
    {
        if (!empty($this->mysqli)) {
            return $this->mysqli->real_escape_string($input);
        }

        return '';
    }

    /**
     * Return last insert id
     */
    public function insertId()
    {
        return $this->mysqli->insert_id;
    }

    /**
     * Perform an insert query
     *
     * @param string $table
     * @param string[] $data
     * @return integer|boolean
     */
    public function buildInsertQuery($table, $data)
    {
        $sql = "INSERT INTO " . $this->quoteSpecialName($table) . " ";

        if (!empty($data['ON DUPLICATE'])) {
            $onDuplicate = $data['ON DUPLICATE'];
            unset($data['ON DUPLICATE']);
        }

        $keys = $values = '';

        foreach ($data as $key => $value) {
            if (!empty($keys)) $keys .= ', ';
            if (!empty($values)) $values .= ', ';

            $keys .= '`' . $key . '`';
            $values .= $this->escapeItem($value);
        }

        $keys = '(' . $keys . ')';
        $values = '(' . $values . ')';

        $sql .= $keys . ' VALUES ' . $values;

        if (!empty($onDuplicate)) {
            $sql .= " ON DUPLICATE KEY UPDATE " . $onDuplicate;
        }

        $sql .= ';';

        return $sql;
    }

    /**
     * Delete an item
     *
     * @param string $table
     * @param string $whereKey
     * @param string $whereValue
     * @return integer
     */
    public function buildDeleteQuery($table, $whereKey, $whereValue)
    {
        $sql = "DELETE FROM " . $this->quoteSpecialName($table) . " WHERE " . $this->quoteSpecialName($whereKey) . " = " . $this->escapeItem($whereValue) . ';';


        return $sql;
    }

    /**
     * MySQL style special naming backticks quotes
     *
     * @param string $name
     * @return string
     */
    protected function quoteSpecialName($name)
    {
        //If user already back ticked, return the original string
        if ($name[0] == '`' && $name[mb_strlen($name) - 1] == '`') return $name;

        //Back tick string
        $name = '`' . $name . '`';

        //Check if its a qualified table name, if so escape around the dot as well
        $name = str_replace('.', '`.`', $name);

        return $name;
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
        $sql = "UPDATE " . $this->quoteSpecialName($table) . " SET ";

        $values = '';

        foreach ($data as $key => $value) {
            if (!empty($values)) $values .= ', ';
            $values .= $this->quoteSpecialName($key) . ' = ' . $this->escapeItem($value);
        }

        $sql .= $values . ' WHERE ' . $where;

        if (!empty($limit)) {
            $sql .= ' LIMIT ' . $limit;
        }

        $sql .= ';';

        return $sql;
    }

    /**
     * @param mixed $input
     * @param bool $tableItem
     * @return mixed|string
     */
    public function escapeItem($input, $tableItem = false)
    {
        if ($input === null) return 'NULL';

        $returnValue = '';

        if (is_numeric($input) || is_bool($input)) {
            //Miserable hack because is_double('1.2') is false
            if (preg_match('#\d?\.\d+#', $input)) {
                $returnValue = doubleval($input);
            } else {
                $returnValue = intval($input);
            }
        } else if (is_float($input)) {
            $returnValue = floatval($input);
        } else if (is_double($input)) {
            $returnValue = doubleval($input);
        } else if (is_string($input)) {
            if (empty($input)) {
                $returnValue = '';
            } else {
                $returnValue = $this->real_escape($input);
            }
        } else if ($input instanceof \DateTime) {
            $returnValue = $input->format('Y-m-d H:i:s');
        } else if (is_object($input) && method_exists($input, '__toString')) {
            $input = (string)$input;

            if (empty($input)) {
                $returnValue = '';
            } else {
                $returnValue = $this->real_escape($input);
            }
        } else {
            $returnValue = NULL;
        }

        $delimiter = "'";

        if ($tableItem) {
            $delimiter = '`';
        }

        $output = ($returnValue !== NULL) ? $delimiter . $returnValue . $delimiter : 'NULL';

        return $output;
    }

    /**
     * "Wheel" a string value in the database
     *
     * @param $table
     * @param $column
     * @param $value
     * @param string $separator
     * @param int $maxTries
     * @return string
     * @throws \Slab\Database\Exceptions\Query
     */
    public function wheel($table, $column, $value, $separator = '_', $maxTries = 10)
    {
        $tempValue = $value;

        for ($i = 2; $i <= $maxTries; ++$i) {
            $result = $this->query("select count(*) as cnt from " . $this->quoteSpecialName($table) . " where " . $this->quoteSpecialName($column) . " = '" . $this->real_escape($tempValue) . "' limit 1;", null);

            $row = $result->row();
            if ($row->cnt == 0) {
                return $tempValue;
            }

            $tempValue = $value . $separator . $i;
        }

        throw new \Slab\Database\Exceptions\Query("Failed to wheel value " . $value . " over " . $maxTries . " tries on " . $table . "." . $column);
    }
}
