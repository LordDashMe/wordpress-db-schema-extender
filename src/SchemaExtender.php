<?php

/*
 * This file is part of the Wordpress DB Schema Extender.
 *
 * (c) Joshua Clifford Reyes <reyesjoshuaclifford@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LordDashMe\Wordpress\DB;

use LordDashMe\Wordpress\DB\Exception\InvalidArgumentPassed;
use LordDashMe\Wordpress\DB\Exception\InvalidDatabaseInstance;
use LordDashMe\Wordpress\DB\Exception\WPDatabaseUpdateFunctionsNotFound;

/**
 * Schema Extender Class.
 * 
 * A WordPress Database schema extender that provided 
 * a nice structure of table migration and data seeds.
 * 
 * @author Joshua Clifford Reyes <reyesjoshuaclifford@gmail.com>
 */
class SchemaExtender
{
    /**
     * The current database instance provided in the initialization process.
     * 
     * @var mixed
     */
    protected $db = null;

    /**
     * The current table name pointer.
     * 
     * @var string
     */
    protected $tableName = '';

    /**
     * The table primary key that will be use.
     * 
     * @var string
     */
    protected $tablePrimaryKey = '';

    /**
     * The table columns that will be use.
     * 
     * @var array
     */
    protected $tableColumns = array();

    /**
     * Stored all the queries define after the initialization process of the class.
     * 
     * @var array
     */
    protected $queries = array();

    /**
     * Stored all the queries for table seeding.
     * 
     * @var array
     */
    protected $seedQueries = array();

    /**
     * Hold the current seed queries index.
     * 
     * @var int
     */
    protected $seedQueryIndex = 0;

    /**
     * The setter method for the table name class property.
     * 
     * @param  string  $name
     * 
     * @return $this
     */
    public function setTableName($name)
    {
        $this->tableName = $name;

        return $this;
    }

    /**
     * The getter method for the table name class property.
     * 
     * @return string
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * Alias for converting the given table name with 
     * the current prefix setted in the configuration.
     * 
     * @param  string  $name
     * 
     * @return string
     */
    public function tableName($name)
    {
        return $this->getTableNamePrefix($name);
    }

    /**
     * Get the current wordpress config for table name prefix.
     * 
     * @return string
     */
    public function getTableNamePrefix($name)
    {
        return $this->db->prefix . $name;
    }

    /**
     * Get the character set collate in the config file.
     * 
     * @return string
     */
    public function getCharacterSetCollate()
    {
        return $this->db->get_charset_collate();
    }

    /**
     * Set the table columns with the given statement.
     * 
     * @param  string  $name
     * @param  string  $statement
     * 
     * @return $this
     */
    public function column($name, $statement)
    {
        $this->tableColumns[$name] = $statement;

        return $this;
    }

    /**
     * Get all the table columns stored.
     * 
     * @return array
     */
    public function columns()
    {
        return $this->tableColumns;
    }

    /**
     * Unset all the columns stored to free some memory.
     * 
     * @return void
     */
    public function flushColumns()
    {
        $this->tableColumns = array();
    }

    /**
     * The primary key for the current table pointer.
     * 
     * @param  string  $columnName
     * 
     * @return void
     */
    public function primaryKey($columnName)
    {
        $this->tablePrimaryKey = $columnName;
    }

    /**
     * Unset the primary key stored to free some memory.
     * 
     * @return void
     */
    public function flushPrimaryKey()
    {
        $this->tablePrimaryKey = '';
    }

    /**
     * Get all the stored queries.
     * 
     * @return array
     */
    public function getQueries()
    {
        return $this->queries;
    }
    
    /**
     * Get the current total queries.
     * 
     * @return int
     */
    public function totalQueries()
    {
        return \count($this->queries);
    }

    /**
     * The stter for the seed queries class property.
     * 
     * @param  string  $key
     * @param  mixed   $value
     * 
     * @return void
     */
    public function setSeedQuery($key, $value)
    {
        $index = $this->getSeedQueryIndex();

        $this->seedQueries[$index][$key] = $value;
    }
    
    /**
     * Get all the stored seed queries.
     * 
     * @return array
     */
    public function getSeedQueries()
    {
        return $this->seedQueries;
    }

    /**
     * Get the current total seed queries.
     * 
     * @return int
     */
    public function totalSeedQueries()
    {
        return \count($this->seedQueries);
    }

    /**
     * Set the current seed query index.
     * 
     * @param  int  $index
     * 
     * @return void
     */
    public function setSeedQueryIndex($index)
    {
        $this->seedQueryIndex = $index;
    }

    /**
     * Get the current seed query index.
     * 
     * @return int
     */
    public function getSeedQueryIndex()
    {
        return $this->seedQueryIndex;
    }

    /**
     * Unset the seed query index stored to free some memory.
     * 
     * @return void
     */
    public function flushSeedQueryIndex()
    {
        $this->seedQueryIndex = '';
    }

    /**
     * The initialization point of the class. All of the main process included
     * in this method.
     * 
     * @param  \Closure  $callback
     * 
     * @return void
     */
    public function init($callback = null)
    {
        $this->getWordpressDatabaseInstance();

        if ($callback instanceof \Closure) {
            $callback($this);
        }
    }

    /**
     * Tightly coupled to the wordpress database instance.
     * 
     * @return void
     */
    protected function getWordpressDatabaseInstance()
    {
        global $wpdb;

        if ((! \class_exists('wpdb')) || (! $wpdb) || (! $wpdb instanceof \wpdb)) {
            throw InvalidDatabaseInstance::wordpressDatabaseIsNotSet();
        }

        $this->db = $wpdb;
    }

    /**
     * The composer of the database table schema.
     * 
     * @param  string    $name
     * @param  \Closure  $callback
     * 
     * @throws LordDashMe\Wordpress\DB\Exception\InvalidArgumentPassed
     * 
     * @return void
     */
    public function table($name, $callback = null)
    {   
        if (! $callback instanceof \Closure) {
            throw InvalidArgumentPassed::isNotClosure();
        }

        $this->setTableName($name);

        $callback($this);
        
        $tableNamePrefixed = $this->getTableNamePrefix($this->getTableName());
        $tableStructure = \substr($this->getColumnsQuery() . $this->getPrimaryKeyQuery(), 0, -2);
        $tableCharsetCollate = $this->getCharacterSetCollate();

        $this->buildCreateTableQuery($tableNamePrefixed, $tableStructure, $tableCharsetCollate);

        $this->flushColumns();
        $this->flushPrimaryKey();
    }

    /**
     * Process the given columns into valid query.
     * 
     * @return string
     */
    protected function getColumnsQuery()
    {
        $columns = '';

        foreach ($this->columns() as $column => $statement) {
            $columns .= "`{$column}` {$statement}, ";
        }

        return $columns;
    }

    /**
     * Process the given primary key into valid query.
     * 
     * @return string
     */
    protected function getPrimaryKeyQuery()
    {
        if (! empty($this->tablePrimaryKey)) {
            return "PRIMARY KEY (`{$this->tablePrimaryKey}`), ";  
        }

        return '';
    }

    /**
     * Build a create table query base on the complete details given.
     * 
     * @param  string  $name              The table name with prefix.
     * @param  string  $structure         The structure of the tables containing columns, etc.
     * @param  string  $charsetCollate    The character set for the table structure.
     * 
     * @return void
     */
    protected function buildCreateTableQuery($name, $struture, $charsetCollate)
    {
        $total = $this->totalQueries();

        $this->queries[$total] = "CREATE TABLE `{$name}` ({$struture}) {$charsetCollate};";
    }

    /**
     * The composer for seeding the given table name.
     * 
     * @param  string          $tableName
     * @param  array|\Closure  $callback
     * 
     * @throws LordDashMe\Wordpress\DB\Exception\InvalidArgumentPassed
     * 
     * @return $this
     */
    public function tableSeed($tableName, $callback)
    {
        if (! is_array($callback) && ! ($callback instanceof \Closure)) {
            throw InvalidArgumentPassed::isNotArrayOrClosure();    
        }

        $this->setTableName($tableName);

        // Make sure to flush the old seed query index
        // to avoid collision with the new incoming value.
        $this->flushSeedQueryIndex();

        if ($callback instanceof \Closure) {
            $obj = (object) array();
            $callback = (array) $callback($obj);
        }

        $seeds = array();

        foreach ($callback as $column => $value) {
            $seeds[$column] = $value;
        }

        $this->setSeedQueryIndex($this->totalSeedQueries());

        $this->setSeedQuery('table', $this->getTableNamePrefix($this->getTableName()));
        $this->setSeedQuery('record', $seeds);

        return $this;
    }

    /**
     * The repetition that will apply for the given record.
     * 
     * @param  int  $counter    Total number of repetition for the given record.
     * 
     * @return void
     */
    public function iterate($counter)
    {
        $this->setSeedQuery('iterate', $counter);
    }

    /**
     * Allow to compose raw database query.
     * 
     * @param  string  $query
     * 
     * @throws LordDashMe\Wordpress\DB\Exception\InvalidArgumentPassed
     * 
     * @return void
     */
    public function rawQuery($query = '')
    {
        if (! \is_string($query)) {
            throw InvalidArgumentPassed::isNotString();
        }

        $total = $this->totalQueries();

        $this->queries[$total] = \trim($query);
    }

    /**
     * The queries processing action, combine all the queries and seeds.
     * 
     * @throws LordDashMe\Wordpress\DB\Exception\WPDatabaseUpdateFunctionsNotFound
     * 
     * @return void
     */
    public function migrate()
    {
        $this->processQueries();
        $this->processTableSeeds();
    }

    /**
     * Process the queries using the wordpress "dbDelta".
     * This function is tightly coupled to the wordpress "dbDelta".
     * 
     * @return void
     */
    protected function processQueries()
    {
        if (! \function_exists('dbDelta')) {
            throw WPDatabaseUpdateFunctionsNotFound::dbDeltaIsNotExist();   
        }

        $queries = '';

        foreach ($this->getQueries() as $index => $query) {
            $queries .= \trim($query);
        }

        \dbDelta($queries); 
    }

    /**
     * Process the seed queries using the wordpress database "insert".
     * 
     * @return void
     */
    protected function processTableSeeds()
    {
        foreach ($this->getSeedQueries() as $index => $query) {
            
            if (isset($query['iterate'])) {
                for ($x = 1; $x <= $query['iterate']; $x++) {
                    $this->db->insert($query['table'], $query['record']);
                }
                continue;
            }

            $this->db->insert($query['table'], $query['record']);
        }
    }

    /**
     * Process the drop table base on the given table name.
     * Foreign key checks disable before processing the drop action and
     * enable it back again after the process.
     * 
     * @param  string  $tableName
     * 
     * @return void
     */
    public function dropTable($tableName)
    {
        $this->db->query('SET FOREIGN_KEY_CHECKS = 0;');
        $this->db->query("DROP TABLE IF EXISTS `{$this->getTableNamePrefix($tableName)}`;");
        $this->db->query('SET FOREIGN_KEY_CHECKS = 1;');  
    }

    /**
     * Alis for drop table supporting multiple table name via array.
     * 
     * @param  array  $tableNames
     * 
     * @return void
     */
    public function dropTables($tableNames)
    {
        foreach ($tableNames as $tableName) {
            $this->dropTable($tableName);
        }
    }
}
