<?php
/**
*
* BLogic
* The Business Logic Web Framework
* 
* @package		BLogic
* @subpackage	Utils
* @version		3.0
* 
* @license		GPLv3 see license.txt
* @copyright	2010 Sqonk Pty Ltd.
*
*
* This file is distributed
* on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either
* express or implied. See the License for the specific language governing
* permissions and limitations under the License.
**/

define("S_ALPHANUM", 1);
define("S_SQL", 2);
define("S_SYSTEM", 4);
define("S_HTML", 8);
define("S_INT", 16);
define("S_FLOAT", 32);

class BLDateUtils
{
	// convert dd/mm/yyyy to yyyy-mm-dd
	static public function ausDateToMySQLDate(string $ausDate): string
	{
		$date = str_replace('/', '-', $ausDate);
		$mysql = date('Y-m-d', strtotime($date));
		debugln("ausDateToMySQLDate: $ausDate ($date) to $mysql", 1);
		return $mysql;
	}

	static public function switchUSAusDate(string $date): string
	{
		$parts = explode("/", $date);
		return $parts[1]."/".$parts[0]."/".$parts[2];
	}
	
	static public function isDate(string $date): bool
	{
		return (bool)strtotime($date);
	}
}

class BLArrayUtils
{
    static public function valuesForRecordKey(array $array, $key): array
    {
        $values = array();
        foreach ($array as $rec)
        {
            if (! $rec instanceof BlGenericRecord) {
                throw new Exception("valuesForRecordKey: object in array is not a generic record.");
            }
            $values[] = $rec->field($key);
        }
        return $values;
    }

	static public function indexOf($needle, array $haystack, bool $valueLookup = false): int|bool
	{              
		$keys = $valueLookup ? array_values($haystack) : array_keys($haystack);
		for ($i = 0; $i < count($keys); $i++) 
		{      
	        if ($keys[$i] == $needle)       
	            return $i;                   
	    }
	    return false;
	}

	static public function implode_assoc(string $delim, array $array, string $keyValueDelim): string
	{
		$new_array = array();
		foreach ($array as $key => $value)
		{
			$new_array[] = $key.$keyValueDelim.$value;
		}
		return implode($delim, $new_array);
	}
	
	static public function implodeWithQuotes(string $delim, array $array): string
	{
		for ($i = 0; $i < count($array); $i++)
		{
			$value = $array[$i];
			$array[$i] = "\"$value\"";
		}
		return implode($delim, $array);
	}
	
	static public function first(array $array) {
        $keys = array_keys($array);
		return count($keys) > 0 ? $array[$keys[0]] : false;
	}
    
    static public function last(array $array) {
        return end($array);
    }
	
	static public function numberArray(int $start, int $end): array {
		return range($start, $end);
	}
	
	static public function is_assoc(array $array): bool
	{
	    // Keys of the array
	    $keys = array_keys($array);

	    // If the array keys of the keys match the keys, then the array must
	    // not be associative (e.g. the keys array looked like {0:0, 1:1...}).
	    $keys_of_keys = array_keys($keys);
		$diff = array_diff($keys, $keys_of_keys);

		return count($diff) > 0; 
	}
	
	static public function breakIntoSetsOf(int $number, array $array): array {
		return array_chunk($array, $number);
	}
}

class BLStringUtils
{
	static public function endsWith(string $haystack, string $needle): bool
	{
	    if (strlen($needle) > strlen($haystack))
	        return false;
		$posFromRight = strlen($haystack) - strlen($needle);
	    return strrpos($haystack, $needle, $posFromRight) === $posFromRight;
	}
	
	static public function startsWith(string $haystack, string $needle): bool
	{
	    if (strlen($needle) > strlen($haystack))
	        return false;
		return strrpos($haystack, $needle) === 0;
	}
    
    static public function contains(string $haystack, string $needle): bool
    {
        return strpos($haystack, $needle) !== false;
    }

	static public function quotedValue(array $array, $key, $defaultValue = ''): string
	{
		$value = safeValue($array, $key, $defaultValue);
		return "\"$value\"";
	}
	
	static public function hasAlphaNumeric(string $value): bool
	{
		return preg_match('/[a-z]+$/i', $value) || preg_match('/[0-9]+$/i', $value);
	}
	
	static public function generatePassword(int $length = 9, int $strength = 0): string
	{
		$vowels = 'aeuy';
		$consonants = 'bdghjmnpqrstvz';

		if ($strength & 1)
			$consonants .= 'BDGHJLMNPQRSTVWXZ';

		if ($strength & 2) 
			$vowels .= "AEUY";

		if ($strength & 4) 
			$consonants .= '23456789';

		if ($strength & 8) 
			$consonants .= '@#$%';

		$password = '';
		$alt = time() % 2;
		for ($i = 0; $i < $length; $i++) 
		{
			if ($alt == 1) 
			{
				$password .= $consonants[(rand() % strlen($consonants))];
				$alt = 0;
			} 
			else 
			{
				$password .= $vowels[(rand() % strlen($vowels))];
				$alt = 1;
			}
		}
		return $password;
	}
	
	static public function stripLineEndings(string $text, string $replacement = ' '): string
	{
		if (strpos($text, "\r\n") !== false) {
			$text = implode($replacement, explode("\r\n", $text));
		}
		if (strpos($text, "\r") !== false) {
			$text = implode($replacement, explode("\r", $text));
		}
		if (strpos($text, "\n") !== false) {
			$text = implode($replacement, explode("\n", $text));
		}
		return $text;
	}
	
	
	/* 
        Note this is for translating text to a clean representation. 
        It is not meant to be used a security measure. Instead use the strip method for such purposes.
	*/
	static public function clean(string $text): string
	{
		$text = iconv('macintosh', 'UTF-8', $text);
		$text = preg_replace('/[^(\x20-\x7F)]*/','', $text);

		// First, replace UTF-8 characters.
		$text = str_replace(array("\xe2\x80\x98", "\xe2\x80\x99", "\xe2\x80\x9c", "\xe2\x80\x9d", "\xe2\x80\x93", "\xe2\x80\x94", "\xe2\x80\xa6"),
		 	array("'", "'", '"', '"', '-', '--', '...'),
		 	$text
		);

		// Next, replace their Windows-1252 equivalents.
		 $text = str_replace(array(chr(145), chr(146), chr(147), chr(148), chr(150), chr(151), chr(133)),
		 	array("'", "'", '"', '"', '-', '--', '...'),
		 	$text
		);

		return $text;
	}
    
	/*
		Generic method for sanitising an SQL string before running it in a database. As the built-in version
		requires an active database connection to work, this can be useful for those situations where 
		that can't be guarenteed.
	*/
    static public function sql_generic_escape_string(string $inp): string
    { 
        return mysql_generic_escape_string($inp);
    } 

	/*
		Filter out all non alpha-numeric characters. Optionally pass in a minimum and maximum string length
		to invalidate any resulting string that does not meet the given boundaries.
	*/
	static public function strip_non_alpha_numeric(string $string, ?int $min = null, ?int $max = null)
	{
	    $string = preg_replace("/[^a-zA-Z0-9]/", "", $string);
	    $len = strlen($string);
    
		if (($min && ($len < $min)) || ($max && ($len > $max)))
	      return false;
	
	    return $string;
	}
    
	/* 
		Is the supplied variable capable of being transformed into a string.

		NOTE: Entities (generic records) are considered special in this circumstance and will
		always return FALSE.
	*/
	static public function is_stringable($value): bool
	{
		return is_string($value) or is_numeric($value) or
			(is_object($value) and ! $value instanceof BLGenericRecord and method_exists($value, '__toString'));
	}

	/* 
        Short-hand version of sanitize() for html output.
    */
	static public function strip(string|array $value, string $encoding = "UTF-8"): string|array
	{
		if (is_array($value)) 
			foreach ($value as $key => $item) 
				$value[$key] = self::strip($item, $encoding);
        
        else if (self::is_stringable($value))
		     $value = htmlspecialchars($value, ENT_QUOTES | ENT_HTML401, 'UTF-8');
        
        return $value;
	}
}
