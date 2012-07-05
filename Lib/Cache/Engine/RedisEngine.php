<?php
/**
 * Redis Cache Class. Uses Predis as the PHP connection class
 */
class RedisEngine extends CacheEngine {

	/**
	 * Settings
	 *
	 * @var array
	 */
	public $settings = array();

	/**
	 * Initialize the Redis Cache Engine
	 *
	 * Called automatically by the cache frontend
	 * To reinitialize the settings call Cache::engine('EngineName', [optional] settings = array());
	 *
	 * @param array $settings array of setting for the engine
	 * @return boolean true
	 */
	public function init($settings = array()) {
		$this->settings = array_merge(
			array(
				'engine' => 'Redis',
				'prefix' => Inflector::slug(basename(dirname(dirname(APP)))) . '_'
			),
			$settings,
			RedisCache::settings('cache')
		);

		parent::init($this->settings);

		if (!isset($this->redis)) {
			$this->redis = new Redis();
			$this->redis->pconnect($this->settings['hostname'], $this->settings['port']);
			$this->redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);
		}

		return true;
	}

	/**
	 * READ
	 * Return whatever is stored in the key
	 *
	 * @param string $key
	 * @access public
	 * @return boolean
	 */
	public function read($key = '') {
		return $this->redis->get($key);
	}

	/**
	 * WRITE
	 * SET if no duration passed in else SETEX
	 *
	 * @param string $key
	 * @param mixed $data
	 * @param integer $duration defaults to 3600 (seconds)
	 * @access public
	 * @return boolean
	 */
	public function write($key = '', $data = null, $duration = 3600) {
		return $this->redis->setex($key, $duration, $data);
	}

	/**
	 * INCREMENT
	 * Increase the value of a stored key by $offset
	 *
	 * @param string $key
	 * @param integer $offset
	 * @access public
	 * @return boolean
	 */
	public function increment($key = '', $offset = 1) {
		return $this->redis->incrby($key, $offset);
	}

	/**
	 * DECREMENT
	 * Reduce the value of a stored key by $offset
	 *
	 * @param string $key
	 * @param integer $offset
	 * @access public
	 * @return boolean
	 */
	public function decrement($key = '', $offset = 1) {
		return $this->redis->decrby($key, $offset);
	}

	/**
	 * DELETE
	 * DEL the key from store
	 *
	 * @param string $key
	 * @access public
	 * @return boolean
	 */
	public function delete($key = '') {
		// Predis::del returns an integer 1 on delete, convert to boolean
		return $this->redis->delete($key) ? true : false;
	}

	/**
	 * CLEAR - not implemented
	 * DEL all keys with settings[prefix]
	 *
	 * @param boolean $check
	 * @access public
	 * @return boolean
	 */
	public function clear($check) {
		$keys = $this->redis->keys(sprintf('%s*', $this->settings['prefix']));
		return $this->redis->delete($keys);
	}

	/**
	 * GARBAGE COLLECTION - not required
	 *
	 * @access public
	 * @return boolean
	 */
	public function gc($expires = null) {
		return true;
	}
}
