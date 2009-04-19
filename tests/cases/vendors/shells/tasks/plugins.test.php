<?php
App::import('Vendors', 'PluginManager.PluginsTask', array('file' => 'shells/tasks/plugins.php'));

Mock::generate('ShellDispatcher');
Mock::generate('Shell', 'InstallerTask');

define('TEST_APP_ROOT', dirname(dirname(dirname(dirname(dirname(__FILE__))))));
define('TEST_APP', TEST_APP_ROOT . DS . 'test_app');

class PluginsTaskTestCase extends CakeTestCase {
	function setUp() {
		$this->Dispatcher = new MockShellDispatcher();
		$this->Dispatcher->params = array(
			'working' => TEST_APP, 
			'app' => 'test_app', 
			'root' => TEST_APP_ROOT, 
			'webroot' => 'webroot', 
		);
		$this->PluginsTask = new PluginsTask($this->Dispatcher);
	}
	
	function testClassExists() {
		$this->assertTrue(class_exists('PluginsTask'));
	}

	function testList() {
		$result = $this->PluginsTask->_list();
		debug($result);
		$expected = array('one', 'three', 'two');
		$this->assertEqual($result, $expected);
	}

	function testUrl() {
		$result = $this->PluginsTask->_url('one');
		$expected = 'http:://path.to.plugin.one/';
		$this->assertEqual($result, $expected);
	}

	function testUrlWithoutDotUrlFile() {
		$result = $this->PluginsTask->_url('two');
		$this->assertFalse($result);
	}

	function testIsUrl() {
		$this->assertTrue($this->PluginsTask->_isUrl('http://example.com/plugin.git'));
		$this->assertTrue($this->PluginsTask->_isUrl('https://example.com/plugin.git'));
		$this->assertTrue($this->PluginsTask->_isUrl('git://example.com/plugin.git'));
		$this->assertTrue($this->PluginsTask->_isUrl('svn://example.com/plugin'));
		$this->assertTrue($this->PluginsTask->_isUrl('ssh://example.com/plugin.git'));
		$this->assertTrue($this->PluginsTask->_isUrl('file://home/user/plugin.git'));
		$this->assertFalse($this->PluginsTask->_isUrl('one'));
		$this->assertFalse($this->PluginsTask->_isUrl('two'));
		$this->assertFalse($this->PluginsTask->_isUrl('three'));
	}

	function testExtractParams() {
		$result = $this->PluginsTask->_extractParams('git://example.com/plugin.git', 'plugin');
		$expected = array(
			'url'    => 'git://example.com/plugin.git',
			'name'   => 'plugin'
		);
		$this->assertEqual($result, $expected);
		$result = $this->PluginsTask->_extractParams('svn://example.com/plugin', 'plugin');
		$expected = array(
			'url'    => 'svn://example.com/plugin',
			'name'   => 'plugin'
		);
		$this->assertEqual($result, $expected);
		$result = $this->PluginsTask->_extractParams('file://home/user/plugin.git', 'plugin');
		$expected = array(
			'url'    => 'file://home/user/plugin.git',
			'name'   => 'plugin'
		);
		$this->assertEqual($result, $expected);
	}
}
?>