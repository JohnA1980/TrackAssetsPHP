<?php 
	require_once BLOGIC."/BLogic.php"; 
	require_once ROOT.'/Components/Controllers/PageWrapper.php';
	 
	class AssetDetails extends PageWrapper 
	{ 
        protected $currentView;
        
        
        protected $asset;
        protected $selectedExpenseLineItem;

		public function __construct($formData) 
		{ 
			// $templateName = $this->templateNameBasedOnDevice("AssetDetails", array());
			$templateName = "AssetDetails";
			parent::__construct($formData, $templateName);
            
			$this->currentView = doDecrypt($this->formValueForKey("currentView"));
			if (! $this->currentView)
				$this->currentView = "Details";
			
			$this->currentChart = doDecrypt($this->formValueForKey("currentChart"));
			if (! $this->currentChart)
				$this->currentChart = "Net Income Chart";
			
		

		} 
        
		public function currentView(){
			return $this->currentView;
		}
        
        
        public function asset()
        {
            if (! $this->asset)
            {
                $id = doDecrypt($this->formValueForKey("selectedID"));
                debugln("ID : " . $id);
                if ($id) {
                    $this->asset = BLGenericRecord::recordMatchingKeyAndValue("Asset", "assetID", $id);
                }
                else {
                    $this->asset = BLGenericRecord::newRecordOfType("Asset");
                    $this->asset->vars["investmentPortfolioID"] = $_SESSION["investmentPortfolioID"];
                    $this->asset->save();
                }
            }
            return $this->asset;
        }
        
        public function save()
        {
            $this->processFormValueKeyPathsForSave();
            try {
                $this->asset()->save();
                
                if($this->selectedIncomeSource){
                	$this->selectedIncomeSource()->save();
                }
                
                if($this->selectedIncomeLineItem){
                	$this->selectedIncomeLineItem()->save();
                }
                
                if($this->selectedExpenseLineItem){
                	$this->selectedExpenseLineItem()->save();
                }
                
                $page = $this->pageWithName("AssetList");
                $page->alertMessage = "Asset saved.";
                
            }
            catch (Exception $error) {
                debugln($error->getMessage());
                $this->errorMessage = "There was a problem saving the asset, please try again.";
            }
        }
                
        /************************* Expenses Tab here!!! **********************************/
        protected $allExpenseTypes;
        
        public function allExpenseTypes(){
        	if(!$this->allExpenseTypes){
        		$this->allExpenseTypes = BLGenericRecord::find("ExpenseType", null, null);
        	}			
        	return $this->allExpenseTypes;
        }
        
        public function selectedExpenseLineItem(){
        	if (! $this->selectedExpenseLineItem)
        	{
        		$id = doDecrypt($this->formValueForKey("selectedExpenseLineItemID"));
        		//echo "selectedExpenseLineItemID : " . $id;
        		if ($id) {
        			$this->selectedExpenseLineItem = BLGenericRecord::recordMatchingKeyAndValue("ExpenseLineItem", "expenseLineItemID", $id);
        		}
        	}
        	return $this->selectedExpenseLineItem;
        }

        public function createExpenseLineItem(){
        	$newExpenseLineItem = BLGenericRecord::newRecordOfType("ExpenseLineItem");
        	$newExpenseLineItem->vars["assetID"] = $this->asset()->vars["assetID"];
        	$newExpenseLineItem->save();
        	
        	$this->setFormValueForKey(doEncrypt($newExpenseLineItem->vars["expenseLineItemID"]), "selectedExpenseLineItemID");
        	
            // debuging statements? why not in the debug log?
        	//echo "new expense lineItem: " . $newExpenseLineItem->vars["assetID"];
        	//echo "new expense lineItem ID: " . $newExpenseLineItem->vars["expenseLineItemID"];        	
        	
        }
        
        public function resetExpenseLineItemForm(){
        	foreach ($this->formData as $key => $value)
        	{
        		if (BLStringUtils::startsWith($key, "selectedExpenseLineItem.")) {
        			$this->setFormValueForKey("", $key);
        		}
        	}
        }
        
        
        public function deleteExpenseLineItem(){
        	$expenseLineItemID = doDecrypt($this->formValueForKey("deleteExpenseLineItemID"));
        	 
        	//debugln("deleteExpenseLineItem: " . $expenseLineItemID);
        	if ($expenseLineItemID) {
        		debugln("going to deleted expenseLineItem : " . $expenseLineItemID);
        		$expenseLineItem = BLGenericRecord::recordMatchingKeyAndValue("ExpenseLineItem", "expenseLineItemID", $expenseLineItemID);
        		$expenseLineItem->vars["deleted"] = date("Y-m-d H:i:s");
        		$expenseLineItem->save();
        		
        		$this->setFormValueForKey(null, "selectedExpenseLineItemID");
        	}
        }
        
        
        public function hasSelectedExpenseLineItem(){
        	return !is_null($this->selectedExpenseLineItem());
        }
        
        public function incomeSources(){
            return $this->asset()->nonDeletedIncomeSources();
        }
        
        public function reportsChartArray(){
        	$reports = array("Expenses Summary Chart", "Net Income Chart");
        	return $reports;
        }
        
        public $fromMonth;
        public $toMonth;
        
        public function chartArrayTable(){
//         	return $chartTable= "[
//           ['Month', 'Rent', 'Expenses', 'Profit'],
//           ['Jan', 1500, 400, 1100],
//           ['Feb', 1600, 460, 1540],
//           ['Mar', 1000, 1120, -120],
//           ['Apr', 1500, 3000, -1500]
//         ]";
			return $this->asset()->financialYearSummaryAsString();
        }
        
        
        
        /***************** Income Tab code ***************/
        protected $selectedIncomeSource;
        protected $selectedIncomeLineItem;
        
        public function selectedIncomeSource(){
        	
        	if (!$this->selectedIncomeSource)
        	{
        		$selectedIncomeSourceID = doDecrypt($this->formValueForKey("selectedIncomeSourceID"));
        		debugln("selectedIncomeSource baby: " . $selectedIncomeSourceID);
        		if(is_null($selectedIncomeSourceID) && !is_null($this->asset()->firstIncomeSource())){
        			
        			$selectedIncomeSourceID = $this->asset()->firstIncomeSource()->vars["incomeSourceID"];
        			debugln("firstIncomeSource: " . $selectedIncomeSourceID);
        			$this->setFormValueForKey(doEncrypt($selectedIncomeSourceID), "selectedIncomeSourceID");
        			$this->setFormValueForKey(doEncrypt($selectedIncomeSourceID), "selectedIncomeSourceSelect");
        		}
        		
        		//echo "ID : " . $id;
        		if ($selectedIncomeSourceID) {
        			$this->selectedIncomeSource = BLGenericRecord::recordMatchingKeyAndValue("IncomeSource", "incomeSourceID", $selectedIncomeSourceID);
        		}
        	}
        	return $this->selectedIncomeSource;
        } 
        
        public function selectedIncomeLineItem()
        {
        	if (! $this->selectedIncomeLineItem)
        	{
        		$id = doDecrypt($this->formValueForKey("selectedIncomeLineItemID"));
        		if ($id) {
        			$this->selectedIncomeLineItem = BLGenericRecord::recordMatchingKeyAndValue("IncomeLineItem", "incomeLineItemID", $id);
        		}
        	}
        	return $this->selectedIncomeLineItem;
        }
        
        public function safeNameAndOccupant(){
        	if($this->selectedIncomeSource()){
        		return $this->selectedIncomeSource()->safeNameAndOccupant();
        	} 
        	else{
        		return "";
        	}
        }

		public function processUpload(): void
		{
			$resp = ['result' => 0, 'msg' => ''];
			try
			{
				if (! $token = $this->formValueForKey('token')) {
					$this->output_json(['result' => 1, 'msg' => 'Missing token']);
					return;
				}
			
				$qual = new BLAndQualifier([
					new BLKeyValueQualifier('recID', OP_EXACT_MATCH, NULL_VALUE),
					new BLKeyValueQualifier('token', OP_EQUAL, $token),
					new BLKeyValueQualifier('type', OP_EQUAL, 'LineItemAttachment')
				]);
				if (! $file = blrecord::first('LineItemAttachment', qualifier:$qual)) {
					$this->output_json(['result' => 1, 'msg' => 'No matching photo for given token']);
				}
				
				$file->setFields([
					'recID' => $this->selectedIncomeLineItem()->field('id')
				]);
				$file->save();
				
				$resp['html'] = $this->pageWithName('IncomeLineItemAttachmentView')->setLineItem($this->selectedIncomeLineItem())->asString();
			}
			catch (Exception $error) {
				bldebug::ln($error);
				$resp = ['result' => 1, 'msg' => $error->getMessage()];
			}
			
			$this->output_json($resp);
		}
        
        protected $selectIncomeSource;
        
        public function selectIncomeSource()
        {
        	$selectedIncomeSourceSelect = $this->formValueForKey("selectedIncomeSourceSelect");
        	
        	$this->setFormValueForKey(doEncrypt($selectedIncomeSourceSelect), "selectedIncomeSourceID");
        	debugln("selectedIncomeSourceID: " . doDecrypt($this->formValueForKey("selectedIncomeSourceID")));
        }
        
     
        public function createIncomeLineItem() {
        	
        	//$lineItem = BLGenericRecord::newRecordOfType("IncomeLineItem");
            
            //$sourceID = doDecrypt($this->formValueForKey("selectedIncomeSourceID"));
            
        	$sourceID = $this->selectedIncomeSource()->vars["incomeSourceID"];
        	
            debugln("this is the sourceID: " . $sourceID);
            
            if (!$sourceID){
            	$this->errorMessage = "There was an error creating the invoice line item. Please try again.";
            	debugln($this->errorMessage);
            	return;
            }
            	
            $newLineItem = $this->selectedIncomeSource()->autoAddIncomeLineItem();
            
           	//$lineItem->vars["incomeSourceID"] = $sourceID;
        	//debugln("this is the sourceID: " . $sourceID);
            
        	$this->processFormValueKeyPathsForSave();
            
            
             //   @John: Always wrap save() calls in a try-catch. I've set the data sources to throw exceptions when 
             //   a save fails. This allows you to gracefully fall back with a message to user and handle it 
            //    as needed.
            
            try {
                $newLineItem->save();
                debugln("this is the new incomelineItemID: " . $newLineItem->vars["incomeLineItemID"]);
                $savedID = doEncrypt($newLineItem->vars["incomeLineItemID"]);
                
                $this->setFormValueForKey($savedID, "selectedIncomeLineItemID");
                //also have to set the local variable of incomelineItem back to null; 
                //This might be better somewhere else?
                $this->selectedIncomeLineItem = null;
                
                $this->resetIncomeLineItemForm();
            }
        	catch (Exception $error) {
        	    $this->errorMessage = "There was an error creating the invoice line item. Please try again.";
                debugln($error->getMessage());
        	}
        }
        
        public function markAsPaid(){
        	
        
        	$id = doDecrypt($this->formValueForKey("incomeToBeMarkedAsPaidID"));
        	if ($id) {
        		debugln("marked As Paid ID: " . $id);
        		$theIncomeLineItem = BLGenericRecord::recordMatchingKeyAndValue("IncomeLineItem", "incomeLineItemID", $id);
        		$theIncomeLineItem->markAsPaid();
        		$theIncomeLineItem->save();
        		
        	
        		$this->createIncomeLineItem();
        	}
        }
        
        /*
            Because your editor form is inline you need to clear out previous form values. formValueForKeyPath() stores
            cached values so user changes are not lost on page refresh.
        */
        public function resetIncomeLineItemForm()
        {
            foreach ($this->formData as $key => $value)
            {
                if (BLStringUtils::startsWith($key, "selectedIncomeLineItem.")) {
                	debugln("Restting form value to null : " . $key);
                    $this->setFormValueForKey("", $key);
                }
            }
        }
        
      
        public function deleteIncomeLineItem(){
        	$incomeLineItemID = doDecrypt($this->formValueForKey("deleteIncomeLineItemID"));
        	
        	debugln("deleteIncomeLineItemID: " . $incomeLineItemID);
        	if ($incomeLineItemID) {
        		debugln("going to deleted inclineItem : " . $incomeLineItemID);
        		$incomeLineItem = BLGenericRecord::recordMatchingKeyAndValue("IncomeLineItem", "incomeLineItemID", $incomeLineItemID);
        		$incomeLineItem->vars["deleted"] = date("Y-m-d H:i:s");
        		$incomeLineItem->save();
        		$this->setFormValueForKey(null, "selectedIncomeLineItemID");
        	}
        }
        
        
        
        /*************** current Charts tab ****************/
        public $currentChart;
        protected $financialYearMonths;
        protected $startDateExpenseSummary;
        protected $endDateExpenseSummary;
        
        public function selectCurrentChart()
        {
        	$this->currentChart = $this->formValueForKey("chartSelection");
        	
        	debugln("this is the currentChart selection: " .$this->currentChart );
        	//$this->setFormValueForKey( $currentChart, "currentChart");
        }
        
        
        public function financialYearMonths(){
        	
        	if(!$this->financialYearMonths){
				$this->financialYearMonths = financialYearMonths();
        	}
        	return $this->financialYearMonths;
        }
        
        public function currentExpenseSummaryJSON(){
        	
        	$this->startDateExpenseSummary = doDecrypt($this->formValueForKey("startDateExpenseSummary"));
        	$this->endDateExpenseSummary = doDecrypt($this->formValueForKey("endDateExpenseSummary"));
        	
        	$currentDate = new DateTime();
        	
        	if(!$this->startDateExpenseSummary){
        		$this->startDateExpenseSummary = $currentDate->format('Y-m-d');
        	}
        	
        	if(!$this->endDateExpenseSummary){
        		$this->endDateExpenseSummary = $currentDate->format('Y-m-d');
        	}
        	
        	debugln("startDateExpenseSummary: " . $this->startDateExpenseSummary . " endDateExpenseSummary" . $this->endDateExpenseSummary);
        	$startDate = DateTime::createFromFormat('Y-m-d', $this->startDateExpenseSummary);
        	$endDate = DateTime::createFromFormat('Y-m-d', $this->endDateExpenseSummary);

        	debugln("startDateExpenseSummary: " .$startDate->format("Y-m-d") . " endDateExpenseSummary" . $this->endDateExpenseSummary);
			return $this->asset()->expenseSummaryJSON($startDate, $endDate);
        	
        }
	} 
	
	
	

?>