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
* This file is distributed
* on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either
* express or implied. See the License for the specific language governing
* permissions and limitations under the License.
**/

require_once dirname(__FILE__)."/BLDataSource.php";
require_once dirname(__FILE__)."/BLKeyValueQualifier.php";
require_once dirname(__FILE__)."/../Utils/Utils.php";

class BLJSONDataSource extends BLDataSource
{
	protected string $database_file;
	protected ?array $data_set = null;
	protected bool $auto_init_new;
	protected $lock;
	
	public function __construct(string $filepath, bool $createNewIfNonExistant = true)
	{
		$this->database_file = $filepath;
		$this->auto_init_new = $createNewIfNonExistant;
	}
	
	public function __destruct()
	{
		if ($this->data_set)
			$this->close();
		if ($this->lock)
			$this->releaseLock();
	}
	
	public function database(): string
	{
		return $this->database_file;
	}
	
	public function close()
	{
		if ($this->data_set) {
			$this->data_set = null;
		}
		else {
			dumpStack("WARNING: JSON Database already closed.");
		}
	}
	
	public function isOpen(): bool
	{
		return ($this->data_set != null);
	}
	
	protected function loadDataSet(bool $force_reload = false): void
	{
		if (! $this->data_set || $force_reload)
			$this->data_set = $this->loadDataFromDataFile();
	}
	
	protected function loadDataFromDataFile(): ?array
	{
		$fh = $this->lock;
		$data = null;
		if (file_exists($this->database_file))
		{
			if ($fh) {
				// data file is under exclusive lock while being synchronised so use
				// same resource handle.
				$json = "";
				while (! feof($fh)) {  
					$json .= fread($fh, 8192);
				}
			}
			else {
				$json = file_get_contents($this->database_file);
			}
			
			$data = json_decode($json, true);
		}
		else if ($this->auto_init_new) {
			debugln("JSON Database: no existing file found, initialising empty dataset.", 3);
            $data = [];
		}
		else {
			throw new Exception("JSON Database could not be found.");
		}
		return $data;
	}
	
	private function getLock()
	{
		$mode = file_exists($this->database_file) ? "r+" : "x+";
		$this->lock = fopen($this->database_file, $mode);
		$gotLock = flock($this->lock, LOCK_EX);
		if (! $gotLock) {
			fclose($fh);
		}
		return $gotLock;	
	}
	
	private function releaseLock()
	{
		if (! $this->lock) {
			dumpStack("## WARNING: JSON Datasource tried to release non-existant lock.");
			return;
		}
		$result = flock($this->lock, LOCK_UN);
		if ($result) {
			fclose($this->lock);
			$this->lock = null;
		}
		return $result;
	}
	
	protected function synchroniseDatabaseFile(): void
	{
		if (! $this->lock) {
			trigger_error("JSON Database tried to synchronise without first having a lock.", E_USER_WARNING);
		}
		if (! $this->data_set) {
			trigger_error("JSON Database tried to synchronise before data set has been initialised.", E_USER_WARNING);
		}

		// write changes to file.
		try {
			ftruncate($this->lock, 0);
			rewind($this->lock);
			fwrite($this->lock, json_encode($this->data_set));
			fflush($this->lock);
		}
		catch (Exception $error) {
			
			debugln("JSON Datasource: ".$error->getMessage());
			throw new Exception($error->getMessage());
		}
	}
	
	public function save(string $tableName, ?array $primaryKeys, array $vars, string $tableEncoding, array $binaryFields, array $readOnlyFields, array|string $pkNames): ?int
	{
		if (debugLogging() > 2)
		{
			$reqTime = microtime(true);
			debugln("save request for: $tableName");
			if (debugLogging() > 3)
				debugln($vars);
		}
		
		
		if (! $this->getLock()) {
			throw BLDataSourceException("JSON Datasource: could not acquire lock, save operation aborted.");
		}
		$this->loadDataSet(true);
        $this->data_set ??= [];
		
		$subset = safeValue($this->data_set, $tableName, array());
		$pk_table = safeValue($this->data_set, "__pk_table", array());
		$data_to_save = $vars;
		$newPK = null;
		
		if ($primaryKeys)
		{
			$keys = [];
			if (is_array($pkNames))
			{
				foreach ($pkNames as $key) 
				{
					if (! in_array($key, $primaryKeys)) {
						$this->releaseLock();
						throw new Exception("JSON Datasource: no primary key provided for entity $tableName for key '$key'.");
					}
					$keys[] = $primaryKeys[$key];
				}
				$key = implode(",", $keys);
			}
			else {
				$key = $primaryKeys[$pkNames];
			}
			$subset[$key] = $data_to_save;
		}
		else
		{
			if (is_array($pkNames)) {
				$keys = [];
				foreach ($pkNames as $key) 
				{
					if (! in_array($key, $vars)) {
						$this->releaseLock();
						throw new Exception("JSON Datasource: no primary key provided for entity $tableName for key '$key'.");
					}
					$keys[] = $vars[$key];
				}
				$key = implode(",", $keys);
			}
			else if (in_array($pkNames, $readOnlyFields)) { 
				// if the pk is in the read-only fields then it's expecting an auto-increment.
				$key = safeValue($pk_table, $tableName, 1);
				$data_to_save[$pkNames] = $key; // store the new pk into the dataset being saved.
				$newPK = $key;
				
				// increment the pk_table record.
				$pk_table[$tableName] = ($key+1);
				$this->data_set["__pk_table"] = $pk_table;
			}
			else if (! in_array($pkNames, $vars)) {
				$this->releaseLock();
				throw new Exception("JSON Datasource: no primary key provided for entity $tableName yet the primary key '$pkNames' is not flagged for auto-increment. To flag it as such place it into the array returned by readOnlyAttributes()");
			}
			
			$subset[$key] = $data_to_save;
		}
		
		$this->data_set[$tableName] = $subset;
		
		$this->synchroniseDatabaseFile();
		$this->releaseLock();
		
		if (debugLogging() > 2)
		{
			debugln("time: ".(microtime(true)-$reqTime)." seconds.");
			debugln("====");
		}
		
		return $newPK;
	}
	
	public function delete(string $tableName, array $primaryKeys): void
	{		
		if (debugLogging() > 2)
		{
			$reqTime = microtime(true);
			debugln("delete request for: $tableName");
			debugln($record->vars, 4);
		}
		
		if (! $this->getLock()) {
			throw new Exception("JSON Datasource: could not acquire lock, save operation aborted.");
		}
		$this->loadDataSet(true);
		
		$key = implode(',', array_values($primaryKeys));
		
		$subset = safeValue($this->data_set, $tableName, []);
		if (safeValue($subset, $key) != "")
		{
			debugln("deleting record", 3);
			unset($subset[$key]);
			$this->data_set[$tableName] = $subset;
		
			$this->synchroniseDatabaseFile();
		}
		else {
			$this->releaseLock();
			throw new Exception("JSON Datasource: could not delete record from '$tableName' as it could not be found for pk '$key'");
		}
		
		$this->releaseLock();
		if (debugLogging() > 2)
		{
			debugln("time: ".(microtime(true)-$reqTime)." seconds.");
			debugln("====");
		}
	}
	
	protected function qualifyRecord(array $record, BLQualifier $qualifier): bool
	{
		$result = false;
		if ($qualifier instanceof BLOrQualifier)
		{
			$subquals = $qualifier->subQualifiers();
			foreach ($subquals as $q) {
				$result = $this->qualifyRecord($record, $q);
				if ($result)
					break;
			}
		}
		else if ($qualifier instanceof BLAndQualifier)
		{
			$subquals = $qualifier->subQualifiers();
			foreach ($subquals as $q) {
				$result = $this->qualifyRecord($record, $q);
				if (! $result)
					break;
			}
		}
		else
		{
			$field = $qualifier->leftHand();
			$op = $qualifier->operator();
			$value = $qualifier->rightHand();
			if (! $op) {
				throw new Exception("JSON Datasource: all qualifiers have an operator when using this kind of datasource.");
			}
			
			$fieldValue = safeValue($record, $field);
			if ($op == OP_EQUAL) {
				$result = ($fieldValue == $value);
			}
			else if ($op == OP_GREATER) {
				$result = ($fieldValue > $value);
			}
			else if ($op == OP_LESS) {
				$result = ($fieldValue < $value);
			}
			else if ($op == OP_GREATER_EQUAL) {
				$result = ($fieldValue >= $value);
			}
			else if ($op == OP_LESS_EQUAL) {
				$result = ($fieldValue <= $value);
			}
			else if ($op == OP_CONTAINS || $op == OP_IN) {
				if (BLStringUtils::endsWith($value, "%")) {
					$value = substr($value, 0, -1);
					$fieldValue = safeValue($record, $field);
					$result = BLStringUtils::startsWith($fieldValue, $value);
				}
				else if (BLStringUtils::startsWith($value, "%")) {
					$value = substr($value, 1);
					$fieldValue = safeValue($record, $field);
					$result = BLStringUtils::endsWith($fieldValue, $value);
				}
				else {
					$result = (strpos($fieldValue, $value) !== false);
				}
			}
			else if ($op == OP_NOT_IN) {
				$result = (strpos($fieldValue, $value) === false);
			}
			else if ($op == OP_EXACT_MATCH) {
				$result = ($fieldValue === $value);
			}
			else if ($op == OP_EXACT_NOT_MATCH) {
				$result = ($fieldValue !== $value);
			}
			else if ($op == OP_BETWEEN) {
				$result = ($fieldValue >= $value && $fieldValue <= $value);
			}
		}
		return $result;
	}
	
	public function find(?BLQualifier $qualifier, ?array $order, array $additionalParams): array
	{
		$tableName = safeValue($additionalParams, "tableName");
		if (! $tableName) {
			throw new Exception("JSON Datasource: table name was not specified for the search!");
		}
		if (debugLogging() > 2)
		{
			$reqTime = microtime(true);
			debugln("find request for: $tableName");
			if ($qualifier)
				debugln("qualifier: ".$qualifier->toString());
		}
		$this->loadDataSet();
		
		$subset = safeValue($this->data_set, $tableName, array());
		$found = array();
		if ($qualifier)
		{
			foreach ($subset as $record)
			{
				$pass = $this->qualifyRecord($record, $qualifier);
				debugln("qual: $pass", 3);
				if ($pass) {
					$found[] = $record;
				}
			}
		}
		else
		{
			$found = $subset;
		}
		
		$limit = safeValue($additionalParams, "limit");
		$offset = safeValue($additionalParams, "offset");
		
		if ($offset)
		{
			$found = array_slice($found, $offset, $limit);
		}
		else if ($limit)
		{
			$found = array_slice($found, 0, $limit);
		}
		
		if (is_array($order) && count($order) > 0) 
		{
			$hints = safeValue($additionalParams, "sortHints", array());
			usort($found, function($a, $b) use(&$order, &$hints) {
				$result = 0;
				foreach ($order as $key => $direction)
				{
					$SORT_UP = ($direction == ORDER_ASCEND) ? -1 : 1;
					$SORT_DOWN = ($direction == ORDER_ASCEND) ? 1 : -1;
					// it is assumed that both records being compared are the same type of entity.
					$hint = safeValue($hints, $key);
					$valueA = safeValue($a, $key);
					$valueB = safeValue($b, $key);
					
					if ($hint == SORT_HINT_DATE || BLDateUtils::isDate($valueA) || BLDateUtils::isDate($valueB)) 
					{
						// date sort
						$timeA = strtotime($valueA);
						$timeB = strtotime($valueB);
						if ($timeA > $timeB)
							$result = $SORT_DOWN;
						else if ($timeB > $timeA)
							$result = $SORT_UP;
					}
					else if ($hint == SORT_HINT_NUMBER || is_numeric($valueA) || is_numeric($valueB)) 
					{
						if ($valueA > $valueB)
							$result = $SORT_DOWN;
						else if ($valueA < $valueB)
							$result = $SORT_UP;
					}
					else 
					{
						$result = strcmp($valueA, $valueB);
					}
					
					if ($result != 0)
						break; // non-even results return immediately, even result cycle to the next sorting key.
				}
				return $result;
			});
		}
		
		if (debugLogging() > 2)
		{
			debugln("time: ".(microtime(true)-$reqTime)." seconds.");
			debugln("====");
		}
		return $found;
	}
	
	public function countForQualifier(string $tableName, ?BLQualifier $qualifier, array $additionalParams = []): int
	{
		$additionalParams["tableName"] = $tableName;
		return count($this->find(qualifier:$qualifier, additionalParams:$additionalParams, order:null));
	}
}
