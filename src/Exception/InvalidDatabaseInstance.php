<?php

/*
 * This file is part of the Wordpress DB Schema Extender.
 *
 * (c) Joshua Clifford Reyes <reyesjoshuaclifford@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LordDashMe\Wordpress\DB\Exception;

use LordDashMe\Wordpress\DB\Exception\SchemaExtenderException;

/**
 * Invalid Database Instance Exception Class.
 * 
 * @author Joshua Clifford Reyes <reyesjoshuaclifford@gmail.com>
 */
class InvalidDatabaseInstance extends SchemaExtenderException
{
    const WP_DB_IS_NOT_SET = 1;

    public static function wordpressDatabaseIsNotSet(
        $message = 'Cannot resolved wordpress database instance.', 
        $code = self::WP_DB_IS_NOT_SET, 
        $previous = null
    ) {
        return new static($message, $code, $previous);
    }
}