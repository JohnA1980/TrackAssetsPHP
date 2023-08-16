<?php 
	require_once BLOGIC."/BLogic.php"; 
	
	 
	class UploaderModal extends PLController 
	{ 
		// Title label of the modal window.
		public $title = "Upload";
		
		// This variable can be used to reference the inner uploader and configure it.
		public $uploader;
		
		public function __construct($formData) 
		{ 
			parent::__construct($formData, "UploaderModal");
			
			$this->uploader = $this->pageWithName("Uploader");
			
			global $extraCSS;
			if (! in_array("css/bootstrap.css", $extraCSS) && ! in_array("css/bootstrap.min.css", $extraCSS)) {
				debugln("#### WARNING: The UploaderModal requires Bootstrap and the Bootstrap CSS file was not directly detected. If you have included it elsewhere you can safely disable this warning in the component constructor.");
				dumpStack();
			}
		} 
	} 
?>
