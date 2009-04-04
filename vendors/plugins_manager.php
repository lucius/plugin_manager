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
                case 'ssh':
                    $status = $this->_installUsingGit( $_url, $_pluginName );
                    break;
                case 'svn':
                    $status = $this->_installUsingSvn( $_url, $_pluginName );
                    break;
                case 'http':
                case 'https':
                    if( !$status = $this->_installUsingSvn($_url, $_pluginName) )
                    {
                        $this->mainShell->formattedOut( __d('plugin', '  -> Tentando instalar usando ', true), false );
                        $status = $this->_installUsingGit( $_url, $_pluginName );
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

        function _saveUrlFile( $_url, $_pluginName)
        {
            $filePath = APP."plugins/$_pluginName/.url";

            $this->mainShell->formattedOut( __d('plugin', "  -> salvando arquivo .url... ", true), false );

            if( file_put_contents($filePath, $_url) === false )
            {
                $this->mainShell->formattedOut( __d('plugin',"[fg=black][bg=red] ERRO [/bg][/fg]\n    - nao sera possivel realizar a atualizacao do plugin atraves\n      do plugin_manager.", true) );
            }
            else
            {
                $this->mainShell->formattedOut( __d('plugin',"[fg=black][bg=green]  OK  [/bg][/fg]", true) );
            }
        }

        function _runInstallHook( $_pluginName )
        {
            $this->mainShell->formattedOut( __d('plugin', "  -> verificando a existencia do hook de instalacao...", true) );

            if( file_exists(APP.'plugins/'.$_pluginName.'/vendors/shells/'.$_pluginName.'_installer.php') )
            {
                $this->mainShell->formattedOut( __d('plugin', "    - carregando... ", true), false );
                $className = Inflector::camelize($_pluginName.'_installer');
                //                if( !App::import('Vendors', Inflector::camelize($_pluginName).'.'.$className) )
                if( !include(APP.'plugins/'.$_pluginName.'/vendors/shells/'.$_pluginName.'_installer.php') )
                {
                    $this->mainShell->formattedOut( __d('plugin', "[fg=black][bg=red] ERRO [/bg][/fg]", true) );
                    exit;
                }

                $this->mainShell->formattedOut( __d('plugin',"[fg=black][bg=green]  OK  [/bg][/fg]\n  -> executando hook de instalacao...", true) );
                $installer = new $className( $this->mainShell );

                $installer->startup( );
                $installer->_installDeps( );

                if( method_exists($installer, 'install' ) )
                {
                    $installer->install( );
                }
            }
            else
            {
                $this->mainShell->formattedOut( __d('plugin', "    - O hook nao existe\n", true) );
            }
        }

        function _getDependencies( $_pluginName )
        {
            if( file_exists(APP.'plugins/'.$_pluginName.'/vendors/shells/'.$_pluginName.'_installer.php') )
            {
                $className = Inflector::camelize($_pluginName.'_installer');
                include(APP.'plugins/'.$_pluginName.'/vendors/shells/'.$_pluginName.'_installer.php');
                $installer = new $className( array('mainShell' => $this->mainShell) );
                return $installer->deps;
            }

            return null;
        }

        function _removeDependencies( $_deps, $_all )
        {
            $folder = new Folder();
            $opt = 'y';

            foreach( $_deps as $name => $url )
            {
                if( !$_all )
                {
                    $this->mainShell->formattedOut( String::insert(__d('plugin', "\n\nTem certeza que deseja remover [fg=yellow]:plugin[/fg]?", true), array('plugin'=>$name)) );
                    $this->mainShell->formattedOut( __d('plugin', "[fg=green](Y)[/fg] Sim\n[fg=red](N)[/fg] Nao\n", true) );
                    $remove['deps'] = $this->mainShell->in( '', array('Y', 'N') );
                }

                if( strtolower($opt == 'y') )
                {
                    if( $folder->delete( APP.'plugins/'.$name) )
                    {
                        $this->mainShell->formattedOut( String::insert(__d('plugin', "\n\n[fg=yellow]:plugin[/fg] removido com sucesso!", true), array('plugin'=>$name)) );
                    }
                }
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
                    $out .= __d( 'plugin', " *[/fg]\n", true );
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
                if( $this->_isInstalled($_nameOrUrl) )
                {
//                    $this->update( $_nameOrUrl );
                    exit;
                }
                else
                {
                    $url = $_nameOrUrl;
                    // @TODO 
                    $pluginName = $this->_getName( );
                }
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
                $this->_saveUrlFile( $url, $pluginName );
                $this->_runInstallHook( $pluginName );
            }
        }

        function installDep( $_pluginName, $_url )
        {
            $this->mainShell->formattedOut( String::insert(__d('plugin', "         - Instalando [u]:plugin[/u]...", true), array('plugin'=>$_pluginName)) );

            if( !($method = $this->_getMethod($_url)) )
            {
                $this->mainShell->formattedOut( String::insert(__d('plugin', "A URL '[fg=red][u]:url[/u][/fg]' nao parece ser valida.", true), array('url'=>$_url)) );
            }

            $status = $this->_doInstall( $method, $_url, $_pluginName );
            if( $status )
            {
                $this->_saveUrlFile( $_url, $_pluginName );
                $this->_runInstallHook( $_pluginName );
            }
            return $status;
        }

        function uninstallPlugin( $_pluginName )
        {
            $this->mainShell->formattedOut( String::insert(__d('plugin', "Tem certeza que deseja remover [fg=yellow]:plugin[/fg]?", true), array('plugin'=>$_pluginName)) );
            $this->mainShell->formattedOut( __d('plugin', "[fg=green](Y)[/fg] Sim\n[fg=red](N)[/fg] Nao\n", true) );
            $remove['plugin'] = $this->mainShell->in( '', array('Y', 'N') );

            if( strtolower($remove['plugin']) == 'y' )
            {
                if( !App::import('Folder') )
                {
                   $this->out( __d('plugin', "Impossivel caregar 'Folder'") );
                }

                if( $deps = $this->_getDependencies($_pluginName ) )
                {
                    $this->mainShell->formattedOut( __d('plugin', "\n\nDeseja remover as dependecias instaladas?", true) );
                    $this->mainShell->formattedOut( __d('plugin', "[fg=green](N)[/fg] Nenhuma\n[fg=yellow](S)[/fg] Passo-a-Passo\n[fg=red](A)[/fg] Todas\n", true) );
                    $remove['deps'] = $this->mainShell->in( '', array('N', 'S', 'A') );
                }

                if( strtolower($remove['deps']) != 'n' )
                {
                    $this->_removeDependencies( $deps, (strtolower($remove['deps']) == 'a') );
                }

                $folder = new Folder();
                if( $folder->delete( APP.'plugins/'.$_pluginName) )
                {
                    $this->mainShell->formattedOut( String::insert(__d('plugin', "[fg=yellow]:plugin[/fg] removido com sucesso!", true), array('plugin'=>$_pluginName)) );
                }


            }
        }

        function update( $_pluginName )
        {
            if( !App::import('Folder') )
            {
                $this->out( __d('plugin', "Impossivel caregar 'Folder'") );
            }

            $this->mainShell->formattedOut( String::insert(__d('plugin', "Atualizando [fg=green]:plugin[/fg]...", true), array('plugin'=>$_pluginName)), false );

            
            $url = $this->_getPluginUrl($_pluginName);
            if( $url === false )
            {
                $this->mainShell->formattedOut( __d('plugin',"[fg=black][bg=red] ERRO [/bg][/fg]", true) );
                $this->mainShell->formattedOut( __d('plugin',"  -> O plugin nao existe ou nao possui uma url para atualizacao.", true) );

                exit;
            }

            $this->mainShell->out( "\n", false );

            $pluginFolder = new Folder();
            $pluginFolder->move( array(
                                        'from' => APP.'plugins/'.$_pluginName,
                                        'to'   => APP.'plugins/'.$_pluginName.'-old'
                                    ) );

            $method = $this->_getMethod( $url );
            $status = $this->_doInstall( $method, $url, $_pluginName );

            if( $status )
            {
                $this->_saveUrlFile( $url, $_pluginName );
                $this->_runInstallHook( $_pluginName );
                $pluginFolder->delete( APP.'plugins/'.$_pluginName.'-old' );
            }
            else
            {
                $pluginFolder->move( array(
                                        'to'   => APP.'plugins/'.$_pluginName.'-old',
                                        'from' => APP.'plugins/'.$_pluginName
                                    ) );

            }
        }
    }

?>
