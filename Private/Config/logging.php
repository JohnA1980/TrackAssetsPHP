<?php
    
    $_app_name = str_replace(" ", "_", appName());

    if (DEPLOYED)
    {
        setLogPath(LOGS."/$_app_name"."_live.log"); 
        setDebugLogging(0);
    }
    else if (TESTING)
    {
        setLogPath(LOGS."/$_app_name"."_test.log");
        setDebugLogging(1);
    }
    else
    {
        setLogPath(LOGS."/$_app_name.log"); 
    	setDebugLogging(2);
    	error_reporting(E_ALL);
    }
    unset($_app_name);

    // roll log the file if it exceeds the file limit (5MB is the default limit, which 
    // can be adjusted with the second parameter of setLogPath()).
    if (TESTING || DEPLOYED)
        rollLogFileIfNeeded();
?>