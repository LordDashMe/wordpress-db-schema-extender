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

use LordDashMe\Wordpress\DB\Exception\InvalidDatabaseInstance;

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
     * The database instance that will be using by the Schema Extender class.
     * 
     * @var mixed
     */
    protected $db = null;

    protected $seeds = array();

    protected $queries = array();

    protected $tableColumns = array();

    protected $tablePrimaryKey = array();

    protected $tableTemporaryCacheName = '';

    /**
     * Holds the logic that's provided outside of the Schema Extender class.
     * 
     * @var mixed
     */
    protected $functionCallBack = null;
    
    /**
     * The initialization process of the schema extender lays here.
     * 
     * @param  mixed  $functionCallBack    Holds the outside logic.
     * 
     * @return void
     */
    public function init($functionCallBack = null)
    {
        $this->provideDatabase();

        $this->functionCallBack = $functionCallBack;
    }

    /**
     * Provide a database instance that will be use of the Schema Extender class.
     * 
     * @return void
     */
    protected function provideDatabase()
    {
        global $wpdb;

        if ((! class_exists('wpdb')) || (! $wpdb) || (! $wpdb instanceof \wpdb)) {
            throw InvalidDatabaseInstance::wordpressDatabaseIsNotSet();
        }

        $this->db = $wpdb;
    }

    public function table($name, $functionTableSchemaCallback)
    {   
        $this->tableTemporaryCacheName = $name;

        $functionTableSchemaCallback($this);

        $columnsQuery = '';
        foreach ($this->tableColumns[$name] as $columnName => $columnStatement) {
            $columnsQuery .= "`{$columnName}` {$columnStatement},";
        }

        $primaryKeyQuery = '';
        if (isset($this->tablePrimaryKey[$name])) {
            $primaryKey = $this->tablePrimaryKey[$name];
            $primaryKeyQuery .= "PRIMARY KEY (`{$primaryKey}`),";
        }
        
        $tableName = $this->tableName($name);
        $tableSchema = substr($columnsQuery . $primaryKeyQuery, 0, -1);
        $tableCharacterSetCollate = $this->getCharacterSetCollate();

        $this->queries[$this->getQueriesIndex()] = "CREATE TABLE `{$tableName}` ({$tableSchema}) {$tableCharacterSetCollate};";
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
        $this->tableTemporaryCacheName = $this->getSeedsIndex();

        $this->seeds[$this->tableTemporaryCacheName]['table'] = $this->tableName($table);

        if ($functionSeedColumnsCallback || is_array($functionSeedColumnsCallback)) {

            if ($functionSeedColumnsCallback instanceof \Closure) {
                $object = (object) [];
                $functionSeedColumnsCallback = (array) $functionSeedColumnsCallback($object);  
            }

            $seedColumns = [];
            foreach ($functionSeedColumnsCallback as $field => $value) {
                $seedColumns[$field] = $value;
            }

            $this->seeds[$this->tableTemporaryCacheName]['record'] = $seedColumns;

            return $this;
        }
    }

    public function iterate($counter)
    {
        $this->seeds[$this->tableTemporaryCacheName]['iterate'] = $counter;
    }

    public function raw($queries = '')
    {
        if (is_string($queries)) {
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

    protected function getQueriesIndex()
    {
        return count($this->queries);
    }

    protected function getSeedsIndex()
    {
        return count($this->seeds);   
    }
}