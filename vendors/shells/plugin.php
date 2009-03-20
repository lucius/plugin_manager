<?php

    if( !App::import('Vendors', 'ImprovedCakeShell.ImprovedCakeShell') )
    {
	App::import('Vendors', 'PluginManager.PluginManager');
        PluginManager::installDep('git://github.com/lucius/improved_cake_shell.git');
    }

    class PluginShell extends ImprovedCakeShell 
    {
        function main()
        {
        }
    }

?>
