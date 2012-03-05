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