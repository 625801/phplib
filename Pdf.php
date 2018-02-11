<?
namespace IL;
use \IL\Config;
use \IL\Text;
use \IL\Tmp;
use \Exception;

class Pdf
{
	static function toText($pdfPath) {
		if (!file_exists($pdfPath)) {
			throw new Exception("Path to the pdf file is wrong: '$pdfPath'");
			return;
		}
		
		$textFile = Tmp::filePath("txt");
		$cmd =  sprintf(
			"%s -nopgbrk -layout %s %s 2>&1",
			escapeshellarg(Config::get("pdftotext")),
			escapeshellarg($pdfPath),
			escapeshellarg($textFile)
		);

		shell_exec($cmd);
		$text = file_get_contents($textFile);
		if (file_exists($textFile))
		{
			try
			{
				unlink($textFile);
			}
			catch(Exception $e)
			{
				
			}
		}
		$text = Text::toAscii($text);
		
		return $text;
	}
	
	static function toTextFile($pdfPath, $txtPath = null) {
		$text = self::toText($pdfPath);
		$txtPath = Tmp::filePath("txt");
		file_put_contents($txtPath, $text);
		return $txtPath;
	}
}