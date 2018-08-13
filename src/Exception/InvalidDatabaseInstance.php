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

use LordDashMe\Wordpress\DB\Exception\Base;

/**
 * Invalid Database Instance Exception Class.
 * 
 * @author Joshua Clifford Reyes <reyesjoshuaclifford@gmail.com>
 */
class InvalidDatabaseInstance extends Base
{
    const ERROR_CODE_UNRESOLVED_WP_DB_INSTANCE = 100;

    public static function wordpressDatabaseIsNotSet($message = '', $code = null, $previous = null)
    {
        $message = 'Cannot resolved wordpress database instance.';

        return new static($message, self::ERROR_CODE_UNRESOLVED_WP_DB_INSTANCE, $previous);
    }
}