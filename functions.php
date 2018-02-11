<?
function logData()
{
	static $debugFile;
	if (!$debugFile) {
		$pathBase = sys_get_temp_dir() . '/CFI';
		$path = $pathBase . "/logData.txt";
		if (!file_exists($pathBase)) {
			mkdir($pathBase, 0777, true);
		}
		$debugFile = $path;
	}
	
	$args = func_get_args();
	
	foreach ($args as $data)
	{
		ob_start(); var_dump($data); $data = ob_get_clean();
		$data = PHP_EOL . date('m/d/Y h:i:s a', time()) . "\t" . $data;
		file_put_contents($debugFile, $data, FILE_APPEND);
	}
}

function getNumberOfLines($path)
{
	$linecount = 0;
	$fp = fopen($path, "r");
	while (!feof($fp))
	{
		$line = fgets($fp);
		$linecount++;
	}
	fclose($fp);
	return $linecount;
}

/**
 * echos progres to the std out
 * $cur - is the current element's index
 * $min
 * $max
 * $displayEvery
 * $reuseSpace = true
 */
function printProgress($cur, $min = 1, $max, $displayEvery = 100, $reuseSpace = true)
{
	static $lastLineLength;
	
	if (($cur % $displayEvery == 0) || ($cur == $max))
	{
		if ($reuseSpace && $lastLineLength)
		{
			echo str_repeat(chr(8), $lastLineLength);
		}
		
		$doneCnt = ($cur - $min + 1); 
		$str = sprintf("%04d out of %04d (%.2f %% ) is done", $doneCnt, $max, ($doneCnt / $max * 100));
		if (!$reuseSpace)
			$str .= "\n";
		echo $str;
		
		$lastLineLength = strlen($str);
	}
}
