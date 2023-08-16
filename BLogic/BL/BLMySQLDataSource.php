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

class BLMySQLDataSource extends BLDataSource
{
	protected $link = null;
	protected $open = false;
	
	protected $qualifierOperators = array(
		OP_EQUAL => "=",
		OP_NOT_EQUAL => "!=",
		OP_GREATER => ">",
		OP_LESS => "<",
		OP_GREATER_EQUAL => ">=",
		OP_LESS_EQUAL => "<=",
		OP_CONTAINS => "like",
		OP_EXACT_MATCH => "IS",
		OP_NOT_CONTAINS => "not like",
		OP_EXACT_NOT_MATCH => "IS NOT",
		OP_IN => "IN",
		OP_NOT_IN => "NOT IN",
		OP_BETWEEN => "BETWEEN"
	);
	protected $database;
	protected $user;
	protected $password;
	protected $host;
    protected $port;
    protected $socket;

	public function __construct($host, $user, $password, $database, $port = null, $socket = null)
	{
	    $this->database = $database;
	    $this->user = $user;
	    $this->host = $host;
	    $this->password = $password;
        $this->port = $port ? $port : ini_get("mysqli.default_port");
        $this->socket = $socket ? $socket : ini_get("mysqli.default_socket");
	}

	public function __destruct()
	{
		if ($this->open)
			$this->close();
	}
	
	public function database()
	{
		return $this->database;
	}
	
	public function link()
	{
        if (! $this->link)
        {
            debugln("BLMySQLDataSource: connecting to ".$this->host.":".$this->database." [user: $this->user, pw: ****]", 3);
            if (debugLogging() > 3)
                dumpStack();
            $this->link = new mysqli($this->host, $this->user, $this->password, $this->database);		
            if (! $this->link->connect_error)
            {
                $this->open = true;
                debugln("connected", 3);
            }
            else
            {
                throw new BLDataSourceException("Connection to MySQL failed: ".$this->link->connect_error, $this->link->connect_errno, $this);
            }
        }
        return $this->link;
	}

	public function close()
	{
		if ($this->open)
		{
			$this->link()->close();
			$this->open = false;
		}
		else
			dumpStack("WARNING: MySQL link already closed!");
	}
	
	public function checkConnection()
	{
		if (! $this->link()->ping())
		{
            $this->link = null;
            if (! $this->link()->ping()) {
			    $this->open = false;
			    trigger_error("The MySQL connection has been dropped!", E_USER_NOTICE);
                return false;
            }
		}
		else if (! $this->open)
		{
			trigger_error("The MySQL connection has been closed previously!", E_USER_NOTICE);
			return false;
		}
		return true;
	}
	
	public function processError()
	{
		$error = $this->link()->error;
		$errNo = $this->link()->errno;
		if ($errNo == 1046 && $this->checkConnection())
			return array(-1, 1046);
		
		if ($this->customErrorHandler)
			$error = call_user_func($this->customErrorHandler, $error, $errNo);
							
		return array($error, $errNo);
	}
	
	protected $customErrorHandler;
	
	public function setCustomErrorHandler(?callable $method)
	{
		$this->customErrorHandler = $method;
	}

	public function isOpen(): bool
	{
		return $this->open;
	}
	
	public function symbolForOperator(string $operator): string
	{
		return $this->qualifierOperators[$operator];
	}
	
	public function buildSQLFromQualifier(BLQualifier $qualifier): string
	{
		$str = "";
		if ($qualifier instanceof BLOrQualifier || $qualifier instanceof BLAndQualifier)
		{
			$qualifiers = $qualifier->subQualifiers();
			$prepatedStatements = array();
			foreach ($qualifiers as $qual)
			{
				$prepatedStatements[] = $this->buildSQLFromQualifier($qual);
			}
			$type = $qualifier instanceof BLOrQualifier ? "OR" : "AND";
			$str = "(".implode(" $type ", $prepatedStatements).")";
		}
		else
		{
			$str = $qualifier->leftHand();
			if ($qualifier->operator())
			{
				$str .= " ".$this->symbolForOperator($qualifier->operator())." ";
			}				
			$value = $qualifier->rightHand();
			if ($value !== null)
			{
				//debugln("$str: $value");
				if ($value === NULL_VALUE)
					$value = "NULL";
				else if ($value === 0)
					$value = "'0'";
                else if ($qualifier->operator() == OP_BETWEEN) {
                    $value = '"'.$this->link()->real_escape_string($value[0]).'" AND "'.$this->link()->real_escape_string($value[1]).'"';
                }
				else if (is_array($value)) {
					$valueStr = '(';
					foreach($value as $val) {
						$valueStr .= "'".$this->link()->real_escape_string($val)."',";
					}
					$value = trim($valueStr, ',').')';
				} else
					$value = "'".$this->link()->real_escape_string($value)."'";
				//debugln("final $str: $value");
				if (strpos($str, "?") === false)
					$str .= $value;
				else
					$str = str_replace("?", $value, $str);
			}
		}
		return $str;
	}

	protected function buildPrimaryKeyQualification(array $primaryKeys): string
	{
		$sql = "";
		$i = 0;
		foreach ($primaryKeys as $key => $value)
		{
			if ($i > 0)
				$sql .= " AND ";
			$sql .= $key . " = ".$this->link()->real_escape_string($value);
			$i++;
		}
		return $sql;
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
		
		$this->link()->set_charset($tableEncoding);
		if ($primaryKeys)
		{
			$sql = "UPDATE `$tableName` SET ";
			$i = 0;
			foreach ($vars as $column => $value)
			{
				if (in_array($column, $readOnlyFields))
					continue;
				if ($i > 0)
					$sql .= ", ";
				if ($value === "0" || $value === 0)
					$sql .= "$column = '0'";
				else if ($value === NULL || $value === "")
					$sql .= "$column = NULL";
				else
				{
					if (is_array($value)) {
						debugln("## WARNING: PHP array given for $column");
						debugln($value);
						trigger_error("PHP array given for $column");
					}
					if (! in_array($column, $binaryFields)) {
						$value = $this->link()->real_escape_string(stripslashes($value));
					}
					else
						$value = $this->link()->real_escape_string($value);
					$sql .= $column . " = '".$value."'";
				}
				$i++;
			}
			$sql .= " WHERE ".$this->buildPrimaryKeyQualification($primaryKeys);
		}
		else
		{
			$sql = "INSERT INTO `$tableName` (";
			$i = 0;
			foreach ($vars as $column => $value)
			{
				if (in_array($column, $readOnlyFields))
					continue;
				if ($i > 0)
					$sql .= ", ";
				$sql .= $column;
				$i++;
			}
			$sql .= ") VALUES (";
			$i = 0;
			foreach ($vars as $column => $value)
			{
				if (in_array($column, $readOnlyFields))
					continue;
				if ($i > 0)
					$sql .= ", ";
				if ($value === "0" || $value === 0)
					$sql .= "'0'";
				else if ($value === NULL || $value === "")
					$sql .= "NULL";
				else
				{
					if (is_array($value)) {
						debugln("## WARNING: PHP array given for $column");
						debugln($value);
						trigger_error("PHP array given for $column");
					}
					if (! in_array($column, $binaryFields)) {
						$value = $this->link()->real_escape_string(stripslashes($value));
					}
					else
						$value = $this->link()->real_escape_string($value);
					$sql .= "'".$value."'";
				}
				$i++;
			}
			$sql .= ")";
		}
		if (debugLogging() > 2)
			debugln($sql);
		$result = $this->link()->query($sql);
		if (! $result)
		{
			list($error, $errorNo) = $this->processError();
            debugln("error saving: $errorNo $error", 3);
            if ($error == -1)
			{
				$result = $this->link()->query($sql);
				if (! $result)
				{
					list($error, $errorNo) = $this->processError();
                    debugln("$errorNo $error");
					throw new BLDataSourceException("Could not execute sql\n$sql\n$error", $errorNo, $this);
				}
                else {
                    debugln("save successful on second attempt", 2);
                    if (debugLogging() > 2)
                        debugln($result);
                }
			}
			else if ($error)
			{
                debugln("$errorNo $error");
				throw new BLDataSourceException("Could not execute sql\n$sql\n$error", $errorNo, $this);
			}
            else {
                throw new BLDataSourceException("Could not execute sql\n$sql\n(NO ERROR RETURNED)", $errorNo, $this);
            }
		} 
        else {
            debugln("save successful", 2);
            if (debugLogging() > 2)
                debugln($result);
        }
			
		if (debugLogging() > 2)
		{
			debugln("time: ".(microtime(true)-$reqTime)." seconds.");
			debugln("====");
		}
		return (! $primaryKeys) ? $this->link()->insert_id : null;
	}

	public function delete(string $tableName, array $primaryKeys): void
	{
		$sql = "DELETE FROM `$tableName` WHERE ".$this->buildPrimaryKeyQualification($primaryKeys)." LIMIT 1";
		if (! $result = $this->link()->query($sql))
		{
			list($error, $errorNo) = $this->processError();
            if ($error == -1)
			{
				$result = $this->link()->query($sql);
				if (! $result)
				{
                    list($error, $errorNo) = $this->processError();
					throw new BLDataSourceException("Could not execute sql\n$sql\n$error", $errorNo, $this);
				}
                else {
                    debugln("delete successful on second attempt", 2);
                    if (debugLogging() > 2)
                        debugln($result);
                }
			}
            else if ($error)
			{
				throw new BLDataSourceException("Could not execute sql\n$sql\n$error", $errorNo, $this);
			}
            else {
                throw new BLDataSourceException("Could not execute sql\n$sql\n(NO ERROR RETURNED)", $errorNo, $this);
            }
		}
	}

	public function find(?BLQualifier $qualifier, string|array|null $order, array $additionalParams): array
	{
		$this->link()->set_charset($additionalParams["encoding"]);
		$tableName = safeValue($additionalParams, "tableName");
		if (! $tableName)
		{
			throw new Exception("BLMySQLDataSource: table name was not specified for the search!");
		}
		$distinct = safeValue($additionalParams, "distinct", true);
        $extraSelect = safeValue($additionalParams, "select", '');
		$joins = safeValue($additionalParams, "joins", array());
		$limit = safeValue($additionalParams, "limit");
		$offset = safeValue($additionalParams, "offset");
		$sqlIdentity = safeValue($additionalParams, "sqlTableIdentity");
        $groupBy = safeValue($additionalParams, "group");
		
		if (debugLogging() > 2)
		{
			$reqTime = microtime(true);
			debugln("find request for: $tableName");
			if ($qualifier)
				debugln("qualifier: ".$qualifier->toString());
		}

		$sql = "SELECT";
        $extra = $extraSelect ? ", $extraSelect" : '';
		if ($distinct)
			$sql .= " DISTINCT";
		$sql .= $sqlIdentity ? " $sqlIdentity.* $extra FROM `$tableName`" : " * $extra FROM `$tableName`";
		if ($sqlIdentity)
			$sql .= " $sqlIdentity";
		if (sizeof($joins) > 0)
		{
			foreach ($joins as $join)
			{
				$sql .= (strstr($join, 'JOIN') === FALSE) ? " LEFT JOIN $join" : " $join";
			}
		}
		if ($qualifier)
			$sql .=	" WHERE ".$this->buildSQLFromQualifier($qualifier);
		
        if ($groupBy) {
            $sql .= ' GROUP BY '.$this->link()->real_escape_string($groupBy);
        }
                    
        if (is_string($order) && strlen($order) > 0)
			$sql .= " ORDER BY ".$order;
		else if (is_array($order) && sizeof($order) > 0)
		{
			$orderStr = "";
			foreach ($order as $sortRule => $mode)
			{
				$orderStr .= "$sortRule $mode, ";
			}
			$orderStr = substr($orderStr, 0, strlen($orderStr)-2);
			$sql .= " ORDER BY $orderStr";
		}
                   
		if ($limit)
		{
			$limit = $this->link()->real_escape_string($limit);
			$limitCombine = ($offset != null) ? $this->link()->real_escape_string($offset)."," : "";
			$sql .= " LIMIT ".$limitCombine.$limit;
		}
		if (debugLogging() > 2)
			debugln($sql);
		$result = $this->link()->query($sql, MYSQLI_USE_RESULT);
        if (debugLogging() > 2) {
            list($error, $errorNo) = $this->processError();
            debugln("MySQL Error: $errorNo: $error");
        }
		if (! $result)
		{
			list($error, $errorNo) = $this->processError();
            if ($error == -1)
			{
				$result = $this->link()->query($sql, MYSQLI_USE_RESULT);
				if (! $result)
				{
                    list($error, $errorNo) = $this->processError();
					throw new BLDataSourceException("Could not execute sql\n$sql\n$errorNo $error", $errorNo, $this);
				}
                else {
                    debugln("find successful on second attempt", 2);
                    if (debugLogging() > 2)
                        debugln($result);
                }
			}
			else if ($error)
			{
				throw new BLDataSourceException("Could not execute sql\n$sql\n$error", $errorNo, $this);
			}
            else {
                throw new BLDataSourceException("Could not execute sql\n$sql\n(NO ERROR RETURNED)", $errorNo, $this);
            }
		}
        
        $rows = array();
        if ($result)
        {
            $count = $result->num_rows;
            debugln("MySQL Count: $count", 3);
			$rows = array();
			while ($fetchedRow = $result->fetch_assoc())
			{
				$row = array();
				foreach ($fetchedRow as $key => $value)
				{
					if (debugLogging() > 3)
						debugln("sql key=$key value=$value");
					if ($value === 0)
						$value = "0";
					$row[$key] = $value;
				}
				$rows[] = $row;
			}
			$result->close();
        }
		
		if (debugLogging() > 2)
		{
			debugln("time: ".(microtime(true)-$reqTime)." seconds. found: ".sizeof($rows)." records.");
			debugln("====");
		}
		
		return $rows;
	}
	
	/*
		Standardised count function for all data sources.
		 
		Note: The keys 'tableName', 'limit' and 'offset' from $additionalParams
		are ignored as they are not relevant for the use of this function.
	*/
	public function countForQualifier(string $tableName, ?BLQualifier $qualifier, array $additionalParams = []): int
	{
		if (debugLogging() > 2)
		{
			$reqTime = microtime(true);
			debugln("count request for: $tableName");
			if ($qualifier)
				debugln("qualifier: ".$qualifier->toString());
		}
		
		$joins = safeValue($additionalParams, "joins", array());
		$sqlIdentity = safeValue($additionalParams, "sqlTableIdentity");
        $groupBy = safeValue($additionalParams, "group");

        $sql = 'SELECT '
                .($groupBy ? 'COUNT(DISTINCT '.  mysqli_real_escape_string($this->link(), $groupBy).' )' : ' COUNT(*)'). "AS count FROM `$tableName`" ;
        if ($sqlIdentity)
            $sql .= " $sqlIdentity";
        if (sizeof($joins) > 0)
        {
            foreach ($joins as $join)
            {
                $sql .= (strstr($join, 'JOIN') === FALSE) ? " LEFT JOIN $join" : " $join";;
            }
        }
        if ($qualifier)
            $sql .=	" WHERE ".$this->buildSQLFromQualifier($qualifier);

        debugln($sql, 3);

		$result = $this->link()->query($sql);
		if (! $result)
		{
			list($error, $errorNo) = $this->processError();
            if ($error == -1)
			{
				$result = $this->link()->query($sql);
				if (! $result)
				{
					list($error, $errorNo) = $this->processError();
					throw new BLDataSourceException("Could not execute sql\n$sql\n$error", $errorNo, $this);
				}
                else {
                    debugln("countForQualifier successful on second attempt", 2);
                    if (debugLogging() > 2)
                        debugln($result);
                }
			}
			else if ($error)
			{
				throw new BLDataSourceException("Could not execute sql\n$sql\n$error", $errorNo, $this);
			}
            else {
                throw new BLDataSourceException("Could not execute sql\n$sql\n(NO ERROR RETURNED)", $errorNo, $this);
            }
		}
        
        if ($result)
        {
            $count = $result->num_rows;
            if ($count == 0)
                return 0;
            $row = $result->fetch_assoc();
            $result->close();
        }
		else
			return 0;
		
		unset($tableName, $qualifier, $distinct, $sqlTableIdentity, $sql);
		if (debugLogging() > 2)
		{
			debugln("time: ".(microtime(true)-$reqTime)." seconds.");
			debugln("====");
		}
		return empty($row) ? 0 : $row["count"];
	}
	
	/*
        Backwards-compatibile method for rawRowsForSQL. Please use the latter as this will
        be removed in the future.
    
        @deprecated
    */
    public function rowsForRawSQL(string $sql, string $tableEncoding = "utf8", int &$outCount = null): array
	{
		return $this->rawRowsForSQL($sql, $tableEncoding, $outCount);
	}

	public function rawRowsForSQL(string $sql, string $tableEncoding = "utf8", int &$outCount = null): array
	{
		if (debugLogging() > 2)
		{
			$reqTime = microtime(true);
			debugln("rowsForRawSQL request: $sql");
		}
		
		$this->link()->set_charset($tableEncoding);
		$result = $this->link()->query($sql);
		if ($result === null)
		{
			list($error, $errorNo) = $this->processError();
            if ($error == -1)
			{
				$result = $this->link()->query($sql);
				if ($result === null)
				{
					list($error, $errorNo) = $this->processError();
					throw new BLDataSourceException("Could not execute sql\n$sql\n$error", $errorNo, $this);
				}
                else {
                    debugln("rawRowsForSQL successful on second attempt", 2);
                    if (debugLogging() > 2)
                        debugln($result);
                }
			}
			else if ($error)
			{
				throw new BLDataSourceException("Could not execute sql\n$sql\n$error", $errorNo, $this);
			}
            else {
                throw new BLDataSourceException("Could not execute sql\n$sql\n(NO ERROR RETURNED)", $errorNo, $this);
            }
		}
		
        $rows = array();
        if (is_object($result))
        {
			$count = $result->num_rows;
			if ($outCount !== null) {
                $outCount = $count;
			}
			while ($fetchedRow = $result->fetch_assoc())
			{
                $rows[] = $fetchedRow;
			}
			$result->close();
        }	
		
		if (debugLogging() > 2)
		{
			debugln("time: ".(microtime(true)-$reqTime)." seconds. found: ".sizeof($rows)." records.");
			debugln("====");
		}
		return $rows;
	}
}
