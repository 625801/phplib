<?
namespace IL;

class Config {
	public static $data = [];
	
	public static function get($name) {
		if (!array_key_exists($name, self::$data)) {
			return null;
		}
		return self::$data[$name];
	}
	
	public static function getAll() {
		return self::$data;
	}
	
	public static function set($name, $value) {
		self::$data[$name] = $value;
	}
	
	public static function setIfNotExists($name, $value) {
		if (self::get($name) === null)
			self::$data[$name] = $value;
		return;
	}
	
	public static function setAll($values) {
		foreach($values as $k => $v) {
			self::set($k, $v);
		}
	}
	
	public static function setAllIfNotExists($values) {
		foreach($values as $k => $v) {
			self::setIfNotExists($k, $v);
		}
	}
}