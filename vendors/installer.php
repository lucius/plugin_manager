<?php

    class InstallerPM
    {
        var deps = null;

        var shell = null;

        function __construct( $_mainShell )
        {
            $this->shell = $_mainShell;

            $this->_installDeps( );

            $this->startup( );
        }

        function _installDeps( )
        {
            $this->shell->formattedOut( __d('plugin', 'Verificando a existencia de dependencias...') );

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
