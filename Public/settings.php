<?php
    if (basename(__FILE__) == basename($_SERVER["SCRIPT_FILENAME"]))
        die("no direct script access allowed!");
        
	/*
		Deployment or testing is set by placing an empty '.production' or '.testing' file in the top of 
		the public web directory respectively.
	*/
	
	$deployed = file_exists(".production") ? 1 : 0;
	define("DEPLOYED", $deployed);	
	$testing = file_exists(".testing") ? 1 : 0;
	define("TESTING", $testing);
	
	// set the relative paths to the BLogic framework and your app's private folder for each mode.	
	// NOTE: to utilise the BLFileMakerDataSource you must also define the paths to the constant FMPATH.
	if (DEPLOYED)
	{
		define("ROOT", "../Live/Private");
		define("BLOGIC", "../Live/BLogic");
		define("LOGS", "../Logs");
		//define("PRODUCTION_FOLDER", "../../../Files/");
		define ("PRODUCTION_FOLDER", "~/Files/");
		define("DEV", "");
	}
	else if (TESTING)
	{
		define("ROOT", "../../Testing/Private");
		define("BLOGIC", "../../Testing/BLogic");
		define("LOGS", "../../Logs");
		define("DEV", "");
	}
	else
	{
		define("ROOT", "../Private");
		define("BLOGIC", "../BLogic");
		define("LOGS", "../Logs");
		
		define("PRODUCTION_FOLDER", "/Users/john/Sites/Upload/");
		define("DEV", "../dev");
	}
	
	require_once ROOT."/settings.php"; // load the rest of the config from the private settings.
		
?>