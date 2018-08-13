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

/**
 * @todo Pending Exception Classes.
 */
use LordDashMe\Wordpress\DB\Exception\InvalidDatabaseInstance;
use LordDashMe\Wordpress\DB\Exception\WPDatabaseUpdateFunctionsNotFound;

/**
 * @todo Add Comments After The Remaning Tasks On The Development.
 * 
 * Schema Extender Class.
 * 
 * A WordPress Database schema extender that provided 
 * a nice structure of table migration and data seeds.
 * 
 * @author Joshua Clifford Reyes <reyesjoshuaclifford@gmail.com>
 */
class SchemaExtender
{
    protected $db = null;

    protected $seeds = array();

    protected $queries = array();

    protected $tableColumns = array();

    protected $tablePrimaryKey = array();

    protected $tableTemporaryCacheName = '';
    
    /**
     * The initialization process of the schema extender lays here.
     * 
     * @param  mixed  $functionInitCallBack    Holds the outside logic.
     * 
     * @return void
     */
    public function init($functionInitCallBack = null)
    {
        $this->provideDatabase();

        if ($functionInitCallBack instanceof \Closure) {
            $functionInitCallBack($this);
        }
    }

    /**
     * Provide a database instance that will be use of the Schema Extender class.
     * 
     * @return void
     */
    protected function provideDatabase()
    {
        global $wpdb;

        if ((! \class_exists('wpdb')) || (! $wpdb) || (! $wpdb instanceof \wpdb)) {
            throw InvalidDatabaseInstance::wordpressDatabaseIsNotSet();
        }

        $this->db = $wpdb;
    }

    public function table($name, $functionTableSchemaCallback)
    {   
        /**
         * @todo Provide Exception Here To Catch Non-Closure 2nd Args.
         */

        $this->tableTemporaryCacheName = $name;

        $functionTableSchemaCallback($this);
        
        $tableName = $this->tableName($name);
        $tableSchema = \substr(
            $this->parseColumnsStringQuery($tableName) . $this->parsePrimaryKeyStringQuery($tableName), 0, -1
        );
        $tableCharacterSetCollate = $this->getCharacterSetCollate();

        $this->queries[$this->getQueriesIndex()] = "
            CREATE TABLE `{$tableName}` ({$tableSchema}) {$tableCharacterSetCollate};
        ";
    }

    private function parseColumnsStringQuery($tableName)
    {
        $columnsQuery = '';

        foreach ($this->tableColumns[$tableName] as $columnName => $columnStatement) {
            $columnsQuery .= "`{$columnName}` {$columnStatement},";
        }

        return $columnsQuery;
    }

    private function parsePrimaryKeyStringQuery($tableName)
    {
        $primaryKeyQuery = '';
        
        if (isset($this->tablePrimaryKey[$tableName])) {
            $primaryKey = $this->tablePrimaryKey[$tableName];
            $primaryKeyQuery .= "PRIMARY KEY (`{$primaryKey}`),";
        }

        return $primaryKeyQuery;
    }

    public function column($name, $statement)
    {
        $this->tableColumns[$this->tableTemporaryCacheName][$name] = $statement;

        return $this;
    }

    public function primaryKey($name)
    {
        $this->tablePrimaryKey[$this->tableTemporaryCacheName] = $name;
    }

    public function seed($table, $functionSeedColumnsCallback)
    {
        /**
         * @todo Provide Exception Here To Catch Non-Closure or Array Type of 2nd Args.
         */
        $this->tableTemporaryCacheName = $this->getSeedsIndex();

        $this->seeds[$this->tableTemporaryCacheName]['table'] = $this->tableName($table);

        if ($functionSeedColumnsCallback instanceof \Closure) {
            $object = (object) array();
            $functionSeedColumnsCallback = (array) $functionSeedColumnsCallback($object);  
        }

        $seedColumns = array();
        foreach ($functionSeedColumnsCallback as $field => $value) {
            $seedColumns[$field] = $value;
        }

        $this->seeds[$this->tableTemporaryCacheName]['record'] = $seedColumns;

        return $this;
    }

    public function iterate($counter)
    {
        $this->seeds[$this->tableTemporaryCacheName]['iterate'] = $counter;
    }

    public function raw($queries = '')
    {
        if (\is_string($queries)) {
            $this->queries[$this->getQueriesIndex()] = $queries;
        }
    }

    public function tableName($name)
    {
        return $this->getTablePrefix() . $name;
    }


    public function getTablePrefix()
    {
        return $this->db->prefix;
    }

    public function getCharacterSetCollate()
    {
        return $this->db->get_charset_collate();
    }

    public function getQueries($type = 'queries')
    {
        return $this->{$type};
    }

    public function migrate()
    {
        $this->loadQueries();
        $this->loadTableSeeds(); 
    }

    protected function loadQueries()
    {
        /**
         * @todo Test Exception Here.
         */
        if (! \function_exists('dbDelta')) {
            WPDatabaseUpdateFunctionsNotFound::dbDeltaIsNotExist();
        }

        dbDelta($this->processQueries());
    }

    private function processQueries()
    {
        $processedQueries = '';

        foreach ($this->queries as $index => $query) {
            $processedQueries .= \trim($query);
        }

        return $processedQueries;
    }

    protected function loadTableSeeds()
    {
        foreach ($this->seeds as $index => $query) {
            
            if (isset($query['iterate'])) {
                $this->processSeedsIteration($query);  
                continue;
            }

            $this->db->insert($query['table'], $query['record']);
        }
    }

    private function processSeedsIteration($query)
    {
        for ($x = 0; $x <= $query['iterate']; $x++) {
            $this->db->insert($query['table'], $query['record']);
        }
    }

    private function getQueriesIndex()
    {
        return \count($this->queries);
    }

    private function getSeedsIndex()
    {
        return \count($this->seeds);   
    }
}