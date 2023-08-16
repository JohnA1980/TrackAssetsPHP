<?php
require_once BLOGIC."/BL/BLMySQLDataSource.php";
	

$dbconfig_file = ROOT.'/Persistence/database.json';
if (file_exists($dbconfig_file)) 
{
	$dbconfig = json_decode(file_get_contents($dbconfig_file));
	BLDataSource::setDefaultDataSource(new BLMySQLDataSource($dbconfig->host, $dbconfig->username, $dbconfig->password, $dbconfig->database));
	unset($dbconfig);
}
else {
	debugln("**** WARNING: no database credentials file found! The app will probably not work.");
}
unset($dbconfig_file);
