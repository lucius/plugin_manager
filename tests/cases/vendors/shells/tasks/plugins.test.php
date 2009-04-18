<?php
App::import('Vendors', 'PluginManager.Shells.Tasks.Plugins', array('file' => 'shells/tasks/plugins.php'));

Mock::generate('ShellDispatcher');

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
}
?>