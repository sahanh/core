<?php
/**
 * Fuel is a fast, lightweight, community driven PHP5 framework.
 *
 * @package    Fuel
 * @version    1.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2011 Fuel Development Team
 * @link       http://fuelphp.com
 */

/**
 * The Autloader is responsible for all class loading.  It allows you to define
 * different load paths based on namespaces.  It also lets you set explicit paths
 * for classes to be loaded from.
 *
 * @package     Fuel
 * @subpackage  Core
 */
class Autoloader {

	/**
	 * @var  array  holds all the namespace paths
	 */
	protected static $loaders = array();

	/**
	 * @var  bool  whether to initialize a loaded class
	 */
	protected static $auto_initialize = null;

	/**
	 * Adds a namespace and its loader to the search path.
	 *
	 * @param   string  the namespace
	 * @param   string  the loader
	 */
	public static function add_loader(\Loader $loader)
	{
		static::$loaders[$loader->namespace] = $loader;
	}

	/**
	 * Adds an array of namespace paths. See {add_namespace}.
	 *
	 * @param   array  the namespaces
	 * @param   bool   whether to prepend the namespace to the search path
	 */
	public static function add_loaders(array $namespaces, $prepend = false)
	{
		if ( ! $prepend)
		{
			static::$loaders = array_merge(static::$loaders, $namespaces);
		}
		else
		{
			static::$loaders = $namespaces + static::$loaders;
		}
	}

	/**
	 * Returns the namespace's loader or false when it doesn't exist.
	 *
	 * @param   string      the namespace to get the loader for
	 * @return  Loader|bool  the namespace loader or false
	 */
	public static function namespace_loader($namespace)
	{
		if ( ! array_key_exists($namespace, static::$loaders))
		{
			return false;
		}

		return static::$loaders[$namespace];
	}

	/**
	 * Register's the autoloader to the SPL autoload stack.
	 */
	public static function register()
	{
		spl_autoload_register('Autoloader::load', true, true);
	}

	public static function load($class)
	{
		$loaded = false;
		$class = ltrim($class, '\\');

		if (empty(static::$auto_initialize))
		{
			static::$auto_initialize = $class;
		}
		foreach (static::$loaders as $ns => $loader)
		{
			if (strncmp($class, $ns, strlen($ns)) and $loader->load($class))
			{
				static::_init_class($class);
				$loaded = true;
			}
		}

		// Prevent failed load from keeping other classes from initializing
		if (static::$auto_initialize == $class)
		{
			static::$auto_initialize = null;
		}

		return $loaded;
	}

	/**
	 * Checks to see if the given class has a static _init() method.  If so then
	 * it calls it.
	 *
	 * @param  string  the class name
	 */
	private static function _init_class($class)
	{
		if (static::$auto_initialize === $class)
		{
			static::$auto_initialize = null;
			if (method_exists($class, '_init') and is_callable($class.'::_init'))
			{
				call_user_func($class.'::_init');
			}
		}
	}
}

/* End of file autoloader.php */
