<?php
App::import('Plugins', 'ImprovedCakeShell.ImprovedCakeShell');
App::import('Vendors', 'PluginManager.GitTask', array('file' => 'shells/tasks/adapters/git.php'));
App::import('Vendors', 'PluginManager.SvnTask', array('file' => 'shells/tasks/adapters/svn.php'));

/**
 * Classe Base para o instalador
 */
class InstallerTask extends ImprovedCakeShell {
	var $tasks = array('Git', 'Svn');

	function install($url, $name) {
        $this->formattedOut(String::insert(__d('plugin', "Instalando [u]:plugin[/u]...", true), array('plugin' => $url)));

		// $this->_create($name);
		$path = $this->params['working'] . DS . 'plugins' . DS . $name;
		if ($this->_install($url, $path, $name)) {
			$this->formattedOut(__d('plugin', '[fg=black][bg=green]  OK  [/bg][/fg]', true));
			$this->_createUrlFile($url, $path);
			$this->_runInstallHook($name);
			return true;
		} else {
			$this->formattedOut(__d('plugin', '[fg=black][bg=red] ERRO [/bg][/fg]', true));
		}
		return false;
	}

	/**
	 * Cria a pasta dentro de APP/plugins
	 */
	function _create($name) {
		App::import('Folder');

		$path = $this->params['working'] . DS . 'plugins' . DS . $name;
		$folder = new Folder($path, true);

		return file_exists($folder->path) && is_dir($folder->path);
	}

	/**
	 * Instala o plugin, deverá ser sobreescrevida pelo instalador
	 */
	function _install($url, $path, $name) {
        $this->formattedOut(__d('plugin', '  -> selecionando modo de instalacao: ', true), false);

		if ($status = $this->Git->install($url, $path)) {
		} else if ($status = $this->Svn->install($url, $path, $name)) {
		}
		return $status;
	}

	/**
	 * Cria um arquivo .url na pasta do plugin com a url do repositório para posterior atualização do mesmo.
	 */
	function _createUrlFile($url, $path) {
		$file = $path . DS . '.url';
		if (!file_exists($file)) {
			file_put_contents($file, $url);
		}
	}

	/**
	 * Executa o instalador do plugin caso ele exista
	 */
	function _runInstallHook($plugin) {
		//TODO: Executar usando o próprio console do Cake
		$this->formattedOut(__d('plugin', "  -> verificando a existencia do hook de instalacao...", true));

		$installer = APP . 'plugins/' . $plugin . '/vendors/shells/' . $plugin . '_installer.php';
		if (file_exists($installer)) {
			$this->formattedOut(__d('plugin', "    - carregando... ", true), false);
			$className = Inflector::camelize($plugin . '_installer');
			if (!include($installer)) {
				$this->formattedOut(__d('plugin', "[fg=black][bg=red] ERRO [/bg][/fg]", true));
				return;
			}

			$this->formattedOut(__d('plugin',"[fg=black][bg=green]  OK  [/bg][/fg]\n  -> executando hook de instalação...", true));
			$installer = new $className($this);

			$installer->startup();
			$installer->_installDeps();

			if (method_exists($installer, 'install')) {
				$installer->install();
			}
		} else {
			$this->formattedOut(__d('plugin', "    - O hook nao existe\n", true));
		}
	}
}
?>