<?php

namespace LordDashMe\Wordpress\DB\Tests\Unit\Facade;

use Mockery as Mockery;
use PHPUnit\Framework\TestCase;
use LordDashMe\Wordpress\DB\Facade\SchemaExtender;

class SchemaExtenderTest extends TestCase
{
    /**
     * @test
     */
    public function it_should_init_schema_extender_class_in_a_static_way()
    {
        $this->mockedWordpressDabaseInstanceGlobal();

        SchemaExtender::init();
    }

    public function mockedWordpressDabaseInstanceGlobal()
    {
        global $wpdb;

        $wpdb = Mockery::mock('wpdb');
    }
}