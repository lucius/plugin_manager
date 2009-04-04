<?php

    class InstallerPM
    {
        var deps = null;

        var shell = null;

        function __construct( $_mainShell )
        {
            $this->shell = $_mainShell;

            $this->startup( );
            $this->_installDeps( );
        }

        function _installDeps( )
        {
            if( !App::import( 'Vendors', 'PluginManager.'.$_resource ) )
            {
                $this->formattedOut( String::insert(__d('plugin', "Impossivel carregar [fg=red][u]PluginsManager[/u][/fg]\n", true) );
                exit;
            }

            $this->shell->formattedOut( __d('plugin', '      -> Verificando a existencia de dependencias...', true), false );

            if( empty($this->deps) )
            {
                $this->shell->formattedOut( __d('plugin', '[fg=black][bg=green]  OK  [/bg][/fg]') );
                exit;
            }

            foreach( $this->deps as $name => $url )
            {
                PluginsManager::installDep( $name, $url );
            }
        }

        function startup( )
        {
        }
    }

?>
