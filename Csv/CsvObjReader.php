<?php
namespace IL\Csv;

use stdClass;
use Exception;
//use Iterator;

class CsvObjReader extends CsvReader
{
	protected $headers;
	
	public $lowercase = false;
	
	public function __construct($filename)
	{
		parent::__construct($filename);
	}
	
	public function lowercaseHeaders($bool)
	{
		$this->lowercase = $bool;
	}
	
	public function isLowercaseHeaders()
	{
		return $this->lowercase;
	}
	
	public function getIterator()
	{
		$iter = parent::getIterator();
		foreach ($iter as $i => $row)
		{
			// First row is column headers (property names)
			if ($i === 0)
			{
				if ($this->lowercase)
				{
					foreach ($row as $i => $n)
						$row[$i] = strtolower($n);
				}
				$this->headers = $row;
				continue;
			}
			
			// Create the object
			$obj = new stdClass();
			foreach ($this->headers as $i => $h)
				$obj->{$h} = $row[$i];
			
			yield $obj;
		}
	}
	
	/*public function __set($name, $value)
	{
		if ($name == 'lowercase')
			$this->lowercaseHeaders($value);
		elseif ($name == 'trim')
			$this->setTrimValues($value);
		else
			$this->{$name} = $value;
	}
	
	public function __get($name)
	{
		if ($name == 'lowercase')
			return $this->isLowercaseHeaders();
		elseif ($name == 'trim')
			return $this->isTrimValues();
		elseif (property_exists($this, $name))
			return $this->{$name};
		else
			trigger_error("Invalid property $name", E_USER_ERROR);
	}*/
}

/*
class CsvObjReader implements Iterator
{
	private $file;
	private $pos;
	private $obj;
	private $headers;
	private $delimiter = ',';
	
	public $lowercase = false;
	public $trim = false;
	
	public function __construct($filepath)
	{
		$this->file = fopen($file_path, 'r');
		if ($this->file === false)
			throw new Exception("Cannot open file: $filepath");
	}
	
	public function __destruct()
	{
		if ($this->file)
			fclose($this->file);
	}
	
	public function setDelimiter($delim)
	{
		$this->delimiter = $delim;
	}
	
	public function rewind()
	{
		rewind($this->file);
		$this->pos = -1;
		$this->obj = null;
		$this->headers = fgetcsv($this->file, 0, $this->delimiter);
		if ($this->lowercase || $this->trim)
		{
			foreach ($this->headers as $i => $h)
			{
				if ($this->trim)
					$h = trim($h);
				if ($this->lowercase)
					$h = strtolower($h);
				$this->headers[$i] = $h;
			}
		}
		$this->next();
	}
	
	public function next()
	{
		do {  //Skip empty lines.
			$row = fgetcsv($this->file, 0, $this->delimiter);
		} while ($row !== false && count($row) == 1 && is_null($row[0]));
		
		if ($row === false)
		{
			$this->pos = -1;
			$this->obj = null;
			return;
		}
		
		$trim = $this->trim;
		$obj = new stdClass();
		foreach ($this->headers as $i => $h)
			$obj->{$h} = $trim ? trim($row[$i]) : $row[$i];
		
		$this->obj = $obj;
		$this->pos ++;
	}
	
	public function valid()
	{
		return !is_null($this->obj);
	}
	
	public function key()
	{
		return $this->pos;
	}
	
	public function current()
	{
		return $this->obj;
	}
}
*/
