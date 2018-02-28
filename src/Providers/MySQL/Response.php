<?php
/**
 * MySQLi Response Object
 *
 * @author Eric
 * @package Slab
 * @subpackage Database
 * @class MySQL
 */
namespace Slab\Database\Providers\MySQL;

class Response extends \Slab\Database\Providers\BaseResponse
{
    /**
     * @param \Mysqli_Result $queryResult
     * @param mixed|null $rowsAffected
     * @param null|string $error
     * @param $suggestedClass
     * @return mixed|void
     */
    public function initializeFromQueryResults($queryResult, $rowsAffected, $error, $suggestedClass)
    {
        $this->error = $error;
        $this->numberOfResults = $rowsAffected;

        if (!is_object($queryResult)) {
            $this->results = $queryResult;
            return;
        }

        $this->numberOfResults = $queryResult->num_rows;

        $this->results = array();

        $joinClasses = [];
        if (is_array($suggestedClass) && !empty($suggestedClass)) {
            $class = array_shift($suggestedClass);
            $joinClasses = $suggestedClass;
        } else {
            $class = !empty($suggestedClass) ? $suggestedClass : '\stdClass';
        }

        while (($object = $queryResult->fetch_object($class)) !== null) {
            //Perform any joins
            if (!empty($joinClasses)) {
                foreach ($joinClasses as $joinClass) {
                    if ($joinClass instanceof \Slab\Database\Models\MySQL\Join) {
                        $joinClass->resolve($object);
                    }
                }
            }

            //Append result
            $this->results[] = $object;

            //Run walk callback on the object if necessary
            if (method_exists($object, '__mapObject')) {
                $object->__mapObject();
            }
        }

        $queryResult->close();
    }
}