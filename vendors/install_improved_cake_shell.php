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

        /* 
         * Inserir Autenticacao no proxy
         */
        function install( )
        {
            $this->out( __d('plugin', 'Instalando dependencias necessarias para o funcionamento do gerenciador de Plugins...', true) );
            $this->out( __d('plugin', '  -> Instalando: Improved Cake Shell...', true) );

            $comando = 'git clone git://github.com/lucius/improved_cake_shell.git '.APP.'plugins/improved_cake_shell';
            $this->out( __d('plugin', '  -> Executando: '.$comando, true) );

            $this->out( __d('plugin', '     '.shell_exec($comando), true) );

            if( file_exists(APP.'plugins/improved_cake_shell') )
            {
                include(APP.'plugins/improved_cake_shell/vendors/shell/improved_cake_shell.php');
                $this->out( __d('plugin', '     Instalado com sucesso!', true) );
            }
            else
            {
                $this->out( __d('plugin', '     Falha na instalacao!', true) );
            }

        }

/*
 * Fazer Corretamente
 */
        function _excludeGitFolder( )
        {
            if( !App::import('Folder') && !App::import('File') )
            {

            }
            $app = new Folder( APP.'plugins/improved_cake_shell/.git/' );
            $app->delete();
        }
    }

?>
