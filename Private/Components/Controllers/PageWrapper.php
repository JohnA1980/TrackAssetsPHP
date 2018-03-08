<?php 
	require_once BLOGIC."/BLogic.php"; 
	require_once ROOT."/Components/Controllers/SessionController.php";
	require_once BLOGIC."/Utils/FormUtils.php";
	require_once ROOT."/Utils/DateUtils.php";
		
	class PageWrapper extends SessionController 
	{ 
		protected $innerTemplate;
		protected $hasInvalidTransactionID = false;
		
		public $errorMessage;
		public $alertMessage;
        
		protected $currentInvestmentPortfolio;
		
		public function __construct($formData, $innerTemplate) 
		{ 
			
			
			parent::__construct($formData, "PageWrapper"); 
			
			
			$this->innerTemplate = $this->templateForName($innerTemplate);
			$this->innerTemplate->set("controller", $this);
            
            flushJS();
            addJS("js/ajax.js");
            addJS("js/jquery/jquery.js");
            addJS("js/jquery/jquery-ui.js");
            addCSS("js/jquery/jquery-ui.css");
            addCSS("js/jquery/jquery-ui.theme.css");

            addCSS("css/bootstrap.min.css");
            addCSS("css/layout.css");
            
            
		} 
		
		// prevent page reloads and back button from repeating previous actions.
		public function handleRequest()
		{
			$page = parent::handleRequest();
			if ($page)
				return $page;
			if (empty($_SESSION["transactionID"]) || $this->formValueForKey("transactionID") != $_SESSION["transactionID"])
			{
				$this->hasInvalidTransactionID = true;
			}
			$_SESSION["transactionID"] = uniqid();
		}
		
		public function renderInnerTemplate()
		{
			echo $this->innerTemplate->fetch();
		}
		
		public function hasInvalidTransactionID()
		{
			return $this->hasInvalidTransactionID;
		}
		
		public function formMethod()
		{
			return "post";
		}
		
		public function currentInvestmentPortfolio()
        {
            /*
                @John:
                Something you can consider doing here is storing the whole generic record as a dictionary
                in the session. It saves a MySQL fetch on ever page refresh, but you need to make sure that
                anytime the record is saved, you update the session dictionary.
                To see this in action check out what I have done in SessionController with the user.
            */
			if (! $this->currentInvestmentPortfolio)
			{
				$id = $_SESSION["investmentPortfolioID"];
				if ($id) {
					$this->currentInvestmentPortfolio = BLGenericRecord::recordMatchingKeyAndValue("InvestmentPortfolio", "investmentPortfolioID", $id);
				}
			}
			return $this->currentInvestmentPortfolio;
		}
	} 
?>
