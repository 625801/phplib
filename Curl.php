<?php
namespace Http;

use stdClass;
use CURLFile;
use Exception;

class Curl
{
	protected $handle;
	protected $outfile;
	protected $errfile;
	
	protected $request;
	protected $response;
	
	/* Let users associate any data with this object. */
	public $userdata;
	
	public function __construct()
	{
		$this->handle = curl_init();
		$this->setDefaults();
	}
	
	public function __destruct()
	{
		curl_close($this->handle);
		$this->handle = null;
	}
	
	public function getHandle()
	{
		return $this->handle;
	}
	
	public function reset()
	{
		curl_reset($this->handle);
		$this->setDefaults();
	}
	
	protected function setDefaults()
	{
		$this->set(CURLOPT_HEADER, false);  /*Don't write headers as part of body*/
		$this->set(CURLOPT_SAFE_UPLOAD, true);
		$this->set(CURLOPT_RETURNTRANSFER, true);
		$this->set(CURLOPT_HEADERFUNCTION, [$this, 'onResponseHeader']);
		$this->set(CURLOPT_WRITEFUNCTION, [$this, 'onResponseBody']);
		
		$this->request = (object) array( 'method' => 'GET' );
		$this->response = (object) array( 'headerString' => '', 'bodyString' => '' );
	}
	
	public function set($option, $value)
	{
		curl_setopt($this->handle, $option, $value);
	}
	
	public function get($option)
	{
		return curl_getinfo($this->handle, $option);
	}
	
	public function error()
	{
		return curl_error($this->handle);
	}
	
	
	/* Request helpers */
	
	public function setUrl($url)
	{
		$this->request->url = $url;
		return $this;
	}
	
	/** Set the query. Stored as string. */
	public function setQuery($query)
	{
		if (is_object($query)) {
			$query = http_build_query((array)$query);
		}
		elseif (is_array($query)) {
			$query = http_build_query($query);
		}
		
		$this->request->query = $query;
		return $this;
	}
	
	public function setMethod($method)
	{
		$this->set(CURLOPT_CUSTOMREQUEST, $method);
		return $this;
	}
	
	/** Set headers. Stored as array. */
	public function setHeader($header)
	{
		if (!isset($header)) {
			$this->request->header = $header;
		}
		elseif (is_object($header)) {
			$this->request->header = (array)$header;
		}
		elseif (is_array($header)) {
			$this->request->header = $header;
		}
		else {
			throw InvalidArgumentException("header must be an object or an array");
		}
		
		return $this;
	}
	
	/** Set cookies. */
	public function setCookie($cookie)
	{
		if (!$cookie)
		{
			$this->set(CURLOPT_COOKIE, '');
		}
		elseif (is_object($cookie) || is_array($cookie))
		{
			$cookie = array();
			foreach ($cookie as $name => $value)
			{
				$cookie[] = "$name=$value";
			}
			$cookie = implode('; ', $cookie);
			
			$this->set(CURLOPT_COOKIE, $cookie);
		}
		elseif (is_string($cookie))
		{
			$this->set(CURLOPT_COOKIE, $cookie);
		}
		else
		{
			throw InvalidArgumentException("cookie must be an object or an array");
		}
		
		return $this;
	}
	
	/** Sets the form payload. Stored as array, in case we have uploads. */
	public function setForm($form)
	{
		if (!isset($form))
		{
			unset($this->request->form);
		}
		elseif (is_object($form) || is_array($form))
		{
			$uploads = false;
			foreach ($form as $name => $value)
			{
				if ($value instanceof CURLFile)
				{
					$uploads = true;
					break;
				}
			}
			
			$this->request->form = is_object($form) ? (array)$form : $form;
			$this->request->uploads = $uploads;
		}
		elseif (is_string($form))
		{
			$this->request->form = $form;
			$this->request->uploads = false;
		}
		else
		{
			throw InvalidArgumentException("form must be an object, array, or a string");
		}
		
		return $this;
	}
	
	/** Sets the json payload. Given $data is json_encoded. Stored as string. */
	public function setJson($data)
	{
		if (isset($data)) {
			$this->request->json = json_encode($data);
		}
		else {
			unset($this->request->json);
		}
		
		return $this;
	}
	
	public function setOutput($output)
	{
		$this->request->output = $output;
		return $this;
	}
	
	public function setVerbose($verbose)
	{
		$this->request->verbose = $verbose;
		return $this;
	}
	
	public function setSslVerify($boolean)
	{
		$this->set(CURLOPT_SSL_VERIFYPEER, (bool)$boolean);
		return $this;
	}
	
	public function setTimeout($timeout)
	{
		$this->set(CURLOPT_TIMEOUT, (int)$timeout);
		return $this;
	}
	
	public function setConnectTimeout($timeout)
	{
		$this->set(CURLOPT_CONNECTTIMEOUT, (int)$timeout);
		return $this;
	}
	
	public function setUserAgent($agent)
	{
		$this->set(CURLOPT_USERAGENT, $agent);
		return $this;
	}
	
	public function setCookieFile($file)
	{
		$this->set(CURLOPT_COOKIEFILE, $file);
		return $this;
	}
	
	public function setCookieJar($file)
	{
		$this->set(CURLOPT_COOKIEJAR, $file);
		return $this;
	}
	
	public function setFollowRedirects($boolean)
	{
		$this->set(CURLOPT_FOLLOWLOCATION, (bool)$boolean);
		$this->set(CURLOPT_AUTOREFERER, (bool)$boolean);
		return $this;
	}
	
	public function setMaxRedirects($count)
	{
		$this->set(CURLOPT_MAXREDIRS, (int)$count);
		return $this;
	}
	
	/** Set authentication. */
	public function setAuth($user, $pass, $type = 'BASIC')
	{
		$const = '\\CURLAUTH_' . strtoupper($type);
		$auth = defined($const) ? constant($const) : CURLAUTH_BASIC;
		$this->set(CURLOPT_HTTPAUTH, $auth);
		$this->set(CURLOPT_USERPWD, $user.':'.$pass);
		return $this;
	}
	
	/* END Request helpers. */
	
	/** Internal API. This method is public so that CurlMulti can call it. */
	public function beforeExecute()
	{
		$request = $this->request;
		
		if (!isset($request->url)) {
			throw new Exception("URL is not set");
		}
		
		// Append query to the URL, if it is given.
		$url = $request->url;
		if (isset($request->query)) {
			$url = (strpos($url, '?') === false ? '?' : '&') . $request->query;
		}
		
		$this->set(CURLOPT_URL, $url);
		
		// Produce and set the POST payload.
		if (isset($request->form))
		{
			$postFields = $request->uploads ? $request->form : http_build_query($request->form);
			$contentType = $request->uploads ? 'multipart/form-data' : 'application/x-www-form-urlencoded';
		}
		elseif (isset($request->json))
		{
			$postFields = $request->json;
			$contentType = 'application/json';
		}
		
		if (isset($postFields)) {
			$this->set(CURLOPT_POSTFIELDS, $postFields);
		}
		
		// Make a copy of the headers.
		$header = isset($request->header) ? $request->header : array();
		
		// Set our content type, unless already set by user.
		if (isset($contentType))
		{
			foreach ($header as $name => $value)
			{
				if (strcasecmp($name, 'Content-Type') === 0)
				{
					$contentTypeFound = true;
					break;
				}
			}
			
			if (!isset($contentTypeFound)) {
				$header['Content-Type'] = $contentType;
			}
		}
		
		// Set the headers.
		if (!empty($header))
		{
			$httpHeader = array();
			foreach ($header as $name => $value) {
				$httpHeader[] = "$name: $value";
			}
			
			$this->set(CURLOPT_HTTPHEADER, $httpHeader);
		}
		
		if (isset($request->output))
		{
			$this->outfile = fopen($request->output, 'wb');
			$this->set(CURLOPT_FILE, $this->outfile);
		}
		
		if (isset($request->verbose))
		{
			if (is_string($request->verbose))
			{
				$this->errfile = fopen($request->verbose, 'wb');
				$this->set(CURLOPT_VERBOSE, true);
				$this->set(CURLOPT_STDERR, $this->errfile);
			}
			else {
				$this->set(CURLOPT_VERBOSE, (bool)$request->verbose);
			}
		}
	}
	
	/** Execute the request. */
	public function execute()
	{
		$this->beforeExecute();
		curl_exec($this->handle);
		$this->afterExecute();
		
		return $this;
	}
	
	/** Internal API. This method is public so that CurlMulti can call it. */
	public function afterExecute()
	{
		if (isset($this->outfile))
		{
			fflush($this->outfile);
			fclose($this->outfile);
			$this->outfile = null;
			$this->set(CURLOPT_FILE, null);
		}
		
		if (isset($this->errfile))
		{
			fflush($this->errfile);
			fclose($this->errfile);
			$this->errfile = null;
			$this->set(CURLOPT_VERBOSE, false);
		}
		
		$this->parseResponse();
	}
	
	protected function parseResponse()
	{
		$response = $this->response;
		
		$response->headerString = trim($response->headerString);
		
		if (empty($response->headerString)) {
			return;
		}
		
		$headers = array();
		$cookies = array();
		
		// We are only interested in the last set of headers. We find them here.
		$pos = strrpos($response->headerString, "\r\n\r\n");
		$lastHeaders = ($pos === false) ? $response->headerString : substr($response->headerString, $pos + 4);
		$lines = explode("\r\n", $lastHeaders);
		
		$firstLine = $lines[0];
		list($protocol, $code, $status) = explode(' ', $firstLine, 3);
		$response->code = $code;
		$response->status = $status;
		
		for ($i = 1; $i < count($lines); ++$i)
		{
			$line = $lines[$i];
			
			if (substr_compare($line, 'set-cookie', 0, 10, true) === 0)
			{
				list($name, $value) = $this->parseCookie($line);
				$cookies[$name] = $value;
				continue;
			}
			
			list($name, $value) = explode(':', $line, 2);
			$headers[$name] = trim($value);
		}
		
		// Set parsed headers.
		if (!empty($headers)) {
			$response->header = $headers;
		}
		
		// Set parsed cookies.
		if (!empty($cookies)) {
			$response->cookie = $cookies;
		}
	}
	
	protected function parseCookie($line)
	{
		if (strpos($line, ';') !== false) {
			list($line, $junk) = explode(';', $line, 2);
		}
		
		list($name, $line) = explode(':', trim($line), 2);
		$nameValue = explode('=', trim($line), 2);
		
		return $nameValue;
	}
	
	/** Internal API. */
	public function onResponseHeader($handle, $header)
	{
		$this->response->headerString .= $header;
		return strlen($header);
	}
	
	/** Internal API. */
	public function onResponseBody($handle, $content)
	{
		if (isset($this->outfile)) {
			fwrite($this->outfile, $content);
		}
		else {
			$this->response->bodyString .= $content;
		}
		
		return strlen($content);
	}
	
	
	/* Response helpers */
	
	public function getCode()
	{
		return isset($this->response->code) ? (int)$this->response->code : $this->get(CURLINFO_HTTP_CODE);
	}
	
	public function getStatus()
	{
		return isset($this->response->status) ? $this->response->status : '';
	}
	
	public function getHeader($name = '')
	{
		if ($name == '') {
			return isset($this->response->header) ? $this->response->header : array();
		}
		
		if (!isset($this->response->header)) {
			return '';
		}
		
		foreach ($this->response->header as $key => $value)
		{
			if (strcasecmp($name, $key) === 0) {
				return $value;
			}
		}
		
		return '';
	}
	
	public function getCookie($name = '')
	{
		if ($name == '') {
			return isset($this->response->cookie) ? $this->response->cookie : array();
		}
		
		if (!isset($this->response->cookie)) {
			return '';
		}
		
		foreach ($this->response->cookie as $key => $value)
		{
			if (strcasecmp($name, $key) === 0) {
				return $value;
			}
		}
		
		return '';
	}
	
	/** Returns the whole header string, including redirects. */
	public function getRawHeader()
	{
		return $this->response->headerString;
	}
	
	/** Gets the response body. */
	public function getBody()
	{
		return $this->response->bodyString;
	}
	
	/* END Response helpers. */
	
	
	public function __debugInfo()
	{
		return [
			'request' => $this->request,
			'response' => $this->response,
		];
	}
}
