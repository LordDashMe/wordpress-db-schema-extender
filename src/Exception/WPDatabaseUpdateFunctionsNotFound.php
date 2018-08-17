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
 * WP Database Update Functions Not Found Exception Class.
 * 
 * @author Joshua Clifford Reyes <reyesjoshuaclifford@gmail.com>
 */
class WPDatabaseUpdateFunctionsNotFound extends Base
{
    const ERROR_CODE_UNRESOLVED_DB_DELTA_FUNC = 100;

    public static function dbDeltaIsNotExist($message = '', $code = null, $previous = null)
    {
        $message = 'The wordpress "dbDelta" function is not exist. Make sure to require the file path "wp-admin/includes/upgrade.php" before the Schema Extender class.';

        return new static($message, self::ERROR_CODE_UNRESOLVED_DB_DELTA_FUNC, $previous);
    }
}