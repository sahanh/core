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
 * The Loader class allows for searching through a search path for a given
 * file, as well as loading a given file.
 *
 * @package     Fuel
 * @subpackage  Core
 * @author      Dan Horrigan <dan@dhorrigan.com>
 */
class Loader {
	
	/**
	 * A factory method to create new Loaders.  Does no more than create
	 * new Loader objects, but allows you to use method chaining off of
	 * the constructor.
	 *
	 * @param   array  $paths  The paths to initialize with
	 * @return  Loader
	 */
	public static function factory(array $paths = array())
	{
		return new static($paths);
	}
	
	/**
	 * Holds all of the search paths
	 *
	 * @var  array
	 */
	protected $paths = array();
	
	/**
	 * Takes in an array of paths, preps them and gets the party started.
	 *
	 * @param  array  $paths  The paths to initialize with
	 */
	public function __construct(array $paths = array())
	{
		$this->paths = $this->prep_paths($paths);
	}
	
	/**
	 * Adds a path to the search path at a given position.
	 * 
	 * Possible positions:
	 *   (null):  Append to the end of the search path
	 *   (-1):    Prepend to the start of the search path
	 *   (index): The path will get inserted AFTER the given index
	 *
	 * @param   string  $path  The path to add
	 * @param   int     $pos   The position to add the path
	 * @return  $this
	 * @throws  OutOfBoundsException
	 */
	public function add_path($path, $pos = null)
	{
		if ($pos === null)
		{
			array_push($this->paths, $this->prep_path($path));
		}
		elseif ($pos === -1)
		{
			array_unshift($this->paths, $this->prep_path($path));
		}
		else
		{
			if ($pos > count($this->paths))
			{
				throw new \OutOfBoundsException("Cannot add path.  Position $pos is out of range.");
			}
			array_splice($this->paths, $pos, 0, $this->prep_path($path));
		}
		return $this;
	}
	
	/**
	 * Adds multiple paths to the search path at a given position.
	 * 
	 * Possible positions:
	 *   (null):  Append to the end of the search path
	 *   (-1):    Prepend to the start of the search path
	 *   (index): The path will get inserted AFTER the given index
	 *
	 * @param   array  $paths  The paths to add
	 * @param   int    $pos    The position to add the path
	 * @return  $this
	 * @throws  OutOfBoundsException
	 */
	public function add_paths(array $paths, $pos = null)
	{
		foreach ($paths as $path)
		{
			$this->add_path($path, $pos);
		}
		return $this;
	}
	
	/**
	 * Prepares a path for usage.  It ensures that the path has a trailing
	 * Directory Separator.
	 *
	 * @param   string  $path  The path to prepare
	 * @return  string
	 */
	public function prep_path($path)
	{
		return rtrim($path, DS).DS;
	}

	/**
	 * Prepares an array of paths.
	 *
	 * @param   array  $paths  The paths to prepare
	 * @return  array
	 */
	public function prep_paths(array $paths)
	{
		foreach ($paths as &$path)
		{
			$path = $this->prep_path($path);
		}
		return $paths;
	}
}