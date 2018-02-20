<?php
    /*
        The top level settings file will load all other config files under config/. You should place all app-level settings in config/app.php.
    */
    
    require_once BLOGIC."/BLogic.php";
	require_once BLOGIC."/PL/PLRequestResponseHandler.php";

    // load all configuration files
	foreach (glob(ROOT.'/Config/*.php') as $filename) {
		require_once $filename;
	}
    
	// include any application specific utility methods and globals.
	foreach (glob(ROOT.'/Utils/*.php') as $filename) {
		require_once $filename;
	}
	
    if (! DEPLOYED && ! TESTING)
    {
		/* 
        This will gracefully load custom configs from a file with the developer's machine host name, short circuiting problems caused by certain settings that are different between people.
        */
        $hostname = php_uname("n");
		$developer_custom_settings_file = DEV."/_$hostname.settings.php";
		if (file_exists($developer_custom_settings_file))
		    require_once $developer_custom_settings_file;
    }	
?>

