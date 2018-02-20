<?php 
	require_once BLOGIC."/BLogic.php"; 
	require_once ROOT.'/Components/Controllers/PageWrapper.php';
	 
	class AssetList extends PageWrapper 
	{ 
		public function __construct($formData) 
		{ 
			$templateName = $this->templateNameBasedOnDevice("AssetList", array());
			parent::__construct($formData, $templateName);
			$firstDate = DateTime::createFromFormat('d/m/Y', '31/01/2015');
			isExactlyOneMonthFromDate($firstDate, DateTime::createFromFormat('d/m/Y', '28/02/2015'));
			debugln("firstDate: " . $firstDate->format("d/m/y"));
		
		} 
        
        public function delete()
        {
            $id = doDecrypt($this->formValueForKey("selectedID"));
            if ($id)
            {
                try {
                    $asset = BLGenericRecord::recordMatchingKeyAndValue("Asset", "assetID", $id);
                    $asset->vars["deleted"] = date("Y-m-d H:i:s");
                    $asset->save();
                }
                catch (Exception $error) {
                    debugln($error->getMessage());
                    $this->errorMessage = "There was a problem deleting the selected asset, please try again.";
                }
            }
            else {
                $this->errorMessage = "The asset you tried to delete could not be found!";
            }
        }
	} 
?>
