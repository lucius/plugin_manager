<?php

    class InstallImprovedCakeShell
    {
        var $stdout = null;

        function __construct( )
        {
            $this->stdout = fopen( 'php://stdout', 'w' );
        }

        function out( $string, $newline = true )
        {
            if( $newline )
            {
                fwrite( $this->stdout, $string."\n" );
            }
            else
            {
                fwrite( $this->stdout, $string);
            }
        }

        function install( )
        {
            $this->out( __d('plugin', "Instalando dependencias necessarias para o funcionamento do Plugin Manager...\n  -> Instalando: Improved Cake Shell...", true) );

            $comando = 'git clone git://github.com/lucius/improved_cake_shell.git '.APP.'plugins/improved_cake_shell';
            $this->out( __d('plugin', '    - Executando: '.$comando, true) );

            $this->out( '      '.shell_exec($comando) );

            if( file_exists(APP.'plugins/improved_cake_shell') )
            {
                include( APP.'plugins/improved_cake_shell/vendors/shell/improved_cake_shell.php' );
                $this->out( __d('plugin', '     Instalado com sucesso!', true) );

                $this->_excludeGitFolder( );
            }
            else
            {
                $this->out( __d('plugin', "    FALHA NA INSTALACAO!\n     Plugin Manager nao podera ser executado", true) );
            }
        }

        function _excludeGitFolder( )
        {
            if( !App::import('Folder') )
            {
                $this->out( __d('plugin', "Impossivel caregar 'Folder'") );
            }

            $gitFolder = APP.'plugins/improved_cake_shell/.git/';
            $folder = new Folder();
            $folder->delete( $gitFolder );
        }
    }

?>
