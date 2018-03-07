<?php
namespace Http;

class CurlMulti
{
	protected $handle;
	
	public function __construct() {
		$this->handle = curl_multi_init();
	}
	
	public function __destruct() {
		curl_multi_close($this->handle);
	}
	
	public function getHandle() {
		return $this->handle;
	}
	
	public function execute(array $curls, $pollInterval = 1000)
	{
		foreach ($curls as $curl)
		{
			$curl->beforeExecute();
			curl_multi_add_handle($this->handle, $curl->getHandle());
		}
		
		$active = 0;
		do {
			curl_multi_exec($this->handle, $active);
			if ($pollInterval) {
				usleep($pollInterval);
			}
		} while ($active > 0);
		
		foreach ($curls as $curl)
		{
			curl_multi_remove_handle($this->handle, $curl->getHandle());
			$curl->afterExecute();
		}
	}
}
