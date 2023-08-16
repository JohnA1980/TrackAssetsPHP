<?php 
	//
	// Asset.php
	// 
	// Created on 2014-11-01 @ 10:00 am.
	 
	require_once BLOGIC."/BLogic.php"; 
	require_once ROOT."/Utils/DateUtils.php";
 
	class Asset extends BLGenericRecord 
	{ 
		public function __construct($dataSource = null) 
		{ 
			parent::__construct($dataSource); 

			$this->defineRelationship(new BLToManyRelationship("incomelineitems", $this, "IncomeLineItem", "assetID", "assetID"));
			$this->defineRelationship(new BLToManyRelationship("expenselineitems", $this, "ExpenseLineItem", "assetID", "assetID"));
			$this->defineRelationship(new BLToManyRelationship("incomesources", $this, "IncomeSource", "assetID", "assetID"));
			$this->defineRelationship(new BLToManyRelationship("occupants", $this, "Occupant", "assetID", "assetID"));
		} 
	 
		public function tableName() : string
		{ 
			return "Asset"; 
		} 
		 
		public function pkNames(): array|string
		{ 
			return "assetID"; 
		}

		//FIXME : need to filter deleted occupants
		public function nonDeletedOccupants(){
			return $this->valueForRelationship("occupants");
		}
		
		public function sortedExpenseLineitemsBasedOnPaymentDate(){
			return $this->nonDeletedExpenseLineItems(array("expenseDate" => ORDER_DESCEND));
		}
		
		//FIXME : need to filter deleted expenselineitems
		public function nonDeletedExpenseLineItems($order = null){
			$qual = new BLAndQualifier(array(
					new BLKeyValueQualifier("assetID", OP_EQUAL, $this->vars["assetID"]),
					new BLKeyValueQualifier("deleted", OP_EXACT_MATCH, NULL_VALUE),
						
			));
			$found = BLGenericRecord::find("ExpenseLineItem", $qual, ($order ? $order : array("expenseDate" => ORDER_DESCEND)));
			debugln("found non deleted ExpenseLineItems: " . count($found));
			
			return $found;
		}
	
		
		public function incomesources(){
			return $this->valueForRelationship("incomesources");
		}
		
		public function firstIncomeSource(){
			$incomeSources = $this->nonDeletedIncomeSources();
			if(sizeof($incomeSources) > 0){
				return $incomeSources[0];
			}
			else{
				return null;
			}
		}
		
		
		public function nonDeletedIncomeSources(){
			$qual = new BLAndQualifier(array(
					new BLKeyValueQualifier("assetID", OP_EQUAL, $this->vars["assetID"]),
					new BLKeyValueQualifier("deleted", OP_EXACT_MATCH, NULL_VALUE),
						
			));
			$found = BLGenericRecord::find("IncomeSource", $qual);
			echo "found non deleted: " . count($found);
		
			return $found;
			//return $this->incomelineItems();
		}
		
// 		public NSArray<IncomeLineItem> nonDeletedIncomeLineItems(){
// 			if(incomesources().count() == 1){
// 				return incomesources().get(0).nonDeletedIncomeLineItems();
// 			}
// 			else{
// 				NSMutableArray<IncomeLineItem> incomeLineItems = new NSMutableArray<IncomeLineItem>();
		
// 				for(IncomeSource incomeSource : incomesources()){
// 					incomeLineItems.addObjectsFromArray(incomeSource.nonDeletedIncomeLineItems());
// 				}
		
// 				return incomeLineItems;
// 			}
// 		}
		
		
		public function nonDeletedIncomeLineItems(){
			$allIncomeLineItems = array();
			
			if(count($this->incomesources()) > 0){
				$incomeSources = $this->incomesources();
				
				foreach ($incomeSources as $incomeSource){
					$allIncomeLineItems = array_merge($allIncomeLineItems, $incomeSource->nonDeletedIncomeLineItems());
				}
			}
				
			return $allIncomeLineItems;
			
		}
		
		public function unpaidIncomeLineItems(){
			$allIncomeLineItems = array();
				
			if(count($this->incomesources()) > 0){
				$incomeSources = $this->incomesources();
		
				foreach ($incomeSources as $incomeSource){
					$allIncomeLineItems = array_merge($allIncomeLineItems, $incomeSource->unpaidIncomeLineItems());
				}
			}
		
			return $allIncomeLineItems;
				
		}
		
		public function safeName(){
				
			if(!is_null($this->vars["name"])){
				return $this->vars["name"];
			}
			else{
				return "";
			}
		}
		
	/*
		public function ()
		{
			$categories = $this->valueForRelationship("categories");
			if (sizeof($categories) > 0)
			{
				$names = array();
				foreach ($categories as $category)
					$names[] = $category->vars["name"];
				unset($categories);
				return implode(", ", $names);
			}
			return "";
		}

	*/	
		/*
		public function upComingPayments(){
		
		}
		
		public NSArray sortedIncomeLineItems(){
			return TrackerUtils.sortMyArrayDescending(nonDeletedIncomeLineItems(), SORT_KEYS_ILI);
		}	
	
			
		public NSArray upComingPayments(){
			NSMutableArray<EOQualifier> andQuals = new NSMutableArray<EOQualifier>();
			//andQuals.addObject(new EOKeyValueQualifier("fromDate", EOQualifier.QualifierOperatorLessThanOrEqualTo, new NSTimestamp()));
			andQuals.addObject(new EOKeyValueQualifier("paymentDate", EOQualifier.QualifierOperatorEqual, null));
			return EOQualifier.filteredArrayWithQualifier(sortedIncomeLineItems(), new EOAndQualifier(andQuals));
		}*/
	
		/*	
		public NSArray<ExpenseLineItem> nonDeletedExpenseLineItems(){
			return EOQualifier.filteredArrayWithQualifier(expenselineitems(), deletedQualifier);
		}
		
		public NSArray sortedExpenseLineItems(){
			return TrackerUtils.sortMyArrayDescending(nonDeletedExpenseLineItems(), SORT_KEYS_ELI);
		}
	
		public NSArray<IncomeLineItem> nonDeletedIncomeLineItems(){
			if(incomesources().count() == 1){
				return incomesources().get(0).nonDeletedIncomeLineItems();
			}
			else{
				NSMutableArray<IncomeLineItem> incomeLineItems = new NSMutableArray<IncomeLineItem>();
				
				for(IncomeSource incomeSource : incomesources()){
					incomeLineItems.addObjectsFromArray(incomeSource.nonDeletedIncomeLineItems());
				}
				
				return incomeLineItems;
			}
		}
		
	
		
		public financeTracker.common.InvestmentPortfolio investmentportfolio()
		{
			return (financeTracker.common.InvestmentPortfolio)storedValueForKey("investmentportfolio");
		}
		
		public void setInvestmentportfolio(financeTracker.common.InvestmentPortfolio value)
		{
			takeStoredValueForKey(value, "investmentportfolio");
		}
		
		public NSArray<financeTracker.common.User> users()
		{
			return (NSArray<financeTracker.common.User>)storedValueForKey("users");
		}
	
		public void setUsers(NSArray<financeTracker.common.User> value)
		{
			takeStoredValueForKey(value, "users");
		}
	
		public void addToUsers(financeTracker.common.User object)
		{
			includeObjectIntoPropertyWithKey(object, "users");
		}
	
		public void removeFromUsers(financeTracker.common.User object)
		{
			excludeObjectFromPropertyWithKey(object, "users");
		}
	
		//TODO: will need to change this is to raw rows or an actual sql query 
		public BigDecimal grossExpenses(){
			BigDecimal totalAmount = new BigDecimal("0.00");
			for(ExpenseLineItem expenseLineItem : nonDeletedExpenseLineItems()){
				totalAmount = totalAmount.add(expenseLineItem.safeAmount());
			}
			return totalAmount;
		}
		
		public BigDecimal grossIncome(){
			BigDecimal totalAmount = new BigDecimal("0.00");
			for(IncomeLineItem incomeLineItem : nonDeletedIncomeLineItems()){
				totalAmount = totalAmount.add(incomeLineItem.safeAmount());
			}
			
			return totalAmount;
		}
		
		public BigDecimal netIncome(){
			return grossIncome().subtract(grossExpenses());
		}
		
		public String displayName(){
			return name() != null ? name() : "No Name";
		}*/
		
		
		/*
			Override this method if you have any database fields which should not
			be modified or saved back to the server. This provides only 'quiet' protection.
			It does not pass any errors or warnings back if field data has changed, it merely
			ommits the fields from the save request.
		*/
		public function readOnlyAttributes(): array
		{
			return array("assetID");
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
		
		protected $incomeSummaryForFinancialYear;
		
		
		//return [income, expense, netIncome]
		public  function incomeSummaryForFinancialYear(){
			
			if(!$this->incomeSummaryForFinancialYear){
				$result = finanicalYear();
				//$startFinancialYear = new DateTime($result['start']);
				//$endFinancialYear = new DateTime($result['end']);
				
				$startFinancialYear = $result['start'];
				$endFinancialYear = $result['end'];
				
				
				debugln("financial year start Time: " . $startFinancialYear->format("Y-m-d"));
				debugln("financial year end Time: " . $endFinancialYear->format("Y-m-d"));
				
// 				$allIncomeLineItems = $this->incomeBetweenDates($startFinancialYear, $endFinancialYear);
// 				$allExpenseLineItems = $this->expensesBetweenDates($startFinancialYear, $endFinancialYear);
	
// 				$returningSummary["income"] = $allIncomeLineItems;
// 				$returningSummary["expenses"] = $allExpenseLineItems;
// 				$returningSummary["netIncome"] = ($allIncomeLineItems - $allExpenseLineItems);
				
				$this->incomeSummaryForFinancialYear = $this->baseSummaryArrayForDateRange($startFinancialYear, $endFinancialYear);
				
				//debugln("Income: " . $allIncomeLineItems);
				
				//$this->incomeSummaryForFinancialYear = $returningSummary;
			}
			
			return $this->incomeSummaryForFinancialYear;
			 			
		}
		
		private function totalAmountArrayOfIncome($incomeLineItems){
			return $this->baseTotalAmountForArrayAndKey($incomeLineItems, "amountPaid");			
		}
		
		private function totalAmountArrayOfExpense($expenseLineItems){
			return $this->baseTotalAmountForArrayAndKey($expenseLineItems, "amount");
		}
		
		private function baseTotalAmountForArrayAndKey($theArray, $key){
			$totalAmount = null;
			
			foreach ($theArray as $baseItem) {
				$totalAmount = $totalAmount + $baseItem->vars[$key];
			}
				
			return $totalAmount;
		}
		
		public function financialYearSummaryMatrix(){
			$financialYearMonths = financialYearMonths();

			foreach($financialYearMonths as $monthRange){
				debugln("arraykeys : " . implode(array_keys($monthRange)));
				$calculatedSummary = $this->baseSummaryArrayForDateRange($monthRange['start'], $monthRange['end']);
				
				
				
				$summaryArray[] = array($monthRange['start']->format('M-y'), $calculatedSummary["income"], $calculatedSummary["expenses"], $calculatedSummary["netIncome"]);
			}
			
			return $summaryArray;
		}
		
		public function financialYearSummaryAsString(){
			$theSummaryMatrix = $this->financialYearSummaryMatrix();
			array_unshift($theSummaryMatrix , array('Month', 'Rent', 'Expenses', 'Profit'));
			//debugln("implode " . implode($this->financialYearSummaryMatrix(), ""));
			debugln("json data: " . json_encode($theSummaryMatrix));
			//return $this->financialYearSummaryMatrix()->implode();
			return json_encode($theSummaryMatrix);
		}
		
		/*
		public function chartArrayTable(){
			return $chartTable= "[
          ['Month', 'Rent', 'Expenses', 'Profit'],
          ['Jan', 1500, 400, 1100],
          ['Feb', 1600, 460, 1540],
          ['Mar', 1000, 1120, -120],
          ['Apr', 1500, 3000, -1500]
        ]";
		}*/
		
		
		
// 			return $finaicalSummaryArray = array
// 			(
// 					array("Jan", 1000,500, 500),
// 					array("Feb", 800,600, 200),
// 					array("Mar",100, 100),
// 					array("Apr", 600,200, 400)
// 			);
		
		
		public function baseSummaryArrayForDateRange($startDate, $endDate){
			$returningSummary["income"] = $this->incomeBetweenDates($startDate, $endDate);
			$returningSummary["expenses"] = $this->expensesBetweenDates($startDate, $endDate);
			$returningSummary["netIncome"] = ($returningSummary["income"] - $returningSummary["expenses"]);
			
			debugln("Returning Summary: " .  implode(",", $returningSummary));
			return $returningSummary;
		}
		//select *
		// 		from IncomeLineItem ili
		// 		left join IncomeSource ins on (ili.incomeSourceID = ins.incomeSourceID)
		// 		and ins.assetID = 1;
		

		function incomeBetweenDates($startDate, $endDate){
			$allIncomeLineItems = $this->incomeLineItemsBetweenDates($startDate, $endDate);
			$totalAmount = 0;
			
			foreach ($allIncomeLineItems as $incomLineItem){
				$totalAmount = $totalAmount + $incomLineItem->vars["amountPaid"];
			}
			
			return $totalAmount;
		}
		
		//need to fix up the qualifier for this one
		//returns all incomeLineItems
		function incomeLineItemsBetweenDates($startDate, $endDate)
		{
			debugln("incoming enddate is: " . $endDate->format("d/m/y"));
			
			$qual = new BLAndQualifier(array(
					//new BLKeyValueQualifier("ins.assetID", OP_EQUAL, $this->vars["assetID"]),
					//new BLKeyValueQualifier("ili.paymentDate", OP_GREATER_EQUAL, $startDate->format("Y-m-d")),
					//new BLKeyValueQualifier("ili.paymentDate", OP_LESS_EQUAL, $endDate->format("Y-m-d"))
					
					new BLKeyValueQualifier("ili.deleted", OP_EXACT_MATCH, NULL_VALUE),
					new BLKeyValueQualifier("ins.deleted", OP_EXACT_MATCH, NULL_VALUE),
					new BLKeyValueQualifier("ins.assetID", OP_EQUAL, $this->vars["assetID"]),
					new BLKeyValueQualifier("paymentDate", OP_LESS_EQUAL, $endDate->format("Y-m-d")),
					new BLKeyValueQualifier("paymentDate", OP_GREATER_EQUAL, $startDate->format("Y-m-d"))
					
			));
			//$order = array("ili.paymentDate" => ORDER_ASCEND);
			debugln("before incomeLineItem search");
			return BLGenericRecord::find("IncomeLineItem", $qual, null, array("sqlTableIdentity" => "ili", "joins" => array("IncomeSource ins on ins.incomeSourceID = ili.incomeSourceID")));

		}
		
		function expensesBetweenDates($startDate, $endDate){
			$allExpenses = $this->expenseLineItemsBetweenDates($startDate, $endDate);
			$totalAmount = 0;
			//debugln("Number of expenses: " . count($allExpenses));	
			foreach ($allExpenses as $expense){
				$totalAmount = $totalAmount + $expense->vars["amount"];
			}
				
			return $totalAmount;
		}
		
		
		//need to fix this shit up too
		function expenseLineItemsBetweenDates($startDate, $endDate){
			$qual = new BLAndQualifier(array(
					new BLKeyValueQualifier("deleted", OP_EXACT_MATCH, NULL_VALUE),
					new BLKeyValueQualifier("assetID", OP_EQUAL, $this->vars["assetID"]),
					new BLKeyValueQualifier("expenseDate", OP_LESS_EQUAL, $endDate->format("Y-m-d")),
					new BLKeyValueQualifier("expenseDate", OP_GREATER_EQUAL, $startDate->format("Y-m-d"))
						
			));
			
			//$order = array("ili.paymentDate" => ORDER_ASCEND);
			debugln("before expenseLineItem search");
			return $found = BLGenericRecord::find("ExpenseLineItem", $qual);
			debugln("after expenseLineItem search");
			
			
		}
		
		// 		function metricsEnteredToday($user, $date, $endDate)
		// 		{
		// 			$qual = new BLAndQualifier(array(
		// 					new BLKeyValueQualifier("w.patientID", OP_EQUAL, $user),
		// 					new BLKeyValueQualifier("e.collectionDate", OP_GREATER_EQUAL, $date),
		// 					new BLKeyValueQualifier("e.collectionDate", OP_LESS_EQUAL, $endDate)
		// 			));
		// 			$order = array("e.collectionDate" => ORDER_ASCEND);
		// 			return BLGenericRecord::find("WellnessEntry", $qual, $order, array("sqlTableIdentity" => "e", "joins" => array("Wellness w on w.id = e.wellnessID")));
		// 		}
		
		
		//this is an example
		public function attendanceCountForYear($year = null)
		{
			if (empty($this->vars["personsID"]) || empty($this->vars["homeCountryID"]))
				return "N/A";
			if ($this->attendanceCountYear === null)
			{
				$this->attendanceCountYear = 0;
		
				if ($year == null)
					$year = date("Y");
					
				$yearStart = $year."-01-01";
				$yearEnd = date("$year-12-31");
		
				$qual = array(
						new BLKeyValueQualifier("countryID", OP_EQUAL, $this->vars["homeCountryID"]),
						new BLKeyValueQualifier("paymentDate", OP_GREATER_EQUAL, $yearStart),
						new BLKeyValueQualifier("paymentDate", OP_LESS_EQUAL, $yearEnd),
						new BLKeyValueQualifier("deleted", OP_EXACT_MATCH, NULL_VALUE)
				);
				// if ($this->vars["stateID"])
				// {
				// 	$qual[] = new BLOrQualifier(array(
					// 		new BLKeyValueQualifier("stateID", OP_EQUAL, $this->vars["stateID"]),
					// 		new BLKeyValueQualifier("stateID", OP_EXACT_MATCH, NULL_VALUE)
					// 	));
					// }
				$found = BLGenericRecord::find("Attendance", new BLAndQualifier($qual));
	
				//debugln("found attendances for year: ".sizeof($year));
	
				if (sizeof($found) > 0)
				{
					$quals = array();
					foreach ($found as $attendance)
					{
						$quals[] = new BLKeyValueQualifier("attendanceID", OP_EQUAL, $attendance->vars["attendanceId"]);
					}
					$qual = new BLAndQualifier(array(
							new BLKeyValueQualifier("personID", OP_EQUAL, $this->vars["personsID"]),
							new BLOrQualifier($quals)
					));
					$this->attendanceCountYear = BLDataSource::defaultDataSource()->countForQualifier("PersonAttendance", $qual);
				}
			}
			return $this->attendanceCountYear;
		}
					
		
		public function groupedExpenseLineItemForDateRange($start, $end){
			$allExpenseLineItems = $this->expenseLineItemsBetweenDates($start, $end);
			$expenseSummary = null;
			debugln("count in expenseLineItems: " . sizeof($allExpenseLineItems));
			foreach($allExpenseLineItems  as $eli){
				//$expenseTypeKey = "'" . $eli->safeExpenseTypeName() . "'";
				$expenseTypeKey = $eli->safeExpenseTypeName();
				debugln("aaaa: " . $expenseTypeKey);
				

// 				$search_array = array('first' => null, 'second' => 4);
				
// 				// returns false
// 				isset($search_array['first']);
				
// 				// returns true
// 				array_key_exists('first', $search_array);
// 				
				
				if(!isset($expenseSummary[$expenseTypeKey])){
					$expenseSummary[$expenseTypeKey] = array($eli);
				}
				else{
					$expenseSummary[$expenseTypeKey][] = $eli;
					debugln("The Array is : " . implode(", ", $expenseSummary[$expenseTypeKey])); 
				}
			}
			return $expenseSummary;
		}
		
		public function financialYearExpenseSummary(){
			$financialYearMonths = financialYearMonths();
			
			debugln("financialYearExpenseSummary");
			foreach($financialYearMonths as $month){
				debugln("month start: " . $month["start"]->format("y/m"));
				$expenseSummaryArray[$month["start"]->format("m")] = $this->groupedExpenseLineItemForDateRange($month["start"], $month["end"]);
				
			}
				
			return $expenseSummaryArray;
		}
		
		
		public function expenseSummaryJSON($startDate, $endDate){
			debugln("expenseSummaryJSON : startdate: " . $startDate->format("y-m") . " endDate: " . $endDate->format("y-m"));
			$groupedExpenseLineItemForDateRange = $this->groupedExpenseLineItemForDateRange($startDate, $endDate);
			
			if($groupedExpenseLineItemForDateRange){
			
				//debugln("groupedExpenseLineItemForDateRange class name: ". get_class($groupedExpenseLineItemForDateRange));
				$arrayKeys = array_keys($groupedExpenseLineItemForDateRange);
				foreach($arrayKeys as $key){
					debugln("the key is: ". $key);
					$expenseSummaryArray[] = array($key, $this->totalAmountArrayOfExpense($groupedExpenseLineItemForDateRange[$key]));
				}
			
				
				if($expenseSummaryArray){
					debugln("Is set");
					return json_encode($expenseSummaryArray);
				}
			}
			else{
				debugln("Not Set");
				return null;
			}
			
			
// 			return "[
//           ['Water Bills', 300],
//           ['Electricity', 400],
//           ['Rates', 100],
//           ['General Maintenence', 100],
//           ['Internet', 59]
//         ]";
		}
		
	} 
?>
