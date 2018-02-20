<?php 
	require_once BLOGIC."/BLogic.php"; 
	require_once ROOT.'/Components/Controllers/PageWrapper.php';
	 
	class PortfolioList extends PageWrapper 
	{ 
		public function __construct($formData) 
		{ 
			$templateName = $this->templateNameBasedOnDevice("PortfolioList", array());
			parent::__construct($formData, $templateName);
		}

		
		public function selectPortfolioAction(){
            $id = doDecrypt($this->formValueForKey("selectedInvementPortfolioID"));
            
            debugln("this is the port id: " . $id);
            debugln("undecrypted: " . doDecrypt(implode($this->formData)));
            if ($id)
            {
                $portfolio = BLGenericRecord::recordMatchingKeyAndValue("InvestmentPortfolio", "InvestmentPortfolioID", $id);
				
                $_SESSION["investmentPortfolioID"] = $portfolio->vars["investmentPortfolioID"];
            }
            else {
                $this->errorMessage = "The asset you tried to select could not be found!";
            }
        }
	} 
	
?>
