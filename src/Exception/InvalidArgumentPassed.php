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
 * Invalid Argument Passed Exception Class.
 * 
 * @author Joshua Clifford Reyes <reyesjoshuaclifford@gmail.com>
 */
class InvalidArgumentPassed extends SchemaExtenderException
{
    const IS_NOT_CLOSURE = 1;
    const IS_NOT_ARRAY_OR_CLOSURE = 2;
    const IS_NOT_STRING = 3;
    const IS_NOT_NUMERIC = 4;

    public static function isNotClosure(
        $message = 'The given argument is not a closure type.', 
        $code = self::IS_NOT_CLOSURE, 
        $previous = null
    ) {
        return new static($message, $code, $previous);
    }

    public static function isNotArrayOrClosure(
        $message = 'The given argument not match the required type array or closure.', 
        $code = self::IS_NOT_ARRAY_OR_CLOSURE, 
        $previous = null
    ) {
        return new static($message, $code, $previous);
    }

    public static function isNotString(
        $message = 'The given argument is not a string type.', 
        $code = self::IS_NOT_STRING, 
        $previous = null
    ) {
        return new static($message, $code, $previous);
    }

    public static function isNotNumeric(
        $message = 'The given argument is not a numeric type.', 
        $code = self::IS_NOT_NUMERIC, 
        $previous = null
    ) {
        return new static($message, $code, $previous);
    }
}
