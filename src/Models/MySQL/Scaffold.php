<?php
/**
 * Scaffold Class can help you build models from already built database tables
 *
 * @package Slab
 * @subpackage Database
 * @author Eric
 */
namespace Slab\Database\Models\MySQL;

class Scaffold
{
    /**
     * @var \Slab\Database\Driver
     */
    private $driver;

    /**
     * @var \Mustache_Engine
     */
    private $mustache;

    /**
     * Scaffold constructor.
     * @param \Slab\Database\Driver $driver
     */
    public function __construct(\Slab\Database\Driver $driver)
    {
        $this->driver = $driver;
    }

    /**
     * @param $tableName
     * @param $baseModelNamespace
     * @param $className
     * @throws \Slab\Database\Exceptions\Mapping
     */
    public function printScaffold($tableName, $baseModelNamespace, $className)
    {
        if (!class_exists('\Mustache_Engine'))
        {
            throw new \Slab\Database\Exceptions\Mapping("Scaffolding requires Mustache package to be installed.");
        }

        if (mb_strlen($baseModelNamespace) < 2)
        {
            throw new \Slab\Database\Exceptions\Mapping("Base namespace is too small.");
        }

        $this->mustache = new \Mustache_Engine();

        $data = $this->generateScaffoldData($tableName, $baseModelNamespace, $className);

        $resourceDir = __DIR__ . '/../../../resources/templates/scaffold/';
        $dataObject = $this->mustache->render(file_get_contents($resourceDir . 'dataobject.mustache'), $data);

        $loader = $this->mustache->render(file_get_contents($resourceDir . 'loader.mustache'), $data);

        echo $dataObject . PHP_EOL . PHP_EOL . $loader;
    }

    /**
     * @param $tableName
     * @param $baseModelNamespace
     * @param $className
     * @param $modelDirectory
     * @throws \Slab\Database\Exceptions\Mapping
     */
    public function writeScaffold($tableName, $baseModelNamespace, $className, $modelDirectory)
    {
        if (!class_exists('\Mustache_Engine'))
        {
            throw new \Slab\Database\Exceptions\Mapping("Scaffolding requires Mustache package to be installed.");
        }

        if (mb_strlen($baseModelNamespace) < 2)
        {
            throw new \Slab\Database\Exceptions\Mapping("Base namespace is too small.");
        }

        if (!is_dir($modelDirectory))
        {
            throw new \Slab\Database\Exceptions\Mapping("Directory " . $modelDirectory . " does not exist.");
        }

        $this->mustache = new \Mustache_Engine();

        $data = $this->generateScaffoldData($tableName, $baseModelNamespace, $className);

        $resourceDir = __DIR__ . '/../../../resources/templates/scaffold/';

        $dataObject = $this->mustache->render(file_get_contents($resourceDir . 'dataobject.mustache'), $data);
        $loader = $this->mustache->render(file_get_contents($resourceDir . 'loader.mustache'), $data);

        if (!is_dir($modelDirectory . '/' . $data->className))
        {
            mkdir($modelDirectory . '/' . $data->className);
        }

        file_put_contents($modelDirectory . '/' . $data->className . '/DataObject.php', $dataObject);
        file_put_contents($modelDirectory . '/' . $data->className . '/Loader.php', $loader);
    }

    /**
     * @param $tableName
     * @param $baseModelNamespace
     * @param $className
     * @return \stdClass
     */
    private function generateScaffoldData($tableName, $baseModelNamespace, $className)
    {
        $info = $this->driver->query("DESCRIBE `" . $tableName . '`');

        if ($baseModelNamespace[0] == '\\')
        {
            $baseModelNamespace = mb_substr($baseModelNamespace, 1);
        }

        if ($baseModelNamespace[mb_strlen($baseModelNamespace) - 1] == '\\')
        {
            $baseModelNamespace = mb_substr($baseModelNamespace, 0, -1);
        }

        $segments = explode('\\', $baseModelNamespace);

        $templateData = new \stdClass();
        $templateData->className = $className;
        $templateData->package = $segments[0];
        $templateData->namespace = $baseModelNamespace;
        //$templateData->className = $segments[count($segments) - 1];
        $templateData->tableName = $tableName;
        $templateData->loaderName = '\\' . $baseModelNamespace . '\\' . $className . '\\Loader';
        $templateData->dataObjectName = '\\' . $baseModelNamespace . '\\' . $className . '\\DataObject';
        $templateData->fields = [];
        $templateData->primaryField = 'id';

        foreach ($info->result() as $column) {

            $fieldObject = new \stdClass();
            $fieldObject->modifier = false;
            $fieldObject->type = $this->convertMysqlTypeToPHP($column->Type,$fieldObject->modifier);
            $fieldObject->name = $this->generateCamelCaseColumn($column->Field);
            $fieldObject->mappedName = $fieldObject->name;
            if (!empty($fieldObject->modifier))
            {
                $fieldObject->mappedName .= ':' . $fieldObject->modifier;
            }

            if (!empty($column->Key) && $column->Key == 'PRI') {
                $templateData->primaryField = $fieldObject->name;
            }

            $fieldObject->column = $column->Field;
            $fieldObject->default = '';

            if (!empty($column->Default)) {
                if (is_string($column->Default)) {
                    $fieldObject->default = " = '" . addslashes($column->Default) . "'";
                } else if (is_int($column->Default)) {
                    $fieldObject->default = ' = ' . $column->default;
                }
            }

            $fieldObject->comment = '';
            if (stripos($column->Type, 'enum') !== false) {
                $fieldObject->comment .= 'Valid values of ' . $column->Type;
            }

            $templateData->fields[] = $fieldObject;
        }

        return $templateData;
    }

    /**
     * @param $type
     * @param $modifier
     * @return string
     */
    private function convertMysqlTypeToPHP($type, &$modifier)
    {
        if (stripos($type, 'int') !== false) {
            return 'number';
        } else if (stripos($type, 'char') !== false || stripos($type, 'text') !== false) {
            return 'string';
        } else if (stripos($type, 'date') !== false) {
            $modifier = 'date';
            return '\DateTime';
        } else if (stripos($type, 'enum') !== false) {
            return 'string';
        }

        return $type;
    }

    /**
     * @param $columnName
     * @return string
     */
    private function generateCamelCaseColumn($columnName)
    {
        $output = '';
        $capNext = false;

        for ($i = 0; $i < mb_strlen($columnName); ++$i) {
            $character = $columnName[$i];

            if ($character == '_') {
                $capNext = true;
            } else {
                if ($capNext) {
                    $capNext = false;
                    $output .= strtoupper($character);
                } else {
                    $output .= $character;
                }
            }
        }

        return $output;
    }
}
