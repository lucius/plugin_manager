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
            switch( $_method )
            {
                case 'git':
                    $this->_installUsingGit( $_url, $_pluginName );
                    break;
                 case 'svn':
                     $this->_installUsingSvn( $_url, $_pluginName );
                     break;
                 case 'http':
                 case 'https':
                     if( !$this->_installUsingSvn($_url, $_pluginName) )
                     {
                         $this->_installUsingGit( $_url, $_pluginName );
                     }
                     break;
                 case 'ssh':
                     if( !$this->_installUsingGit($_url, $_pluginName) )
                     {
                         $this->_installUsingSvn( $_url, $_pluginName );
                     }
                     break;
            }
        }

        function _installUsingSvn( $_url, $_pluginName )
        {
            if( !App::import('Vendors', 'PluginManager.SvnHandler') )
            {
            }

            $params = array( 'mainShell' => $this->mainShell );
            $svnHandler = new SvnHandlerPM( $params );

            $svnHandler->install( $_url, $_pluginName );

            return true;
        }


        function _installUsingGit( $_url, $_pluginName )
        {
            if( !App::import('Vendors', 'PluginManager.GitHandler') )
            {
            }

            $params = array( 'mainShell' => $this->mainShell );
            $gitHandler = new GitHandlerPM( $params );

            $gitHandler->install( $_url, $_pluginName );

            return false;
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
                    $url = $this->_findPluginUrl( $_nameOrUrl );
                    $method = $this->_getMethod($url);
                }
            }
            $this->_doInstall( $method, $url, $pluginName );

            $this->_runInstallHook( $pluginName );
        }

        function installDep( $_pluginName, $_url )
        {
            if( !($method = $this->_getMethod($_nameOrUrl)) )
            {
                
            }

            $this->_doInstall( $method, $url, $pluginName );

            $this->_runInstallHook( $pluginName );
        }

        function _runInstallHook( $_pluginName )
        {
            if( file_exists(APP.'plugins/'.$_pluginName.'/vendors/shells/'.$_pluginName.'_installer.php') )
            {
                $className = Inflector::camelize($_pluginName.'_installer');
                if( !App::import('Vendors', $className) )
                {

                }

                $installer = new $className( $this->mainShell );
 
                if( method_exists($installer, 'install' ) )
                {
                    $installer->install( );
                }
            }
        }
    }

?>
