<?php
	require_once BLOGIC."/BL/BLMySQLDataSource.php";
	
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
		BLDataSource::setDefaultDataSource(new BLMySQLDataSource("localhost", "johna", "games", "trackassets"));
	}
?>