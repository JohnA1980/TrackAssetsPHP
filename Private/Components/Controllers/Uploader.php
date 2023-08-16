<?php 
	require_once BLOGIC."/BLogic.php"; 
	
	/*
		The upload works by instanciating it inside of the component you are using it within and setting the public parameters
		accordingly. 
	
		In the web browser the javascript requires two out of the 3 paramters that inturn get sent with the ajax request.
		Each time the the page is reloaded a new unique token is generated, the idea being that all uploads that take place
		during one instance of a page load can be later referenced and processed by your app accordingly. 
	*/
	
	 
	class Uploader extends PLController 
	{ 
		// A unique ID, passed with the AJAX request, that gets stored with the uploaded data in the database.
		public $token;
		
		// The type of file being uploaded. Refer to the array in Config/uploads.php. 
		// This variable should correspond to a key in this array.
		public $type = "generic";
		
		// If you wish the browser to receive a notifcation when a file has completed then you should set this to
		// the name of your javascript callback method, which should in the format of methodName(token, type).
		public $jsCallback;
		
		public function __construct($formData) 
		{ 
			parent::__construct($formData, "Uploader");
			
			$this->token = uniqid();
			
			global $extraCSS;
			if (! in_array("css/file-upload.css", $extraCSS))
				debugln("#### WARNING: file-upload.css is not included. The uploader may not display properly.");
			global $extraJS;
			if (! in_array("js/file-upload.js", $extraJS))
				debugln("#### WARNING: file-upload.js is not included. The uploader may not function properly.");
		} 
	} 
?>
