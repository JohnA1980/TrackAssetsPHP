<?php 
	//
	// User.php
	// 
	// Created on 2014-11-01 @ 10:02 am.
	 
	require_once BLOGIC."/BLogic.php"; 
	 
	class User extends BLGenericRecord 
	{ 
		public function __construct($dataSource = null) 
		{ 
			parent::__construct($dataSource); 
            
            $this->defineRelationship(new BLToManyRelationship("assetJoin", $this, "AssetUser", "userID", "userID"));
            $this->defineRelationship(new BLFlattenedRelationship("assets", $this, "Asset", "assetID", "assetID", "assetJoin"));
            $this->defineRelationship(new BLToManyRelationship("portfolios", $this, "InvestmentPortfolio", "userID", "ownerUserID", new BLKeyValueQualifier("deleted", OP_EXACT_MATCH, NULL_VALUE)));
            $this->defineRelationship(new BLToManyRelationship("portfoliousers", $this, "InvestmentPortfolioUser", "userID", "userID"));
		
		} 
		
		public function tableName() 
		{ 
			return "User"; 
		} 
		 
		public function pkNames() 
		{ 
			return "userID"; 
		}
		
		
		//TODO: fix this shizz up
		public function firstPortfolio() {
            // arrayValueForRelationship() is a useful alternative function that will always
            // return an array, even if the relationship result is null.
			$portfolios =  $this->arrayValueForRelationship("portfolios");
			if (count($portfolios) > 0) {
				return $portfolios[0];
			}
			else{
				$portfolioUsers = $this->valueForRelationship("portfoliousers");
				debugln("Pre Got herass");
				debugln("Pre Got here = " . sizeof($portfolioUsers));
				if( sizeof($portfolioUsers) > 0){
					debugln("Got here");
					$portfolioID = $portfolioUsers[0]->vars["investmentPortfolioID"];
					
					$portfolio = BLGenericRecord::recordMatchingKeyAndValue("InvestmentPortfolio", "investmentPortfolioID", $portfolioID);
						
					return $portfolio;
				}
				
			} 
				
		}
		
		public function firstPortfolioID() {
            $portfolio = $this->firstPortfolio();
			if (is_null($portfolio)) {
				return null;
			}
			return $portfolio->vars["investmentPortfolioID"];
		}
		
		
		public function nonDeletedInvestmentPortfolios() {
            // NOTE: I've added a qualifier to the relationship so deleted records are always filtered.
			return $this->valueForRelationship("portfolios");
		}
		
		/*
			Override this method if you have any database fields which should not
			be modified or saved back to the server. This provides only 'quiet' protection.
			It does not pass any errors or warnings back if field data has changed, it merely
			ommits the fields from the save request.
		*/
		public function readOnlyAttributes()
		{
			return array("userID");
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
	} 
?>
