<?php
namespace IL\Csv;

class CsvObjWriter extends CsvWriter
{
	private $headers;
	
	public function __construct($filename)
	{
		parent::__construct($filename);
	}
	
	public function getHeaders()
	{
		return $this->headers;
	}
	
	public function setHeaders(array $headers)
	{
		$this->headers = $headers;
		parent::write($headers);
	}
	
	public function write($object)
	{
		if (is_null($this->headers))
		{
			$headers = array();
			foreach ($object as $key => $val)
				$headers[] = $key;
			
			$this->setHeaders($headers);
		}
		
		$data = array();
		foreach ($this->headers as $h)
			$data[] = $object->{$h};
		
		parent::write($data, $this->delimiter);
	}
}