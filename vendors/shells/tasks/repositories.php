<?php
App::import('Plugins', 'ImprovedCakeShell.ImprovedCakeShell');

class RepositoriesTask extends ImprovedCakeShell {
	var $tasks = array('Plugins');
	var $repositories = array();
	var $path = null;
	
	function __construct(&$dispatch) {
		parent::__construct($dispatch);

		$this->path = $this->params['working'] . DS . 'plugins/plugin_manager/.reps';
		$this->_parser();
	}

	/**
	 * Adiciona um novo repositório se ele já não estiver na lista
	 */
	//TODO: Adicionar uma parametro $name ao repositorio para fácil acesso e identificacao
	function add($url) {
		$this->formattedOut(String::insert(__d('plugin', 'Inserindo repositorio [u]:rep_url[/u] [/fg]', true), array('rep_url'=> $url)), false);

		if ($this->_isHttp($url)) {
			if ($this->_exists($url)) {
				$this->formattedOut(__d('plugin', "[b] ... [/b]\n  -> O repositorio ja existe\n", true));
				$this->hr();
				$this->_stop();
			}

			array_push($this->repositories, trim($url));

			if ($this->_save()) {
				$this->formattedOut(__d('plugin', '[bg=green][fg=black]  OK  [/fg][/bg]', true));
			} else {
				$this->formattedOut(__d('plugin', "[bg=red][fg=black] ERRO [/fg][/bg]\n  -> O arquivo nao pode ser acessado", true));
			}
		} else {
			$this->formattedOut(__d('plugin', "[bg=red][fg=black] ERRO [/fg][/bg]\n  -> O parametro a ser adicionado nao parece ser uma URL", true));
		}
	}

	/**
	 * Remove um repositorio do arquivo .reps
	 */
	//TODO: Listar as urls para o usuário selecionar qual deseja remover
	function remove($url = null) {
		$this->formattedOut(String::insert(__d('plugin', '[fg=red]Excluindo[/fg] repositorio [u]:rep_url[/u] [/fg]', true), array('rep_url' =>  $url)), false);

		if (empty($url)) {
			$url = $this->_select();
		}

		if (!$this->_exists($url)) {
			$this->formattedOut(__d('plugin', "[bg=red][fg=black] ERRO [/fg][/bg]\n  -> O repositorio nao existe", true));
			$this->out('');
			$this->hr();
			$this->_stop();
		}

		unset($this->repositories[array_search($url, $this->repositories)]);

		if ($this->_save()) {
			$this->formattedOut(__d('plugin', '[bg=green][fg=black]  OK  [/fg][/bg]', true));
		} else {
			$this->formattedOut(__d('plugin', "[bg=red][fg=black] ERRO [/fg][/bg]\n  -> O arquivo nao pode ser acessado", true));
		}
	}

	/**
	 * Lista todos os plugins de um repositorio
	 */
	function plugins($url = null, $proxy = false) {
		while (empty($url)) {
			$url = $this->_select();
		}
		$this->_show($url, $proxy);
	}

	function find($pattern, $proxy = false) {
		$this->formattedOut(String::insert(__d('plugin', "Buscando plugins: [u]:pattern[/u]\n", true), array('pattern' => $pattern)));

		$plugins = $this->_plugins();
		$found = false;
		foreach($plugins as $plugin) {
			if (preg_match('/.*'. $pattern . '.*/', $plugin['name'])) {
				$out = String::insert(__d('plugin', "[fg=green]    :pluginName", true), array('pluginName'=> $plugin['name']));

				if ($this->Plugins->_exists($plugin['name'])) {
					$out .= __d('plugin', " *[/fg]\n", true);
				} else {
					$out .= String::insert(__d('plugin', "[/fg]\n      :pluginUrl\n", true), array('pluginUrl'=> $plugin['url']));
				}

				$this->formattedOut($out);

				$found = true;
			}
		}

		if (!$found) {
			$this->formattedOut(__d('plugin', "Nao foram encontrados plugins\n", true));
			$this->hr();
			$this->_stop();
		}

		$this->formattedOut(__d('plugin', '* Plugins ja instalados', true));
	}

	/**
	 * Carrega a lista de repositorios do arquivo
	 */
	function _parser() {
		if (!file_exists($this->path)) {
			//TODO: Ao invés de mostrar um erro, cria um novo arquivo com o repositorio padrão
			$errorMessage = String::insert(__d('plugin', " [fg=red]O arquivo de repositorios nao pode ser encontrado!\n O local correto do arquivo e :path [/fg]\n", true), array('path' => $this->path));
			$this->formattedOut($errorMessage);
			$this->hr();
			$this->_stop();
		}

		$fileContent = file_get_contents($this->path);
		$repositories = explode("\n", $fileContent);
		foreach($repositories as $repository) {
			$repository = trim($repository);
			if($this->_isHttp($repository)) {
				$this->repositories[] = $repository;
			}
		}
	}

	/**
	 * Verificar se a url do repositório é uma url válida
	 */
	function _isHttp($url) {
		$pattern = "(http?://([-\w\.]+)+(:\d+)?(/([\w/_\.]*(\?\S+)?)?)?)";
		return preg_match($pattern, $url);
	}

	/**
	 * Localiza uma url na lista de repositorios
	 */
	function _exists($url) {
		if (array_search($url, $this->repositories) === false) {
			return false;
		}
		return true;
	}

	/**
	 * Salva a lista de repositorios no arquivo .reps
	 */
	function _save() {
		$contents = implode("\n", $this->repositories);
		if (file_put_contents($this->path, $contents) === false) {
			return false;
		}

		return true;
	}

	function _select() {
		if (empty($this->repositories)) {
			$this->formattedOut(__d('plugin', "Nao existem repositorios para serem listados\n", true));
			$this->stop();
		}

		$this->formattedOut(__d('plugin', 'Selecione um Repositorio para remover', true));
		$this->out('');

		foreach ($this->repositories as $key => $repository) {
			$this->formattedOut(String::insert(__d('plugin', '[fg=green](:counter)[/fg]  [u]:rep_url[/u]', true), array('counter' => ($key + 1), 'rep_url' => $repository)));
		}

		$options = range(1, count($this->repositories));
		$selected = $this->in(__d('plugin', '(q) para sair', true));
		
		if ($selected == 'q') {
			$this->_stop();
		}
		
		$selected = intval($selected) - 1;
		
		if (isset($this->repositories[$selected])) {
			return $this->repositories[$selected];
		}
		return false;
	}

	/**
	 * Mostra o conteúdo de um repositório
	 */
	function _show($url, $proxy = false) {
		$this->formattedOut(String::insert(__d('plugin', "Listando plugins disponiveis em [u]:rep_url[/u]\n", true), array('rep_url' => $url)));

		$plugins = $this->_plugins($url, $proxy);
		foreach ($plugins as $key => $plugin) {
			$out = String::insert(__d('plugin', "[fg=green]    :pluginName", true), array('pluginName'=> $plugin['name']));

			if ($this->Plugins->_exists($plugin)) {
				$out .= __d('plugin', " *[/fg]\n", true);
			} else {
				$out .= String::insert(__d('plugin', "[/fg]\n      :pluginUrl\n", true), array('pluginUrl' => $plugin['url']));
			}

			$this->formattedOut($out);
		}

		if (empty($pluginList[2])) {
			$this->formattedOut(__d('plugin', 'Nao foram encontrados plugins no repositorio', true));
		}
	}

	/**
	 * Retorna um array com os plugins do repositorio
	 */
	function _plugins($url = null, $proxy = false) {
		if (empty($url)) {
			$url = $this->repositories;
		}
		if (is_array($url)) {
			$repositories = $url;
			$allPlugins = array();
			foreach ($repositories as $repository) {
				$plugins = $this->_plugins($repository, $proxy);
				$allPlugins = array_merge($allPlugins, $plugins);
			}
			return $allPlugins;
		}
		
		$content = $this->_html($url, $proxy); 

		if ($content['erro']) {
			$this->formattedOut(String::insert(__d('plugin', "\n[fg=black][bg=red] ERRO [/bg][/fg] :erro\n       Se voce usa proxy para se conectar a internet, tente usar a opcao\n       \"-proxy username:password@endereco.do.proxy:porta\"\n", true), array('erro' =>  $content['text'])));

			$this->hr();
			$this->_stop();
		}

		$this->_validateHttpErrors($content['text']);

		$plugins = array();
		$text = html_entity_decode($content['text']);
		preg_match_all('/\<li\>\<a href="(?P<url>.*)"\>(?P<name>.*)\<\/a\>\<\/li\>/i', $text, $plugins, PREG_SET_ORDER);

		return $plugins;
	}

	/**
	 * Pega o conteúdo html do repositorio usando o Curl
	 */
	function _html($url, $proxy = false) {
		if (!function_exists('curl_init')) {
			$this->formattedOut(__d('plugin', "\nA biblioteca [fg=black][bg=red]PHP CURL[/bg][/fg] nao esta habilitada.\nDescomente a linha com o conteudo\n[fg=red]  - [u]extension=php_curl.so[/u][/fg] ou\n[fg=red]  - [u]extension=php_curl.dll[/u][/fg]\nno php.ini\n", true));
			$this->hr();
			$this->_stop();
		}

		$options = array(
			CURLOPT_URL                  => $url,
			CURLOPT_PROXY                => $proxy,
			CURLOPT_TIMEOUT              => 10,
			CURLOPT_HEADER               => true,
			CURLOPT_MAXREDIRS            => 10,
			CURLOPT_FOLLOWLOCATION       => true,
			CURLOPT_RETURNTRANSFER       => true,
			CURLOPT_FRESH_CONNECT        => true,
			CURLOPT_HTTPHEADER           => array("Pragma: "),
			CURLOPT_DNS_USE_GLOBAL_CACHE => false,
			CURLOPT_DNS_CACHE_TIMEOUT    => 1,
			CURLOPT_ENCODING             => 'deflate'
		);

		$cu = curl_init();
		curl_setopt_array($cu, $options);

		$content['text'] = curl_exec($cu);
		if ($content['erro'] = curl_errno($cu)) {
			$content['text'] = curl_error($cu);
		}

		curl_close($cu);

		return $content;
	}

	/**
	 * Finaliza a execução do script em caso de erro no HTTP
	 */
	function _validateHttpErrors($text) {
		if (!preg_match("/HTTP.* [2][0][0-6]/i", $text)) {
			$error = array();
			preg_match_all("/\<title\>(.*)\<\/title\>/i", $text, $error);

			$this->formattedOut(String::insert(__d('plugin', "\n[fg=black][bg=red] ERRO: :erro [/bg][/fg]\n", true), array('erro' =>  $error[1][0])));
			$this->hr();
			$this->_stop();
		}
	}
}
?>