<?php 
	//
	// Occupant.php
	// 
	// Created on 2014-11-01 @ 10:02 am.
	 
	require_once BLOGIC."/BLogic.php"; 
	 
	class Occupant extends BLGenericRecord 
	{ 
		public function __construct($dataSource = null) 
		{ 
			$this->defineRelationship(new BLToManyRelationship("documents", $this, "File", "occupantID", "occupantID"));
			parent::__construct($dataSource); 
		} 
	 
		public function tableName(): string
		{ 
			return "Occupant"; 
		} 
		 
		public function pkNames(): array|string
		{ 
			return "occupantID"; 
		}
		
		/*
			Override this method if you have any database fields which should not
			be modified or saved back to the server. This provides only 'quiet' protection.
			It does not pass any errors or warnings back if field data has changed, it merely
			ommits the fields from the save request.
		*/
		public function readOnlyAttributes(): array
		{
			return array("occupantID");
		}	
		
		public function fullName(){
			$given = $this->vars["given"];
			$surname = $this->vars["surname"];
			
			$fullName = "";
			
			if(!is_null($given)){
				$fullName = $given;
			}
			
			if(!is_null($surname)){
				$fullName = $fullName." ".$surname;
			}
				
			return $fullName;	
		}
		

		public function startDate(){
			return $this->vars["startDate"];
		}
		
		public function startDateObject(){
			if(!is_null($this->startDate())){
				return new DateTime($this->startDate());
			}
			return null;
		}
		
		/*
		 @John: If you specify a function name at the end of a keypath in valueForKeyPath, it will find
		 and call the method!
		
		 processFormValueKeyPathsForSave() will also check for and use setter methods to!
		 */
		
		public function startDateFormatted()
		{
			$date = $this->startDate();
			return $date ? date("d/m/Y", strtotime($date)) : "";
		}
		
		
		public function setStartDateFormatted($value)
		{
			if ($value) {
				// This nifty line will transforms a dd/mm/yyyy into a yyyy-mm-dd.
				$value = implode("-", array_reverse(explode("/", $value)));
			}
			$this->vars["startDate"] = $value;
		}
		
		public function nonDeletedDocuments($order = null){
			 debugln("nonDeletedDocuments");
			$qual = new BLAndQualifier(array(
					new BLKeyValueQualifier("occupantID", OP_EQUAL, $this->vars["occupantID"]),
					new BLKeyValueQualifier("deleted", OP_EXACT_MATCH, NULL_VALUE),
					
			));
			 $found = BLGenericRecord::find("File", $qual, ($order ? $order : array("created" => ORDER_DESCEND)));
			 debugln("found non deleted documents: " + count($found));
			 
			 return $found;
			//return $this->incomelineItems();
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
