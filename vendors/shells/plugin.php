<?php

    if( !App::import( 'Plugins', 'ImprovedCakeShell.ImprovedCakeShell' ) )
    {
        App::import( 'Vendors', 'InstallImprovedCakeShell.InstallImprovedCakeShell' );

        $installICS =& ClassRegistry::init( 'InstallImprovedCakeShell' );

        $installICS->install( );
    }

    class PluginShell extends ImprovedCakeShell 
    {
        function main()
        {
            if( empty($this->args) )
            {
                $this->formattedOut( __d('plugin', 'Voce precisa especifiar o que deseja fazer...', true) );
                $this->out( '' );

                $this->_listaOpcoesDisponiveis( );

                exit;
            }

            switch( $this->args[0] )
            {
                case 'add-rep':
                    $this->_addrep( $this->args[1] );
                    break;
                case 'rem-rep':
                    $this->_remrep( $this->args[1] );
                    break;
                case 'list-rep':
//                    $this->_listrep( $this->args[1]);
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

            $this->formattedOut( __d('plugin', '  [fg=yellow]find[/fg] [fg=green]nome_do_plugin[/fg]', true) );
            $this->formattedOut( __d('plugin', '    Busca um plugin na lista de repositorios disponiveis', true) );
            $this->out( '' );
            
            $this->formattedOut( __d('plugin', '  [fg=yellow]list[/fg]', true) );
            $this->formattedOut( __d('plugin', '    Lista os plugins instalados atualmente', true) );
            $this->out( '' );

            $this->formattedOut( __d('plugin', '  [fg=yellow]install[/fg] [fg=green]url_plugin[/fg]', true) );
            $this->formattedOut( __d('plugin', '    Instala o plugin especificado na url, executando o script', true) );
            $this->formattedOut( __d('plugin', '    de instalacao, se existir', true) );
            $this->out( '' );

            $this->formattedOut( __d('plugin', '  [fg=yellow]uninstall[/fg] [fg=green]nome_plugin[/fg]', true) );
            $this->formattedOut( __d('plugin', '    Remove o plugin especificado, executando o script de desinstalacao', true) );
            $this->formattedOut( __d('plugin', '    se existir', true) );
            $this->out( '' );

            $this->formattedOut( __d('plugin', '  [fg=yellow]update[/fg] [fg=green]nome_plugin[/fg]', true) );
            $this->formattedOut( __d('plugin', '    Verifica se existem atualizacoes disponiveis para o plugin especificado', true) );
            $this->formattedOut( __d('plugin', '    e as instala', true) );
            $this->out( '' );
        }
        
        function _addrep( $url )
        {
        }

        function _remrep( $url )
        {
        }

        function _listrep( $url = null )
        {
        }

        function _find( $plugin_name )
        {
        }

        function _list( )
        {
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
