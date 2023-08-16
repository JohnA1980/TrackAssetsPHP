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

require_once dirname(__FILE__)."/../BL/BLGenericRecord.php";

$bl_encryptionEnabled = true;

function invalidEmail(string $email): bool
{
	return filter_var($email, FILTER_VALIDATE_EMAIL, FILTER_NULL_ON_FAILURE) == null;
}

function validEmail(string $email): bool
{
	return ! invalidEmail($email);
}

function download_http(string $url, array $extra_http_headers = [], int $timeout = 30): string
{
	$cl = curl_init($url);
	curl_setopt($cl, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($cl, CURLOPT_RETURNTRANSFER, 1);
	foreach ($extra_http_headers as $h) {
		curl_setopt($ch, CURLOPT_HTTPHEADER, $h);
	}
	curl_setopt($cl, CURLOPT_TIMEOUT, $timeout);
	$response = curl_exec($cl);
	curl_close($cl);
	return $response;
}

function mysql_generic_escape_string(string $inp): string
{ 
    if (is_array($inp)) 
        return array_map(__METHOD__, $inp); 

    if (! empty($inp) && is_string($inp)) 
    { 
        return str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $inp); 
    } 

    return $inp; 
} 

function safeValue(array $anArray, $key, $defaultValue = null)
{
	if (debugLogging() > 3)
	{
		$key_str = is_array($key) ? "(".var_export($key, true).")" : $key;
		$d_str = is_array($defaultValue) ? "(".var_export($defaultValue, true).")" : $defaultValue;
		debugln("safeValue: '$key_str' default: '$d_str'");
		debugln($anArray);
	}
	$value = ! isset($anArray[$key]) ? $defaultValue : $anArray[$key];
	unset($anArray, $key);

	if (debugLogging() > 3)
	{
		$v_str = is_array($value) ? "(".var_export($value, true).")" : $value;
		debugln("safeValue: $v_str");
	}
		
	return $value;
}

// ------ NOTE: NO LONGER ENCRYPTS ---------
// Deprecated but woven into the framework at points. Left here for compatibility but
// only returns the value supplied.
function doEncrypt(?string $value): string {
	return $value ?? '';
}

function doDecrypt(?string $value): string {
	return $value ?? '';
}
// ----------

/*
	Takes a flat array of generic records and splits them into a tree of
	dictionaries based on the key paths passed in.
	
	You need to sort the array or records prior to calling this functions.
*/
function itemsSplitIntoHeirarchyWithKeys(array $items, $keys, bool $keepEmptyKeys = false): array
{
	return itemsSplitIntoHeirarchyWithKeysAndStartPos($items, $keys, 0, $keepEmptyKeys);
}

function itemsSplitIntoHeirarchyWithKeysAndStartPos(array $items, $keys, $pos, bool $keepEmptyKeys = false): array
{
	$key = $keys[$pos];
	$sets = array();
	$currentSet = null;
	$currentKeyValue = null;
	for ($i = 0; $i < sizeof($items); $i++)
	{
		if (debugLogging() > 2)
			debugln("$i");
		$item = $items[$i];
		if (is_array($item))
		{
			$keyValue = $item[$key];
		}
		else
		{
			// generic record.
			if (strpos($key, ".") !== false)
				$keyValue = $item->valueForKeyPath($key);
			else {
				$keyValue = $item->field($key);
			}
		}
		
		if (debugLogging() > 2)
			debugln("keyValue='$keyValue'");
		if (! $keyValue && ! $keepEmptyKeys)
			continue;
		if ($keyValue != $currentKeyValue)
		{
			if (debugLogging() > 2)
				debugln("'$keyValue' != $currentKeyValue");
			$nextPos = $pos+1;
			if (debugLogging() > 2)
				debugln("nextPos=$nextPos");
			if ($currentSet && $pos+1 < sizeof($keys))
				$sets[$currentKeyValue] = itemsSplitIntoHeirarchyWithKeysAndStartPos($currentSet, $keys, $nextPos);
			else if ($currentSet)
				$sets[$currentKeyValue] = $currentSet;
			$currentSet = array();
			$currentKeyValue = $keyValue;
		}
		$currentSet[] = $item;
		if (debugLogging() > 2)
			debugln("current set size: ".count($currentSet));
	}
	// trailing set
	if ($currentSet && $pos+1 < sizeof($keys))
	{
		$nextPos = $pos+1;
		$sets[$currentKeyValue] = itemsSplitIntoHeirarchyWithKeysAndStartPos($currentSet, $keys, $nextPos);
	}
	else if ($currentSet)
		$sets[$currentKeyValue] = $currentSet;
	return $sets;
}
	
function proportionalWidthAndHeight($w, $h, $max): array
{
	if (! $w || ! $h || ! $max)
		return null;
	$isWidth = $w > $h;
	$shortestSide = min($w/$h, $h/$w) * $max;

	if ($isWidth)
	{
		$width = $max;
		$height = $shortestSide;
	}
	else
	{
		$width = $shortestSide;
		$height = $max;
	}
	return array("height" => $height, "width" => $width);
}
    
function mysql_date(bool $dateOnly = false): string
{
    $formmater = $dateOnly ? "Y-m-d" : "Y-m-d H:i:s"; 
    return date($formmater);
}

function mysql_date_only(): string
{
    return mysql_date(true);
}

/*
	Strip and escape input, then output directly to an HTML page.
*/
function print_html(?string $input, string $encoding = "UTF-8"): void
{
    if ($input !== null and $input !== '')
    	print BLStringUtils::strip($input, $encoding);
}

/*
    Alias to bl::print_html
*/
function esc(?string $input, string $encoding = "UTF-8"): void
{
    print_html($input, $encoding);
}
