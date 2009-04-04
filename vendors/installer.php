<?php

    class InstallerPM
    {
        var $deps = null;

        var $shell = null;

        function __construct( $_mainShell )
        {
            $this->shell = $_mainShell;

            $this->startup( );
            $this->_installDeps( );
        }

        function _installDeps( )
        {
            if( !App::import( 'Vendors', 'PluginManager.PluginsManager' ) )
            {
                $this->shell->formattedOut( __d('plugin', "Impossivel carregar [fg=red][u]PluginsManager[/u][/fg]\n", true) );
                exit;
            }

            $this->shell->formattedOut( __d('plugin', '      -> Verificando a existencia de dependencias...', true), false );

            if( empty($this->deps) )
            {
                $this->shell->formattedOut( __d('plugin', '[fg=black][bg=green]  OK  [/bg][/fg]'), false );
                exit;
            }

            $this->shell->out( "\n", false );

            $pm = new PluginsManagerPM( array('mainShell' => $this->shell) );
            foreach( $this->deps as $name => $url )
            {
                if( $pm->installDep( $name, $url ) )
                {
                    $this->shell->formattedOut( String::insert(__d('plugin', "    [fg=green][u]:plugin[/u][/fg] instalado com sucesso!\n", true), array('plugin'=>$name)) );
                }
                else
                {
                    $this->shell->formattedOut( String::insert(__d('plugin', "    Nao foi possivel instalar [fg=red][u]:plugin[/u][/fg]\n", true), array('plugin'=>$name)) );
                }
            }
        }

        function startup( )
        {
        }
    }

?>
