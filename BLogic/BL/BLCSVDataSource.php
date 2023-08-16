<?php
/**
*
* BLogic
* The Business Logic Web Framework
* 
* @package		BLogic
* @subpackage	BL
* @version		3.0
* 
* @license		GPLv3 see license.txt
* @copyright	2010 Sqonk Pty Ltd.
*
*
*
* This file is distributed
* on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either
* express or implied. See the License for the specific language governing
* permissions and limitations under the License.
**/

require_once dirname(__FILE__)."/BLDataSource.php";

/*
	TODO 
		- deleting
		- qualification and sorting on search
*/
class BLCSVDataSource extends BLDataSource
{
	protected $rows;
	protected $url;
	protected $enclosure;
	protected $delimiter;
	protected $keyMapping;
	protected $skipFirstRow;
	protected $firstRow;
	protected $hasChanges = false;
	
	public function __construct($url, $keyMapping, $delimiter = ",", $enclosure = null, $skipFirstRow = false)
	{
		if (! $url)
		{
			trigger_error("### BLCSVDataSource: url must not be null!");
			dumpStack();
		}
			
		// if (strpos($url, "http://") !== 0)
		// 	trigger_error("### BLCSVDataSource: url must begin with http!");
			
		$this->url = $url;
		$this->delimiter = $delimiter;
		$this->enclosure = $enclosure;
		$this->keyMapping = $keyMapping;
		$this->skipFirstRow = $skipFirstRow;
	}
	
	public function flushIfNeeded()
	{
		if ($this->hasChanges)
			$this->flushToDisk();
	}
	
	private $csvFile;
	
	protected function csvFile()
	{
		if ($this->csvFile)
			return $tis->csvFile;
			
		$url = $this->url;
		if (debugLogging() > 0)
			debugln("loading csv $url");
			
		if (strpos($url, "http://") !== 0)
		{
			$csv = file_get_contents($url);
		}	
		else
		{
			$cl = curl_init($url);
			curl_setopt($cl, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($cl, CURLOPT_RETURNTRANSFER, 1);
			$csv = curl_exec($cl);
			curl_close($cl);
			//debugln($csv);
		}
        
        $csv = trim($csv); // remove any blank rows from end of the file.
		
		if ($csv === false || $csv === null)
		{
			trigger_error("BLCSVDataSource: error retrieving csv file, no data returned!");
			return;
		}
		
		if ($csv == "")
		{
			debugln("Warning: csv contents is empty!");
		}
		
		$encoding = mb_detect_encoding($csv);
		if (debugLogging() > 0)
			debugln("encoding for file: $encoding");
			
		if ($encoding != "UTF-8" && $encoding != "")
		{
			if (debugLogging() > 0)
				debugln("converting to UTF-8");
			$csv = mb_convert_encoding($csv, "UTF-8", $encoding);
		}
		
		if (strpos($csv, "\r\n") !== false)
		{
			if (debugLogging() > 0)
				debugln("converting windows line ending");
			$csv = str_replace("\r\n", "\n", $csv);
		}
		else if (strpos($csv, "\r") !== false)
		{
			if (debugLogging() > 0)
				debugln("converting macintosh line ending");
			$csv = str_replace("\r", "\n", $csv);
		}
		else if (debugLogging() > 0)
		{
			debugln("has standard unix line ending");
		}
		
		$csvFile = $csv;
		return $csvFile;
	}
	
	protected $rowCount;
	
	public function count()
	{
		if (! $this->rowCount)
		{
			$csv = $this->csvFile();
			$this->rowCount = substr_count($csv, "\n");
		}
		return $this->rowCount;
	}
	
	public function getRows($startLine, $lineCount, $fromEnd = false)
	{
		$csv = $this->csvFile();
		
	}
	
	protected function _getCSVOld($line)
	{
	    $string = preg_replace_callback(
                '|"[^"]+"|',
                create_function(
                    // single quotes are essential here,
                    // or alternative escape all $ as \$
                    '$matches',
                    'return str_replace(\',\',\'*comma*\',$matches[0]);'
                ), $line);
        $rr = explode($this->delimiter, $string);
        return $rr;
	}
	
	protected function loadAllRows()
	{
		$csv = $this->csvFile();
		
		$rawRows = explode("\n", $csv);
		if (debuglogging() > 1)
			debugln("row count: ".sizeof($rawRows));
			
		$this->rows = array();
		$skip = $this->skipFirstRow ? true : false;
		foreach ($rawRows as $line)
		{
		    if (strnatcmp(phpversion(),'5.3.0') >= 0)
			    $rr = str_getcsv($line, $this->delimiter, $this->enclosure);
			else
			{
			    $rr = $this->_getCSVOld($line);
                //$rr = str_replace('*comma*', $this->delimiter, $array);
			}
			
			if ($skip)
			{
				$skip = false;
				$this->firstRow = $rr;
				continue;
			}				
			
			if (count($rr) < count($this->keyMapping))
				continue;
			$rec = array();
			for ($i = 0; $i < count($this->keyMapping); $i++)
			{
				$rec[$this->keyMapping[$i]] = $rr[$i];
			}
			$this->rows[] = $rec;
		}
	}
	
	public function rawRows()
	{
		$csv = $this->csvFile();
		
		$rawRows = explode("\n", $csv);
		if (debuglogging() > 1)
			debugln("row count: ".sizeof($rawRows));
			
		$rows = array();
		foreach ($rawRows as $line)
		{
		    if (strnatcmp(phpversion(),'5.3.0') >= 0)
			    $rr = str_getcsv($line, $this->delimiter, $this->enclosure);
			else
			    $rr = $this->_getCSVOld($line);
			$rows[] = $rr;
		}
		
		return $rows;
	}
	
	public function flushToDisk()
	{
		// if (debugLogging() > 0)
		// {
		// 	debugln("flushing csv to ".$this->url);
		// }
					
		$f = fopen($this->url, "w");
		
		$out = "\"".implode("\",\"", $this->keyMapping)."\"\n";
		fwrite($f, $out, strlen($out));
		
		foreach ($this->rows as $row)
		{
			$line = array();
			for ($i = 0; $i < count($this->keyMapping); $i++)
			{
				$line[] = BLStringUtils::quotedValue($row, $this->keyMapping[$i]);
			}
			$out = utf8_encode(implode(",", $line)."\n");
			fwrite($f, $out, strlen($out));
			
			unset($line, $out);
			gc_collect_cycles();
		}
		fclose($f);
		
		$this->hasChanges = false;
	}
	
	public function save($tableName, $primaryKeys, $vars, $tableEncoding, $binaryFields, $readOnlyFields, $pkNames)
	{
		if (! $this->rows)
			$this->loadAllRows();
			
		$this->hasChanges = true;
		
		if ($primaryKeys)
		{
			$indexes = $this->findWithKeyPairs($primaryKeys);
			
			if (count($indexes) != 1)
			{
				trigger_error("Could not save CSV row as original record could not reliably be determined, ".count($indexes)." found!");
				return;
			}
			$rowIndex = $indexes[0];
			if (debugLogging() > 2)
			{
				debugln("saving row $rowIndex");
				debugln($vars);
			}
			foreach ($vars as $key => $value)
			{
				$this->rows[$rowIndex][$key] = $value;
			}
		}
		else
		{
			$rec = array();
			
			if (debugLogging() > 2)
			{
				debugln("saving new row");
				debugln($vars);
			}
			
			// first init row with all key mappings, this ensures all fields are created (you MUST declare a map for all keys in CSV file!).
			for ($i = 0; $i < count($this->keyMapping); $i++)
			{
				$rec[$this->keyMapping[$i]] = "";
			}
			
			// now set real values being saved
			foreach ($vars as $key => $value)
			{
				$rec[$key] = $value;
			} 
							
			$this->rows[] = $rec;
			
			$pk = is_array($pkNames) ? $pkNames[0] : $pkNames;
			return $rec[$pk];
		}
	}
	
	public function delete($record)
	{
		if ($record->primaryKeys)
		{
			$indexes = $this->findWithKeyPairs($primaryKeys);
			
			if (count($indexes) != 1)
			{
				trigger_error("Could not delete CSV row as original record could not reliably be determined, ".count($indexes)." found!");
				return;
			}
			$rowIndex = $indexes[0];
			if (debugLogging() > 2)
			{
				debugln("deleting row $rowIndex");
				debugln($record->vars);
			}
			unset($this->rows[$rowIndex]);
			$this->rows = array_diff($this->rows, array(""));
		}
	}
	
			
	public function find($qualifier, $order, $additionalParams)
	{
		if (! $this->rows)
			$this->loadAllRows();
		
		return $this->rows;
	}
	
	protected function findWithKeyPairs($keyPairs)
	{
		if (! $this->rows)
			$this->loadAllRows();
		
		$indexes = array();
		$pos = 0;	
		for ($pos = 0; $pos < count($this->rows); $pos++)
		{
			$pass = true;
			foreach ($keyPairs as $key => $value)
			{
				if ($this->rows[$pos][$key] != $value)
				{
					$pass = false;
					break;
				}
			}
			
			if ($pass)
			{
				$indexes[] = $pos; // store index of row
			}
		}
		
		return $indexes;
	}
}
