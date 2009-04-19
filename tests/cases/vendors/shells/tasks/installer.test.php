<?php
App::import('Vendors', 'PluginManager.GitTask', array('file' => 'shells/tasks/adapters/git.php'));
App::import('Vendors', 'PluginManager.SvnTask', array('file' => 'shells/tasks/adapters/svn.php'));
App::import('Vendors', 'PluginManager.InstallerTask', array('file' => 'shells/tasks/installer.php'));

Mock::generate('ShellDispatcher');

define('TEST_APP_ROOT', dirname(dirname(dirname(dirname(dirname(__FILE__))))));
define('TEST_APP', TEST_APP_ROOT . DS . 'test_app');

define('FOUR_PLUGIN_LOCATION', TEST_APP . DS . 'plugins' . DS . 'four');
define('THREE_PLUGIN_LOCATION', TEST_APP . DS . 'plugins' . DS . 'three');

class InstallerTaskTestCase extends CakeTestCase {
	function setUp() {
		$this->Dispatcher = new MockShellDispatcher();
		$this->Dispatcher->params = array(
			'working' => TEST_APP, 
			'app' => 'test_app', 
			'root' => TEST_APP_ROOT, 
			'webroot' => 'webroot', 
		);
		$this->InstallerTask = new InstallerTask($this->Dispatcher);
	}

	function tearDown() {
		App::import('Folder');
		$folder = new Folder(FOUR_PLUGIN_LOCATION);
		
		if (file_exists($folder->path)) {
			$folder->delete();
		}
	}

	function testClassExists() {
		$this->assertTrue(class_exists('InstallerTask'));
	}

	function testCreateUrlFile() {
		$this->InstallerTask->_createUrlFile('http://example.com/plugin.git', THREE_PLUGIN_LOCATION);
		$this->assertTrue(file_exists(THREE_PLUGIN_LOCATION . DS . '.url'));
		$this->assertEqual(file_get_contents(THREE_PLUGIN_LOCATION . DS . '.url'), 'http://example.com/plugin.git');
		unlink(THREE_PLUGIN_LOCATION . DS . '.url');
	}
}
?>