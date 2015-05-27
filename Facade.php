<?php namespace Epic\Facade;

class Facade
{
    protected static $facadeContainer;

    public static function add($instance = null, $alias = null)
    {
        $calledBy = get_called_class();
        if ($calledBy != __CLASS__) {
            return forward_static_call([$calledBy, '__callStatic'], __FUNCTION__, func_get_args());
        }

        if (!is_array($instance)) {
            $instance = [($alias === null) ? 0 : $alias => $instance];
        }

        $getAlias = function ($object, $alias = null) {
            if (null === $alias) {
                $fullClassPath = get_class($object);
                if (false === strpos($fullClassPath, '\\')) {
                    $alias = $fullClassPath;
                } else {
                    $pathParts = explode('\\', $fullClassPath);
                    $alias = $pathParts[count($pathParts) - 1];
                }
            }
            return $alias;
        };

        foreach ($instance as $alias => $object) {
            if (!is_object($object)) {
                throw new \InvalidArgumentException('Object expected but other type is given.');
            }
            if (is_int($alias)) {
                $alias = null;
            }
            $facadeName = $getAlias($object, $alias);

            if (isset(static::$facadeContainer[$facadeName])) {
                throw new \LogicException('Facade with name "' . $alias . '" already exist. Please provide another alias');
            } else {
                static::$facadeContainer[$facadeName] = $object;
            }

            try {
                static::facadeAlias($facadeName);
            }catch (\Exception $e) {
                unset(static::$facadeContainer[$facadeName]);
                throw $e;
            }
        }

        return true;
    }

    public static function __callStatic($method, $args)
    {
        $instance = static::$facadeContainer[get_called_class()];
        return call_user_func_array(array($instance, $method), $args);

    }

    protected static function facadeAlias($alias = null)
    {
        $calledBy = get_called_class();
        if ($calledBy != __CLASS__) {
            return forward_static_call([$calledBy, '__callStatic'], __FUNCTION__, func_get_args());
        }

        if (1 !== preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $alias)) {
            throw new \LogicException('Class name "' . $alias . '" is invalid.');
        }

        if (!class_exists($alias) && $alias) {
            eval("class $alias extends \\Epic\\Facade\\Facade {}");
        } else {
            throw new \LogicException('Class with name "' . $alias . '" already exist. Please provide another alias.');
        }

        return true;
    }
}
