<?php

    class RepositoriesManagerPM
    {
        var $mainShell = null;

        var $repositories = array( );

        var $repsPath = '';

        function __construct( $_mainShell )
        {
            $this->mainShell = $_mainShell;

            $this->repsPath = APP."plugins/plugin_manager/.reps";
            $this->_parser( );
        }

        function add( $url )
        {
            $pattern = "(http?://([-\w\.]+)+(:\d+)?(/([\w/_\.]*(\?\S+)?)?)?)";

            $this->mainShell->formattedOut( String::insert(__d('plugin', 'Inserindo repositorio [u]:rep_url[/u] [/fg]', true), array('rep_url'=> $url)), false );
            
            if( preg_match($pattern, $url) )
            {
                array_push( $this->repositories, trim($url) );

                $contents = implode("\n", $this->repositories);
                if( file_put_contents($this->repsPath, $contents) )
                {
                    $this->mainShell->formattedOUt( __d('plugin', '[bg=green][fg=black]  OK  [/fg][/bg]', true) );
                }
                else
                {
                    $this->mainShell->formattedOUt( __d('plugin', '[bg=red][fg=black] FAIL [/fg][/bg]', true) );
                    $this->mainShell->formattedOUt( __d('plugin', '  -> O arquivo nao pode ser acessado', true) );
                }
            }
            else
            {
                $this->mainShell->formattedOUt( __d('plugin', '[bg=red][fg=black] FAIL [/fg][/bg]', true) );
                $this->mainShell->formattedOUt( __d('plugin', '  -> O parametro a ser adicionado nao parece ser uma URL', true) );
            }
        }

        function _parser( )
        {
            $pattern = "(http?://([-\w\.]+)+(:\d+)?(/([\w/_\.]*(\?\S+)?)?)?)";

            if( ! file_exists($this->repsPath) )
            {
                $this->mainShell->formattedOut( __d('plugin', ' [fg=red]O arquivo de repositorios nao pode ser encontrado![/fg]', true) );
                $this->mainShell->formattedOut( String::insert(__d('plugin', ' [fg=red]O local correto do arquivo e :repsPath [/fg]', true), array('repsPath'=> $this->repsPath)) );
                exit;
            }
            $fileContent = file_get_contents( $this->repsPath );
            $fileContent = explode( "\n", $fileContent );
                
            foreach( $fileContent as $repositorie )
            {
                if( preg_match($pattern, $repositorie) )
                {
                    array_push( $this->repositories, trim($repositorie) );
                }
            }

        }
    }

?>
