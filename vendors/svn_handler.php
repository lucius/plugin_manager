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
                $this->mainShell->formattedOut( __d('plugin', "[bg=red][fg=black] FAIL : SVN nao suportado [/fg][/bg]\n", true) );
                exit;
            }
        }

        function _dotSvnPathExists( )
        {
            return file_exists( APP.'.svn/' );
        }

        function _export( $_url, $_pluginName )
        {
            $this->mainShell->out( $_url );
            shell_exec( 'svn export '.$_url.' '.APP.'plugins/'.$_pluginName );
        }

/*        function _externals( $_url, $_pluginName  )
        {
            $this->mainShell->out( '' );
            shell_exec( 'svn propset -q svn:externals -F ' );
        }*/

        function install( $_url, $_pluginName )
        {
            if( $this->_dotSvnPathExists() )
            {
//                $this->_externals( $_url, $_pluginName );
            }
            else
            {
                $this->_export( $_url, $_pluginName );
            }
        }
 
    }

?>
