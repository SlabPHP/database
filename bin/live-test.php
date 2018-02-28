<?php
/* This is a live database test that also illustrates some usage of the data model and joining
 *
 * Make a database on localhost mysql with name 'thingtest' and a user named 'thingtest' with password 'thingtest' with full grants
 *
 * CREATE USER 'thingtest'@'%' IDENTIFIED WITH mysql_native_password;
 * GRANT USAGE ON *.* TO 'thingtest'@'%' REQUIRE NONE WITH MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0;
 * SET PASSWORD FOR 'thingtest'@'%' = '***';
 * CREATE DATABASE IF NOT EXISTS `thingtest`;
 * GRANT ALL PRIVILEGES ON `thingtest`.* TO 'thingtest'@'%';
 * CREATE TABLE `thingtest`.`testingtable` ( `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT , `name` VARCHAR(255) NOT NULL , `description` VARCHAR(255) NOT NULL , `created` DATETIME NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;
 * INSERT INTO `testingtable` (`id`, `name`, `description`, `created`) VALUES (NULL, 'Item One', 'Something about item one.', '2018-02-05 00:00:00'), (NULL, 'Item Two', 'Something about something else.', '2018-02-16 00:00:00');
 * CREATE TABLE `thingtest`.`test_season` ( `test_id` BIGINT NOT NULL , `updated_date` DATETIME NOT NULL , `season` VARCHAR(255) NOT NULL ) ENGINE = InnoDB;
 * INSERT INTO `test_season` (`test_id`, `updated_date`, `season`) VALUES ('1', '2018-02-07 00:00:00', 'Fall'), ('2', '2018-02-10 00:00:00', 'Summer')
 *
 * When done testing, delete the user and the database
 *
 * DROP DATABASE `thingtest`
 * DROP USER `thingtest`
 */
require_once(__DIR__ . '/../vendor/autoload.php');

class Thing extends \Slab\Database\Models\MySQL\DataObject
{
    const DATA_LOADER = '\ThingLoader';

    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $about;

    /**
     * @var \DateTime
     */
    public $dateCreated;

    public function __toString()
    {
        $output = $this->id . '-' . $this->name . ' = ' . $this->dateCreated->format('m/d/Y') . ': ' . $this->about;

        if (!empty($this->season))
        {
            $output .= ' (Season ' . $this->season->season . ' at ' . $this->season->updatedDate->format('m/d/Y') . ')';
        }

        return $output . PHP_EOL;
    }
}

class Season extends \Slab\Database\Models\MySQL\DataObject
{
    const DATA_LOADER = '\SeasonLoader';

    public $testId;

    public $updatedDate;

    public $season;
}

class ThingLoader extends \Slab\Database\Models\MySQL\Loader
{
    const DATA_OBJECT_CLASS = '\Thing';

    const TABLE_NAME = 'testingtable';

    protected $mapping = [
        'id' => 'id',
        'name' => 'name',
        'description' => 'about',
        'created' => 'dateCreated:date'
    ];

    /**
     * @return mixed
     * @throws \Slab\Database\Exceptions\Mapping
     */
    public function fetchThings()
    {
        $seasonJoin =  new \Slab\Database\Models\MySQL\Join('\Season', 's', 'season');

        $sql = "
            select " . $this->getMappingAsSQL() . ", " . $seasonJoin->getMappingSQL() . "
            from " . $this->getTable() . "
            " . $seasonJoin->leftJoinOn('id')  . "
            order by `created` desc;";
        $result = $this->driver->query($sql, [],[static::DATA_OBJECT_CLASS, $seasonJoin]);

        return $result->result();
    }
}

class SeasonLoader extends \Slab\Database\Models\MySQL\Loader
{
    const DATA_OBJECT_CLASS = '\Season';

    const TABLE_NAME = 'test_season';

    const ID_COLUMN = 'test_id';

    protected $mapping = [
        'test_id' => 'testId',
        'updated_date' => 'updatedDate:date',
        'season' => 'season'
    ];
}

// ===== setup
try
{
    $mysql = new \Mysqli();
    $mysql->connect('localhost', 'thingtest', 'thingtest', 'thingtest',3306);

    $provider = new \Slab\Database\Providers\MySQL\Provider();
    $provider->setMySQL($mysql);

    $db = new \Slab\Database\Driver();
    $db->setProvider($provider);

    $loader = new \ThingLoader();
    $loader->setDriver($db);
    $things = $loader->fetchThings();

    foreach ($things as $thing)
    {
        echo (string)$thing;
    }
}
catch (\Throwable $error)
{
    echo 'Test failed: ' . $error->getMessage() . PHP_EOL . 'Make sure you build the database first before running this.' . PHP_EOL;
}



