<?php
/**
 * Database Model Interface
 *
 * @package Slab
 * @subpackage Database
 * @author Eric
 */
namespace Slab\Database\Models;

interface LoaderInterface
{
    /**
     * ModelLoaderInterface constructor.
     * @param \Slab\Database\Driver $driver
     */
    public function setDriver(\Slab\Database\Driver $driver);

    /**
     * @param \Psr\Log\LoggerInterface $log
     * @return mixed
     */
    public function setLog(\Psr\Log\LoggerInterface $log);

    /**
     * @param $id
     * @return mixed
     */
    public function getById($id);
}
