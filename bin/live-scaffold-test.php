<?php
/* This is a live database test that illustrates scaffold usage. It requires you to setup the database below first.
 *
 * Make a database on localhost mysql with name 'thingtest' and a user named 'thingtest' with password 'thingtest' with full grants
 *
 * CREATE USER 'thingtest'@'%' IDENTIFIED WITH mysql_native_password;
 * GRANT USAGE ON *.* TO 'thingtest'@'%' REQUIRE NONE WITH MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0;
 * SET PASSWORD FOR 'thingtest'@'%' = '***';
 * CREATE DATABASE IF NOT EXISTS `thingtest`;
 * GRANT ALL PRIVILEGES ON `thingtest`.* TO 'thingtest'@'%';
 * CREATE TABLE `thingtest`.`testingtable` ( `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT , `name` VARCHAR(255) NOT NULL , `description` VARCHAR(255) NOT NULL , `created` DATETIME NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;
 * CREATE TABLE `thingtest`.`test_season` ( `test_id` BIGINT NOT NULL , `updated_date` DATETIME NOT NULL , `season` VARCHAR(255) NOT NULL ) ENGINE = InnoDB;
 *
 * When done testing, delete the user and the database
 *
 * DROP DATABASE `thingtest`
 * DROP USER `thingtest`
 */
require_once(__DIR__ . '/../vendor/autoload.php');

// ===== setup
try
{
    $mysql = new \Mysqli();
    $mysql->connect('localhost', 'thingtest', 'thingtest', 'thingtest',3306);

    $provider = new \Slab\Database\Providers\MySQL\Provider();
    $provider->setMySQL($mysql);

    $db = new \Slab\Database\Driver();
    $db->setProvider($provider);

    $scaffold = new \Slab\Database\Models\MySQL\Scaffold($db);

    mkdir(__DIR__.'/output');

    $scaffold->writeScaffold('testingtable', '\My\Site\\Models', 'Thing', __DIR__.'/output');
    $scaffold->writeScaffold('test_season', '\My\Site\\Models', 'Season', __DIR__.'/output');
}
catch (\Throwable $error)
{
    echo 'Test failed: ' . $error->getMessage() . PHP_EOL . 'Make sure you build the database first before running this.' . PHP_EOL;
}



