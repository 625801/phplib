<?
namespace IL;
class Tmp
{
	public static function filePath($ext = "txt") {
		$sep = DIRECTORY_SEPARATOR;
		$base = sys_get_temp_dir() . $sep . 'CFI';
		if (!file_exists($base)) {
			mkdir($base, 0777, true);
		}
		$path = sys_get_temp_dir().$sep.'CFI'.$sep.uniqid().".".$ext;
		return $path;
	}
}