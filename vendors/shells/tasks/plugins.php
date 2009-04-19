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
			//TODO: Solicitar para o usuário digitar um nome para o plugin
			$this->error(String::insert(__d('plugin', 'Impossível determinar um nome para o plugin: :url', true), array('url' => $url)));
		}
		//TODO: Se $params['name'] já existir somente atualizar
		$this->Installer->install($params['url'], $params['name']);
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
}
?>