<?php namespace Epic\Facades;

abstract class Facade
{
    protected static $facadeContainer;
    protected static $defaultFacadeAlias = null;

    public static function getFacadeContainer()
    {
        return static::$facadeContainer;
    }

    public static function getDefaultFacadeAlias()
    {
        if (!$alias = static::$defaultFacadeAlias) {
            $fullClassPath = get_called_class();
            if (false === strpos($fullClassPath, '\\')) {

                $alias = $fullClassPath;
            }else {
                $pathParts = explode('\\', $fullClassPath);
                $alias = $pathParts[count($pathParts) - 1];
            }
        }

        return $alias;
    }

    public static function setFacadeContainer($facadeContainer)
    {
        static::$facadeContainer = $facadeContainer;
    }

    protected static function getFacadeAccessor()
    {
        throw new RuntimeException("Facade does not implement getFacadeAccessor method.");
    }

    protected static function getFacadeInstance()
    {
        return false;
    }

    public static function __callStatic($method, $args)
    {
        if (!($instance = static::getFacadeInstance())) {
            $instance = static::getFacadeContainer()[static::getFacadeAccessor()];
        }

        switch (count($args)) {
            case 0:
                return $instance->$method();

            case 1:
                return $instance->$method($args[0]);

            case 2:
                return $instance->$method($args[0], $args[1]);

            case 3:
                return $instance->$method($args[0], $args[1], $args[2]);

            case 4:
                return $instance->$method($args[0], $args[1], $args[2], $args[3]);

            default:
                return call_user_func_array(array($instance, $method), $args);
        }
    }
}