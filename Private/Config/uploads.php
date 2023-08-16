<?php
	
	// This mapping defines the entity types that are accepted as input from the client. It acts as a filter to stop 
	// a possible attack vector from accepting entity names directly from the client. The keys represent the 
	// codes sent from the browser while the values represent the correpsonding entity.
	$allowed_upload_types = [
		"generic" => "FileUpload",
		"lineitem" => "LineItemAttachment"
	];
?>