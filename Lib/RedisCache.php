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
	* RedisCache settings
	*
	* @var array
	*/
	protected static $settings = array();

	/**
	* Default cache settings
	*
	* @var array
	*/
	protected static $defaultCacheSettings = array(
		'engine'	=> 'RedisCache.Redis',
		'serialize'	=> false,
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
		'cookie' => 'CAKEPHP',
		'timeout' => 60,
		'handler' => array(
			'engine' => 'RedisCache.RedisSession'
		)
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
	* Configure a new timeout Closure
	*
	* It must return a strtotime compatible value
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
					$duration = '+10 seconds';
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
		Cache::config($name, array_merge(static::$defaultCacheSettings, array('duration' => static::getCacheTimeout()), $settings));
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
		if (!empty($settings) && is_string($settings)) {
			$settings = array('cookie' => $settings);
		}

		Configure::write('Session', array_merge(static::$defaultSessionSettings, (array)$settings));
	}

	/**
	* Change RedisCache settings
	*
	* Just a wrapper for Configure with a key prefix RedisCache
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