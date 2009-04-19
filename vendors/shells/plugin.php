<?php

    if( !App::import( 'Plugins', 'ImprovedCakeShell.ImprovedCakeShell' ) )
    {
        App::import( 'Vendors', 'InstallImprovedCakeShell.InstallImprovedCakeShell' );

        $installICS =& ClassRegistry::init( 'InstallImprovedCakeShell' );

        $installICS->install();
    }

    class PluginShell extends ImprovedCakeShell 
    {
	 	var $tasks = array('Repositories', 'Plugins');
        var $proxy = false;

        function main()
        {
            $this->_initialize( );

            if( empty($this->args) )
            {
                $this->formattedOut( __d('plugin', 'Voce precisa especifiar o que deseja fazer...', true) );

                $this->_listAvaliableOptions( );

                $this->out( "\n", false );
                $this->hr( );
                exit;
            }

            switch( $this->args[0] )
            {
                case 'add-rep':
                    ( isset($this->args[1]) ) ? $this->_addRep( $this->args[1] ) : $this->_missingParameter( );
                    break;
                case 'rem-rep':
                    $this->_remRep(( isset($this->args[1]) ) ? $this->_remRep( $this->args[1] ) : null);
                    break;
                case 'list-rep':
                    ( isset($this->args[1]) ) ? $this->_listRep( $this->args[1] ) : $this->_listRep( );
                    break;
                case 'find':
                    ( isset($this->args[1]) ) ? $this->_find( $this->args[1] ) : $this->_missingParameter( );
                    break;
                case 'list':
                    $this->_list( );
                    break;
                case 'install':
                    ( isset($this->args[1]) ) ? $this->_install( $this->args[1], @$this->args[2] ) : $this->_missingParameter( );
                    break;
                case 'uninstall':
                    ( isset($this->args[1]) ) ? $this->_uninstall( $this->args[1] ) : $this->_missingParameter( );
                    break;
                case 'update':
                    ( isset($this->args[1]) ) ? $this->_update( $this->args[1] ) : $this->_missingParameter( );
                    break;
                default:
                    $this->formattedOut( __d('plugin', "\n[bg=red][fg=white] OPCAO INVALIDA [/fg][/bg]\n", true) );
                    $this->_listAvaliableOptions( );
                    break;
            }
            $this->hr( );
        }


        function _initialize( )
        {
            Configure::write( 'localePaths', am(Configure::getInstance()->localePaths,dirname(dirname(dirname(__FILE__))).DS.'locale'.DS) );
            Configure::write( 'Cache.disable', true );

            if( !defined('DEFAULT_LANGUAGE') )
            {
                define('DEFAULT_LANGUAGE', 'pt-br');
            }

            if( !empty($this->params['proxy']) )
            {
                $this->proxy = $this->params['proxy'];
            }
        }

        function _missingParameter( )
        {
            $this->formattedOut( __d('plugin', '[bg=red][fg=black] ERRO [/fg][/bg] E necessario um paramentro para executar esta opcao', true) );
        }

        function _listAvaliableOptions( )
        {
            $this->formattedOut( __d('plugin', "REP_OPTIONS", true), false );
            $this->formattedOut( __d('plugin', "PLUGIN_OPTIONS", true) );
        }

        function _importPluginResource( $_resource, $_constructorParams )
        {
            if( !App::import( 'Vendors', 'PluginManager.'.$_resource ) )
            {
                $this->formattedOut( String::insert(__d('plugin', "Impossivel carregar [fg=red][u]:resource[/u][/fg]\n", true), array('resource'=>$_resource)) );

                $this->hr( );
                exit;
            }

            $className = $_resource.'PM';
            return new $className( $_constructorParams );
        } 

        function _addRep( $url )
        {
			$this->Repositories->add($url);
        }

        function _remRep( $url )
        {
			$this->Repositories->remove($url);
        }

        function _selectRepositorie( $_repositories )
        {
            $this->formattedOut( __d('plugin', 'Selecione um Repositorio para listar', true) );
            $this->out( '' );

            $this->_listFoundRepositories( $_repositories );
            $options = range(1, count($_repositories));
            $rep = $this->in( '' );

            $this->out( '' );
            if( in_array($rep, $options) )
            {
                $flipped = array_flip($options);
                $url = $_repositories[$flipped[$rep]];
            }
            else
            {
                $this->formattedOut( __d('plugin', "[bg=red][fg=black] ERRO [/fg][/bg] Opcao Invalida\n", true) );
                $this->hr( );

                exit;
            }

            return $url;
        }

        function _listFoundRepositories( $_repositories )
        {
            $counter = 1;

            foreach( $_repositories as $repositorie )
            {
                $this->formattedOut( String::insert(__d('plugin', '[fg=green](:counter)[/fg]  [u]:rep_url[/u]', true), array('counter' => $counter++, 'rep_url' => $repositorie)) );
            }
        }

        function _listRep( $url = null )
        {
            $repositoriesManager = $this->_importPluginResource( 'RepositoriesManager', array( 'mainShell' => $this ) );

            if( empty($url) )
            {
                $repositories = $repositoriesManager->get( );
                if( !count($repositories) )
                {
                    $this->formattedOut( __d('plugin', "Nao existem repositorios para serem listados\n", true) );
                    exit;
                }

                $url = $this->_selectRepositorie( $repositories );
            }

            $repositoriesManager->showRepositorieContent( $url, $this->proxy );
        }

        function _find( $pluginName )
        {
            $pluginsManager = $this->_importPluginResource( 'PluginsManager', array( 'mainShell' => $this ) );

            $pluginsManager->find( $pluginName );
        }

        function _list( )
        {
			$this->Plugins->listAll();
        }

        function _install( $nameOrUrl, $name = null )
        {
			$this->Plugins->install($nameOrUrl, $name);
        }

        function _uninstall( $pluginName )
        {
			$this->Plugins->uninstall($pluginName);
        }

        function _update( $pluginName )
        {
			$this->Plugins->update($pluginName);
        }

    }

?>
