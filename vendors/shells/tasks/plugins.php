<?php
if (!App::import('Plugins', 'ImprovedCakeShell.ImprovedCakeShell')) {
	App::import('Vendors', 'InstallImprovedCakeShell.InstallImprovedCakeShell');

	$installICS =& ClassRegistry::init('InstallImprovedCakeShell');

	$installICS->install();
}

class PluginsTask extends ImprovedCakeShell {
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
	function install($url) {
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
}
?>