<?php
namespace IL\Csv;

use Exception;
use SplFileObject;

class CsvWriter
{
	private $file;
	private $delimiter = ',';
	private $quoteChar = '"';
	private $escapeChar = "\\";
	
	public function __construct($file)
	{
		if (is_string($file))
			$this->file = new SplFileObject($file, 'w');
		elseif ($file instanceof SplFileObject)
			$this->file = $file;
		else
			throw new Exception("Invalid file");
	}
	
	public function __destruct()
	{
		//if ($this->file)
		//	fclose($this->file);
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
	
	public function getQuoteChar() {
		return $this->quoteChar;
	}
	public function setQuoteChar($char) {
		$this->quoteChar = $char;
	}
	
	public function getEscapeChar() {
		return $this->escapeChar;
	}
	public function setEscapeChar($char) {
		$this->escapeChar = $char;
	}
	
	public function write($row)
	{
		$this->file->fputcsv($row, $this->delimiter, $this->quoteChar, $this->escapeChar);
	}
}