<?php 
	require_once BLOGIC."/BLogic.php"; 
	require_once ROOT.'/Components/Controllers/PageWrapper.php';
	 
	class PettyCashList extends PageWrapper 
	{ 
		protected $investmentPortfolio;
		
		public function __construct($formData) 
		{ 
			$templateName = $this->templateNameBasedOnDevice("PettyCashList", array());
			parent::__construct($formData, $templateName);
		} 
				
		//TODO: someone save the selected Investment Portfolio in session??
		public function investmentPortfolio()
		{
			if (! $this->investmentPortfolio)
			{
				//$id = doDecrypt($this->formValueForKey("selectedID"));
				$investmentPortfolioID = $_SESSION["investmentPortfolioID"];
				//echo "ID : " . $id;
				if ($investmentPortfolioID) {
					$this->investmentPortfolio = BLGenericRecord::recordMatchingKeyAndValue("InvestmentPortfolio", "investmentPortfolioID", $investmentPortfolioID);
				}
			}
			return $this->investmentPortfolio;
		}
		
		protected $selectedPettyCashEntry;
		
		public function selectedPettyCashEntry()
		{
			debugln("1 PettyCash ID " . doDecrypt($this->formValueForKey("selectedPettyCashEntryID")));
			if (! $this->selectedPettyCashEntry)
			{
				$id = doDecrypt($this->formValueForKey("selectedPettyCashEntryID"));
				debugln("PettyCash ID = " + $id);
				if ($id) {
					$this->selectedPettyCashEntry = BLGenericRecord::recordMatchingKeyAndValue("PettyCashEntry", "pettyCashEntryID", $id);
				}
			}
			return $this->selectedPettyCashEntry;
		}
		
		public function createPettyCash(){
			$newPettyCashEntry = BLGenericRecord::newRecordOfType("PettyCashEntry");
			$newPettyCashEntry->vars["investmentPortfolioID"] = $this->investmentPortfolio()->vars["investmentPortfolioID"];
			$newPettyCashEntry->save();
			 
			$this->setFormValueForKey(doEncrypt($newPettyCashEntry->vars["pettyCashEntryID"]), "selectedPettyCashEntryID");
		}
		
		public function deletePettyCash(){
			$pettyCashEntryID = doDecrypt($this->formValueForKey("deletePettyCashEntryID"));
		
			//debugln("deleteExpenseLineItem: " . $expenseLineItemID);
			if ($pettyCashEntryID) {
				debugln("going to deleted petty Cash Entry : " . $pettyCashEntryID);
				$pettyCashEntry = BLGenericRecord::recordMatchingKeyAndValue("PettyCashEntry", "pettyCashEntryID", $pettyCashEntryID);
				$pettyCashEntry->vars["deleted"] = date("Y-m-d H:i:s");
				$pettyCashEntry->save();
		
				$this->setFormValueForKey(null, "selectedPettyCashEntryID");
			}
		}
		
		public function saveAction()
		{
			$this->processFormValueKeyPathsForSave();
			try {
				if($this->selectedPettyCashEntry()){
					$this->selectedPettyCashEntry()->save();
				}
		
			}
			catch (Exception $error) {
				debugln($error->getMessage());
				$this->errorMessage = "There was a problem saving the asset, please try again.";
			}
		}
		
		public function resetPettyEntryForm(){
			foreach ($this->formData as $key => $value)
			{
				if (BLStringUtils::startsWith($key, "selectedPettyCashEntry.")) {
					$this->setFormValueForKey("", $key);
				}
			}
		}
	}

	
?>