<?php

    class SvnHandlerPM
    {
        var $mainShell;

        function __construct( $_params )
        {
            $this->mainShell = $_params['mainShell'];

            $this->_isSvnSupported( );
        }

        function _isSvnSupported( )
        {
            if( !shell_exec('svn --version 2>/dev/null') )
            {
                $this->mainShell->formattedOut( __d('plugin', "\n[bg=red][fg=black] FAIL : SVN nao suportado [/fg][/bg]\n", true) );
                exit;
            }
        }

        function _dotSvnPathExists( )
        {
            return file_exists( APP.'.svn/' );
        }

        function _export( $_url, $_pluginName )
        {
            shell_exec( 'svn export '.$_url.' '.APP.'plugins'.DS.$_pluginName );
            return true;
        }

        function _getExternals( )
        {
            return trim(shell_exec('svn propget svn:externals .') );
        }

        function _externals( $_url, $_pluginName  )
        {
            $this->mainShell->out( '' );

            $externals = $this->_getExternals( );
            $externals .= "\n"."plugins".DS."$_pluginName $_url";

            if( file_put_contents('.externals-tmp', $externals) !== false )
            {
                shell_exec( 'svn propset -q svn:externals . -F .externals-tmp' );
                shell_exec( 'svn update' );
            }

            return true;
        }

        function install( $_url, $_pluginName )
        {
            if( $this->_dotSvnPathExists() )
            {
                $this->mainShell->formattedOut( __d('plugin', '  -> adicionando external... ', true), false );

                if( !$this->_externals($_url, $_pluginName) )
                {
                    $this->mainShell->formattedOut( __d('plugin', '[fg=black][bg=red] FAIL [/bg][/fg]', true) );
                    return false;
                }

                $this->mainShell->formattedOut( __d('plugin', '[fg=black][bg=green]  OK  [/bg][/fg]', true) );
            }
            else
            {
                $this->mainShell->formattedOut( __d('plugin', '  -> importando repositorio... ', true), false );

                if( !$this->_export($_url, $_pluginName) )
                {
                    $this->mainShell->formattedOut( __d('plugin', '[fg=black][bg=red] FAIL [/bg][/fg]', true) );
                    return false;
                }

                $this->mainShell->formattedOut( __d('plugin', '[fg=black][bg=green]  OK  [/bg][/fg]', true) );
            }

            return true;
        }
 
    }

?>
