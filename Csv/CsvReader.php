<?php
namespace IL\Csv;

use IteratorAggregate;
use Exception;

class CsvReader implements IteratorAggregate
{
	protected $file;
	
	public $trim = true;
	public $delimiter = ',';
	
	public function __construct($filename)
	{
		$this->file = fopen($filename, 'r');
		if ($this->file === false)
			throw new Exception("Cannot open file: $filepath");
	}
	
	public function __destruct()
	{
		if ($this->file)
			fclose($this->file);
		$this->file = null;
	}
	
	public function getDelimiter()
	{
		return $this->delimiter;
	}
	
	public function setDelimiter($delim)
	{
		$this->delimiter = $delim;
	}
	
	public function isTrimValues()
	{
		return $this->trim;
	}
	
	public function setTrimValues($bool)
	{
		$this->trim = $bool;
	}
	
	public function getIterator()
	{
		while (true)
		{
			// Read the next line
			$row = fgetcsv($this->file, 0, $this->delimiter);
			
			// Check for the end of file
			if ($row === false)
				return;
			
			// An array with a single NULL is returned for empty lines
			if (count($row) == 1 && $row[0] === null)
				continue;
			
			// Trim values if requested
			if ($this->trim)
			{
				foreach ($row as $i => $val)
					$row[$i] = trim($val);
			}
			
			yield $row;
		}
	}
	
	public function reset()
	{
		rewind($this->file);
	}
}