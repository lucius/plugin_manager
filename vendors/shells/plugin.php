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
                $this->out( '' );

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
            $this->formattedOut( __d('plugin', 'Opcoes disponiveis:', true) );
            $this->out( '' );

            $this->formattedOut( __d('plugin', '  [fg=yellow]add-rep[/fg] [fg=green]url_repositorio[/fg]', true) );
            $this->formattedOut( __d('plugin', '    Adiciona um repositorio de busca', true) );
            $this->out( '' );

            $this->formattedOut( __d('plugin', '  [fg=yellow]rem-rep[/fg] [fg=green]url_repositorio[/fg]', true) );
            $this->formattedOut( __d('plugin', '    Remove um repositorio de busca', true) );
            $this->out( '' );

            $this->formattedOut( __d('plugin', '  [fg=yellow]list-rep[/fg]', true) );
            $this->formattedOut( __d('plugin', '    Lista os repositorios disponiveis', true) );
            $this->out( '' );

            $this->formattedOut( __d('plugin', '  [fg=yellow]list-rep[/fg] [fg=green]url_repositorio[/fg]', true) );
            $this->formattedOut( __d('plugin', '    Lista os plugins disponiveis no repositorio especificado', true) );
            $this->out( '' );

            $this->formattedOut( __d('plugin', '  [fg=yellow]find[/fg] [fg=green]nome_do_plugin[/fg] [fg=red](Indisponivel)[/fg]', true) );
            $this->formattedOut( __d('plugin', '    Busca um plugin na lista de repositorios disponiveis', true) );
            $this->out( '' );
            
            $this->formattedOut( __d('plugin', '  [fg=yellow]list[/fg]', true) );
            $this->formattedOut( __d('plugin', '    Lista os plugins instalados atualmente', true) );
            $this->out( '' );

            $this->formattedOut( __d('plugin', '  [fg=yellow]install[/fg] [fg=green]url_plugin[/fg] [fg=red](Indisponivel)[/fg]', true) );
            $this->formattedOut( __d('plugin', '    Instala o plugin especificado na url, executando o script', true) );
            $this->formattedOut( __d('plugin', '    de instalacao, se existir', true) );
            $this->out( '' );

            $this->formattedOut( __d('plugin', '  [fg=yellow]uninstall[/fg] [fg=green]nome_plugin[/fg] [fg=red](Indisponivel)[/fg]', true) );
            $this->formattedOut( __d('plugin', '    Remove o plugin especificado, executando o script de', true) );
            $this->formattedOut( __d('plugin', '    desinstalacao se existir', true) );
            $this->out( '' );

            $this->formattedOut( __d('plugin', '  [fg=yellow]update[/fg] [fg=green]nome_plugin[/fg] [fg=red](Indisponivel)[/fg]', true) );
            $this->formattedOut( __d('plugin', '    Verifica se existem atualizacoes disponiveis para o plugin', true) );
            $this->formattedOut( __d('plugin', '    especificado e as instala', true) );
            $this->out( '' );
            
            $this->formattedOut( __d('plugin', '  [fg=yellow]-proxy[/fg] [fg=green]username:password@endereco.do.proxy:porta[/fg]', true) );
            $this->formattedOut( __d('plugin', '    Utiliza as configuracoes do proxy para realizar as operacoes', true) );
            $this->formattedOut( __d('plugin', '    desejadas', true) );
            $this->out( '' );
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
                $this->formattedOut( __d('plugin', '[bg=red][fg=black] FAIL [/fg][/bg] Opcao Invalida', true) );

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

        function _find( $plugin_name )
        {
        }

        function _list( )
        {
            $pluginsManager = $this->_importResource( 'PluginsManager', array( 'mainShell' => $this ) );

            $pluginsManager->listInstalledPlugins( );
        }

        function _install( $url )
        {
        }

        function _uninstall( $plugin_name )
        {
        }

        function _update( $plugin_name )
        {
        }

    }

?>
