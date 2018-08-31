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
        global $wpdb;

        $wpdb = null;

        $extender = new SchemaExtender();
        $extender->init();
    }

    /**
     * @test
     * @expectedException LordDashMe\Wordpress\DB\Exception\InvalidArgumentPassed
     * @expectedExceptionCode 100
     */
    public function it_should_throw_exception_in_create_table_schema_when_second_args_not_closure()
    {
        $this->mockedWordpressDabaseInstanceGlobal();
        
        $extender = new SchemaExtender();
        $extender->init(function($context) {

            $context->table('users', null);

        });
    }

    /**
     * @test
     */
    public function it_should_create_table_schema()
    {
        $this->mockedWordpressDabaseInstanceGlobal();

        $extender = new SchemaExtender();
        $extender->init(function($context) {
            
            $context->table('users', function($table) {
                $table->column('id', 'INT(11) NOT NULL AUTO_INCREMENT');
                $table->column('name', 'TEXT NULL');
                $table->primaryKey('id');
            });

        });

        $this->assertEquals(107, strlen($extender->getQueries()[0]));
    }

    /**
     * @test
     * @expectedException LordDashMe\Wordpress\DB\Exception\InvalidArgumentPassed
     * @expectedExceptionCode 101
     */
    public function it_should_throw_exception_in_table_seed_when_second_args_not_array_or_closure()
    {
        $this->mockedWordpressDabaseInstanceGlobal();

        $extender = new SchemaExtender();
        $extender->init(function($context) {

            $context->tableSeed('users', null);

        });
    }

    /**
     * @test
     */
    public function it_should_create_table_seed()
    {
        $this->mockedWordpressDabaseInstanceGlobal();

        $extender = new SchemaExtender();
        $extender->init(function($context) {

            $context->tableSeed('users', function($data) {
                $data->id = 1;
                $data->name = 'John Doe';
                return $data;
            })->iterate(5);

        });

        $this->assertEquals(3, count($extender->getSeedQueries()[0]));
    }

    /**
     * @test
     * @expectedException LordDashMe\Wordpress\DB\Exception\InvalidArgumentPassed
     * @expectedExceptionCode 102 
     */
    public function it_should_throw_exception_in_raw_query_when_given_args_not_string()
    {
        $this->mockedWordpressDabaseInstanceGlobal();

        $extender = new SchemaExtender();
        $extender->init(function($context) {

            $context->rawQuery(null);

        });
    }

    /**
     * @test
     */
    public function it_should_create_raw_query()
    {
        $this->mockedWordpressDabaseInstanceGlobal();

        $extender = new SchemaExtender();
        $extender->init(function($context) {

            $context->rawQuery('
                ALTER TABLE ' . $context->tableName('users') . '
                    ADD KEY `user_id` (`user_id`);
                ALTER TABLE ' . $context->tableName('users_options') . ' 
                    ADD CONSTRAINT `foreign_constraint_users_option_users` 
                    FOREIGN KEY (`user_id`) 
                    REFERENCES ' . $context->tableName('users') . ' (`id`) 
                    ON DELETE CASCADE 
                    ON UPDATE NO ACTION;'
            );

        });

        $this->assertEquals(366, strlen($extender->getQueries()[0]));
    }

    /**
     * @test
     * @expectedException LordDashMe\Wordpress\DB\Exception\WPDatabaseUpdateFunctionsNotFound
     * @expectedExceptionCode 100 
     */
    public function it_should_throw_exception_in_migrate_when_wp_database_update_function_not_exists()
    {
        $this->mockedWordpressDabaseInstanceGlobal();

        $extender = new SchemaExtender();
        $extender->init(function($context) {
            
            $context->table('users', function($table) {
                $table->column('id', 'INT(11) NOT NULL AUTO_INCREMENT');
                $table->column('name', 'TEXT NULL');
                $table->primaryKey('id');
            });

        });

        $extender->migrate();    
    }

    /**
     * @test
     */
    public function it_should_migrate_all_collected_data_tables_and_seeds()
    {
        $this->mockedWordpressDabaseInstanceGlobal();

        global $result;
        global $wpdb;

        $result = array();

        $closure = function($table, $data) {
            
            global $result;
            array_push($result, array(
                'seed_queries' => array(
                    'table' => $table, 
                    'data' => $data
                )
            ));
            
            return true;
        };

        $wpdb->shouldReceive('insert')->withArgs($closure);

        include TESTS_DIR . 'Mocks/wp/wp-admin/includes/upgrade.php';

        $extender = new SchemaExtender();
        $extender->init(function($context) {
            
            $context->table('users', function($table) {
                $table->column('id', 'INT(11) NOT NULL AUTO_INCREMENT');
                $table->column('name', 'TEXT NULL');
                $table->primaryKey('id');
            });

            $context->tableSeed('users', function($data) {
                $data->id = 1;
                $data->name = 'John Doe';
                return $data;
            })->iterate(2);

            $context->tableSeed('users', function($data) {
                $data->id = 1;
                $data->name = 'John Doe';
                return $data;
            });

            $context->table('user_options', function($table) {
                $table->column('id', 'INT(11) NOT NULL AUTO_INCREMENT');
                $table->column('user_id', 'INT(11) NOT NULL');
                $table->column('nick_name', 'TEXT NULL');
            });

            $context->rawQuery('
                ALTER TABLE ' . $context->tableName('users') . '
                    ADD KEY `user_id` (`user_id`);
                ALTER TABLE ' . $context->tableName('users_options') . ' 
                    ADD CONSTRAINT `foreign_constraint_users_option_users` 
                    FOREIGN KEY (`user_id`) 
                    REFERENCES ' . $context->tableName('users') . ' (`id`) 
                    ON DELETE CASCADE 
                    ON UPDATE NO ACTION;'
            );

        });

        $extender->migrate();   

        $this->assertEquals(4, count($result));
    }

    /**
     * @test
     */
    public function it_should_drop_table()
    {
        $this->mockedWordpressDabaseInstanceGlobal();

        global $result;
        global $wpdb;

        $result = '';
        
        $closure = function($query) {
            
            global $result;
            $result = $result . $query;

            return true;
        };

        $wpdb->shouldReceive('query')->withArgs($closure);

        $extender = new SchemaExtender();
        $extender->init();
        $extender->dropTable('user');
        $extender->dropTable('user_options');

        $this->assertEquals(178, strlen($result));
    }

    /**
     * @test
     */
    public function it_should_drop_tables()
    {
        $this->mockedWordpressDabaseInstanceGlobal();

        global $result;
        global $wpdb;

        $result = '';
        
        $closure = function($query) {
            
            global $result;
            $result = $result . $query;

            return true;
        };

        $wpdb->shouldReceive('query')->withArgs($closure);

        $extender = new SchemaExtender();
        $extender->init();
        $extender->dropTables(['user', 'user_options']);

        $this->assertEquals(178, strlen($result));
    }

    public function mockedWordpressDabaseInstanceGlobal()
    {
        global $wpdb;

        $wpdb = Mockery::mock('wpdb');
        $wpdb->prefix = 'wp_';
        $wpdb->shouldReceive('get_charset_collate')
             ->andReturn('UTF-8');   
    }
}
