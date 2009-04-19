<?php
App::import('Plugins', 'ImprovedCakeShell.ImprovedCakeShell');

/**
 * Instalador de plugins utilizando um repositório Git
 */
class GitTask extends ImprovedCakeShell {
	function install($url, $pluginPath) {
		$this->_isSupported();

		$return = true;

		if ($this->_dotGitPathExists()) {
			$output = __d('plugin', '  -> adicionando novo submodulo... ', true);

			$errors = $this->_submodule($url, $pluginPath);
		} else {
			$output = __d('plugin', '  -> clonando repositorio... ', true);

			$errors = $this->_clone($url, $pluginPath);
		}
		if (!empty($errors)) {
			$return = false;
		}

		if ($return) {
			$this->formattedOut(__d('plugin', '[fg=yellow][u]GIT[/u][/fg]', true));
			$this->formattedOut($output, false);
		} else {
			foreach ($errors as $error) {
				$this->formattedOut("    - $error");
			}
		}

		return $return;
	}

	/**
	 * Verificar se o git está instalado e funcionando
	 */
	function _isSupported() {
		if (!shell_exec('git --version 2>/dev/null')) {
			$this->formattedOut(__d('plugin', "[bg=red][fg=black] ERRO : GIT não suportado [/fg][/bg]\n", true));
			$this->_stop();
		}
	}

	/**
	 * Verificar se existe a pasta APP/.git
	 */
    function _dotGitPathExists() {
        return file_exists($this->params['working'] . '.git/');
    }

	/**
	 * Instala o plugin através do git clone
	 */
	function _clone($url, $pluginPath) {
		$return = shell_exec('git clone ' . $url . ' ' . $pluginPath . ' 2>&1');

		$pattern = "/.*fatal.*/i";
		$found = null;

		if (!preg_match_all($pattern, $return, $found)) {
			$this->_excludeGitFolder($pluginPath);
		}

		return $found[0];
	}

	/**
	 * Instala o plugin através do git submodule add
	 */
	function _submodule($url, $pluginPath) {
		$moduleLocation = str_replace($this->params['working'], '', $pluginPath);
		$return = shell_exec('git submodule add ' . $_url . ' ' . $moduleLocation . ' 2>&1');

		$pattern = "/.*fatal.*/i";
		$found = array();
		preg_match_all($pattern, $return, $found);

		if( empty($found[0])) {
			shell_exec('git submodule init && git submodule update');
		}

		return $found[0];
	}

	/**
	 * Remove a pasta .git
	 */
	function _excludeGitFolder($pluginPath) {
		App::import('Folder');

		$gitFolder = $pluginPath . '/.git/';
		$folder    = new Folder($gitFolder, false);
		$folder->delete($gitFolder);
	}
}
?>