<?php 
	//
	// IncomeSource.php
	// 
	// Created on 2014-11-01 @ 10:01 am.
	 
	require_once BLOGIC."/BLogic.php";
	require_once ROOT."/Utils/DateUtils.php";
	require_once ROOT."/Entities/FrequencyType.php";
	 
	class IncomeSource extends BLGenericRecord 
	{ 
		private static $ONE_INTEGER = 1;
		private static $ZERO_INTEGER = 0;
	
		public function __construct($dataSource = null) 
		{ 
			parent::__construct($dataSource); 
			
			$this->defineRelationship(new BLToOneRelationship("asset", $this, "Asset", "assetID", "assetID"));
			$this->defineRelationship(new BLToOneRelationship("frequencytype", $this, "FrequencyType", "frequencyTypeID", "frequencyTypeID"));
			$this->defineRelationship(new BLToOneRelationship("occupant", $this, "Occupant", "occupantID", "occupantID"));
			$this->defineRelationship(new BLToManyRelationship("incomelineitems", $this, "IncomeLineItem", "incomeSourceID", "incomeSourceID"));
			
		} 
	 
		public function tableName(): string
		{ 
			return "IncomeSource"; 
		} 
		 
		public function pkNames(): array|string
		{ 
			return "incomeSourceID"; 
		}
		
		
		/*
			Override this method if you have any database fields which should not
			be modified or saved back to the server. This provides only 'quiet' protection.
			It does not pass any errors or warnings back if field data has changed, it merely
			ommits the fields from the save request.
		*/
		
		public function readOnlyAttributes(): array
		{
			return array("incomeSourceID");
		}	
		
		public function occupant(){
			return $this->valueForRelationship("occupant");
		}
		
		public function isPostPaid(){
			//debugln("this is the ID " . $this->vars["incomeLineItemID"] . " this is post paid: " . $this->vars["postPaidNum"]);
			return !is_null($this->vars["postPaidNum"]) ? $this->vars["postPaidNum"]  == IncomeSource::$ONE_INTEGER: false;
		}
		
		public function isPostPaidString(){
			return $this->isPostPaid() ? "Yes" : "No";
		}
		
		public function safeOccupantFullName() {
            // @John: an easier way of doing this is as follows..
            
            $occupant = $this->valueForKeyPath("occupant.fullName");
            if (! $occupant) {
                $occupant = "No Occupant";
            }
            return $occupant;
            
			/*$occupant = $this->valueForRelationship("occupant");
			
			if(!is_null($occupant) ){
				return $occupant->fullName();
			}else{
				return "No Occupant";
			}*/
		}
		
		public function safeName() {
			// @John: note the following simpler way to do this instead of adding safe accessor methods...
            
			return $this->field("name");
            
            /*if(!is_null($this->vars["name"])){
				return $this->vars["name"];
			}
			else{
				return "";
			}*/
		}
		
		public function safeNameAndOccupant(){
			return $this->field("name") . " - " .  $this->safeOccupantFullName(); 
		}
		
		public function safeFrequencyName(){
			// @John: an easier way of doing this is as follows..
            
            $type = $this->valueForKeyPath("frequencytype.name");
            if (! $type) {
                $type = "No Frequency Type";
            }
            return $type;
            
            /*$frequencyType = $this->valueForRelationship("frequencytype");
				
			if(!is_null($frequencyType) ){
				return $frequencyType->vars["name"];
			}else{
				return "No Frequency Type";
			}*/
		}
		
		public function incomelineItems(){
			return $this->valueForRelationship("incomelineitems");
		}
		
		public function nonDeletedIncomeLineItems($order = null){
			
			$qual = new BLAndQualifier(array(
					new BLKeyValueQualifier("incomeSourceID", OP_EQUAL, $this->vars["incomeSourceID"]),
					new BLKeyValueQualifier("deleted", OP_EXACT_MATCH, NULL_VALUE),
					
			));
			 $found = BLGenericRecord::find("IncomeLineItem", $qual, ($order ? $order : array("toDate" => ORDER_DESCEND)));
			 //echo "found non deleted: " + count($found);
			 
			 return $found;
			//return $this->incomelineItems();
		}
		
		public function sortedIncomeLineitemsBasedOnEndDate(){
			return $this->nonDeletedIncomeLineItems(array("toDate" => ORDER_DESCEND));
		}
		
		
		function unpaidIncomeLineItems()
		{
			$qual = new BLAndQualifier(array(
					new BLKeyValueQualifier("incomeSourceID", OP_EQUAL, $this->vars["incomeSourceID"]),
					new BLKeyValueQualifier("deleted", OP_EXACT_MATCH, NULL_VALUE),
					new BLKeyValueQualifier("paymentDate", OP_EXACT_MATCH, NULL_VALUE)
						
			));
			//$order = array("ili.paymentDate" => ORDER_ASCEND);
			//return BLGenericRecord::find("IncomeLineItem", $qual, $order, array("sqlTableIdentity" => "ili", "joins" => array("IncomeSource ins on ins.incomeSourceID = ili.incomeSourceID")));
			return $found = BLGenericRecord::find("IncomeLineItem", $qual);
			//return $found = BLGenericRecord::find("IncomeLineItem");
				
		}
		
	
		
		public function lastEndDateOnIncomeLineItem(){
			$sortedIli = $this->sortedIncomeLineitemsBasedOnEndDate();
			
			if(count($sortedIli) > 0){
				$lastIncomeLineItem =  $sortedIli[0];
				debugln("Name of the object: " . $lastIncomeLineItem->vars["incomeLineItemID"]);
				return $lastIncomeLineItem->toDateObject();
			}
		}
		
		
		public function autoAddIncomeLineItem(){
			 
			$lastEndDateOnIncomeLineItem = $this->lastEndDateOnIncomeLineItem();
			
			$occupantStartDate = $this->valueForKeyPath("occupant.startDateObject");
			//$occupant = $this->valueForKeyPath("occupant");
			
			//debugln("this is the lastEndDateOnIncomeLineItem : " . $lastEndDateOnIncomeLineItem->format('Y-m-d'));
			//debugln("occupant startDate: " . $occupantStartDate->format('Y-m-d'));

			//create the LineItem
			$lineItem = BLGenericRecord::newRecordOfType("IncomeLineItem");
			$lineItem->vars["incomeSourceID"] = $this->vars["incomeSourceID"];
			$lineItem->vars["descText"] = "Rent";
			$lineItem->vars["amount"] = $this->vars["paymentAmount"];
			$lineItem->vars["postPaidNum"] = $this->vars["postPaidNum"];
			
			$lineItem->vars["occupantID"] = $this->valueForKeyPath("occupant.occupantID");
			
			debugln("blah here 1 ");
			
			//if startDate of occpant or lastDate of incomelineitem is not null then proceed
			if($occupantStartDate || $lastEndDateOnIncomeLineItem){
				
				debugln("goat here 1 ");
				//getting startDate
				if($lastEndDateOnIncomeLineItem){
					debugln("laste Date on incomelineItem: " . $lastEndDateOnIncomeLineItem->format("d/m/y"));
					$startDate =  addIntervalToDate($lastEndDateOnIncomeLineItem, 'P1D'); ;
				}
				else {
					$startDate = $occupantStartDate;
				}
				debugln("goat here: start date is : " . $startDate->format("d/m/y"));
				
				//Add end date
				if($this->isMonthly()){
					//$endDate = clone $startDate;
					//addIntervalToDate($endDate, 'P1M');
					$endDate = addIntervalToDate($startDate, 'P1M');
					
					//'d' gets the day of the month
					$startDateDay = $startDate->format('d');
					
					//t gets the maximum day of the month
					$maximumDayForMonth = $startDate->format('t');
					
					debugln("maximum day of month is : " . $maximumDayForMonth);
					
					$dayForEndDate = $startDateDay > $maximumDayForMonth ? $maximumDayForMonth : $startDateDay;
					
					debugln("dayForEnddate: " . $dayForEndDate);

					//setting endDates Day of month
					$endDate = $endDate->setDate($endDate->format("y"), $endDate->format("m"), $dayForEndDate);

					//reduce endDate by 1
					$endDate = minusIntervalToDate($endDate, 'P1D');
					//calendar.set(Calendar.DAY_OF_MONTH, (dayForEndDate -1));
			
					//endTime = new NSTimestamp(calendar.getTime());
					//System.out.println("endTime: " + DateUtils.dateFormat.format(endTime));
				}
				else{
					$numOfDays = $this->valueForRelationship("frequencytype")->vars["numberOfDays"];
					$endDate = addIntervalToDate($startDate, 'P'.$numOfDays .'D');
				}
			
				debugln("Start Day: " . $startDate->format("d/m/y") . " EndDay set is : " .  $endDate->format("d/m/y"));
				$lineItem->vars["fromDate"] = $startDate->format("y/m/d");
				$lineItem->vars["toDate"] = $endDate->format("y/m/d");
			}
			else{
				debugln("no start time");
			}
			
			return $lineItem;
			
		}
		
		public function isMonthly(){
			return $this->vars["frequencyTypeID"] == FrequencyType::$MONTHLY;	
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
