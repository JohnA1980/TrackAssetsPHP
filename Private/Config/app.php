<?php
    /*
        A repository for any app specific settings.
    */
    
    PLController::$componentROOT = ROOT."/Components";
    
    date_default_timezone_set("Australia/Melbourne"); // set the default time zone.
    

    if (DEPLOYED || TESTING)
    {
    	$level = TESTING ? 1 : 0;
    	setDebugLogging($level);
	    
    	$domain = "http://www.trackassets.com.au";
    	if (TESTING) {
    		$domain .= "/test";
        } 
	
    	// add your developer email address to this array to receive critical 
    	// error reports from the production server.
    	installGeneralErrorHandlers(array("john@sqonk.com"), "error.html");
        
    	/* The domain name is used in some situations such as error handling where it will auto-forward
    	to an error page at the top level. */
        setDomainName($domain);
    }
    // NOTE: your development machine domain and other settings are generated and held in the dev folder.
	
    setAppName("Track Asset"); 

    // Triggered when user tries to request a component or action that does not exist.
    if (DEPLOYED)
	    setNotFoundPage("not_found.html");
?>