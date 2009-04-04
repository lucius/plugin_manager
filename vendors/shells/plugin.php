<?php

    if( !App::import( 'Plugins', 'ImprovedCakeShell.ImprovedCakeShell' ) )
    {
        App::import( 'Vendors', 'InstallImprovedCakeShell.InstallImprovedCakeShell' );

        $installICS =& ClassRegistry::init( 'InstallImprovedCakeShell' );

        $installICS->install( );
    }

    class PluginShell extends ImprovedCakeShell 
    {
        var $proxy = false;

        function _initialize( )
        {
            Configure::write( 'localePaths', am(Configure::getInstance()->localePaths,dirname(dirname(dirname(__FILE__))).DS.'locale'.DS) );
            if( !defined('DEFAULT_LANGUAGE') )
            {
                define('DEFAULT_LANGUAGE', 'eng');
            }

            if( !empty($this->params['proxy']) )
            {
                $this->proxy = $this->params['proxy'];
            }
        }

        function main()
        {
            $this->_initialize( );

            if( empty($this->args) )
            {
                $this->formattedOut( __d('plugin', 'Voce precisa especifiar o que deseja fazer...', true) );

                $this->_listaOpcoesDisponiveis( );

                $this->out( '' );
                $this->hr( );
                exit;
            }

            switch( $this->args[0] )
            {
                case 'add-rep':
                    $this->_addRep( $this->args[1] );
                    break;
                case 'rem-rep':
                    $this->_remRep( $this->args[1] );
                    break;
                case 'list-rep':
                    ( isset($this->args[1]) ) ? $this->_listRep( $this->args[1] ) : $this->_listRep( );
                    break;
                case 'find':
                    $this->_find( $this->args[1] );
                    break;
                case 'list':
                    $this->_list( );
                    break;
                case 'install':
                    $this->_install( $this->args[1] );
                    break;
                case 'uninstall':
                    $this->_uninstall( $this->args[1] );
                    break;
                case 'update':
                    $this->_update( $this->args[1] );
                    break;
                default:
                    $this->formattedOut( __d('plugin', '[bg=red][fg=white] OPCAO INVALIDA [/fg][/bg]', true) );
                    $this->out( '' );
                    $this->_listaOpcoesDisponiveis( );
                    break;
            }
            $this->hr( );
        }

        function _listaOpcoesDisponiveis( )
        {
            $this->formattedOut( __d('plugin', "REP_OPTIONS", true), false );
            $this->formattedOut( __d('plugin', "PLUGIN_OPTIONS", true) );
        }

        function _importResource( $_resource, $_constructorParams )
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
            $repositoriesManager = $this->_importResource( 'RepositoriesManager', array( 'mainShell' => $this ) );
            $repositoriesManager->add( $url );
        }

        function _remRep( $url )
        {
            $repositoriesManager = $this->_importResource( 'RepositoriesManager', array( 'mainShell' => $this ) );
            $repositoriesManager->remove( $url );
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
                $this->formattedOut( __d('plugin', '[bg=red][fg=black] ERRO [/fg][/bg] Opcao Invalida', true) );

                $this->out( '' );
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
            $repositoriesManager = $this->_importResource( 'RepositoriesManager', array( 'mainShell' => $this ) );

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
            $pluginsManager = $this->_importResource( 'PluginsManager', array( 'mainShell' => $this ) );

            $pluginsManager->find( $pluginName );
        }

        function _list( )
        {
            $pluginsManager = $this->_importResource( 'PluginsManager', array( 'mainShell' => $this ) );

            $pluginsManager->listInstalledPlugins( );
        }

        function _install( $nameOrUrl )
        {
            $pluginsManager = $this->_importResource( 'PluginsManager', array( 'mainShell' => $this ) );

            $pluginsManager->installPlugin( $nameOrUrl );
        }

        function _uninstall( $pluginName )
        {
        }

        function _update( $pluginName )
        {
        }

    }

?>
