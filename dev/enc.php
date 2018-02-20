<?php 
	chdir("../Public");
	require "settings.php";
	
	$string = $argv[1];
	echo doEncrypt($string)."\n";
?>