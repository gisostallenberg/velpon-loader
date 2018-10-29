<?php

namespace GisoStallenberg\VelponLoader;

class VelponLoader
{
    /**
     * The current loader instance
     *
     * @var VelponLoader
     */
    private static $loader;

    /**
     * The stack description to handle
     *
     * @var array
     */
    private $stack = [];

    /**
     * The suffix for classes that are pluggable
     *
     * @var string
     */
    private $pluginClassSuffix = 'Pluggable';

    /**
     * The suffix used to identify chains (the class should not be defined, so the autoload can create the chain)
     *
     * @var string
     */
    private $glueClassSuffix = 'Velpon';

    /**
     * Register this class as autoloader
     *
     * The stack should be described as:
     * ['pluginname' => ['TopLevelClass' => 'PluginClass', ...]]
     *
     *
     * @param array $stack
     * @return bool
     */
    public static function register(array $stack = [])
    {
        if (static::$loader instanceof static) {
            return false;
        }

        static::$loader = new static($stack);

        return spl_autoload_register(
            [
                static::$loader,
                'autoload'
            ],
            true,
            true
        );
    }

    /**
     * Unregister the current loader
     *
     * @return bool
     */
    public static function unregister()
    {
        $result = spl_autoload_unregister(
            [
                static::$loader,
                'autoload'
            ]
        );
        static::$loader = null;

        return $result;
    }

    /**
     * Constructor.
     *
     * @param array $stack
     */
    private function __construct(array $stack = [])
    {
        $this->stack = $stack;
    }

    /**
     * Autoload the classes
     *
     * @param $className
     * @return bool
     */
    private function autoload($className)
    {
        if ($this->isPluggableClass($className)) {
            $this->startPluginChain($className);
            return true;
        }

        if ($this->isVelponClass($className) ) {
            $this->chainVelponClass($className);
            return true;
        }

        return false;
    }

    /**
     * Give the class name of the pluggable class
     *
     * @param $className
     * @return string
     */
    private function getPluggableClassName($className)
    {
        return sprintf(
            '%s%s',
            $className,
            $this->pluginClassSuffix
        );
    }

    /**
     * Check suffix
     *
     * @param $haystack
     * @param $needle
     * @return bool
     */
    private function hasSuffix($haystack, $needle)
    {
        return (substr($haystack, 0 - strlen($needle)) === $needle);
    }

    /**
     * See if this class is meant to be pluggable.
     *
     * @param $className
     * @return bool
     */
    private function isPluggableClass($className)
    {
        if ($this->hasSuffix($className, $this->pluginClassSuffix) ) {
            return false;
        }

        return class_exists($this->getPluggableClassName($className));
    }

    /**
     * See if this class is meant to be the glue.
     *
     * @param $className
     * @return bool
     */
    private function isVelponClass($className)
    {
        return $this->hasSuffix($className, $this->glueClassSuffix);
    }

    /**
     * Start chaining classes
     *
     * @param $className
     */
    private function startPluginChain($className)
    {
        $targetClass = $this->getTopTargetClass($className);
        class_alias($targetClass, $className);
    }

    /**
     * Get the first class in chain
     *
     * @param $className
     * @return string
     */
    private function getTopTargetClass($className)
    {
        $targetClass = $className . $this->pluginClassSuffix; //  default, when no plugin is specifying the class

        foreach ($this->getStackInLoadOrder() as $pluginName => $pluginClasses) {
            if (array_key_exists($className, $pluginClasses)) {
                $targetClass = $pluginClasses[$className];
                break;
            }
        }

        return $targetClass;
    }

    /**
     * Chain a velpon class to the next plugin
     *
     * @param $className
     */
    private function chainVelponClass($className)
    {
        $targetClass = $this->getVelponTargetClass($className);

        if ($targetClass !== false) {
            class_alias($targetClass, $className);
        }
    }

    /**
     * Get the next class in chain
     *
     * @param $className
     * @return bool|string
     */
    private function getVelponTargetClass($className)
    {
        $targetClass = false;
        $baseClass = substr($className, 0, 0 - strlen($this->glueClassSuffix));

        $classToPlug = false;
        $pluggableClassName = false;
        foreach ($this->getStackInLoadOrder() as $pluginName => $pluginClasses) {
            if ($classToPlug === false && ($classToPlug = array_search($baseClass, $pluginClasses)) !== false) {
                $pluggableClassName = $this->getPluggableClassName($classToPlug);
                continue; // loop to next plugin to try to find the target class
            }

            if ($classToPlug !== false) {
                $targetClass = $pluginClasses[$classToPlug];
            }
        }

        if ($targetClass === false) {
            return $pluggableClassName;
        }

        return $targetClass;
    }

    /**
     * Give the stack in the order the classes should be loaded
     *
     * @return array
     */
    private function getStackInLoadOrder()
    {
        return array_reverse(
            $this->stack,
            true
        );
    }
}
