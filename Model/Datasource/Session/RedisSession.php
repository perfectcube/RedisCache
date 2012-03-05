<?php
App::uses('CakeSessionHandlerInterface', 'Model/Datasource/Session');

/**
 * Redis Session Store Class. Uses Predis as the PHP connection class
 */
class RedisSession implements CakeSessionHandlerInterface {

	/**
	 * Placeholder for cached Redis resource
	 *
	 * @var object
	 * @access protected
	 */
	protected $redis;

	/**
	 * Seconds until key should expire
	 *
	 * @var int
	 * @access protected
	 */
	protected $timeout;

	/**
	 * Prefix to apply to all Redis session keys
	 *
	 * @var string
	 * @access protected
	 */
	protected $prefix;

	/**
	  * OPEN
	  * - Connect to Redis
	  * - Calculate and set timeout for SETEX
	  * - Set session_name as key prefix
	  *
	  * @access public
	  * @return boolean true
	  */
	public function open() {
		$this->settings = array_merge(
			array(
				'engine' => 'Redis',
				'prefix' => Inflector::slug(basename(dirname(dirname(APP)))) . '_session_'
			),
			Configure::read('Session')
		);

		$this->timeout = Configure::read('Session.timeout') * Security::inactiveMins();

		$this->redis = new Redis();
		$this->redis->pconnect($this->settings['hostname'], $this->settings['port']);
		$this->redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_IGBINARY);
		return true;
	}

	/**
	 * CLOSE
	 * - Disconnect from Redis
	 *
	 * @access public
	 * @return boolean true
	 */
	public function close() {
		return true;
	}

	/**
	 * READ
	 * - Return whatever is stored in session ID (as key)
	 * - Session ID is autoprefixed by Predis to create the key
	 *
	 * @param string $session_id
	 * @access public
	 * @return boolean
	 */
	public function read($session_id = '') {
		return $this->redis->get($session_id);
	}

	/**
	 * WRITE
	 * - SETEX data with timeout calculated in open()
	 * - Session ID is autoprefixed by Predis to create the key
	 *
	 * @param string $session_id
	 * @param mixed $data
	 * @access public
	 * @return boolean
	 */
	public function write($session_id = '', $data = null) {
		if ($session_id && is_string($session_id)) {
			return $this->redis->setex($session_id, $this->timeout, $data);
		}
		return false;
	}

	/**
	 * DESTROY
	 * - DEL the key from store
	 * - Session ID is autoprefixed by Predis to create the key
	 *
	 * @param string $session_id
	 * @access public
	 * @return boolean
	 */
	public function destroy($session_id = '') {
		// Predis::del returns an integer 1 on delete, convert to boolean
		return $this->redis->del($session_id) ? true : false;
	}

	/**
	 * GARBAGE COLLECTION
	 * not needed as SETEX automatically removes itself after timeout
	 * ie. works like a cookie
	 *
	 * @param int $expires defaults to null
	 * @access public
	 * @return boolean
	 */
	public function gc($expires = null) {
		return true;
	}
}