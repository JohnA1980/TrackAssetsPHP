<?php 
	require_once BLOGIC."/BLogic.php"; 
	require_once ROOT.'/Components/Controllers/PageWrapper.php';
	 
	class PendingIncomeSummary extends PageWrapper 
	{ 
		protected $investmentPortfolio;
		
		public function __construct($formData) 
		{ 
			$templateName = $this->templateNameBasedOnDevice("PendingIncomeSummary", array());
			parent::__construct($formData, $templateName);
		} 
		
		public function allAssets(){
			
			
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
		
		
	}

	
	
?>
