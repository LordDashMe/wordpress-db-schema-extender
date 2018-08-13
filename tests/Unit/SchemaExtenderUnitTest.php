<?php

namespace LordDashMe\Wordpress\DB\Tests\Unit;

use Mockery as Mockery;
use PHPUnit\Framework\TestCase;
use LordDashMe\Wordpress\DB\SchemaExtender;

class SchemaExtenderUnitTest extends TestCase
{
    /**
     * @test
     */
    public function it_should_load_schema_extender_class()
    {
        $this->assertInstanceOf(SchemaExtender::class, new SchemaExtender());
    }
    
    /**
     * @test
     * @expectedException LordDashMe\Wordpress\DB\Exception\InvalidDatabaseInstance
     * @expectedExceptionCode 100
     */
    public function it_should_throw_exception_when_wpdb_not_detected()
    {
        $schemaExtender = new SchemaExtender();
        $schemaExtender->init();
    }

    /**
     * @test
     */
    public function it_should_create_table_schema()
    {
        global $wpdb;

        $wpdb = Mockery::mock('wpdb');
        $wpdb->prefix = 'wp_';
        $wpdb->shouldReceive('get_charset_collate')
             ->andReturn('UTF-8');

        $schemaExtender = new SchemaExtender();
        $schemaExtender->init();

        $schemaExtender->table('users', function($table) {
            $table->column('id', 'INT(11) NOT NULL AUTO_INCREMENT');
            $table->column('name', 'TEXT NULL');
            $table->primaryKey('id');
        });

        $queries = $schemaExtender->getQueries();

        $this->assertEquals(105, strlen($queries[0]));
    }

    /**
     * @test
     */
    public function it_should_create_table_seed_via_closure()
    {
        global $wpdb;

        $wpdb = Mockery::mock('wpdb');
        $wpdb->prefix = 'wp_';
        $wpdb->shouldReceive('get_charset_collate')
             ->andReturn('UTF-8');

        $schemaExtender = new SchemaExtender();
        $schemaExtender->init();

        $schemaExtender->seed('users', function($column) {
            $column->id = 1;
            $column->name = 'John Doe';
            return $column;
        })->iterate(5);

        $queries = $schemaExtender->getQueries('seeds');

        $this->assertEquals(
            array(
                'id' => 1,
                'name' => 'John Doe'
            ),
            $queries[0]['record']
        );
    }

    /**
     * @test
     */
    public function it_should_create_table_seed_via_array()
    {
        global $wpdb;

        $wpdb = Mockery::mock('wpdb');
        $wpdb->prefix = 'wp_';
        $wpdb->shouldReceive('get_charset_collate')
             ->andReturn('UTF-8');

        $schemaExtender = new SchemaExtender();
        $schemaExtender->init();

        $schemaExtender->seed('users', [
            'id' => 1, 'name' => 'John Doe'
        ])->iterate(5);

        $queries = $schemaExtender->getQueries('seeds');

        $this->assertEquals(
            array(
                'id' => 1,
                'name' => 'John Doe'
            ),
            $queries[0]['record']
        );
    }

    /**
     * @test 
     */
    public function it_should_accept_raw_queries()
    {
        global $wpdb;

        $wpdb = Mockery::mock('wpdb');
        $wpdb->prefix = 'wp_';
        $wpdb->shouldReceive('get_charset_collate')
             ->andReturn('UTF-8');   
        
        $schemaExtender = new SchemaExtender();
        $schemaExtender->init();

        $schemaExtender->table('users', function($table) {
            $table->column('id', 'INT(11) NOT NULL AUTO_INCREMENT');
            $table->column('name', 'TEXT NULL');
            $table->primaryKey('id');
        });

        $schemaExtender->raw('
            ALTER TABLE ' . $schemaExtender->tableName('users') . '
                ADD KEY `user_id` (`user_id`);
            ALTER TABLE ' . $schemaExtender->tableName('users') . ' 
                ADD CONSTRAINT `user_options_constraint` 
                FOREIGN KEY (`user_id`) 
                REFERENCES ' . $schemaExtender->tableName('user_options') . ' (`id`) 
                ON DELETE CASCADE 
                ON UPDATE NO ACTION;
        ');

        $queries = $schemaExtender->getQueries();

        var_dump($queries);
    }
}