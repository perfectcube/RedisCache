<?php
/**
 * CacheSessionTest
 *
 * PHP 5
 *
 * CakePHP(tm) Tests <http://book.cakephp.org/view/1196/Testing>
 * Copyright 2005-2011, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2011, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       Cake.Test.Case.Model.Datasource.Session
 * @since         CakePHP(tm) v 2.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

App::uses('CakeSession', 'Model/Datasource');
App::uses('RedisSession', 'Redis.Model/Datasource/Session');
class_exists('CakeSession');


class RedisSessionTest extends CakeTestCase {


    /**
     * Cache name used when testing
     *
     * @var string
     * @access protected
     */
    protected static $_cache_name = 'redis_session_test';


    /**
     * Store session settings before running tests so they
     * can be put back in place after tests are complete
     *
     * @var string
     * @access protected
     */
    protected static $_sessionBackup;


    /**
     * test case startup
     *
     * @return void
     */
    public static function setupBeforeClass() {
        Configure::write('Session', array(
               'cookie' => 'redis_session_test'
               , 'timeout' => 1
               , 'handler' => array(
                   'engine' => 'Redis.RedisSession'
               )
       	));

        self::$_sessionBackup = Configure::read('Session');

        Configure::write('Session.handler.config', 'session_test');
    }

    /**
     * cleanup after test case.
     *
     * @return void
     */
    public static function teardownAfterClass() {
        Cache::clear(self::$_cache_name);
        Cache::drop(self::$_cache_name);

        Configure::write('Session', self::$_sessionBackup);
    }


    /**
     * setup
     *
     * @return void
     */
    public function setUp() {
        parent::setUp();
        $this->storage = new RedisSession();
    }


    /**
     * teardown
     *
     * @return void
     */
    public function tearDown() {
        parent::tearDown();
        unset($this->storage);
    }


    /**
     * test opening and closing a connection
     *
     * @return void
     */
    public function testOpenAndClose() {
        $this->assertTrue($this->storage->open());
        $this->assertTrue($this->storage->close());
    }


    /**
     * test write() fails with an empty session_id
     *
     * @return void
     */
    public function testWriteFailsWithEmptySessionId() {
        $this->storage->open();

        $this->assertFalse($this->storage->write('', 'Some value'));

        $this->storage->close();
    }


    /**
     * test write() fails with an empty session_id
     *
     * @return void
     */
    public function testWriteFailsWithArraySessionId() {
        $this->storage->open();

        $this->assertFalse($this->storage->write(array('test'), 'Some value'));

        $this->storage->close();
    }


    /**
     * test write()
     *
     * @return void
     */
    public function testWrite() {
        $this->storage->open();

        $this->assertNull($this->storage->read('abc'));
        $this->assertTrue($this->storage->write('abc', 'Some value'));
        $this->assertSame('Some value', $this->storage->read('abc'), 'Value was not written.');
        $this->storage->destroy('abc');

        $this->storage->close();
    }


    /**
     * test reading.
     *
     * @return void
     */
    public function testRead() {
        $this->storage->open();

        $this->storage->write('test_one', 'Some other value');
        $this->assertSame('Some other value', $this->storage->read('test_one'), 'Incorrect value.');
        $this->storage->destroy('test_one');

        $this->storage->close();
    }


    /**
     * test destroy
     *
     * @return void
     */
    public function testDestroy() {
        $this->storage->open();

        $this->storage->write('test_one', 'Some other value');
        $this->assertTrue($this->storage->destroy('test_one'));
        $this->assertNull($this->storage->read('test_one'));

        $this->storage->close();
    }
}