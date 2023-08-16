<?php 
	//
	// Asset.php
	// 
	// Created on 2014-11-01 @ 10:00 am.
	 
	require_once BLOGIC."/BLogic.php"; 
	require_once ROOT."/Utils/DateUtils.php";
 
	class PettyCashEntry extends BLGenericRecord 
	{ 
		public function __construct($dataSource = null) 
		{ 
			parent::__construct($dataSource); 

			$this->defineRelationship(new BLToOneRelationship("createdby", $this, "User", "userID", "userID"));
			$this->defineRelationship(new BLToOneRelationship("investmentportfolio", $this, "InvestmentPortfolio", "investmentPortfolioID", "investmentPortfolioID"));
		} 
	 
		public function tableName(): string
		{ 
			return "PettyCashEntry"; 
		} 
		 
		public function pkNames(): array|string
		{ 
			return "pettyCashEntryID"; 
		}


		public function transactionDate(){
			return $this->vars["transactionDate"];
		}
		
		public function transactionDateObject(){
			if(!is_null(transactionDate())){
				return new DateTime(transactionDate());
			}
			return null;
		}
		
		/*
		 @John: If you specify a function name at the end of a keypath in valueForKeyPath, it will find
		 and call the method!
		
		 processFormValueKeyPathsForSave() will also check for and use setter methods to!
		 */
		
		public function transactionDateFormatted()
		{
			$date = $this->transactionDate();
			return $date ? date("d/m/Y", strtotime($date)) : "";
		}
		
		
		public function setTransactionDateFormatted($value)
		{
			if ($value) {
				// This nifty line will transforms a dd/mm/yyyy into a yyyy-mm-dd.
				$value = implode("-", array_reverse(explode("/", $value)));
			}
			$this->vars["transactionDate"] = $value;
		}
		
		public function runningTotal(){
			return $this->valueForRelationship("investmentportfolio")->runningTotalForPettyCash($this);
		}

		
		
	} 
?>
