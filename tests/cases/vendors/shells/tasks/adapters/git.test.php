<?php
App::import('Vendors', 'PluginManager.GitTask', array('file' => 'shells/tasks/adapters/git.php'));

Mock::generate('ShellDispatcher');

define('TEST_APP_ROOT', dirname(dirname(dirname(dirname(dirname(__FILE__))))));
define('TEST_APP', TEST_APP_ROOT . DS . 'test_app');

define('TEST_GIT_PLUGIN', TEST_APP_ROOT . DS . 'test_git_plugin');
define('FOUR_PLUGIN_LOCATION', TEST_APP . DS . 'plugins' . DS . 'four');

class GitTaskTestCase extends CakeTestCase {
	function setUp() {
		$this->Dispatcher = new MockShellDispatcher();
		$this->Dispatcher->params = array(
			'working' => TEST_APP, 
			'app' => 'test_app', 
			'root' => TEST_APP_ROOT, 
			'webroot' => 'webroot', 
		);
		$this->GitTask = new GitTask($this->Dispatcher);
	}

	function tearDown() {
		App::import('Folder');
		$folder = new Folder(FOUR_PLUGIN_LOCATION);
		
		if (file_exists($folder->path)) {
			$folder->delete();
		}
	}

	function testClassExists() {
		$this->assertTrue(class_exists('GitTask'));
	}

	function testInstall() {
		# code...
	}
}
?>