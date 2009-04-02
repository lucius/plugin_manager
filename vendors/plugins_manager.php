<?php

    class PluginsManagerPM
    {
        var $mainShell;

        var $avaliablePlugins = array( );

        function __construct( $_params )
        {
            $this->mainShell = $_params['mainShell'];
        }

        function _getInstalledPlugins( )
        {
            if( !App::import('Core', 'Folder') )
            {
                $this->mainShell->formattedOut( __d('plugin', "Impossivel carregar [fg=red][u]Core.Folder[/u][/fg]\n", true) );
                exit;
            }

            $pluginsFolder = new Folder( APP.'plugins' );
            $listPluginsFolder = $pluginsFolder->ls( );

            return $listPluginsFolder[0];
        }

        function _getPluginUrl( $_pluginName )
        {
            $url = false;

            $urlPluginPath = APP.'plugins/'.$_pluginName.'/.url';
            if( file_exists($urlPluginPath) )
            {
                $url = file_get_contents( $urlPluginPath );
            }

            return $url;
        }

        function _isInstalled( $_pluginName )
        {
            return file_exists( APP.'plugins/'.$_pluginName );
        }

        function _getlistAvaliablePlugins( )
        {
            if( !App::import('Vendors', 'PluginManager.RepositoriesManager') )
            {
                $this->mainShell->formattedOut( __d('plugin', "Impossivel carregar [fg=red][u]PluginManager.RepositoriesManager[/u][/fg]\n", true) );
                exit;
            }

            $repositoriesManager = new RepositoriesManagerPM( array( 'mainShell' => $this->mainShell ) );

            $repositories = $repositoriesManager->get( ); 

            $return = array( );

            foreach( $repositories as $repositorie )
            {
                $pluginList = $repositoriesManager->getRepositorieContent( $repositorie, $this->mainShell->proxy );

                foreach( $pluginList[2] as $key => $pluginName )
                {
                    $return[] = array(
                                        'name' => $pluginName,
                                        'url'  => $pluginList[1][$key]
                                    );
                }
            }

            return $return;
        }

        function _getMethod( $_url )
        {
            $pattern[] = "/^(git):\/\//";
            $pattern[] = "/\.(git)$/";
            $pattern[] = "/^(svn):\/\//";
            $pattern[] = "/^(http):\/\//";
            $pattern[] = "/^(https):\/\//";
            $pattern[] = "/^(ssh):\/\//";

            $found = array();

            foreach( $pattern as $pat )
            {
                if( preg_match($pat, $_url, $found) )
                {
                    return $found[1];
                }
            }

            return false;
        }

        function _findPluginUrl( $_pluginName )
        {
            $avaliable = $this->_getListAvaliablePlugins( );

            foreach( $avaliable as $plugin )
            {
                if( $plugin['name'] == $_pluginName )
                {
                    return $plugin['url'];
                }
            }

            return false;
        }

        function _doInstall( $_method, $_url, $_pluginName )
        {
            $this->mainShell->formattedOut( __d('plugin', '  -> selecionando modo de instalacao: ', true), false );

            switch( $_method )
            {
                case 'git':
                    $status = $this->_installUsingGit( $_url, $_pluginName );
                    break;
                case 'svn':
                    $status = $this->_installUsingSvn( $_url, $_pluginName );
                    break;
                case 'http':
                case 'https':
                    if( !$status = $this->_installUsingSvn($_url, $_pluginName) )
                    {
                        $this->mainShell->formattedOut( __d('plugin', 'Tentando instalar usando ', true), false );
                        $status = $this->_installUsingGit( $_url, $_pluginName );
                    }
                     break;
                case 'ssh':
                    if( !$status = $this->_installUsingGit($_url, $_pluginName) )
                    {
                        $this->mainShell->formattedOut( __d('plugin', 'Tentando instalar usando ', true), false );
                        $status = $this->_installUsingSvn( $_url, $_pluginName );
                    }
                    break;
            }

            return $status;
        }

        function _installUsingSvn( $_url, $_pluginName )
        {
            $this->mainShell->formattedOut( __d('plugin', '[fg=yellow][u]SVN[/u][/fg]', true) );

            if( !App::import('Vendors', 'PluginManager.SvnHandler') )
            {
                $this->formattedOut( __d('plugin', "Impossivel carregar [fg=red][u]SvnHandler[/u][/fg]\n", true) );
                exit;
            }

            $params = array( 'mainShell' => $this->mainShell );
            $svnHandler = new SvnHandlerPM( $params );

            $status = $svnHandler->install( $_url, $_pluginName );

            return $status;
        }


        function _installUsingGit( $_url, $_pluginName )
        {
            $this->mainShell->formattedOut( __d('plugin', '[fg=yellow][u]GIT[/u][/fg]', true) );

            if( !App::import('Vendors', 'PluginManager.GitHandler') )
            {
                $this->formattedOut( __d('plugin', "Impossivel carregar [fg=red][u]GitHandler[/u][/fg]\n", true) );
                exit;
            }

            $params = array( 'mainShell' => $this->mainShell );
            $gitHandler = new GitHandlerPM( $params );

            $status = $gitHandler->install( $_url, $_pluginName );

            return $status;
        }

        function _saveUrlFile( $_method, $_url, $_pluginName)
        {
            $filePath = APP."plugins/$_pluginName/.url-$_method";

            if( file_put_contents($filePath, $_url) === false )
            {
                $this->mainShell->formattedOut( __d('plugin',
"  -> [fg=black][bg=black] ERRO [/bg][/fg] salvando .url file
     nao sera possivel realizar a atualizacao do plugin atraves
     do plugin_manager.", true) );
            }
        }

        function _runInstallHook( $_pluginName )
        {
            $this->mainShell->formattedOut( __d('plugin', "  -> verificando a existencia do hook de instalacao...", true) );

            if( file_exists(APP.'plugins/'.$_pluginName.'/vendors/shells/'.$_pluginName.'_installer.php') )
            {
                $this->mainShell->formattedOut( __d('plugin', "\n    - carregando... ", true), false );
                $className = Inflector::camelize($_pluginName.'_installer');
                if( !App::import('Vendors', $className) )
                {
                    $this->formattedOut( __d('plugin', "[fg=black][bg=red] ERRO [/bg][/fg]", true) );
                    exit;
                }

                $this->mainShell->formattedOut( __d('plugin',
"[fg=black][bg=green]  OK  [/bg][/fg]
  -> executando hook de instalacao...", true) );

                $installer = new $className( $this->mainShell );
 
                if( method_exists($installer, 'install' ) )
                {
                    $installer->install( );
                }
            }
            else
            {
                $this->mainShell->formattedOut( __d('plugin', "    - O hook nao existe \n ", true) );
            }
        }

        function listInstalledPlugins( )
        {
            $this->mainShell->formattedOut( String::insert(__d('plugin', "Listando plugins instalados em [u]:app[/u]:\n", true), array('app'=> APP_DIR)) );

            $installedPlugins = $this->_getInstalledPlugins( );

            foreach( $installedPlugins as $plugin )
            {
                $out = String::insert( __d('plugin', '  [fg=green]:plugin', true), array('plugin'=> $plugin) );

                if( $this->_getPluginUrl($plugin) !== false )
                {
                    $out .= __d( 'plugin', " *[/fg]", true );
                }
                else
                {
                    $out .= __d( 'plugin', "[/fg]\n", true );
                }

                $this->mainShell->formattedOut( $out );
            }

            $this->mainShell->formattedOut( __d('plugin', '* Plugins que podem ser atualizados utilizando o [u]Plugin Manager[/u]', true) );
        }

        function find( $_pattern )
        {
            $this->mainShell->formattedOut( String::insert(__d('plugin', "Buscando plugins: [u]:pattern[/u]\n", true), array('pattern'=>$_pattern)) );

            $avaliable = $this->_getListAvaliablePlugins( );

            $found = false;

            foreach( $avaliable as $plugin )
            {
                if( preg_match('/.*'.$_pattern.'.*/', $plugin['name']) )
                {
                    $out = String::insert( __d('plugin', "[fg=green]    :pluginName", true), array('pluginName'=> $plugin['name']) );

                    if( $this->_isInstalled($plugin['name']) )
                    {
                        $out .= __d( 'plugin', " *[/fg]\n", true );
                    }
                    else
                    {
                        $out .= String::insert( __d('plugin', "[/fg]\n      :pluginUrl\n", true), array('pluginUrl'=> $plugin['url']) );
                    }

                    $this->mainShell->formattedOut( $out );

                    $found = true;
                }
            }

            if( !$found )
            {
                $this->mainShell->formattedOut( __d('plugin', "Nao foram encontrados plugins\n", true) );

                $this->mainShell->hr( );
                exit;
            }

            $this->mainShell->formattedOut( __d('plugin', '* Plugins ja instalados', true) );
        }

        function installPlugin( $_nameOrUrl )
        {
            $this->mainShell->formattedOut( String::insert(__d('plugin', "Instalando [u]:plugin[/u]...", true), array('plugin'=>$_nameOrUrl)) );

            if( $method = $this->_getMethod($_nameOrUrl) )
            {
                $url = $_nameOrUrl;
                $pluginName = 'teste_svn';
            }
            else
            {
                if( $this->_isInstalled($_nameOrUrl) )
                {
//                    $this->update( $_nameOrUrl );
                    exit;
                }
                else
                {
                    $pluginName = $_nameOrUrl;
                    $url = $this->_findPluginUrl($_nameOrUrl);
                    if( !$url )
                    {
                        $this->mainShell->formattedOut( String::insert(__d('plugin', "O plugin [fg=red][u]:plugin[/u][/fg] nao foi encontrado.", true), array('plugin'=>$_nameOrUrl)) );
                        exit;
                    }
                    $method = $this->_getMethod($url);
                }
            }

            $status = $this->_doInstall( $method, $url, $pluginName );

            if( $status )
            {
                $this->_saveUrlFile( $method, $url, $pluginName );
            }

            $this->_runInstallHook( $pluginName );
        }

        function installDep( $_pluginName, $_url )
        {
            if( !($method = $this->_getMethod($_url)) )
            {
                $this->mainShell->formattedOut( String::insert(__d('plugin', "A URL '[fg=red][u]:url[/u][/fg]' nao parece ser valida.", true), array('url'=>$url)) );
            }

            $status = $this->_doInstall( $method, $url, $pluginName );

            if( $status )
            {
                $this->_saveUrlFile( $method, $url, $pluginName );
            }

            $this->_runInstallHook( $pluginName );
        }
    }

?>
