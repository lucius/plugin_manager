<?php

    class RepositoriesManagerPM
    {
        var $mainShell = null;

        var $repositories = array( );

        var $repsPath = '';

        var $exclude = '';

        function __construct( $_params )
        {
            $this->mainShell = $_params['mainShell'];

            $this->repsPath = APP."plugins/plugin_manager/.reps";
            $this->_parser( );
        }

        function _parser( )
        {

            if( ! file_exists($this->repsPath) )
            {
                $errorMessage  = __d( 'plugin', ' [fg=red]O arquivo de repositorios nao pode ser encontrado!', true );
                $errorMessage .= String::insert( __d('plugin', "\n O local correto do arquivo e :repsPath [/fg]\n", true), array('repsPath'=> $this->repsPath) );
                
                $this->mainShell->formattedOut( $errorMessage );

                $this->mainShell->hr( );
                exit;
            }
            $fileContent = file_get_contents( $this->repsPath );
            $fileContent = explode( "\n", $fileContent );
                
            foreach( $fileContent as $repositorie )
            {
                if( $this->_isHttp($repositorie) )
                {
                    array_push( $this->repositories, trim($repositorie) );
                }
            }

        }

        function _isHttp( $_url )
        {
            $pattern = "(http?://([-\w\.]+)+(:\d+)?(/([\w/_\.]*(\?\S+)?)?)?)";

            return preg_match( $pattern, $_url );
        }

        function _find( $_url )
        {
            if( array_search( $_url, $this->repositories) === false )
            {
                return false;
            }

            return true;
        }

        function _save( )
        {
            $contents = implode("\n", $this->repositories);
            if( file_put_contents($this->repsPath, $contents) === false )
            {
                return false;
            }

            return true;
        }

        function add( $_url )
        {
            $this->mainShell->formattedOut( String::insert(__d('plugin', 'Inserindo repositorio [u]:rep_url[/u] [/fg]', true), array('rep_url'=> $_url)), false );
            
            if( $this->_isHttp($_url) )
            {
                if( $this->_find($_url) )
                {
                    $this->mainShell->formattedOut( __d('plugin', '[b] ... [/b]', true) );
                    $this->mainShell->formattedOut( __d('plugin', '  -> O repositorio ja existe', true) );

                    $this->mainShell->out( '' );
                    $this->mainShell->hr( );

                    exit;
                }

                array_push( $this->repositories, trim($_url) );

                if( $this->_save() )
                {
                    $this->mainShell->formattedOut( __d('plugin', '[bg=green][fg=black]  OK  [/fg][/bg]', true) );
                }
                else
                {
                    $this->mainShell->formattedOut( __d('plugin', '[bg=red][fg=black] FAIL [/fg][/bg]', true) );
                    $this->mainShell->formattedOut( __d('plugin', '  -> O arquivo nao pode ser acessado', true) );
                }
            }
            else
            {
                $this->mainShell->formattedOut( __d('plugin', '[bg=red][fg=black] FAIL [/fg][/bg]', true) );
                $this->mainShell->formattedOut( __d('plugin', '  -> O parametro a ser adicionado nao parece ser uma URL', true) );
            }
        }

        function remove( $_url )
        {
            $this->mainShell->formattedOut( String::insert(__d('plugin', '[fg=red]Excluindo[/fg] repositorio [u]:rep_url[/u] [/fg]', true), array('rep_url'=> $_url)), false );

            if( !$this->_find($_url) )
            {
                $this->mainShell->formattedOut( __d('plugin', '[bg=red][fg=black] FAIL [/fg][/bg]', true) );
                $this->mainShell->formattedOut( __d('plugin', '  -> O repositorio nao existe', true) );

                $this->mainShell->out( '' );
                $this->mainShell->hr( );
                exit;
            }
            unset( $this->repositories[array_search($_url, $this->repositories)] );

            if( $this->_save() )
            {
                $this->mainShell->formattedOut( __d('plugin', '[bg=green][fg=black]  OK  [/fg][/bg]', true) );
            }
            else
            {
                $this->mainShell->formattedOut( __d('plugin', '[bg=red][fg=black] FAIL [/fg][/bg]', true) );
                $this->mainShell->formattedOut( __d('plugin', '  -> O arquivo nao pode ser acessado', true) );
            }
        }

        function get( )
        {
            return $this->repositories;
        }

        function showRepositorieContent( $_url, $_proxy = false )
        {
            $this->mainShell->formattedOut( String::insert(__d('plugin', 'Listando plugins disponiveis em [u]:rep_url[/u]', true), array('rep_url'=> $_url)) );

            $content = $this->_getUrlContent( $_url, $_proxy ); 

            if( $content['erro'] )
            {
                $this->mainShell->out( '' );
                $this->mainShell->formattedOut( String::insert(__d('plugin', '[fg=black][bg=red] FAIL [/bg][/fg] :erro', true), array('erro'=> $content['text'])) );
                $this->mainShell->formattedOut( __d('plugin', '       Se voce usa proxy para se conectar a internet, tente usar a opcao', true) );
                $this->mainShell->formattedOut( __d('plugin', '       "-proxy username:password@endereco.do.proxy:porta"', true) );

                $this->mainShell->out( '' );
                $this->mainShell->hr( );
                exit;
            }

            $this->_validateHttpErrors( $content['text'] );

            $pluginList = array( );

            $text = html_entity_decode($content['text']);
            preg_match_all( '/\<li\>\<a href="(.*)"\>(.*)\<\/a\>\<\/li\>/i', $text, $pluginList );

            $this->mainShell->out( '' );
            foreach( $pluginList[2] as $key => $pluginTitle )
            {
                $this->mainShell->formattedOut( String::insert(__d('plugin', '[fg=green]    :pluginTitle[/fg] - :url', true), array('pluginTitle'=> $pluginTitle, 'url' => $pluginList[1][$key])) );
            }

            if( !count($pluginList[2]) )
            {
                $this->mainShell->formattedOut( __d('plugin', 'Nao foram encontrados plugins no repositorio', true) );
            }
        }

        function _validateHttpErrors( $_text )
        {
            if( preg_match("/HTTP.* [1345][0-1][0-7]/i", $_text) )
            {
                $error = array();
                preg_match_all("/\<title\>(.*)\<\/title\>/i", $_text, $error);

                $this->mainShell->out( '' );
                $this->mainShell->formattedOut( String::insert(__d('plugin', '[fg=black][bg=red] FAIL: :erro [/bg][/fg]', true), array('erro'=> $error[1][0])) );

                $this->mainShell->out( '' );
                $this->mainShell->hr( );
                exit;
            }
        }

        function _getUrlContent( $_url, $_proxy = false )
        {
            if( !function_exists('curl_init') )
            {
                $errorMessage  =  __d( 'plugin', 'A biblioteca PHP CURL nao esta habilitada. Descomente a linha', true );
                $errorMessage .=  __d( 'plugin', "\nDescomente a linha com o conteudo", true );
                $errorMessage .=  __d( 'plugin', "\n[fg=red]  -[u]extension=php_curl.so[/u][/fg] ou ", true );
                $errorMessage .=  __d( 'plugin', "\n[fg=red]  -[u]extension=php_curl.dll[/u][/fg]", true );
                $errorMessage .=  __d( 'plugin', "\nno php.ini", true );

                $this->mainShell->formattedOut( $errorMessage );

                $this->mainShell->out( '' );
                $this->mainShell->hr( );

                exit;
            };
        
            $cu = curl_init( );

            $options = array(
                CURLOPT_URL => $_url,
                CURLOPT_PROXY => $_proxy,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_HEADER => true,
                CURLOPT_RETURNTRANSFER => true
            );
            
            curl_setopt_array( $cu, $options );

            $content['text'] = curl_exec( $cu );

            if( $content['erro'] = curl_errno($cu) )
            {
                $content['text'] = curl_error( $cu );
            }

            curl_close( $cu ); 

            return $content;
        }

    }

?>
