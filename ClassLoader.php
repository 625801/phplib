<?php
namespace IL;

class ClassLoader
{
	private static $instance;
	
	public static function getInstance()
	{
		if (!isset(self::$instance))
		{
			$loader = new self();
			$loader->register();
			
			self::$instance = $loader;
		}
		
		return self::$instance;
	}
	
	
	private $dirs;
	private $maps;
	
	public function __construct()
	{
		$this->dirs = array();
		$this->maps = array();
	}
	
	public function register()
	{
		spl_autoload_register(array($this, 'loadClass'));
	}
	
	public function unregister()
	{
		spl_autoload_unregister(array($this, 'loadClass'));
	}
	
	public function add($directory)
	{
		if ($directory == '')
			throw new \InvalidArgumentException("Directory cannot be empty.");
		
		$directory = str_replace("\\", '/', $directory);
		$directory = rtrim($directory, '/') . '/';  /*should end with a slash*/
		
		if (!array_key_exists($directory, $this->dirs))
			$this->dirs[] = $directory;
	}
	
	public function remove($directory)
	{
		$offset = array_search($directory, $this->dirs);
		if ($offset !== false)
			array_splice($this->dirs, $offset, 1);
	}
	
	public function map($namespace, $directory)
	{
		if ($namespace == '')
			throw new \InvalidArgumentException("Namespace cannot be empty. Otherwise, use add() method.");
		
		$namespace = str_replace("\\", '/', $namespace);
		$namespace = trim($namespace, '/') . '/';  /*should end with a slash*/
		
		$directory = str_replace("\\", '/', $directory);
		$directory = rtrim($directory, '/') . '/';  /*should end with a slash*/
		
		$this->maps[$namespace][] = $directory;
	}
	
	public function unmap($namespace)
	{
		if ($namespace == '')
			return;
		
		$namespace = str_replace("\\", '/', $namespace);
		$namespace = trim($namespace, '/') . '/';  /*should end with a slash*/
		
		if (array_key_exists($namespace, $this->maps))
			unset($this->maps[$namespace]);
	}
	
	public function loadClass($class)
	{
		if (!$this->loadPsr0($class))
			$this->loadPsr4($class);
	}
	
	protected function loadPsr0($class)
	{
		$file = str_replace("\\", '/', $class) . '.php';
		$file = ltrim($file, '/');
		
		foreach ($this->dirs as $dir)
		{
			$path = $dir . $file;
			if (file_exists($path))
			{
				require_once($path);
				return true;
			}
		}
		
		return false;
	}
	
	protected function loadPsr4($class)
	{
		$class = str_replace("\\", '/', $class) . '.php';
		
		foreach ($this->maps as $namespace => $directories)
		{
			$len = strlen($namespace);
			if (substr_compare($class, $namespace, 0, $len) !== 0)
				continue;
			
			$file = substr($class, $len);  /*without namespace*/
			
			foreach ($directories as $dir)
			{
				$path = $dir . $file;
				if (file_exists($path))
				{
					require_once($path);
					return true;
				}
			}
		}
		
		return false;
	}
	
	public function __debugInfo()
	{
		return array(
			'psr0' => $this->dirs,
			'psr4' => $this->maps,
		);
	}
}