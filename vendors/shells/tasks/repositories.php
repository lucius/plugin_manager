<?php
App::import('Plugins', 'ImprovedCakeShell.ImprovedCakeShell');

class RepositoriesTask extends ImprovedCakeShell {
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
		$this->formattedOut(String::insert(__d('plugin', 'Inserindo repositorio [u]:rep_url[/u] [/fg]', true), array('rep_url'=> $url)), false );

		if ($this->_isHttp($url)) {
			if ($this->_find($url)) {
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
	 * Carrega a lista de repositorios do arquivo
	 */
	function _parser() {
		if (!file_exists($this->path)) {
			//TODO: Ao invés de mostrar um erro, cria um novo arquivo com o repositorio padrão
			$errorMessage = String::insert(__d('plugin', " [fg=red]O arquivo de repositorios nao pode ser encontrado!\n O local correto do arquivo e :path [/fg]\n", true), array('path' => $this->path));
			$this->formattedOut( $errorMessage );
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
	function _find($url) {
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
}
?>