<?php
/**
 * Redis Cache helper class
 *
 * This class is a singleton
 *
 * Copyright 2010-2012, Nodes ApS. (http://www.nodesagency.com/)
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Nodes ApS, 2012
 */
class RedisCache {

	/**
	* Default cache settings
	*
	* @var array
	*/
	protected static $defaultCacheSettings = array(
		'engine'	=> 'RedisCache.Redis',
		'hostname'	=> '127.0.0.1',
		'port'		=> 6379,
		'password'	=> null
	);

	/**
	* Default session settings
	*
	* @var array
	*/
	protected static $defaultSessionSettings = array(
		'cookie'	=> 'CAKEPHP',
		'timeout'	=> 120,
		'handler'	=> array(
			'engine' => 'RedisCache.RedisSession',
			'config' => 'cache'
		),
		'hostname'	=> '127.0.0.1',
		'port'		=> 6379,
		'password'	=> null,
		'cookieTimeout' => 120
	);

	/**
	* Lambda function to return default timeouts
	*
	* The default lambda returns +999 days if debug = 0 and +10 seconds if debug >= 1
	* This is the CakePHP 2.0 default
	*
	* @var Closure
	*/
	protected static $timeoutClosure;

	/**
	* Change the timeout closure
	*
	* @param Closure $value
	* @return void
	*/
	public static function configureCacheTimeout(Closure $value) {
		static::$timeoutClosure = $value;
	}

	/**
	* Get the cache timeout
	*
	* Override the default with configureCacheTimeout
	*
	* @return string strtotime compatible string
	*/
	public static function getCacheTimeout() {
		if (empty(static::$timeoutClosure)) {
			static::configureCacheTimeout(function() {
				$duration = '+999 days';
				if (Configure::read('debug') >= 1) {
					$duration = '+1 seconds';
				}
				return $duration;
			});
		}

		return call_user_func(static::$timeoutClosure);
	}

	/**
	* Configure a cache engine
	*
	* @param string $name Name of the Cache engine
	* @param array $settings Additional settings to merge into \defaultCacheSettings
	* @return void
	*/
	public static function configureCache($name, $settings = array()) {
		$settings = array_merge(
			array(
				'duration'	=> static::getCacheTimeout(),
				'prefix'	=> basename(dirname(dirname(APP)))
			),
			static::$defaultCacheSettings,
			Configure::read('RedisCache.cache'),
			$settings
		);

		$settings['prefix'] .= '.' . str_replace('_', '.', $name) . '.';
		$settings['prefix'] = str_replace('..', '.', $settings['prefix']);

		Cache::config($name, $settings);
	}

	/**
	* Configure CakePHP for Redis sessions
	*
	* If $settings is a string, it will be used as cookie name
	*
	* @param array|string $settings Additional settings to merge into \defaultSessionSettings
	* @return void
	*/
	public static function configureSession($settings = array()) {
		$settings = array_merge(
			array(
				'prefix' => 'sessions.' . basename(dirname(dirname(APP)))
			),
			static::$defaultSessionSettings,
			Configure::read('RedisCache.session'),
			$settings
		);

		$settings['prefix'] .= '.';
		$settings['prefix'] = str_replace('..', '.', $settings['prefix']);

		Configure::write('Session', $settings);
	}

	/**
	* Check if a key has configured its settings already
	*
	* @param string $key
	* @return boolean
	*/
	public static function hasSettings($key) {
		return !is_null(Configure::read(sprintf('RedisCache.%s', $key)));
	}

	/**
	* Change RedisCache settings
	*
	* Just a wrapper for Configure with a key prefix RedisCache
	*
	* Key should belong to the "cache" or "session" namespace
	*
	* @param string $key	Configure::read / Configure::write compatible key
	* @param mixed $value	Any value Configure normally would accept
	* @return mixed
	*/
	public static function settings($key, $value = null) {
		$key = sprintf('RedisCache.%s', $key);
		if (empty($value)) {
			return Configure::read($key);
		}
		return Configure::write($key, $value);
	}

	/**
	* Protected constructor to ensure its a singleton
	*
	* @return void
	*/
	protected function __construct() {

	}
}