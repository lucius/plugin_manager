<?php

    class GitHandlerPM
    {
        var $mainShell;

        function __construct( $_params )
        {
            $this->mainShell = $_params['mainShell'];

            $this->_isGitSupported( );
        }

        function _isGitSupported( )
        {
            if( !shell_exec('git --version 2>/dev/null') )
            {
                $this->mainShell->formattedOut( __d('plugin', "[bg=red][fg=black] FAIL : Protocolo GIT nao suportado [/fg][/bg]\n", true) );
                exit;
            }
        }

        function _dotGitPathExists( )
        {
            return file_exists( APP.'.git/' );
        }

        function _clone( $_url, $_pluginName )
        {
            $this->mainShell->out( '' );
            shell_exec( 'git clone '.$_url.' '.APP.'plugins/'.$_pluginName );

            //remover .git
        }

        function _submodule( $_url, $_pluginName  )
        {
            $this->mainShell->out( '' );
            shell_exec( 'git submodule add '.$_url.' plugins/'.$_pluginName );
        }

        function install( $_url, $_pluginName )
        {
            if( $this->_dotGitPathExists() )
            {
                $this->_submodule( $_url, $_pluginName );
            }
            else
            {
                $this->_clone( $_url, $_pluginName );
            }
        }
    }

?>
