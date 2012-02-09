<?php
App::import('Lib', 'RedisCache.RedisCache');

if (!RedisCache::hasSettings('cache')) {
	RedisCache::settings('cache', array(
		'host'		=> '127.0.0.1',
		'port'		=> 6379,
		'password'	=> null
	));
}

if (!RedisCache::hasSettings('session')) {
	RedisCache::settings('session', array(
		'host'		=> '127.0.0.1',
		'port'		=> 6379,
		'password'	=> null
	));
}

// Redis plugin depends on Predis
// @link https://github.com/nrk/predis
$paths = App::path('Lib', 'RedisCache');
nodes\Autoload::addPath($paths[0] . 'Predis' . DS . 'lib' . DS);

//require_once($paths[0] . 'Predis/autoload.php');
//Predis\Autoloader::register();