<?php 
	require_once BLOGIC."/BLogic.php"; 
	require_once ROOT.'/Components/Controllers/PageWrapper.php';
	 
	class CalendarView extends PageWrapper 
	{ 
		public function __construct($formData) 
		{ 
			$templateName = $this->templateNameBasedOnDevice("CalendarView", array());
			parent::__construct($formData, $templateName);
		} 
        
	} 
?>