<?php
App::import('Plugins', 'ImprovedCakeShell.ImprovedCakeShell');

/**
 * Instalador de plugins utilizando um repositório SVN
 */
class SvnTask extends ImprovedCakeShell {
	function install($url, $pluginPath, $name) {
		$this->_isSupported();

		$return = true;
		if ($this->_dotSvnPathExists()) {
			$output = __d('plugin', '  -> adicionando svn:external... ', true);

			$errors = $this->_externals($url, $pluginPath, $name);
		} else {
			$output = __d('plugin', '  -> importando repositorio... ', true);

			$errors = $this->_export($url, $pluginPath);
		}
		if (!empty($errors)) {
			$return = false;
		}

		if ($return) {
			$this->formattedOut(__d('plugin', '[fg=yellow][u]SVN[/u][/fg]', true));
			$this->formattedOut($output, false);
		} else {
			foreach ($errors as $error) {
				$this->formattedOut("    - $error");
			}
		}

		return $return;
	}

	/**
	 * Verificar se o SVN está instalado e funcionando
	 */
	function _isSupported() {
		if (!shell_exec('svn --version 2>/dev/null')) {
			$this->formattedOut(__d('plugin', "[bg=red][fg=black] ERRO : SVN não suportado [/fg][/bg]\n", true));
			$this->_stop();
		}
	}

	/**
	 * Verificar se existe a pasta APP/.svn
	 */
    function _dotSvnPathExists() {
        return file_exists($this->params['working'] . '.svn/');
    }

	/**
	 * Instala o plugin através do svn export
	 */
	function _export($url, $pluginPath) {
		$return = shell_exec('svn export ' . $url . ' ' . $pluginPath . ' 2>&1');

		$pattern = "/^svn\:.*/i";
		$found = null;
		preg_match_all($pattern, $return, $found);

		return $found[0];
	}

	/**
	 * Instala o plugin através do svn eternals
	 */
	function _externals($url, $pluginPath) {
		$this->out('');

		$externals = $this->_getExternals();
		$externals .= "\nplugins" . DS . $name . ' ' . $url;

		if (file_put_contents('.externals-tmp', $externals) !== false) {
			$return = shell_exec('svn propset -q svn:externals . -F .externals-tmp 2>&1');
			shell_exec('svn update');
			unlink('.externals-tmp');
		}

		$pattern = "/^svn\:.*/i";
		$found = null;
		preg_match_all($pattern, $return, $found);

		return $found[0];
	}

	function _getExternals() {
		return trim(shell_exec('svn propget svn:externals .'));
	}
}
?>