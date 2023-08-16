<?php 
	//
	// ExpenseLineItem.php
	// 
	// Created on 2014-11-01 @ 10:01 am.
	 
	require_once BLOGIC."/BLogic.php"; 
	 
	class ExpenseLineItem extends BLGenericRecord 
	{ 
		public function __construct($dataSource = null) 
		{ 
			parent::__construct($dataSource); 
			
			$this->defineRelationship(new BLToOneRelationship("expensetype", $this, "ExpenseType", "expenseTypeID", "expenseTypeID"));
			
            // @John: put your default value fields right in the constructor. Records fecthed from the database
            // will overwrite it.
            $this->vars["created"] = date("Y-m-d H:i:s");
		} 
		
		public function tableName(): string
		{ 
			return "ExpenseLineItem"; 
		} 
		 
		public function pkNames(): array|string
		{ 
			return "expenseLineItemID"; 
		}
		
		public function expenseTypeDisplay(){
			
            /* @John:
                There's nothing wrong with the code you have below.. But it can be done in a much simpler fashion :-)
            */
            
            /*$expenseType = $this->valueForRelationship("expensetype");
			
			if(!is_null($expenseType)){
				
				return $expenseType->vars["name"];
			}
			else{
				return "";
			}*/
            
            // @John: How about the WO Way! A null relationship still results in an empty string.
            return $this->valueForKeyPath("expensetype.name");
		}
		

		/*
			Override this method if you have any database fields which should not
			be modified or saved back to the server. This provides only 'quiet' protection.
			It does not pass any errors or warnings back if field data has changed, it merely
			ommits the fields from the save request.
		*/
		public function readOnlyAttributes(): array
		{
			return array("expenseLineItemID");
		}	
		
		
		public function expenseDate(){
			return $this->vars["expenseDate"];
		}
		
		public function expenseDateObject(){
			if(!is_null(expenseDate())){
				return new DateTime(expenseDate());
			}
			return null;
		}
		
		public function expenseDateFormatted()
		{
			$date = $this->expenseDate();
			return $date ? date("d/m/Y", strtotime($date)) : "";
		}
		
		
		public function setExpenseDateFormatted($value)
		{
			if ($value) {
				// This nifty line will transforms a dd/mm/yyyy into a yyyy-mm-dd.
				$value = $this->preProcessDateStringForSave($value);
			}
			$this->vars["expenseDate"] = $value;
		}
		
		public function safeExpenseTypeName(){
			$expenseType = $this->valueForRelationship("expensetype");
			
			if($expenseType){
				return $expenseType->vars["name"];
			}
			else{
				return "Unknown Expense Type";
			}
		}
		
		public function __toString(){
			return "ExpenseLineItem: " . $this->vars["expenseLineItemID"] . " Amount: " . $this->vars["amount"];
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
		
		public function preProcessDateStringForSave($dateString){
			return implode("-", array_reverse(explode("/", $dateString)));
		}
	} 
?>
