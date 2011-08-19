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

namespace Fuel\Core;

/**
 * The Loader class handles all file & class loading for app, core and packages
 */
class Loader {

	public static function factory($basepath, $namespace = '', $loader = null)
	{
		return new static($basepath, $namespace, $loader);
	}

	/**
	 * @var  string  base path to this package
	 */
	protected $basepath;

	/**
	 * @var  string  namespace of this package
	 */
	protected $namespace;

	/**
	 * @var  array  all registered classes for optimization and aliassing
	 */
	protected $classes = array();

	/**
	 * @var  array  namespaces of which registered classes can be aliased to this package
	 */
	protected $namespace_aliasses = array();

	/**
	 * @var  string|Closure  empty for Fuel v1.0 style, 'psr0' for PSR-0 or a custom closure as loader
	 */
	protected $loader;

	public function __construct($basepath, $namespace = '', $loader = null)
	{
		$this->basepath   = $basepath;
		$this->namespace  = $namespace;
		$this->loader     = $loader;
	}

	/**
	 * Checks if a file is available in this package
	 *
	 * @param   string  subdirectory of this package
	 * @param   string  filename
	 * @param   string  file extension
	 * @return  string|false  either the path or false if not available
	 */
	public function find_file($directory, $file, $ext = '.php')
	{
		return is_file($file = $this->basepath.$directory.DS.$file.$ext) ? $file : false;
	}

	/**
	 * Function used by autoloader for loading a class
	 *
	 * @param   string  class to load
	 * @return  bool    success of classload
	 */
	public function load($classname)
	{
		if ($this->load_registered($classname))
		{
			return true;
		}
		elseif ($this->loader instanceof \Closure)
		{
			return call_user_func($this->loader, $classname);
		}
		elseif ($this->loader == 'psr0')
		{
			return $this->load_psr0($classname);
		}

		return $this->load_fuelv1($classname);
	}

	/**
	 * Loads a registered class or aliases a registered class from other namespace
	 *
	 * @param   string  class name
	 * @return  bool    success of class load
	 */
	public function load_registered($classname, $with_aliases = true)
	{
		if ( ! array_key_exists($classname, $this->classes))
		{
			if ( ! $with_aliases)
			{
				return false;
			}

			foreach ($this->namespace_aliasses as $ns => $loader)
			{
				if (strncmp($classname, $ns, strlen($ns)) === 0)
				{
					$original = $ns.substr($classname, strlen($this->namespace));
					if (class_exists($original, false) or $loader->load_registered($original, false))
					{
						class_alias($original, $classname);
						return true;
					}
				}
			}

			return false;
		}

		require $this->classes[$classname];
		return true;
	}

	/**
	 * Loader compatible with Fuel v1.0
	 *
	 * @param   string  class to load
	 * @return  bool    success of class load
	 */
	protected function load_fuelv1($classname)
	{
		// Remove the namespace
		$path = strtolower(substr($classname, strlen($this->namespace) + 1));
		// Fetch whatever subnamespace is left and translate only backslashes to DSs
		$ns_path = str_replace('\\', '_', substr($path, 0, strrpos($path, '\\')));
		// Translate all underscores in the path to DSs
		$path = $ns_path.str_replace(array('_', '\\'), DS, substr($path, strrpos($path, '\\')));

		if ( ! is_file($file = $this->basepath.'classes'.DS.$path.'.php'))
		{
			return false;
		}

		require_once $file;
		return true;
	}

	/**
	 * Loader compatible with PSR-0
	 *
	 * @param   string  class to load
	 * @return  bool    success of class load
	 */
	protected function load_psr0($classname)
	{
		// Remove the namespace
		$path = substr($classname, strlen($this->namespace) + 1);
		// Fetch whatever subnamespace is left and translate only backslashes to DSs
		$ns_path = str_replace('\\', '_', substr($path, 0, strrpos($path, '\\')));
		// Translate all underscores in the path to DSs
		$path = $ns_path.str_replace(array('_', '\\'), DS, substr($path, strrpos($path, '\\')));

		if ( ! is_file($file = $this->basepath.'classes'.DS.$path.'.php'))
		{
			return false;
		}

		require_once $file;
		return true;
	}

	/**
	 * Adds a classes load path.  Any class added here will not be searched for
	 * but explicitly loaded from the path.
	 *
	 * @param   string  the class name
	 * @param   string  the path to the class file
	 * @return  Loader
	 */
	public function add_class($class, $path)
	{
		$this->classes[$class] = $path;

		return $this;
	}

	/**
	 * Adds multiple class paths to the load path. See {@see Loader::add_class}.
	 *
	 * @param   array  the class names and paths
	 * @return  Loader
	 */
	public function add_classes($classes)
	{
		foreach ($classes as $class => $path)
		{
			$this->classes[$class] = $path;
		}

		return $this;
	}

	/**
	 * Adds a namespace alias. For example "Fuel\Core" for "Fuel\App" would alias any class from
	 * Fuel\Core to Fuel\App if registered with its loader
	 *
	 * @param   string  namespace that can be aliased to this one
	 * @param   Loader  loader for the namespace
	 * @return  Loader
	 */
	public function add_namespace_alias($namespace, $loader)
	{
		$this->namespace_aliasses[$namespace] = $loader;

		return $this;
	}

	public function __get($property)
	{
		if (property_exists($this, $property))
		{
			return $this->{$property};
		}

		throw new \OutOfBoundsException('This Loader instance has no such property.');
	}
}