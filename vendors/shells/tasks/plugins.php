<?php
App::import('Plugins', 'ImprovedCakeShell.ImprovedCakeShell');

class PluginsTask extends ImprovedCakeShell {
	var $tasks = array('Installer');
	/**
	 * Lista todos os plugins instalados
	 */
	function listAll() {
		$this->formattedOut(String::insert(__d('plugin', "Listando plugins instalados em [u]:app[/u]:\n", true), array('app'=> $this->params['working'])));

		$installedPlugins = $this->_list();

		foreach ($installedPlugins as $plugin) {
			$out = String::insert(__d('plugin', '  [fg=green]:plugin', true), array('plugin'=> $plugin));

			if($this->_url($plugin) !== false) {
				$out .= __d('plugin', " *[/fg]\n", true);
			} else {
				$out .= __d('plugin', "[/fg]\n", true);
			}

			$this->formattedOut($out);
		}

		$this->formattedOut(__d('plugin', '* Plugins que podem ser atualizados utilizando o [u]Plugin Manager[/u]', true));
	}

	/**
	 * Instala um plugin a partir de uma url de repositório
	 */
	function install($url, $name = null) {
		$params = $this->_extractParams($url, $name);

		if ($params === false) {
			$this->error(String::insert(__d('plugin', 'Url de plugin inválida: :url', true), array('url' => $url)));
		}
		if (empty($params['name'])) {
			$name = $this->in(__d('plugin', 'digite o nome do plugin que está instalando (ou deixe vazio para encerrar): ', true));
			$name = trim($name);
			if (empty($name)) {
				$this->_stop();
			}
			$params['name'] = $name;
		}
		
		if ($this->_exists($params['name'])) {
			return $this->update($params['name'], $params['url']);
		}
		return $this->Installer->install($params['url'], $params['name']);
	}

	/**
	 * Remove um plugin instalado
	 */
	function uninstall($plugin) {
		$this->formattedOut(String::insert(__d('plugin', "Tem certeza que deseja remover [fg=yellow]:plugin[/fg]?", true), array('plugin' => $plugin)));
		$this->formattedOut(__d('plugin', "[fg=green](Y)[/fg] Sim\n[fg=red](N)[/fg] Nao\n", true));
		$remove['plugin'] = $this->in('', array('Y', 'N'));

		if (strtolower($remove['plugin']) == 'y') {
			App::import('Folder');

			if ($deps = $this->_getDependencies($plugin)) {
				$this->mainShell->formattedOut(__d('plugin', "\n\nDeseja remover as dependecias instaladas?", true));
				$this->mainShell->formattedOut(__d('plugin', "[fg=green](N)[/fg] Nenhuma\n[fg=yellow](S)[/fg] Passo-a-Passo\n[fg=red](A)[/fg] Todas\n", true));
				$remove['deps'] = $this->mainShell->in('', array('N', 'S', 'A'));
			}

			if (!empty($remove['deps']) && strtolower($remove['deps']) != 'n') {
				$this->_removeDependencies($deps, (strtolower($remove['deps']) == 's'));
			}

			$folder = new Folder();
			if ($folder->delete($this->params['working'] . DS . 'plugins/' . $plugin)) {
				$this->formattedOut(String::insert(__d('plugin', "[fg=yellow]:plugin[/fg] removido com sucesso!", true), array('plugin'=>$plugin)));
			}
		}
	}

	function update($plugin, $url = null) {
		App::import('Folder');

		$this->formattedOut(String::insert(__d('plugin', "Atualizando [fg=green]:plugin[/fg]...", true), array('plugin' => $plugin)), false);

		if (empty($url)) {
			$url = $this->_url($plugin);
		}

		if (empty($url)) {
			$this->formattedOut(__d('plugin', "[fg=black][bg=red] ERRO [/bg][/fg]", true));
			$this->formattedOut(__d('plugin', "  -> O plugin nao existe ou nao possui uma url para atualizacao.", true));
			$this->_stop();
		}

		$this->out("\n", false);

		$pluginFolder = new Folder();
		$pluginFolder->move(array(
			'from' => $this->params['working'] . DS . 'plugins/' . $plugin,
			'to'   => $this->params['working'] . DS . 'plugins/' . $plugin . '-old'
		));

		$status = $this->Installer->install($url, $plugin);

		if ($status) {
			$pluginFolder->delete($this->params['working'] . DS . 'plugins/' . $plugin . '-old');
		} else {
			$this->formattedOut(__d('plugin', "[fg=black][bg=red] ERRO [/bg][/fg]", true));
			$this->formattedOut(__d('plugin', "  -> Nao foi possivel atualizar o plugin.", true));
			$pluginFolder->move(array(
				'from'   => $this->params['working'] . DS . 'plugins/' . $plugin . '-old',
				'to' => $this->params['working'] . DS . 'plugins/' . $plugin
			));
		}
	}

	/**
	 * Lista todas as dependências de um plugin
	 */
	function _getDependencies($plugin) {
		$installer = $this->params['working'] . DS . 'plugins/' . $plugin . '/vendors/shells/' . $plugin . '_installer.php';
		if (file_exists($installer)) {
			$className = Inflector::camelize($plugin . '_installer');
			include($installer);
			$installer = new $className(array('mainShell' => $this));
			return $installer->deps;
		}

		return null;
	}

	/**
	 * Remove as dependências de um plugin
	 */
	function _removeDependencies($dependencies, $step) {
		$folder = new Folder();
		$opt = 'y';

		foreach ($dependencies as $name => $url) {
			if ($step) {
				$this->formattedOut(String::insert(__d('plugin', "\n\nTem certeza que deseja remover [fg=yellow]:plugin[/fg]?", true), array('plugin' => $name)));
				$this->formattedOut(__d('plugin', "[fg=green](Y)[/fg] Sim\n[fg=red](N)[/fg] Nao\n", true));
				$remove['deps'] = $this->in('', array('Y', 'N'));
			}

			if (strtolower($opt == 'y')) {
				if ($folder->delete($this->params['working'] . DS . 'plugins/' . $name)) {
					$this->formattedOut(String::insert(__d('plugin', "\n\n[fg=yellow]:plugin[/fg] removido com sucesso!", true), array('plugin' => $name)));
				}
			}
		}
	}

	/**
	 * Retorna um array com todos os plugins instalados
	 */
	function _list() {
		$Folder = new Folder($this->params['working'] . DS . 'plugins');
		$listPluginsFolder = $Folder->ls();

		return $listPluginsFolder[0];
	}

	/**
	 * Retorna a url do repositório para um determinado plugin.
	 */
	function _url($plugin) {
		$urlPluginPath = $this->params['working'] . DS . 'plugins' . DS . $plugin . DS . '.url';
		if (!file_exists($urlPluginPath)) {
			return false;
		}

		$url = file_get_contents($urlPluginPath);
		return trim($url);
	}

	/**
	 * Testa se $string é uma url de repositório válida
	 */
	function _isUrl($url) {
		$prefix = array('git', 'svn', 'http', 'https', 'ssh', 'file');
		$pattern = '/^(' . implode('|', $prefix) . '):\/\//';
		return preg_match($pattern, $url);
	}

	/**
	 * Extrai os parâmetros necessários para a instalação do plugin
	 */
	function _extractParams($url, $name = null) {
		if (!$this->_isUrl($url)) {
			//TODO: Localizar um plugin com o nome = $url
			return false;
		}
		if (is_null($name)) {
			if (preg_match('/(\w+)(\/|\.\w+)?$/', $url, $found)) {
				$name = $found[1];
			}
		}
		
		return compact('url', 'name');
	}

	function _exists($plugin) {
		$path = $this->params['working'] . DS . 'plugins' . DS . $plugin;
		return file_exists($path) && is_dir($path);
	}
}
?>