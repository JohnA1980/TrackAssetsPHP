<?php

	//interval = P1D = Period of 1 Day
	//			= P1M = Period of 1 Month
	//$date being pushed through the function is pass by reference meaning it will be changed
	//why is that??
	function addIntervalToDate($date, $interval){
		//unsure why we would need to clone the incoming date.. but for some reason
		//it changes the value the was orginally passed in.. Shouldn't it be pass by value??
		$newDate = clone $date;
		//debugln("addCalendarDaysToDate Date 1: " . $date->format('d/m/y'));
		
		$newDate->add(new DateInterval($interval)); // P1D means a period of 1 day
		//$date2 = $date->format('Y-m-d');
		debugln("returning date is " . $newDate->format("d/m/y"));
		return $newDate;
	}
	
	function testPassByReference($var){
		$var =  DateTime::createFromFormat('d/m/Y', '28/02/2015');
		debugln("var within test pass: " . $var->format("d/m/y"));
	}
	
	function minusIntervalToDate($date, $interval){
		//unsure why we would need to clone the incoming date.. but for some reason
		//it changes the value the was orginally passed in.. Shouldn't it be pass by value??
		$newDate = clone $date;
		//debugln("minusIntervalToDate Date 1: " . $date->format('d/m/y'));
		$newDate->sub(new DateInterval($interval)); // P1D means a period of 1 day
		//$date2 = $date->format('Y-m-d');
		debugln("returning date is " . $newDate->format("d/m/y"));
		return $newDate;
	}
	
	//FIXME : finsh off this function
	function  isExactlyOneMonthFromDate($firstDate, $secondDate){
		debugln("the First date: " . $firstDate->format("d/m/Y"));
		
		$calcuatedMonthDate = addIntervalToDate($firstDate, "P1M");
		
		$secondDateString = $secondDate->format('d/m/Y');
		$calcuatedMonthDateString = $calcuatedMonthDate->format('d/m/Y');
		
		debugln("isExactlyOneMonthFromDate: first Date: " .  $firstDate->format('d/m/y') ."  second Date String: " . $secondDate->format('d/m/y') . " calcuatedMonthDateString: " .  $calcuatedMonthDateString);
		
		return $secondDateString == $calcuatedMonthDateString;
	}
	
	//FIXME: Add some more jizz here.
	function getNextMonthsDate(){
		
	}
	
	//returns the months between two dates
	function monthsBetweenDates($startDate, $endDate){
		//$start    = new DateTime('2010-12-02');
		$startDate->modify('first day of this month');
		//$end      = new DateTime('2012-05-06');
		$endDate->modify('first day of next month');
		$interval = DateInterval::createFromDateString('1 month');
		$period   = new DatePeriod($startDate, $interval, $endDate);

		$monthsArray = null;
		foreach ($period as $dt) {
			$monthDetails = null;
			$firstDay = clone $dt;
			$lastDay = clone $dt;
			
			$lastDay->modify("last day of this month");
			
			$monthDetails["start"] = $firstDay;
			$monthDetails["end"] = $lastDay;

			$monthsArray[] = $monthDetails;
			//debugln("first day: " . $firstDay->format("d/m/y") . " last day: " . $lastDay->format("d/m/y"));
		}
		
		return $monthsArray;
	}
	
	
	//returns the current financial year
	function finanicalYear()
	{
		$currentTime = new DateTime();
		$result = array();
		$start = new DateTime();
		$start->setTime(0, 0, 0);
		$end = new DateTime();
		$end->setTime(23, 59, 59);
		$year = $currentTime->format('Y');
		$start->setDate($year, 7, 1);
		if($start <= $currentTime){
			$end->setDate($year +1, 6, 30);
		} else {
			$start->setDate($year - 1, 7, 1);
			$end->setDate($year, 6, 30);
		}
		//$result['start'] = $start->getTimestamp();
		//$result['end'] = $end->getTimestamp();
			
			
			
		$result['start'] = $start;
		$result['end'] = $end;
	
		return $result;
	}
	
	function financialYearMonths(){
		$financialYear = finanicalYear();
		return monthsBetweenDates($financialYear['start'], $financialYear['end']);
	}
	
?>