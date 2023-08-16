<?php
    $hostname = php_uname("n");
	$port = safeValue($_SERVER, "SERVER_PORT", 9000);
    setDomainName("http://$hostname:$port"); 
    setDebugLogging(1);
    setLogPath(__STDOUT__);
?>