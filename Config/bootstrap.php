<?php
App::import('Lib', 'RedisCache.RedisCache');

RedisCache::settings('cache', array(
	'hostname'	=> '127.0.0.1',
	'port'		=> 6379,
	'password'	=> null
));

RedisCache::settings('session', array(
	'hostname'	=> '127.0.0.1',
	'port'		=> 6379,
	'password'	=> null
));

// Redis plugin depends on Predis
// @link https://github.com/nrk/predis
App::import('Lib', 'RedisCache.Predis/Autoloader');
Predis\Autoloader::register();