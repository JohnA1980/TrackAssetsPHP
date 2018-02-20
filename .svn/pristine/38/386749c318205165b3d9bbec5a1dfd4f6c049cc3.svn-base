<?php
require_once BLOGIC."/BLogic.php";
require_once ROOT.'/Components/Controllers/PageWrapper.php';
require_once ROOT."/Entities/File.php";

class OccupantDetailsList extends PageWrapper
{
	protected $asset;
	protected $selectedOccupant;
	protected $currentIncomeSource;
	
	public function __construct($formData)
	{
		$templateName = $this->templateNameBasedOnDevice("OccupantDetailsList", array());
		parent::__construct($formData, $templateName);
	}
	
	public function asset()
	{
		if (! $this->asset)
		{
			
			$id = doDecrypt($this->formValueForKey("selectedID"));
			debugln("selected ID is: " . $id);
			
			if ($id) {
				
				$this->asset = BLGenericRecord::recordMatchingKeyAndValue("Asset", "assetID", $id);
			}
		}
		return $this->asset;
	}	
	
	public function currentIncomeSource()
	{
		if (! $this->currentIncomeSource)
		{
			$id = doDecrypt($this->formValueForKey("selectedIncomeSourceID"));
				
			if ($id) {
	
				$this->currentIncomeSource = BLGenericRecord::recordMatchingKeyAndValue("IncomeSource", "incomeSourceID", $id);
			}
		}
		return $this->currentIncomeSource;
	}
	
	public function selectedOccupant()
	{
		if (! $this->selectedOccupant)
		{
			$id = doDecrypt($this->formValueForKey("selectedOccupantID"));			
			if ($id) {
				$this->selectedOccupant = BLGenericRecord::recordMatchingKeyAndValue("Occupant", "occupantID", $id);
			}
		}
		return $this->selectedOccupant;
	}
	
	public function saveAction(){
		$this->processFormValueKeyPathsForSave();
		try {
			if($this->selectedOccupant()){
				debugln("saveAction selectedOccuapnt");
				$this->selectedOccupant()->save();
			}
		} catch (Exception $error) {
                debugln($error->getMessage());
                $this->errorMessage = "There was a problem saving the occupant, please try again.";
            }
	}
	
	public function backAction(){
		debugln("backActionbackActionbackActionbackActionbackAction");
		if($this->currentIncomeSource()){
			if($this->selectedOccupant()){
				$this->selectedOccupant()->save();
			}
			debugln( "backAction selectedID : " .$this->formValueForKey("selectedID"));
			$page = $this->pageWithName("IncomeSourceEdit",  array("selectedID" =>  $this->formValueForKey("selectedID")));
			
			return $page;
		}else{
			debugln( "2nd else backAction selectedID : " . $this->formValueForKey("selectedID"));
			$page = $this->pageWithName("AssetDetails", array("selectedID" => $this->formValueForKey("selectedID")));
			return $page;
		}
		debugln("END backActionbackActionbackActionbackActionbackAction");
	}
	
	public function cancelAction(){
		if($this->currentIncomeSource()){
			$page = $this->pageWithName("IncomeSourceEdit",  array("selectedID" =>  $this->formValueForKey("selectedID")));
			return $page;
		}else{
			$page = $this->pageWithName("AssetDetails", array("selectedID" =>  $this->formValueForKey("selectedID")));
			return $page;
		}
	}
	
	public function selectForIncomeSourceAction(){
		$selectedOccupantID = doDecrypt($this->formValueForKey("selectedOccupantID"));
		debugln("selected OccupantID : " + $selectedOccupantID);
		$this->currentIncomeSource()->vars["occupantID"] = $selectedOccupantID;
		$this->currentIncomeSource()->save();
		
		$page = $this->pageWithName("AssetDetails", array("selectedID" => $this->formValueForKey("selectedID"), "currentView" => doEncrypt("Income")));
		
		return $page;
	}
	
  	public function createOccupant(){
        	$newOccupant = BLGenericRecord::newRecordOfType("Occupant");
        	$newOccupant->vars["assetID"] = $this->asset()->vars["assetID"];
        	$newOccupant->save();
        	
        	$this->setFormValueForKey(doEncrypt($newOccupant->vars["occupantID"]), "selectedOccupantID");
        	
            // debuging statements? why not in the debug log?
        	//echo "new expense lineItem: " . $newExpenseLineItem->vars["assetID"];
        	//echo "new expense lineItem ID: " . $newExpenseLineItem->vars["expenseLineItemID"];        	
        	
        }
        
        public function uploadFileAction(){
        	$page = $this->pageWithName("FileUpload", array("selectedID" =>  $this->formValueForKey("selectedOccupantID"), "selectedIncomeSourceID" =>  $this->formValueForKey("selectedIncomeSourceID"), "parentClass"=> doEncrypt("Occupant")));
        	return $page;
        }
        
        function downloadFile() { // $file = include path
        	//$file = "/Users/john/Sites/Upload/24";
        	//debugln("this is the selectedDocumentID: " . $this->formValueForKey("selectedDocumentID"));
        	//debugln("this is the selectedDocumentID decrypted : " . doDecrypt($this->formValueForKey("selectedDocumentID")));
        	$file = File::objectForID($this->formValueForKey("selectedDocumentID"));
        	//debugln("file is?? " . $file);
        	if(file_exists($file->filePath())) {
        	//if(file_exists("/Users/john/Sites/Upload/24")) {
	        	header('Content-Description: File Transfer');
	        	header('Content-Type: application/octet-stream');
	        	//header('Content-Disposition: attachment; filename='.basename("/Users/john/Sites/Upload/24"));
	        	header('Content-Disposition: attachment; filename='. $file->vars["fileName"]);
	        	header('Content-Transfer-Encoding: binary');
	        	header('Expires: 0');
	        	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	        	header('Pragma: public');
	        	header('Content-Length: ' . filesize($file->filePath()));
	        	//header('Content-Length: ' . filesize("/Users/john/Sites/Upload/24"));
	        	ob_clean();
	        	flush();
	        	readfile($file->filePath());
	        	exit;
        	}
        	else{
        		debugln("file with file path doesbn't exists: " . $file->filePath());
        	}
        	
//         	$filename = 'Test.pdf'; // of course find the exact filename....
//         	header('Pragma: public');
//         	header('Expires: 0');
//         	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
//         	header('Cache-Control: private', false); // required for certain browsers
//         	header('Content-Type: application/pdf');
        	
//         	header('Content-Disposition: attachment; filename="'. basename($filename) . '";');
//         	header('Content-Transfer-Encoding: binary');
//         	header('Content-Length: ' . filesize($filename));
        	
//         	readfile($filename);
        	
//         	exit;
        }
 
}
?>