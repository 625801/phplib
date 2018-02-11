<?php
namespace IL\Csv;

use SplFileObject;
use Exception;

/**
 * This class can write a delimited file.
 */
class DataWriter
{
	private $file;
	
	public $fieldSep = '|';
	public $rowSep = PHP_EOL;
	
	/**
	 * Creates a new instance that will write to the given file.
	 * @param $file File path or SplFileObject in writeable mode.
	 */
	public function __construct($file)
	{
		if (is_string($file))
			$this->file = new SplFileObject($file, 'w');
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
		return $this->fieldSep;
	}
	
	/**
	 * Sets the field separator.
	 */
	public function setFieldSep($sep) {
		$this->fieldSep = $sep;
	}
	
	/**
	 * Gets the row separator.
	 */
	public function getRowSep() {
		return $this->rowSep;
	}
	
	/**
	 * Sets the row separator. For now, only new line chars are accepted.
	 * @param $sep One of \n, \r, \r\n.
	 */
	public function setRowSep($sep)
	{
		if ($sep != "\n" && $sep != "\r" && $sep != "\r\n")
			throw new Exception('Only new line characters are accepted as row separators for now.');
		
		$this->rowSep = $sep;
	}
	
	/**
	 * Writes given array of values to the file.
	 * @param $data Array of values to write.
	 * @returns int Number of bytes written. See SplFileObject::fwrite().
	 */
	public function write($data)
	{
		$line = implode($this->fieldSep, $data) . $this->rowSep;
		return $this->file->fwrite($line);
	}
}