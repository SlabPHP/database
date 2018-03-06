<?php
/**
 * Response Mock
 *
 * @package Slab
 * @subpackage Tests
 * @author Eric
 */
namespace Slab\Tests\Database\Mocks;

class Response implements \Slab\Components\Database\ResponseInterface
{
    /**
     * @var mixed
     */
    private $queryResult;

    /**
     * @var mixed
     */
    private $extra;

    /**
     * @var mixed
     */
    private $suggestedClass;

    /**
     * @param $queryResult
     * @param $linkId
     * @param $suggestedClass
     */
    public function initializeFromQueryResults($queryResult, $rowsAffected, $error, $suggestedClass)
    {
        $this->queryResult = $queryResult;
        $this->extra = $rowsAffected . '-' . $error;
        $this->suggestedClass = $suggestedClass;
    }

    /**
     * @return integer
     */
    public function count()
    {
        return 4;
    }

    /**
     * @return mixed
     */
    public function result()
    {
        return [$this->queryResult, $this->extra, $this->suggestedClass];
    }

    /**
     * @return mixed
     */
    public function row()
    {
        return 'row';
    }
}