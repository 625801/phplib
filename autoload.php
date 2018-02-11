<?php
require_once(__DIR__.'/functions.php');
require_once(__DIR__.'/ClassLoader.php');

$loader = new \IL\ClassLoader();
$loader->add(__DIR__);
$loader->register();
unset($loader);
