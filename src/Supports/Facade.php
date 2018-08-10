<?php

/*
 * This file is part of the WP DB Schema Extender.
 *
 * (c) Joshua Clifford Reyes <reyesjoshuaclifford@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LordDashMe\WP\Supports;

/**
 * The Facade Class. 
 *
 * Use to convert normal class into static class.
 * 
 * @author Joshua Clifford Reyes <reyesjoshuaclifford@gmail.com>
 */
class Facade
{
    /**
     * Holds the first instance of the normal classes.
     *
     * @var array
     */
    protected static $classes = [];

    /**
     * Set the class instance in the class field array
     * for caching and will be use later.
     *
     * @param  string  $classNamespace
     * @param  mixed   $classContext
     *
     * @return void
     */
    public static function setClass($classNamespace, $classContext)
    {
        self::$classes[$classNamespace] = $classContext;
    }

    /**
     * Get the class instance.
     *
     * @param  string  $classNamespace
     *
     * @return mixed
     */
    public static function getClass($classNamespace)
    {
        return isset(self::$classes[$classNamespace]) ? self::$classes[$classNamespace] : false;
    }

    /**
     * Resolves the normal to static call to the object.
     *
     * @param  string  $method
     * @param  array   $args
     *
     * @throws \RuntimeException
     * 
     * @return mixed
     */
    public static function __callStatic($method, $args)
    {
        $classContext = self::resolveFacadeClass();

        if (! $classContext) {
            throw new \RuntimeException('The facade class context is not set properly.');
        }

       return $classContext->{$method}(...$args);
    }

    /**
     * Resolver for the normal class instance.
     *
     * @return mixed
     */
    protected static function resolveFacadeClass()
    {
        $classNamespace = static::getFacadeClass();
        
        $classContext = self::getClass($classNamespace);
        if ($classContext) {
            return $classContext;
        }

        return self::resolveClassNameSpace($classNamespace);
    }

    /**
     * Resolver for normal class namespace and
     * set or cache the resolved normal class to the class field.
     *
     * @param  string  $classNamespace
     *
     * @return void
     */
    protected static function resolveClassNameSpace($classNamespace)
    {
        $classContext = new $classNamespace;

        self::setClass($classNamespace, $classContext);

        return $classContext;
    }

    /**
     * ( NOOP method )
     * 
     * Get the normal class namespace that will be convert to static class.
     *
     * @throws \RuntimeException
     * 
     * @return string
     */
    protected static function getFacadeClass()
    {
        throw new \RuntimeException(
            'Facade needs getFacadeClass(...) method to be declared 
            or override in the inheritor or subclass class.'
        );
    }
}