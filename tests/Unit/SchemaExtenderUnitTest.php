<?php

namespace LordDashMe\Wordpress\DB\Tests\Unit;

use Mockery as Mockery;
use PHPUnit\Framework\TestCase;
use LordDashMe\Wordpress\DB\SchemaExtender;
use LordDashMe\Wordpress\DB\Facade\SchemaExtender as SchemaExtenderFacade;

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
    public function it_should_create_table_seeds_via_closure()
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
    public function it_should_create_table_seeds_via_array()
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

        $schemaExtender->migrate();

        $this->assertEquals(345, strlen($queries[1]));
    }

    /**
     * @test
     */
    public function it_should_accept_closure_in_the_init()
    {
        global $wpdb;

        $wpdb = Mockery::mock('wpdb');
        $wpdb->prefix = 'wp_';
        $wpdb->shouldReceive('get_charset_collate')
             ->andReturn('UTF-8');   
        
        $schemaExtender = new SchemaExtender();
        $schemaExtender->init(function($db) {

            $db->table('users', function($table) {
                $table->column('id', 'INT(11) NOT NULL AUTO_INCREMENT');
                $table->column('name', 'TEXT NULL');
                $table->primaryKey('id');
            });

            $db->seed('users', [
                'id' => 1, 'name' => 'John Doe'
            ])->iterate(5);

        });

        $queries = $schemaExtender->getQueries();
        $seeds = $schemaExtender->getQueries('seeds');

        $this->assertEquals(105, strlen($queries[0]));
        $this->assertEquals(
            array(
                'id' => 1,
                'name' => 'John Doe'
            ),
            $seeds[0]['record']
        );
    }

    /**
     * @test
     */
    public function it_should_load_schema_extender_in_a_static_way()
    {
        global $wpdb;

        $wpdb = Mockery::mock('wpdb');
        $wpdb->prefix = 'wp_';
        $wpdb->shouldReceive('get_charset_collate')
             ->andReturn('UTF-8');

        SchemaExtenderFacade::init();
        SchemaExtenderFacade::table('users', function($table) {
            $table->column('id', 'INT(11) NOT NULL AUTO_INCREMENT');
            $table->column('name', 'TEXT NULL');
            $table->primaryKey('id');
        });

        $queries = SchemaExtenderFacade::getQueries();

        $this->assertEquals(105, strlen($queries[0]));
    }

    public function __ideal_structure__()
    {
        SchemaExtender::init(function($schema) {
            
            $schema->table('users', function($column) {
                $column->id = 'INT(11) NOT NULL AUTO_INCREMENT';
                $column->name = 'TEXT NULL';
            })->primaryKey('id');
            
            $schema->table('users_options', function($column) {
                $column->id = 'INT(11) NOT NULL AUTO_INCREMENT';
                $column->name_option = 'TEXT NULL';
            })->primaryKey('id');

            $schema->table('users_options', [
                'id' => 'INT(11) NOT NULL AUTO_INCREMENT',
                'name_option' => 'TEXT NULL',
            ])->primaryKey('id');

            $schema->raw('
                ALTER TABLE ' . $schema->tableName('users') . '
                    ADD KEY `user_id` (`user_id`);
                ALTER TABLE ' . $schema->tableName('users_options') . ' 
                    ADD CONSTRAINT `foreign_constraint_users_option_users` 
                    FOREIGN KEY (`user_id`) 
                    REFERENCES ' . $schema->tableName('users') . ' (`id`) 
                    ON DELETE CASCADE 
                    ON UPDATE NO ACTION;
            ');

            $schema->seeds('users', function($column) {
                $column->name = 'John Doe' . rand();
                return $column;
            })->repeat(5);

            $schema->seeds('users', [
                ['field' => 'value'],
                ['field' => 'valu' ],
            ]);
        });

        SchemaExtender::init();
        SchemaExtender::table('users', function($column) {
            $column->id = 'INT(11) NOT NULL AUTO_INCREMENT';
            $column->name = 'TEXT NULL';
        })->primaryKey('id');

        SchemaExtender::seeds('users', function($column) {
            $column->name = 'John Doe' . rand();
            return $column;
        })->repeat(5);

        SchemaExtender::seeds('users', [
            ['name' => 'John Doe 1'],
            ['name' => 'John Doe 2'],
        ])->repeat(5);

        SchemaExtender::drop(['users']);
        
        SchemaExtender::migrate();   
    }
}