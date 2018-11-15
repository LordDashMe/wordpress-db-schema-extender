<?php

/*
 * This file is part of the Wordpress DB Schema Extender.
 *
 * (c) Joshua Clifford Reyes <reyesjoshuaclifford@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LordDashMe\Wordpress\DB\Facade;

use LordDashMe\StaticClassInterface\Facade;

/**
 * Schema Extender Facade Class.
 * 
 * @author Joshua Clifford Reyes <reyesjoshuaclifford@gmail.com>
 */
class SchemaExtender extends Facade
{
    /**
     * {@inheritdoc}
     */
    public static function getStaticClassAccessor()
    {
        return 'LordDashMe\Wordpress\DB\SchemaExtender';
    }
}
