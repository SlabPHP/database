# SlabPHP Database Library

Every framework needs some way of wrapping/organizing database stuff right? The SlabPHP database library adds two concepts to the overall framework. The first is an abstraction layer between database providers and to provide variable replacements in query messages. The second is a lightweight data object model for building solid object databaes return objects.

This library was originally built to provide interface abstraction and wrap various mysql functions. They were eventually deprecated and the rest of the framework relied on this abstraction layer so a new provider was created that wrapped a mysqli class.

## Usage

First include this library with composer

    composer require slabphp/database

### Setup

The general idea is to create a provider, create a driver, and then create data models for your objects.

    $mysql = new \Mysqli();
    $mysql->connect(...);

    $provider = new \Slab\Database\Providers\MySQL\Provider();
    $provider->setMySQL($mysql);

    $db = new \Slab\Database\Driver();
    $db->setProvider($provider);

At this point you'll be able to use the $db driver to make direct queries or use the data model system.

### Data Model Example

You can create models and loader objects for those models. For example, lets say you are building a really lightweight blog.

You may build something like the following, where your data object at ~/src/Models/Article/DataObject.php looks like:

    namespace \My\Site\Models\Article;

    class DataObject extends \Slab\Database\Models\MySQL\DataObject
    {
        const DATA_LOADER = '\My\Site\Models\Article\Loader';

        public $id;

        public $title;

        public $body;

        public $created;

        public $authorId;
    }

And your loader at ~/src/Models/Article/Loader.php may look like the following:

    namespace \My\Site\Models\Article;

    class Loader extends \Slab\Database\Models\MySQL\Loader
    {
        const DATA_OBJECT_CLASS = '\My\Site\Models\Article\DataObject';

        const TABLE_NAME = 'articles';

        protected $mapping = [
            'id' => 'id',
            'title' => 'title',
            'body' => 'body',
            'date_created' => 'created:date',
            'author_id' => 'authorId'
        ];

        public function getLatestArticles()
        {
            $sql = "select " . $this->getMappingSQL() . " from " . $this->getTable() . " order by date_created desc limit 20;";
            $resultObject = $this->driver->query($sql, [], static::DATA_OBJECT_CLASS);

            return $resultObject->result();
        }
    }

There are many default functions in the base MySQL loader but the general idea is that is where you store your fetching queries. The DataObject would be your hard object for return results. Aside, this was split out when this was open sourced. Both of these classes used to be in one and used many static methods.

From here, you can use the loader in your controller queries. For example, assume your controller exists with a member $this->driver which is a SlabPHP database driver. Your controller has a function:

    function getArticles()
    {
        try
        {
            $loader = new \My\Site\Models\Articles\Loader($this->driver);
            $this->articles = $loader->getLatestArticles();

            //$this->articles is \My\Site\Models\Articles\DataObject[] an array of dataobjects
        }
        catch (\Throwable $exception)
        {
            //handle appropriately
        }
    }

If you need to create a new loader to use a different data source, this should allow you to have very minimal changes in your business logic.

### Data Model Joins

This library also contains a hopefully easy way of creating a hierarchical data structure using combined queries and joins. For example, lets say we wanted to add an author object to our articles. We have another "authors" table with the following data models created.

    namespace \My\Site\Models\Author;

    class DataObject extends \Slab\Database\Models\MySQL\DataObject
    {
        const DATA_LOADER = '\My\Site\Models\Author\Loader';

        public $id;

        public $name;

        public $lastPostDate;
    }

and the author loader looks like:

    namespace \My\Site\Models\Author;

    class Loader extends \Slab\Database\Models\MySQL\Loader
    {
        const DATA_OBJECT_CLASS = '\My\Site\Models\Author\DataObject';

        const TABLE_NAME = 'authors';

        protected $mapping = [
            'id' => 'id',
            'name' => 'name',
            'last_post_date' => 'lastPostDate:date'
        ];
    }

We could modify the getLatestArticles in the Article\Loader() class to make it look something like this:

    public function getLatestArticles()
    {
        // Step 1, create a join helper. it just means we want to join the author DataObject with a table alias of 'a' and
        // put it in the 'author' member of the resulting object
        $authorJoin = new \Slab\Database\Models\MySQL\Join('\My\Site\Models\Author\DataObject', 'a', 'author');

        // Step 2, add the Author data object fields to the select
        $sql = "select " . $this->getMappingSQL() . ", " . $authorJoin->getMappingSQL();

        $sql .= " from " . $this->getTable() . " order by date_created desc limit 20;";

        // Step 3, finally we craft an array with the returned class as array object 1, and the joins as extra elements
        // This is admittedly kind of a bad design since its unintuitive. Maybe we can fix it later?
        $resultObject = $this->driver->query($sql, [], [static::DATA_OBJECT_CLASS, $authorJoin]);

        return $resultObject->result();
    }

Now the when the controller runs your return object will look like this, where the author object gets mapped from the response fields into an Author child object.

    Article Object
    (
        [id] => 1
        [title] => First Article
        [body] => Hello, this is my first article
        [created] => DateTime Object
            (
                [date] => 2018-02-05 00:00:00.00000
                [timezone_type] => 3
                [timezone] => America/New_York
            )
        [authorId] => 14
        [author] => Author Object
            (
                [id] => 14
                [name] => Steven Exampler
                [lastPostDate] => DateTime Object
                    (
                        [date] => 2018-02-05 00:00:00.00000
                        [timezone_type] => 3
                        [timezone] => America/New_York
                    )
            )
    )

Also notice that the mapped fields with :date after them get automatically translated into \DateTime objects. This is not exclusive to the join, it happens without it as well.