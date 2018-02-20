<?php
    /*
    	The following two variables contain a black list of registered page 
    	names and actions that will be silently ignored. If the website is
    	being hit by a spam bot or other hacking attempt you can place the 
    	registered data it's trying to submit into these arrays.
    */
    global $bannedPageNames;
    $bannedPageNames = array();

    global $bannedActions;
    $bannedActions = array();
    
    if (DEPLOYED || TESTING)
    {
        // === AUTO BLACK LISTING ===
        // If your site starts to report errors of a strange or outright suspicious 
        // nature you can turn on auto page and action name banning. Once a request for an
        // incorrect component or action name passes the set threshold (defaults to 3) then
        // it's placed on the register and all subsequent attempts will result in a delayed
        // HTTP 400 response.
        setBanningEnabled(true);
    
        // When using auto-ban, adjust this from the default 3 if you need to tighten or loosen the tollerance.
        //setBanningTolerance(3); 
    }
?>