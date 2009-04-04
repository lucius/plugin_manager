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
                $this->mainShell->formattedOut( __d('plugin', "[bg=red][fg=black] ERRO : GIT nao suportado [/fg][/bg]\n", true) );
                exit;
            }
        }

        function _dotGitPathExists( )
        {
            return file_exists( APP.'.git/' );
        }

        function _clone( $_url, $_pluginName )
        {
            $pluginPath = APP.'plugins/'.$_pluginName;
            $return = shell_exec( 'git clone '.$_url.' '.$pluginPath.' 2>&1' );

            $pattern = "/.*fatal.*/i";
            $found;
            
            if( !preg_match_all( $pattern, $return, $found) )
            {
                $this->_excludeGitFolder( $pluginPath );
            }

            return $found[0];
        }

        function _excludeGitFolder( $_pluginPath )
        {
            if( !App::import('Folder') )
            {
                $this->out( __d('plugin', "Impossivel caregar 'Folder'") );
            }

            $gitFolder = $_pluginPath.'/.git/';
            $folder = new Folder();
            $folder->delete( $gitFolder );
        }
 
        function _submodule( $_url, $_pluginName  )
        {
            $return = shell_exec( 'git submodule add '.$_url.' plugins/'.$_pluginName.' 2>&1' );

            $pattern = "/.*fatal.*/i";
            $found = array();
            preg_match_all( $pattern, $return, $found);

            return $found[0];
        }

        function install( $_url, $_pluginName )
        {
            $return = true;

            if( $this->_dotGitPathExists() )
            {
                $this->mainShell->formattedOut( __d('plugin', '  -> adicionando novo submodulo... ', true), false );

                $errors = $this->_submodule($_url, $_pluginName);
                if( !empty($errors) )
                {
                    $this->mainShell->formattedOut( __d('plugin', '[fg=black][bg=red] ERRO [/bg][/fg]', true) );
                    $return = false;
                }
            }
            else
            {
                $this->mainShell->formattedOut( __d('plugin', '  -> clonando repositorio... ', true), false );

                $errors = $this->_clone($_url, $_pluginName);
                if( !empty($errors) )
                {
                    $this->mainShell->formattedOut( __d('plugin', '[fg=black][bg=red] ERRO [/bg][/fg]', true) );
                    $return = false;
                }
            }

            if( $return )
            {
                $this->mainShell->formattedOut( __d('plugin', '[fg=black][bg=green]  OK  [/bg][/fg]', true) );
            }
            else
            {
                foreach ( $errors as $error )
                {
                    $this->mainShell->formattedOut( "    - $error" );
                }
            }

            return $return;
        }
    }

?>
