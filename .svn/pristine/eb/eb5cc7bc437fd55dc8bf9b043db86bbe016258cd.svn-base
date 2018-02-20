<?php 
	require_once BLOGIC."/BLogic.php"; 
	require_once ROOT.'/Components/Controllers/PageWrapper.php';
	
	 
	class FileUpload extends PageWrapper 
	{ 
		protected $primaryKeyArray = ["Occupant" => "occupantID", "Asset" => "assetID"]; 
		protected $parentObject;
		protected $fileTypes = ["jpg", "png", "jpeg", "gif", "pdf"];		

		public function __construct($formData) 
		{ 
			$templateName = $this->templateNameBasedOnDevice("FileUpload", array());
			parent::__construct($formData, $templateName);
			
// 			$this->currentView = doDecrypt($this->formValueForKey("currentView"));
// 			if (! $this->currentView)
// 				$this->currentView = "Details";
		} 
		
	
		public function parentObject()
		{
			if (! $this->parentObject)
			{
				$id = doDecrypt($this->formValueForKey("selectedID"));
				$class = doDecrypt($this->formValueForKey("parentClass"));
				
				debugln("this is the class: " . $class);
				debugln("ID : " . $id . " primaryKey: " . $this->primaryKeyArray[$class]);
				
				if ($id) {
					$this->parentObject = BLGenericRecord::recordMatchingKeyAndValue($class, $this->primaryKeyArray[$class], $id);
				}
			}
			return $this->parentObject;
		}

		
		 public function uploadAction(){
	    	debugln("got to the uploadAcion");
	    	debugln("Post data: " . implode($_POST));
	    	$class = doDecrypt($this->formValueForKey("parentClass"));
	    
	    	//$target_dir = "/Users/john/Sites/Upload/";
			$target_dir = PRODUCTION_FOLDER;
	    	debugln("Does Directory exist? " . (file_exists($target_dir) ? 'true' : 'false'));
	    	
	    	//$target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
	    	
	    	
	    	$uploadOk = 1;
	    	$imageFileType = pathinfo(basename($_FILES["fileToUpload"]["name"]),PATHINFO_EXTENSION);
	    	// Check if image file is a actual image or fake image
	    	
	    	debugln("fileType: " . $imageFileType);
	    	debugln("feil tempname: " . $_FILES["fileToUpload"]["tmp_name"]);
	    	
	    	//$check = filesize($_FILES["fileToUpload"]["tmp_name"]);
	    	
	    	
// 	    	$check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
	    	
// 	    	//debugln("this is the check:" . $check);
// 	    	if($check !== false) {
// 	    		debugln( "File is an image - " . $check["mime"] . ".");
// 	    		$uploadOk = 1;
// 	    	} else {
// 	    		debugln("File is not an image.");
// 	    		$uploadOk = 0;
// 	    	}
	    	
// 	    	// Check if file already exists
// 	    	if (file_exists($target_file)) {
// 	    		debugln("Sorry, file already exists.");
// 	    		$uploadOk = 0;
// 	    	}else{
// 	    		debugln("File does not exist");
// 	    	}
	    	
	    	// Check file size
	    	if ($_FILES["fileToUpload"]["size"] > 500000) {
	    		debugln("Sorry, your file is too large.");
	    		$uploadOk = 0;
	    	}
	    	
			debugln("this is the imageFileType: " . $imageFileType );
			if (!in_array(strtolower($imageFileType), $this->fileTypes)) {
				debugln("Sorry, only JPG, JPEG, PNG & GIF files are allowed.");
	    				$uploadOk = 0;
			}

// 	    	// Allow certain file formats
// 	    	if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "JPG" && $imageFileType != "gif" ) {
// 	    				debugln("Sorry, only JPG, JPEG, PNG & GIF files are allowed.");
// 	    				$uploadOk = 0;
// 	    	}
    		// Check if $uploadOk is set to 0 by an error
    		if ($uploadOk == 0) {
    			debugln("Sorry, your file was not uploaded.");
    			// if everything is ok, try to upload file
    		} else {
    			
    			//Create a file object.
    			$newFile = BLGenericRecord::newRecordOfType("File");
    			//Populate
    			$newFile->save();
    			//grab the primary Key of the file ID
    			
    			$target_file = $target_dir . basename($newFile->vars["fileID"]);
    			debugln("Target File = " . $target_file);
    			//$target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
    			
    			
    			$newFile->vars["mimetype"] = $imageFileType;
    			$newFile->vars["size"] = $_FILES["fileToUpload"]["size"];
    			$newFile->vars["fileName"] = $_FILES["fileToUpload"]["name"];
    			
    			if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
    				
    				debugln("The file ". basename( $_FILES["fileToUpload"]["name"]). " has been uploaded.");
					
					$parentObject = $this->parentObject(); 
					
					debugln("this is the pk classID: " . $parentObject->vars[$this->primaryKeyArray[$class]]);
					$newFile->vars["occupantID"] = $parentObject->vars[$this->primaryKeyArray[$class]];
					$newFile->save();
					
					debugln("this is the assetID: " . $parentObject->vars["assetID"]);
					$page = $this->pageWithName("OccupantDetailsList",  array("selectedID" =>  doEncrypt($parentObject->vars["assetID"]) , "selectedIncomeSourceID" =>  $this->formValueForKey("selectedIncomeSourceID")));
    				
					return $page;
    			} else {
    				debugln("Sorry, there was an error uploading your file.");
    				return null;
    			}
    		}
	    	
	    }
	
	}    

	
	
	
?>