<?php
namespace IL\Csv;

use SplFileObject;
use IteratorAggregate;
use Exception;

/**
 * This class can read a delimited file and allows to iterate over the records.
 */
class DataReader implements IteratorAggregate
{
	private $file;
	private $fieldsep = '|';
	
	/**
	 * Creates a new instance that will read from the given file.
	 * @param $file File path or SplFileObject in readable mode.
	 */
	public function __construct($file)
	{
		if (is_string($file))
			$this->file = new SplFileObject($file, 'r');
		elseif ($file instanceof SplFileObject)
			$this->file = $file;
		else
			throw new Exception('Invalid file');
	}
	
	/**
	 * Closes the underlying file.
	 */
	public function __destruct()
	{
		$this->file = null;
	}
	
	/**
	 * Gets the field separator.
	 */
	public function getFieldSep() {
		return $this->fieldsep;
	}
	
	/**
	 * Sets the field separator.
	 */
	public function setFieldSep($sep) {
		$this->fieldsep = $sep;
	}
	
	/**
	 * Iterates over all records.
	 */
	public function getIterator()
	{
		$this->rewind();
		while (!$this->file->eof())
			yield $this->read();
	}
	
	/**
	 * Reads the next data line and returns an array of field values.
	 */
	public function read()
	{
		if ($this->file->eof() || ($line = $this->file->fgets()) === false)
			return null;
		
		$data = explode($this->fieldsep, $line);
		return $data;
	}
	
	/**
	 * Rewind the file pointer to the beginning of the file.
	 */
	public function rewind()
	{
		$this->file->rewind();
	}
}