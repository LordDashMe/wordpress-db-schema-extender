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
 * WP Database Update Functions Not Found Exception Class.
 * 
 * @author Joshua Clifford Reyes <reyesjoshuaclifford@gmail.com>
 */
class WPDatabaseUpdateFunctionsNotFound extends SchemaExtenderException
{
    const DB_DELTA_IS_NOT_EXIST = 1;

    public static function dbDeltaIsNotExist(
        $message = 'The wordpress "dbDelta" function is not exist. Make sure to require the file path "wp-admin/includes/upgrade.php" before the Schema Extender class.', 
        $code = self::DB_DELTA_IS_NOT_EXIST, 
        $previous = null
    ) {
        return new static($message, $code, $previous);
    }
}