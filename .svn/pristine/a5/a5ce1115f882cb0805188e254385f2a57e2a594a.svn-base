<?php 
	//
	// InvestmentPortfolio.php
	// 
	// Created on 2014-11-01 @ 10:02 am.
	 
	require_once BLOGIC."/BLogic.php"; 
	 
	class InvestmentPortfolio extends BLGenericRecord 
	{ 
		public function __construct($dataSource = null) 
		{ 
			parent::__construct($dataSource);

			$this->defineRelationship(new BLToManyRelationship("assets", $this, "Asset", "investmentPortfolioID", "investmentPortfolioID"));
			$this->defineRelationship(new BLToManyRelationship("pettycashentries", $this, "PettyCashEntry", "investmentPortfolioID", "investmentPortfolioID"));
		} 
	 
		public function tableName() 
		{ 
			return "InvestmentPortfolio"; 
		} 
		 
		public function pkNames() 
		{ 
			return "investmentPortfolioID"; 
		}
		
		/*
			Override this method if you have any database fields which should not
			be modified or saved back to the server. This provides only 'quiet' protection.
			It does not pass any errors or warnings back if field data has changed, it merely
			ommits the fields from the save request.
		*/
		public function readOnlyAttributes()
		{
			return array("investmentPortfolioID");
		}	
		

		public function nonDeletedAssets()
		{
				 
				$quals = array();
				
				$quals[] = new BLKeyValueQualifier("investmentPortfolioID", OP_EQUAL, $this->vars["investmentPortfolioID"]);
				$quals[] = new BLKeyValueQualifier("deleted", OP_EXACT_MATCH, NULL_VALUE);
		
				$order = array("name" => ORDER_ASCEND);
				
				return  BLGenericRecord::find("Asset", new BLAndQualifier($quals), $order);
			
		}
		
		
		public function nonDeletedIncomeLineItems(){
			$allIncomeLineItems = array();

			if(count($this->nonDeletedAssets()) > 0){
				$assets = $this->nonDeletedAssets();
		
				foreach ($assets as $asset){
					$allIncomeLineItems = array_merge($allIncomeLineItems, $asset->nonDeletedIncomeLineItems());
				}
			}
		
			echo ("number of income : " . count($allIncomeLineItems));
			return $allIncomeLineItems;
				
		}
		
		public function unpaidIncomeLineItems(){
			$allIncomeLineItems = array();
		
			if(count($this->nonDeletedAssets()) > 0){
				$assets = $this->nonDeletedAssets();
		
				foreach ($assets as $asset){
					$allIncomeLineItems = array_merge($allIncomeLineItems, $asset->unpaidIncomeLineItems());
				}
			}
		
			echo ("number of income : " . count($allIncomeLineItems));
			return $allIncomeLineItems;
		
		}
		
		
		public function sortedPettyCashEntries(){
			return $this->nonDeletedPettyCashEntries(array("transactionDate" => ORDER_DESCEND, "pettyCashEntryID" => ORDER_DESCEND));
		}
		
		
		

		public function nonDeletedPettyCashEntries($order = null){
			$qual = new BLAndQualifier(array(
					new BLKeyValueQualifier("investmentPortfolioID", OP_EQUAL, $this->vars["investmentPortfolioID"]),
					new BLKeyValueQualifier("deleted", OP_EXACT_MATCH, NULL_VALUE),
						
			));
			$found = BLGenericRecord::find("PettyCashEntry", $qual, $order);
			debugln("found non deleted PettyCashEntry: " + count($found));
			
			return $found;
		}
		
		/* 	Override this method if you have any database fields that deal in
			raw binary data.
			WARNING: attributes returned from here do not get escaped when working with the
			MySQLDataSource so be very very careful on trusting the contents of the data
			you are working with!
		*/
		/* public function binaryAttributes()
		{
			return array();
		}
		*/
		/*
		public function awakeFromFetch()
		{
			
		}	 
		
		public function validateForSave()
		{
			
		}
		*/
		
		/*
		// amount of time any apc auto-caching will store a copy for records of this entity
		public function cacheTTL()
		{
			return 60;
		}
		*/
		
		//TODO: might be a MYSQL way to do this
		public function runningTotalForPettyCash($pettyCash){
			$totalSum = 0;
			$allPettyCash = $this->nonDeletedPettyCashEntries(array("transactionDate" => ORDER_ASCEND, "pettyCashEntryID" => ORDER_ASCEND));

			foreach ($allPettyCash as $itemForPettyCash){
				$totalSum = $totalSum + $itemForPettyCash->vars["amount"];
				
				if($itemForPettyCash->vars["pettyCashEntryID"] == $pettyCash->vars["pettyCashEntryID"]){
					break;
				}
			}
		
			return $totalSum;
		}
		
		public function theWholeTotal(){
			$totalSum = 0;
			$allPettyCash = $this->nonDeletedPettyCashEntries(array("transactionDate" => ORDER_ASCEND, "pettyCashEntryID" => ORDER_ASCEND));
			
			foreach ($allPettyCash as $itemForPettyCash){
				$totalSum = $totalSum + $itemForPettyCash->vars["amount"];
			}
			
			return $totalSum;
		}
		
	} 
?>
