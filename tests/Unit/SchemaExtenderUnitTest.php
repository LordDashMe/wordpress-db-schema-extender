<?php

use PHPUnit\Framework\TestCase;
use LordDashMe\WP\SchemaExtender;

class SchemaExtenderUnitTest extends TestCase
{
    /**
     * @test
     */
    public function it_should_load_the_class_instance()
    {
        $this->assertInstanceOf(SchemaExtender::class, new SchemaExtender());
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