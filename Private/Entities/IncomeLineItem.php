<?php 
	//
	// IncomeLineItem.php
	// 
	// Created on 2014-11-01 @ 10:01 am.
	 
	require_once BLOGIC."/BLogic.php"; 
	 
	class IncomeLineItem extends BLGenericRecord 
	{ 
		public static $PENDING = "Pending";
		public static $PAID = "Paid";
		public static $DUE_TODAY = "Due Today";
		public static $OVERDUE = "Overdue";
		
		public static $DATE_FORMAT = "d/m/Y";
		
		private static $ONE_INTEGER = 1;
		private static $ZERO_INTEGER = 0;
		
		public function __construct($dataSource = null) 
		{ 
			parent::__construct($dataSource); 
			
			$this->defineRelationship(new BLToOneRelationship("occupant", $this, "Occupant", "occupantID", "occupantID"));
			$this->defineRelationship(new BLToOneRelationship("incomesource", $this, "IncomeSource", "incomeSourceID", "incomeSourceID"));
			//$this->defineRelationship(new BLToOneRelationship("receipt", $this, "File", "fileID", "fileID"));
			$this->defineRelationship(new BLToOneRelationship("file", $this, "LineItemAttachment", "id", "recID", false, new BLKeyValueQualifier("type", OP_EQUAL, "LineItemAttachment")));
            
            // @John: put your default value fields right in the constructor. Records fecthed from the database
            // will overwrite it.
            $this->vars["created"] = date("Y-m-d H:i:s");
		} 
		
	 
		public function tableName(): string
		{ 
			return "IncomeLineItem"; 
		} 
		 
		public function pkNames(): array|string
		{ 
			return "incomeLineItemID"; 
		}
		
		/*
			Override this method if you have any database fields which should not
			be modified or saved back to the server. This provides only 'quiet' protection.
			It does not pass any errors or warnings back if field data has changed, it merely
			ommits the fields from the save request.
		*/
		public function readOnlyAttributes(): array
		{
			return array("incomeLineItemID");
		}	
		
		
		public function status(){
			
			if(is_null($this->vars["paymentDate"])){
				$fromDate = $this->fromDateObject(); 
				$toDate = $this->toDateObject(); 
				$currentDate = new DateTime();
				
				//echo "From Date is " . $fromDate->format("Y-m-d") . " Current Date: " . $currentDate->format("Y-m-d");
				
				$comparatorDate = $this->isPostPaid() ? $toDate : $fromDate;
				
				debugln("comparatorDate " . $comparatorDate->format(IncomeLineItem::$DATE_FORMAT) . " this is currentDate: " . $currentDate->format(IncomeLineItem::$DATE_FORMAT) . " is equal?? " . ($comparatorDate == $currentDate));
				
				if($comparatorDate->format(IncomeLineItem::$DATE_FORMAT) == $currentDate->format(IncomeLineItem::$DATE_FORMAT)) {
					return IncomeLineItem::$DUE_TODAY;
				}
				else if($comparatorDate < $currentDate){
					return IncomeLineItem::$OVERDUE;
				}
				
				else
					return IncomeLineItem::$PENDING;
				
			}
			else{
				return IncomeLineItem::$PAID;
			}
		}
		
		public function isDueToday(){
			return $this->status() == IncomeLineItem::$DUE_TODAY;
		}
		
		public function isOverDue(){
			return $this->status() == IncomeLineItem::$OVERDUE;
		}

		public function isPending(){
			return $this->status() == IncomeLineItem::$PENDING;
		}

		public function isPaid(){
			return $this->status() == IncomeLineItem::$PAID;
		}
		
		public function isPostPaid(){
			//debugln("this is the ID " . $this->vars["incomeLineItemID"] . " this is post paid: " . $this->vars["postPaidNum"]);
			return !is_null($this->vars["postPaidNum"]) ? $this->vars["postPaidNum"]  == IncomeLineItem::$ONE_INTEGER: false;
		}
		
		public function cssString(){
			if($this->isOverDue())
				return "overdue";
			else if($this->isPending() || $this->isDueToday())
				return "pending";
			else
				return "paid";
		}
		
		public function fromDate(){
			return $this->vars["fromDate"];
		}
		
		public function fromDateObject(){
			if($this->fromDate()){
				return new DateTime($this->fromDate());
			}else{
				return null;
			}
		}
		
		public function fromDateFormatted(){
			if($this->fromDateObject()){
				return $this->fromDateObject()->format(IncomeLineItem::$DATE_FORMAT);
			}
			else{
				return null;
			}
		}
		
		public function setFromDateFormatted($value)
		{
			if ($value) {
				// This nifty line will transforms a dd/mm/yyyy into a yyyy-mm-dd.
				$value = $this->preProcessDateStringForSave($value);
			}
			$this->vars["fromDate"] = $value;
		}

		public function preProcessDateStringForSave($dateString){
			return implode("-", array_reverse(explode("/", $dateString)));
		}
	
		public function toDate(){
			return $this->vars["toDate"];
		}
		
		public function toDateObject(){
			return new DateTime($this->vars["toDate"]);
		}
		
		public function toDateFormatted(){
			return $this->toDateObject()->format(IncomeLineItem::$DATE_FORMAT);
		}
		
		public function setToDateFormatted($value)
		{
			if ($value) {
				// This nifty line will transforms a dd/mm/yyyy into a yyyy-mm-dd.
				$value = $this->preProcessDateStringForSave($value);
			}
			$this->vars["toDate"] = $value;
		}
		
		
		public function paymentDate(){
			return $this->vars["paymentDate"];
		}
		
		public function paymentDateObject(): ?DateTime {
			if ($pd = $this->paymentDate()) {
				return new DateTime($pd);
			}	
			return null;
		}
        
        /*
            @John: If you specify a function name at the end of a keypath in valueForKeyPath, it will find
             and call the method!
        
            processFormValueKeyPathsForSave() will also check for and use setter methods to!
        */
		
		public function paymentDateFormatted()
        {
            $date = $this->paymentDate();
			return $date ? date(IncomeLineItem::$DATE_FORMAT, strtotime($date)) : "";
		}
        
        
        public function setPaymentDateFormatted($value)
        {
            if ($value) {
                // This nifty line will transforms a dd/mm/yyyy into a yyyy-mm-dd.
                $value = implode("-", array_reverse(explode("/", $value)));
            }
            $this->vars["paymentDate"] = $value;
        }
		
		public function __toString(){
			return "new IncomeLIneItem";
		}
		
		
		
		public function occupant(){
			return $this->valueForRelationship("occupant");
		}
		
		public function safeOccupantDisplay(){
			return $this->occupant() != null ? $this->occupant()->fullName() : "No occupant specified";
		}
		
		public function toAndFromString(){
			$toAndFromDates = "";
			
			$toAndFromDates = $toAndFromDates . ($this->fromDate() != null ? $this->fromDateFormatted() : "N/A");
			$toAndFromDates = $toAndFromDates. " to ";
			$toAndFromDates = $toAndFromDates. ($this->toDate() != null ? $this->toDateFormatted() : "N/A");
			$toAndFromDates = $toAndFromDates. ($this->isPostPaid() ? " (P)" : ""); 
			return $toAndFromDates;
		}
		
		public function assetAndSourceName(){
			$incomeSource =  $this->valueForRelationship("incomesource");
			$asset =  $incomeSource->valueForRelationship("asset");
			
			return $asset->safeName() . " - " . $incomeSource->safeName();
		}
		
		public function markAsPaid(){
			$todaysDate = new DateTime();
			$this->vars["paymentDate"] = $todaysDate->format('Y-m-d');
			$this->vars["amountPaid"] = $this->vars["amount"];
		}

		public function markAsVerified(){
			$this->vars["verifiedDate"] = new DateTime();
		}

		public function isVerified(){
			return !is_null($this->vars["verifiedDate"]);
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
		
		
		/*
		 * 
		 *  private void copyOverIncomeSourceDetails(IncomeSource value){
    	if(value.occupant() != null){
    		setOccupant(value.occupant());
    	}
    	setAmount(value.paymentAmount());
    }
    

    public BigDecimal safeAmount(){
    	return amountPaid() != null ? amountPaid() : TrackerUtils.ZERO_DECIMAL;
    }
    
    
	public NSTimestamp paymentDate()
	{
        return (NSTimestamp)storedValueForKey("paymentDate");
    }
	
    public void setPaymentDate(NSTimestamp value)
	{
        takeStoredValueForKey(value, "paymentDate");
    }
	
    public String dueDateString(){
    	return fromDate() != null ? DateUtils.dateOnlyFormat.format(fromDate()) : "N/A";
    }
    
  
    
   
    */
		 
	} 
?>
