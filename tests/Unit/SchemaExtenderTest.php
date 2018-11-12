<?php

namespace LordDashMe\Wordpress\DB\Tests\Unit;

use Mockery as Mockery;
use PHPUnit\Framework\TestCase;
use LordDashMe\Wordpress\DB\SchemaExtender;

class SchemaExtenderTest extends TestCase
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
     */
    public function it_should_throw_exception_invalid_database_instance_when_the_wpdb_is_not_set()
    {
        $this->expectException(\LordDashMe\Wordpress\DB\Exception\InvalidDatabaseInstance::class);
        $this->expectExceptionCode(1);
        $this->expectExceptionMessage('Cannot resolved wordpress database instance.');

        global $wpdb;

        $wpdb = null;

        $extender = new SchemaExtender();
        $extender->init();
    }

    /**
     * @test
     */
    public function it_should_throw_exception_invalid_arugment_passed_when_the_create_table_schema_second_arg_is_not_closure()
    {
        $this->expectException(\LordDashMe\Wordpress\DB\Exception\InvalidArgumentPassed::class);
        $this->expectExceptionCode(1);
        $this->expectExceptionMessage('The given argument is not a closure type.');

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
     */
    public function it_should_throw_exception_invalid_argument_passed_when_the_table_seed_second_arg_value_is_not_array_or_closure()
    {
        $this->expectException(\LordDashMe\Wordpress\DB\Exception\InvalidArgumentPassed::class);
        $this->expectExceptionCode(2);
        $this->expectExceptionMessage('The given argument not match the required type array or closure.');

        $this->mockedWordpressDabaseInstanceGlobal();

        $extender = new SchemaExtender();
        $extender->init(function($context) {

            $context->tableSeed('users', null);

        });
    }

    /**
     * @test
     */
    public function it_should_throw_exception_invalid_argument_passed_when_the_iterate_value_is_not_numeric()
    {
        $this->expectException(\LordDashMe\Wordpress\DB\Exception\InvalidArgumentPassed::class);
        $this->expectExceptionCode(4);
        $this->expectExceptionMessage('The given argument is not a numeric type.');

        $this->mockedWordpressDabaseInstanceGlobal();

        $extender = new SchemaExtender();
        $extender->init(function($context) {

            $context->tableSeed('users', function($data) {
                $data->id = 1;
                $data->name = 'John Doe';
                return $data;
            })->iterate(null);

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
     */
    public function it_should_throw_exception_invalid_argument_passed_when_the_raw_query_given_args_is_not_string()
    {
        $this->expectException(\LordDashMe\Wordpress\DB\Exception\InvalidArgumentPassed::class);
        $this->expectExceptionCode(3);
        $this->expectExceptionMessage('The given argument is not a string type.');

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
     */
    public function it_should_throw_exception_wp_database_update_function_not_found_when_wp_database_update_function_is_not_exist()
    {
        $this->expectException(\LordDashMe\Wordpress\DB\Exception\WPDatabaseUpdateFunctionsNotFound::class);
        $this->expectExceptionCode(1);
        $this->expectExceptionMessage('The wordpress "dbDelta" function is not exist. Make sure to require the file path "wp-admin/includes/upgrade.php" before the Schema Extender class.');

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
