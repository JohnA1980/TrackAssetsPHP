<?php 
	require_once BLOGIC."/BLogic.php"; 
	require_once ROOT.'/Components/Controllers/PageWrapper.php';
	 
	class IncomeSourceEdit extends PageWrapper 
	{ 
        public $currentView;
        
        protected $asset;
        protected $frequencyTypes;
        
		public function __construct($formData) 
		{ 
			$templateName = $this->templateNameBasedOnDevice("IncomeSourceEdit", array());
			parent::__construct($formData, $templateName);
           
		} 
        
        public function asset()
        {
            if (! $this->asset)
            {
                $id = doDecrypt($this->formValueForKey("selectedID"));
                if ($id) {
                    $this->asset = BLGenericRecord::recordMatchingKeyAndValue("Asset", "assetID", $id);
                }
            }
            return $this->asset;
        }
        
        public function saveAction()
        {
            $this->processFormValueKeyPathsForSave();
            try {
            	if($this->selectedIncomeSource()){
                	$this->selectedIncomeSource()->save();
            	}
            	
            	$nextPage = doDecrypt($this->formValueForKey("nextPage"));
				if($nextPage){
					return $this->pageWithName($nextPage);	
				}           	

                return $this;
            }
            catch (Exception $error) {
                debugln($error->getMessage());
                $this->errorMessage = "There was a problem saving the asset, please try again.";
            }
        }
	
        public function incomeSources() {
        	return $this->asset()->arrayValueForRelationship("incomesources");
        }
        
        public function backAction(){
        	$this->processFormValueKeyPathsForSave();
        	try {
        		$this->selectedIncomeSource()->save();
        		return $page;
        	}
        	catch (Exception $error) {
        		debugln($error->getMessage());
        		$this->errorMessage = "There was a problem saving data, please try again.";
        	}
        }
        
        public function cancelAction(){
        	$returnPage = $this->pageWithName("AssetDetails");
        	//$returnPage.setSelectedID($this->formValueForKey("selectedID"));
        	echo("selected ID  : " + doEncrypt($this->formValueForKey("selectedID")));
        	$returnPage->setFormValueForKey(doEncrypt($this->formValueForKey("selectedID")), "selectedExpenseLineItemID");
        	return $returnPage;
        }
	
        protected $selectedIncomeSource;
		
		public function selectedIncomeSource() 
        {
            if (! $this->selectedIncomeSource)
            {
    			$id = doDecrypt($this->formValueForKey("selectedIncomeSourceID"));			
    			if ($id) {
    				$this->selectedIncomeSource = BLGenericRecord::recordMatchingKeyAndValue("IncomeSource", "incomeSourceID", $id);
    			}
            }
            if(is_null($this->selectedIncomeSource)){
            	debugln("selected Income source is null");
            }
            else{
            	debugln("selected Income source is not null");
            }
	        return $this->selectedIncomeSource;
		}
        
        public function saveSelectedIncomeSource() {
            $this->processFormValueKeyPathsForSave(array(), array(), false, "selectedIncomeSource.");
            try {
                $this->selectedIncomeSource()->save();
                
                // now clear out the selected ID so the edit fields go away.
                $this->selectedIncomeSource = null;
                $this->setFormValueForKey("", "selectedIncomeSourceID");
                
                // present user with a confirmation so they know the save was successfull.
                $this->alertMessage = "Changes to income source have been saved.";
            }
            catch (Exception $error) {
                debugln($error->getMessage());
                $this->errorMessage = "Your changes could not be saved, please try again.";
            }
        }
		
		
		public function frequencyTypes()
        {
			if (!$this->frequencyTypes) {
				$this->frequencyTypes = FrequencyType::allFrequencyTypes();
			}
		
			return $this->frequencyTypes;
		}
		
		public function createIncomeSource()
        {
			if ($this->asset()){
				debugln("creating income source", 2);
				$newIncomeSource = BLGenericRecord::newRecordOfType("IncomeSource");
				$newIncomeSource->vars["assetID"] = $this->asset()->vars["assetID"];
			
				$this->processFormValueKeyPathsForSave();

				try {
					$newIncomeSource->save();
				
					$savedID = doEncrypt($newIncomeSource->vars["incomeSourceID"]);
					$this->setFormValueForKey($savedID, "selectedIncomeSourceID");
				}
				catch (Exception $error) {
					$this->errorMessage = "There was an error creating the Income Source. Please try again.";
					debugln($error->getMessage());
				}
			}
			debugln("done creating income source", 2);
		}
		
		
		/*
		 Because your editor form is inline you need to clear out previous form values. formValueForKeyPath() stores
		 cached values so user changes are not lost on page refresh.
		 */
		public function resetIncomeSourceForm()
		{
			foreach ($this->formData as $key => $value)
			{
				if (BLStringUtils::startsWith($key, "selectedIncomeSource.")) {
					$this->setFormValueForKey("", $key);
				}
			}
		}
		
		
			
		public function deleteIncomeSource(){
			$incomeSourceID = doDecrypt($this->formValueForKey("deleteIncomeSourceID"));
		
			//debugln("deleteExpenseLineItem: " . $expenseLineItemID);
			if ($incomeSourceID) {
				debugln("going to deleted incomeSource : " . $incomeSourceID);
				$incomeSource = BLGenericRecord::recordMatchingKeyAndValue("IncomeSource", "incomeSourceID", $incomeSourceID);
				$incomeSource->vars["deleted"] = date("Y-m-d H:i:s");
				$incomeSource->save();
		
				
				$this->setFormValueForKey(null, "selectedIncomeSourceID");
			}
		}

		public function selectOccupantForRoom(){
			$incomeSourceID = doDecrypt($this->formValueForKey("selectedOccupantID"));
			
			//debugln("deleteExpenseLineItem: " . $expenseLineItemID);
			if ($incomeSourceID) {
				debugln("going to deleted incomeSource : " . $incomeSourceID);
				$incomeSource = BLGenericRecord::recordMatchingKeyAndValue("IncomeSource", "incomeSourceID", $incomeSourceID);
				$incomeSource->vars["deleted"] = date("Y-m-d H:i:s");
				$incomeSource->save();
			
			
				$this->setFormValueForKey(null, "selectedIncomeSourceID");
			}
		}
	}
?>
