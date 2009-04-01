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
                $this->mainShell->formattedOut( __d('plugin', "[bg=red][fg=black] FAIL : GIT nao suportado [/fg][/bg]\n", true) );
                exit;
            }
        }

        function _dotGitPathExists( )
        {
            return file_exists( APP.'.git/' );
        }

        function _clone( $_url, $_pluginName )
        {
            shell_exec( 'git clone '.$_url.' '.APP.'plugins/'.$_pluginName );

            //remover .git
            return true;
        }

        function _submodule( $_url, $_pluginName  )
        {
            shell_exec( 'git submodule add '.$_url.' plugins/'.$_pluginName );

            return true;
        }

        function install( $_url, $_pluginName )
        {
            if( $this->_dotGitPathExists() )
            {
                $this->mainShell->formattedOut( __d('plugin', '  -> adicionando novo submodulo... ', true), false );

                if( !$this->_submodule($_url, $_pluginName) )
                {
                    $this->mainShell->formattedOut( __d('plugin', '[fg=black][bg=red] FAIL [/bg][/fg]', true) );
                    return false;
                }

                $this->mainShell->formattedOut( __d('plugin', '[fg=black][bg=green]  OK  [/bg][/fg]', true) );
            }
            else
            {
                $this->mainShell->formattedOut( __d('plugin', '  -> clonando repositorio... ', true), false );

                if( !$this->_clone($_url, $_pluginName) )
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
