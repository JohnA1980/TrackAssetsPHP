<?php
// ===========================
// = Database Initialisation =
// ===========================
//  Enable this to utilise a database. 
//    Datasources include:
//        - BLMySQLDataSource
//        - BLMSSQLDataSource
//        - BLFileMakerDataSource
//        - BLCSVDatasource



	require_once BLOGIC."/BL/BLMySQLDataSource.php";
	
	debugln("database.php set");
	
	if (DEPLOYED)
	{
		// production database
		BLDataSource::setDefaultDataSource(new BLMySQLDataSource("localhost", "trackassets", "jcode1980", "trackassets"));
	}
	else if (TESTING)
	{
		// testing database
		BLDataSource::setDefaultDataSource(new BLMySQLDataSource("localhost", "trackassets", "jcode1980", "trackassets_test"));
	}
	else
	{
		// local development database
		BLDataSource::setDefaultDataSource(new BLMySQLDataSource("localhost", "admin", "", "Trackassets"));
	}

?>
