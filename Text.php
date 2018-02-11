<?
namespace IL;

class Text
{
	static function toAscii($text)
	{
		return preg_replace('@[^\x9\x0a\x0d\x0c\x20-\x7E]@', '', $text);
	}
	
}