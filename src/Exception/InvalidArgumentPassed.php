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
 * Invalid Argument Passed Exception Class.
 * 
 * @author Joshua Clifford Reyes <reyesjoshuaclifford@gmail.com>
 */
class InvalidArgumentPassed extends Base
{
    const ERROR_CODE_UNRESOLVED_ARG_CLOSURE = 100;
    const ERROR_CODE_UNRESOLVED_ARG_ARRAY_CLOSURE = 101;
    const ERROR_CODE_UNRESOLVED_ARG_STRING = 102;

    public static function isNotClosure($message = '', $code = null, $previous = null)
    {
        $message = 'The given argument is not a closure type.';

        return new static($message, self::ERROR_CODE_UNRESOLVED_ARG_CLOSURE, $previous);
    }

    public static function isNotArrayOrClosure($message = '', $code = null, $previous = null)
    {
        $message = 'The given argument not match the required type array or closure.';

        return new static($message, self::ERROR_CODE_UNRESOLVED_ARG_ARRAY_CLOSURE, $previous);
    }

    public static function isNotString($message = '', $code = null, $previous = null)
    {
        $message = 'The given argument is not a string type.';

        return new static($message, self::ERROR_CODE_UNRESOLVED_ARG_STRING, $previous);
    }
}