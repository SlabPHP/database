<?php
/**
 * Base Loader Model
 *
 * @package Slab
 * @subpackage Database
 * @author Eric
 */
namespace Slab\Database\Models\MySQL;

class Loader extends \Slab\Database\Models\BaseLoader
{
    const DATA_OBJECT_CLASS = '\Slab\Database\Models\MySQL\Object';

    const DATABASE_NAME = 'auto';

    const TABLE_NAME = '';

    const ID_COLUMN = 'id';

    /**
     * Mapping fields, should appear as 'database_column' => 'local_variable[:modifier]'
     *
     * The [:modifier] part being optional, valid modifiers include:
     * "date" to convert to a \DateTime object
     * "timezone" to convert to a \DateTimeZone object
     * You can create your own by implementing modifier methods
     *
     * @var string[]
     */
    protected $mapping = [];

    /**
     * Mapping SQL optimizer
     *
     * @var string
     */
    protected $mappingSQL = [];

    /**
     * Mapping options for the instance object
     *
     * @var \stdClass
     */
    protected $mappingOptions = null;

    /**
     * @var \Slab\Database\Driver
     */
    protected $driver;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $log;

    /**
     * Loader constructor.
     * @param \Slab\Database\Driver $driver
     */
    public function setDriver(\Slab\Database\Driver $driver)
    {
        $this->driver = $driver;
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
     * @param null $alias
     * @param bool $qualify
     * @return string
     */
    public function getTable($alias = null, $qualify = true)
    {
        $tableName = '`' . static::TABLE_NAME . '`';

        if ($qualify && static::DATABASE_NAME != 'auto')
        {
            $tableName = '`' . static::DATABASE_NAME . '`.' . $tableName;
        }

        if (!empty($alias)) {
            $tableName .= ' as `' . $alias . '`';
        }

        return $tableName;
    }

    /**
     * Return an object by id, this SHOULD be implemented if the object has an id
     *
     * @param integer $id
     * @return \Slab\Database\Models\BaseDataObject
     *
     * @throws \Exception
     */
    public function getById($id)
    {
        return $this->fetchByColumn(static::ID_COLUMN, $id);
    }

    /**
     * Return the number of items available in the table
     *
     * @param string $where
     * @param string[] $variables
     * @return integer
     *
     */
    public function getCount($where = '', $variables = null)
    {
        $where = $this->formatWhereClause($where);

        $response = $this->driver->query("select count(*) as numberOfItems from " . $this->getTable() . ' ' . $where . ";", $variables);

        if (!$response->count()) return 0;

        $response = $response->row();

        return intval($response->numberOfItems);
    }

    /**
     * Some simple checks for a where clause
     *
     * @param $where
     * @return string
     */
    protected function formatWhereClause($where)
    {
        if (!empty($where)) {
            if (stripos($where, 'where') === false || stripos($where, 'select') !== false) {
                $where = ' where ' . $where;
            }
        }

        return $where;
    }

    /**
     * @param $totalCount
     * @param int $page
     * @param int $pagination
     * @param string $where
     * @return \stdClass[]
     */
    public function getAll(&$totalCount, $page = 1, $pagination = 10, $where = '')
    {
        $totalCount = $this->getCount($where);

        return $this->fetchAll($page, $pagination, $where);
    }

    /**
     * Actually fetch all the items with the given params
     *
     * @param $page
     * @param $pagination
     * @param $where
     * @return \stdClass[]
     */
    public function fetchAll($page, $pagination, $where)
    {
        $index = ceil(($page - 1) * $pagination);

        $where = $this->formatWhereClause($where);

        $sql = "
			select " . $this->getMappingAsSQL() . "
			from " . $this->getTable() . "
			" . $where . "
			limit " . $index . ',' . intval($pagination) . ";";

        $query = $this->driver->query($sql, [], static::DATA_OBJECT_CLASS);

        return $query->result();
    }

    /**
     * Do actual mysql query by specific criteria
     *
     * @param string $column
     * @param string $value
     * @return NULL|mixed
     */
    protected function fetchByColumn($column, $value)
    {
        /**
         * @var \Slab\Database\Providers\BaseResponse $response
         */
        $response = $this->driver->query("select " . $this->getMappingAsSQL() . " from " . $this->getTable() . " where `" . $column . "` = ? limit 1;", array($value), static::DATA_OBJECT_CLASS);

        return $response->row();
    }

    /**
     * @param bool $aliasColumns
     * @param bool $aliasTable
     * @param string $mappedColumnPrefix
     * @return string
     */
    public function getMappingAsSQL($aliasColumns = true, $aliasTable = false, $mappedColumnPrefix = '')
    {
        if (empty($this->mapping)) {
            return '*';
        } else {
            $output = '';
            foreach ($this->mapping as $column => $mappingField) {
                if (!empty($output)) {
                    $output .= ',';
                }

                if (!empty($aliasTable)) {
                    $output .= $aliasTable . '.';
                } else {
                    $output .= '`' . static::TABLE_NAME . '`.';
                }

                $output .= "`" . $column . "`";

                $colonPosition = strpos($mappingField, ':');
                if ($colonPosition > 0) {
                    $mappingField = substr($mappingField, 0, $colonPosition);
                }

                if ($aliasColumns && $column != $mappingField) {
                    $output .= ' as `' . $mappedColumnPrefix . $mappingField . '`';
                }
            }

            return $output;
        }
    }

    /**
     * Return primary key qualified or not
     *
     * @param string $tableAlias
     * @return string
     */
    public function getPrimaryKey($tableAlias = '')
    {
        $output = '';

        if (!empty($tableAlias)) {
            $output .= '`' . $tableAlias . '`.';
        }

        $output .= static::ID_COLUMN;

        return $output;
    }

    /**
     * Get raw columns as SQL, with some prefix options
     *
     * @param string $tableAlias
     * @param string $columnPrefix
     * @return string
     */
    public function getColumnsAsSQL($tableAlias = '', $columnPrefix = '')
    {
        if (empty($this->mapping)) {
            return '*';
        }

        $output = '';
        foreach ($this->mapping as $column => $mappingField) {
            if (!empty($output)) {
                $output .= ',';
            }

            if (!empty($tableAlias)) {
                $output .= '`' . $tableAlias . '`.';
            }

            $output .= '`' . $column . '`';

            if (!empty($columnPrefix)) {
                $output .= ' as `' . $columnPrefix . $column . '`';
            }
        }

        return $output;
    }

    /**
     * Reset mapping options
     */
    protected function resetMappingOptions()
    {
        $this->mappingOptions = new \stdClass();
        $this->mappingOptions->destructiveMapping = false;
        $this->mappingOptions->assignNonMappedColumns = true;
        $this->mappingOptions->fieldPrefix = '';
        $this->mappingOptions->throwOnEmptyId = false;
        $this->mappingOptions->mapUnmappedColumns = true;
    }

    /**
     * Columns will come in pre-mapped in the database result
     *
     * @param $columnsMapped
     * @return $this
     */
    public function setColumnsAreMappedInSQL($columnsMapped)
    {
        $this->mappingOptions->mapUnmappedColumns = $columnsMapped;

        return $this;
    }

    /**
     * Set destructive mapping option
     *
     * @param $destructiveMapping
     * @return $this
     */
    public function setDestructiveMapping($destructiveMapping)
    {
        if (empty($this->mappingOptions)) $this->resetMappingOptions();

        $this->mappingOptions->destructiveMapping = $destructiveMapping;

        return $this;
    }

    /**
     * Set flag for assigning non-mapped columns
     *
     * @param $assignNonMappedColumns
     * @return $this
     */
    public function setAssignNonMappedColumns($assignNonMappedColumns)
    {
        if (empty($this->mappingOptions)) $this->resetMappingOptions();

        $this->mappingOptions->assignNonMappedColumns = $assignNonMappedColumns;

        return $this;
    }

    /**
     * Set mapping option field prefix
     *
     * @param $fieldPrefix
     * @return $this
     */
    public function setFieldPrefix($fieldPrefix)
    {
        if (empty($this->mappingOptions)) $this->resetMappingOptions();

        $this->mappingOptions->fieldPrefix = $fieldPrefix;

        return $this;
    }

    /**
     * Throw an exception if the main id doesn't get mapped?
     *
     * @param $throwOnEmptyId
     * @return $this
     */
    public function setThrowIfEmptyId($throwOnEmptyId)
    {
        if (empty($this->mappingOptions)) $this->resetMappingOptions();

        $this->mappingOptions->throwOnEmptyId = $throwOnEmptyId;

        return $this;
    }

    /**
     * Map a single object to this instance
     *
     * @param \stdClass $databaseResult
     */
    protected function mapObject($databaseResult)
    {
        foreach ($databaseResult as $column => $value) {
            if (!empty($this->mapping[$column])) {
                list($variable, $modifier) = $this->splitVariableWithModifier($this->mapping[$column]);

                $this->$variable = $this->getMappedValue($databaseResult, $column, $this->mapping[$column]);
            } else {
                $this->$column = $value;
            }
        }
    }

    /**
     * @param DataObject $object
     * @param $databaseResult
     * @throws \Slab\Database\Exceptions\Mapping
     */
    public function mapObjectWithOptions(DataObject $object, $databaseResult)
    {
        $hasId = false;
        foreach ($databaseResult as $column => $value) {
            $originalColumn = $column;

            //Resolve column prefixes first, if one exists, we have to validate it and then remove it
            if (!empty($this->mappingOptions->fieldPrefix)) {
                if (strpos($column, $this->mappingOptions->fieldPrefix) === false) continue;

                $column = str_replace($this->mappingOptions->fieldPrefix, '', $column);
            }

            //See if we have a mapping for it, if the value is an unmapped column id
            if (!empty($this->mapping[$column])) {
                list($variable, $modifier) = $this->splitVariableWithModifier($this->mapping[$column]);

                //Map the value and modify it if necessary
                $object->$variable = $this->getMappedValue($databaseResult, $originalColumn, $this->mapping[$column]);
            } //See if we have a mapped value already
            else if (isset($this->$column)) {
                $object->$column = $this->getMappedValue($databaseResult, $originalColumn, $this->mapping[$column]);
            } //No mapping but should we map it anyway?
            else if ($this->mappingOptions->assignNonMappedColumns) {
                $object->$column = $value;
            }

            //Remove the original value from the database result object
            if ($this->mappingOptions->destructiveMapping) {
                unset($databaseResult->$originalColumn);
            }

            if ($column == static::ID_COLUMN && !empty($value)) {
                $hasId = true;
            }
        }

        //Throw an exception if the object we just mapped is missing an id field
        if ($this->mappingOptions->throwOnEmptyId && !$hasId) {
            throw new \Slab\Database\Exceptions\Mapping("ID column is not set in object during mapping.");
        }
    }

    /**
     * Splits variable with a modifier
     *
     * @param string $column
     * @return array
     */
    protected function splitVariableWithModifier($column)
    {
        $modifier = false;
        if (strpos($column, ':') !== false) {
            return explode(':', $column);
        }

        return [$column, false];
    }

    /**
     * Returns the mapped value of $object->$column with the rules of whatever is in $variable
     *
     * @param mixed $object
     * @param string $column
     * @param string $variable
     * @return mixed
     */
    protected function getMappedValue($object, $column, $variable)
    {
        if (empty($object) || empty($column) || empty($object->$column) || empty($variable)) return '';

        list($variable, $modifier) = $this->splitVariableWithModifier($variable);

        $functionName = 'modifier' . ucfirst($modifier);

        if (method_exists($this, $functionName)) {
            return $this->$functionName($object->$column);
        } else {
            return $object->$column;
        }
    }

    /**
     * Return a date time modified input
     *
     * @param string $input
     * @return \DateTime|NULL
     */
    protected function modifierDate($input)
    {
        if (empty($input)) return NULL;
        //if ($input == '0000-00-00 00:00:00') return NULL;

        if ($input instanceof \DateTime) return $input;

        try {
            $output = new \DateTime($input);
            $output->setTimeZone(new \DateTimeZone('America/New_York'));

            return $output;
        } catch (\Exception $exception) {
            if (!empty($this->log)) $this->log->error('Failed to convert \DateTime object from string "' . $input . '"', $exception);
        }

        return NULL;
    }

    /**
     * Return a timezone modified input
     *
     * @param string $input
     * @return \DateTimeZone|NULL
     */
    protected function modifierTimezone($input)
    {
        if ($input instanceof \DateTimeZone) return $input;

        try {
            $output = new \DateTimeZone($input);

            return $output;
        } catch (\Exception $exception) {
            if (!empty($this->log)) $this->log->error("Failed to convert \\DateTimeZone object from string " . $input);
        }

        return NULL;
    }

    /**
     * Map the object to itself
     *
     * @param \stdClass $databaseResult - The database result to map
     * @param bool $assignNonMappedColumns - Leave fields that aren't mapped in the output object
     * @param bool $destructiveMapping - Remove field from databaseResult upon mapping
     * @param string $fieldPrefix - An optional prefix for fields inc ase you are doing multiple joins
     */
    public function buildModel($databaseResult = null)
    {
        $this->resetMappingOptions();

        if (empty($databaseResult)) return;

        $this->mapObject($databaseResult);
    }

    /**
     * Array walk function, overridable
     *
     * The response object will automatically try to fire this when pulling a list of automapped objects.
     *
     * @param DataObject $Object
     */
    public function performMappingOnObject(DataObject $object)
    {
        //By default, we already have our data mapped in the object (hopefully properly)
        //But we do need to convert things that may need converting
        foreach ($this->mapping as $field => $variable) {
            $modifier = false;
            $newField = $variable;

            if (strpos($variable, ':') !== false) {
                list($newField, $modifier) = explode(':', $variable);
                $object->$newField = $this->getMappedValue($object, $newField, $variable);
            }
        }
    }

    /**
     * Get database name
     *
     * @return string
     */
    public function getDatabase()
    {
        return static::DATABASE_NAME;
    }

    /**
     * Attempts to insert the object into the table
     *
     * @param DataObject $object
     * @param boolean $skipID
     * @return bool|\Slab\Database\Providers\BaseResponse
     */
    public function insertObject(DataObject $object, $skipID = true)
    {
        $data = [];

        foreach ($this->mapping as $column => $local) {
            if ($skipID && ($column == static::ID_COLUMN)) continue;

            $modifier = false;
            if (strpos($local, ':') !== false) {
                list($local, $modifier) = explode(':', $local);
            }

            if (!empty($object->$local)) {
                if (!empty($modifier) && is_object($this->$local)) {
                    switch ($modifier) {
                        case 'datetime':
                        case 'date':
                            $data[$column] = $object->$local->format('Y-m-d H:i:s');
                            break;

                        case 'timezone':
                            $data[$column] = $object->$local->getName();
                            break;
                    }
                } else {
                    $data[$column] = $object->$local;
                }
            }
        }

        if (!empty($data)) {
            

            $this->driver->insert(static::TABLE_NAME, $data);

            /**
             * @var \Slab\Database\Providers\MySQL\Provider $mysqlDriver
             */
            $mysqlDriver = $this->driver->getProvider();
            $object->{static::ID_COLUMN} = $mysqlDriver->insertId();

            return $object->{static::ID_COLUMN};
        }

        return false;
    }

    /**
     * Return an array of an object as an unmapped aray, useful for dumping defaults
     *
     * @return array
     */
    public function getUnmappedArray(DataObject $object)
    {
        $output = [];

        foreach ($this->mapping as $unmappedField => $mappedField) {
            if (strpos($mappedField, ':') !== false) {
                list($mappedField, $modifier) = explode(':', $mappedField);
            }

            $output[$unmappedField] = $object->{$mappedField};
        }

        return $output;
    }

    /**
     * @param DataObject $object
     * @return bool|mixed
     * @throws \Exception
     */
    public function updateObject(DataObject $object)
    {
        if (empty($this->mapping[static::ID_COLUMN])) {
            throw new \Exception("ID column " . static::ID_COLUMN . " isn't specified in object model.");
        }

        $idField = $this->mapping[static::ID_COLUMN];
        if (!isset($object->$idField)) {
            throw new \Exception("Field " . $idField . " is not set");
        }

        $data = [];

        foreach ($this->mapping as $column => $local) {
            $modifier = false;
            if (strpos($local, ':') !== false) {
                list($local, $modifier) = explode(':', $local);
            }

            if (!isset($object->$local) || $local == static::ID_COLUMN) continue;

            if (!empty($modifier) && is_object($object->$local)) {
                switch ($modifier) {
                    case 'date':
                        $data[$column] = $object->$local->format('Y-m-d H:i:s');
                        break;

                    case 'timezone':
                        $data[$column] = $object->$local->getName();
                        break;
                }
            } else {
                $data[$column] = $object->$local;
            }
        }

        if (!empty($data)) {
            

            return $this->driver->update(static::TABLE_NAME, $data, static::ID_COLUMN . ' = ' . $this->driver->escape($object->$idField), 1);
        }

        return false;
    }

    /**
     * @param DataObject $object
     * @return mixed
     * @throws \Exception
     */
    public function deleteObject(DataObject $object)
    {
        if (empty($object->{static::ID_COLUMN})) {
            throw new \Exception("Can not delete an object when the id column is not set.");
        }

        return $this->driver->query("delete from " . $this->getTable() . " where `" . static::ID_COLUMN . "` = ? limit 1;", [$object->{static::ID_COLUMN}]);
    }

    /**
     * @param $count
     * @param $keywords
     * @param $fields
     * @param int $page
     * @param int $pagination
     * @return array
     * @throws \Exception
     */
    protected function getSearchedIds(&$count, $keywords, $fields, $page = 1, $pagination = 10)
    {
        if (!is_array($fields) || empty($fields)) {
            throw new \Exception("Invalid or missing fields, please make sure it's an array.");
        }

        if (!is_string($keywords) || empty($keywords)) {
            throw new \Exception("Can't sanitize non-existent search terms.");
        }

        $sql = "
            SELECT " . static::ID_COLUMN . ",
            MATCH (" . implode($fields, ',') . ') AGAINST (' . $this->driver->escape($keywords) . " IN NATURAL LANGUAGE MODE) as relevance
            FROM " . $this->getTable() . "
            having relevance > 0.2
            order by relevance desc
        ";

        $searchQuery = $this->driver->query($sql);

        $searchIds = [];
        foreach ($searchQuery->result() as $row) {
            $searchIds[] = $row->id;
        }

        $count = count($searchIds);

        $offset = (($page - 1) * $pagination);
        $output = array_slice($searchIds, $offset, $pagination);

        return $output;
    }
}