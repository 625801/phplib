<?php
namespace IL;

class Timer
{
	private $time1 = 0;
	private $elapsed = 0;
	
	public function __construct($start = false)
	{
		if ($start)
			$this->start();
	}
	
	public function start()
	{
		$this->time1 = microtime(true);
	}
	
	public function stop()
	{
		$this->elapsed += (microtime(true) - $this->time1);
		$this->time1 = 0;
	}
	
	public function reset()
	{
		$this->elapsed = 0;
		$this->time1 = 0;
	}
	
	public function restart()
	{
		$this->reset();
		$this->start();
	}
	
	public function isRunning()
	{
		return ($this->time1 > 0);
	}
	
	public function elapsed()
	{
		if ($this->isRunning())
			return $this->elapsed + (microtime(true) - $this->time1);
		else
			return $this->elapsed;
	}
	
	public function lap($millis = true)
	{
		$elapsed = $this->elapsed();
		return static::format($elapsed, $millis);
	}
	
	public function __toString()
	{
		return $this->lap();
	}
	
	public static function format($t, $millis = true)
	{
		list($h, $m, $s, $ms) = static::calc($t);
		$fmt = $millis ? '%02d:%02d:%02d.%03d' : '%02d:%02d:%02d';
		return sprintf($fmt, $h, $m, $s, $ms);
	}
	
	protected static function calc($t)
	{
		// Number of seconds in:
		$hour = 3600;
		$minute = 60;
		
		$h = $m = $s = $ms = 0;
		
		// Hours
		if ($t > $hour) {
			$h = intval($t / $hour);
			$t = $t - ($h * $hour);
		}
		
		// Minutes
		if ($t > $minute) {
			$m = intval($t / $minute);
			$t = $t - ($m * $minute);
		}
		
		// Seconds
		if ($t > 0) {
			$s = intval($t);
			$t = $t - $s;
		}
		
		// Microseconds
		if ($t > 0) {
			$ms = intval($t * 1000);
		}
		
		return array($h, $m, $s, $ms);
	}
}
