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

// used by object stores such FileMaker
define("ORDER_ASCEND", "ASC");
define("ORDER_DESCEND", "DESC");
define("RECORD_PK", "__INTERNAL_OS_ID");

define("REL_TO", 0);
define("REL_FROM", 1);
define("REL_MANY", 2);
define("REL_FLAT", 3);
define("REL_TO_ONE", 0);
define("REL_FROM_ONE", 1);
define("REL_FILTERED", 4);

define("SORT_HINT_STRING", 0);
define("SORT_HINT_NUMBER", 1);
define("SORT_HINT_DATE", 2);

abstract class BLGenericRecord implements ArrayAccess, IteratorAggregate
{
	public string|array|null $primaryKeys = null;
	public ?BLDataSource $dataSource = null;
	public array $vars = array();
    
	protected array $relationships = array();
	protected ?string $runtimeKey = null;
	protected bool $valid = true;
    protected bool $isNew = true;
    
    public array $nonEscaped = []; // A whitelist of all fields that should not be escaped on output.

	static public $aggressiveForget = false;
    
	// -------- Class Interfaces

	public function getIterator(): Traversable {
		return new \ArrayIterator($this->vars);
	}

	public function offsetSet(mixed $key, mixed $value): void {
		$this->setValueForKey($value, $key);
	}

	public function offsetExists(mixed $key): bool {
		return array_key_exists($key, $this->vars);
	}

	public function offsetUnset(mixed $key): void
	{
		if (array_key_exists($key, $this->vars))
			unset($this->vars[$key]);
	}

	public function offsetGet(mixed $key): mixed
	{
		$value = $this->field($key) ?? '';
		return in_array($key, $this->nonEscaped) ? $value : BLStringUtils::strip($value);
	}
    
    // ------- Main class methods

	public function __construct($dataSource = null)
	{
		$this->dataSource = $dataSource;
		if (! $this->dataSource)
			$this->dataSource = BLDataSource::defaultDataSource();
		if (! $this->dataSource)
		{
			debugln("### WARNNING: null DS on record creation!");
			dumpStack();
		}
	}

	public function forget(bool $override = false): void
	{
		if (BLGenericRecord::$aggressiveForget || $override)
		{
			$this->valid = false;
			foreach ($this->relationships as $r)
				$r->fault();
			unset($this->primaryKeys, $this->vars, $this->dataSource, $this->relationships);
		}
	}
	
	public function valid(): bool {
		return $this->valid;
	}

	public abstract function tableName(): string;
	public abstract function pkNames(): string|array;

	// Override this method if your your entity table uses a different encoding.
	// Make sure the string you return is a valid encoding name!
	public function tableEncoding(): string
	{
		return "utf8";
	}

	public function primaryKeyNameArray(): array
	{
		$pks = $this->pkNames();
		if (! is_array($pks))
			$pks = array($pks);
		return $pks;
	}
	
	public function pkDescription(): string
	{
		$combined = array();
		foreach ($this->primaryKeys as $key => $value)
		{
			$combined[] = "$key-$value";
		}
		return implode("|", $combined);
	}

	/* 	Override this method if you have any database fields that deal in
		raw binary data.
		WARNING: attributes returned from here do not get escaped when working with the
		MySQLDataSource so be very very careful on trusting the contents of the data
		you are working with!
	*/
	public function binaryAttributes(): array
	{
		return array();
	}
	
	public function unescapedAttributes(): array
	{
		return array();
	}
	
	/*
		Override this method if you have any database fields which should not
		be modified or saved back to the server. This provides only 'quiet' protection.
		It does not pass any errors or warnings back if field data has changed, it merely
		ommits the fields from the save request.
	*/
	public function readOnlyAttributes(): array
	{
		return array();
	}
	
	/*
		If you are using a JSON datasource and need more fine grained control in sorting over how any particular 
		field data type is interpretted then you can use this function to return hints for keys you wish to sort
		by.
		Available Hints: SORT_HINT_STRING, SORT_HINT_NUMBER, SORT_HINT_DATE. The default is always SORT_HINT_STRING.
	*/
	public function sortHintForAttribute(string $key): int
	{
		return SORT_HINT_STRING;
	}
	

    /* Wrapper for has relationship, to allow us only to define name and type - we should try to move all solutions to this approach */
    public function addRelationship(string $name, ?int $type = null, ?string $classname = null, ?string $sourceKey = null, ?string $destKey = null, ?string $mapRel = null, ?BLQualifier $additionalQualifier = null, ?array $sortOrder = null, ?BLDataSource $dataSource = null, bool $ownsDestination = false): void
    {
        if ($type == null) { 
			$type = REL_FROM; 
		}
        if ($classname == null) { 
			$classname = ucfirst($name); 
		}
        $this->hasRelationship(name:$name, type:$type, classname:$classname, sourceKey:$sourceKey, destKey:$destKey, mapRel:$mapRel, additionalQualifier:$additionalQualifier, sortOrder:$sortOrder, dataSource:$dataSource, ownsDestination:$ownsDestination);
    }
    
    public function hasRelationship(string $name, ?int $type = null, ?string $classname = null, ?string $sourceKey = null, ?string $destKey = null, ?string $mapRel = null, ?BLQualifier $additionalQualifier = null, ?array $sortOrder = null, ?BLDataSource $dataSource = null, bool $ownsDestination = false): void
    {
        if ($type == null) { 
			$type = REL_FROM; 
		}
        if ($classname == null) { 
			$classname = ucfirst($name); 
		}
		
		$relationship = null;
		
        switch($type) 
        {
            case(REL_TO): 
                $relationship = new BLToOneRelationship(name:$name, owner:$this, destinationClassName:$classname, sourceKey:$sourceKey, destKey:$destKey, ownsDestination:$ownsDestination, additionalQualifier:$additionalQualifier, sortOrder:$sortOrder, dataSource:$dataSource);
                break;
            case(REL_FROM):
                $relationship = new BLFromOneRelationship(name:$name, owner:$this, destinationClassName:$classname, sourceKey:$sourceKey, destKey:$destKey, ownsDestination:$ownsDestination, additionalQualifier:$additionalQualifier, sortOrder:$sortOrder, dataSource:$dataSource);
                break;
            case(REL_MANY):
                $relationship = new BLToManyRelationship(name:$name, owner:$this, destinationClassName:$classname, sourceKey:$sourceKey, destKey:$destKey, ownsDestination:$ownsDestination, additionalQualifier:$additionalQualifier, sortOrder:$sortOrder, dataSource:$dataSource);
                break;
			case(REL_FILTERED):
				$relationship = new BLFilteredToManyRelationship(name:$name, owner:$this, existingToManyRelationshipName:$mapRel, additionalQualifier:$additionalQualifier, sortOrder:$sortOrder);
				break;
            case(REL_FLAT):
                if (! $sourceKey) {
                    $sourceKey = lcfirst($classname).'ID';
                }
                if (! $destKey) {
                    $destKey = 'id';
                }
                $relationship = new BLFlattenedRelationship(name:$name, owner:$this, destinationClassName:$classname, sourceKey:$sourceKey, destKey:$destKey, joinRelationshipName:$mapRel, additionalQualifier:$additionalQualifier, sortOrder:$sortOrder);
                break;
        }
		if (! $relationship) {
			throw new Exception("Tried to define relationship of unknown type: $type");
		}
		$this->defineRelationship($relationship);
    }
            
	public function defineRelationship(BLRelationship $relationship): void
	{
		$this->relationships[$relationship->name()] = $relationship;
	}
    
	public function className(): string {
		return get_class($this);
	}
    
    
    /*
        ## This method exists to filter all dynamic/relfective method calls that happen 
        automatically via the framework. 
    
        Note that all reflection method calls on a record are now routed through this
        method. Only actions that have been created directly in the subclass can be
        called. While on a rare occasion this might cause an inconvience, the restriction
        should boost security and prevent 'sniper' calls or destructive actions triggered
        by poor field naming.
    */
    protected function callMethodIfExists(string $requestedName): array
    {
        $object = new ReflectionObject($this);
        $methods = get_class_methods($object->getName());
		if (in_array($requestedName, $methods))
		{
			$method = $object->getMethod($requestedName);
			return array(true, $method->invoke($this));
		}
        return array(false, null);
    }
    
    public function field(string $key, $defaultValue = null)
    {
		if (strpos($key, '.') !== false) 
        {
            // keypath.
            if (BLStringUtils::startsWith($key, '@'))
                $key = substr($key, 1);
			$value = $this->valueForKeyPath($key) ?? $defaultValue;
		}
        else if (BLStringUtils::startsWith($key, '@')) 
        {
            // forced lookup of relationship.
            $value = $this->valueForRelationship(substr($key, 1)) ?? $defaultValue;
        }
        else
        {
            /*
                Order of priority:
                    1) Class method, if one exists.
                    2) Field value in entity, if one exists.
                    3) value of relationship, if one exists.
            */
            
            [$ok, $value] = $this->callMethodIfExists($key);
    		if (! $ok) {
                $value = $this->vars[$key] ?? $defaultValue;  
            }
        }

        return $value;
    }
    
    public function fields(...$fields): array
    {
		$default = ''; $count = count($fields);
		if ($count > 0 and is_array($fields[0])) {
			$default = $count > 1 ? $fields[1] : '';
			$fields = $fields[0];
		}
        return array_map(function($field) use ($default) {
			return $this->field($field, $default);
		}, $fields);
    }
    
    public function setFields(array $data): BLGenericRecord
    {
        foreach ($data as $key => $value) {
            if (strpos($key, '.') !== false) {
                throw new Exception("Keypaths should not be passed to setFields(): $key");
            }
            $this->setValueForField($value, $key);
        }
        return $this;
    }
    
    
    // alias
    public function valueForField(string $key, $default = "")
    {
        return $this->field($key, $default);
    }
    
    public function setValueForField($value, string $key): BLGenericRecord
    {
		/* 	First check to see if there is a overriding setter method defined for the path
		 	and if so use that. If not then attempt to get the corresponding
		 	field value. 

			A setter method is in the form of setFieldname(). For example:
			getter = foo()
			setter = setFoo($value)
	
			Also pass the value through any attached transformer.
		*/
        
        $setterMethod = 'set'.strtoupper($key[0]).substr($key, 1);
		$object = new ReflectionObject($this);
		if ($object->hasMethod($setterMethod)) 
			$setter = $object->getMethod($setterMethod)
				->invoke($this, $value);
		else
			$this->vars[$key] = $value;
    
        return $this;
    }

	public function valueForRelationship(string $relationshipName)
	{
		if (empty($this->relationships[$relationshipName])) {
            debugln("## WARNING: No relationship for name $relationshipName", 1);
            debugln("class name: ".$this->className());
            dumpStack();
		    return null;
		}
		$rel = $this->relationships[$relationshipName];
		return $rel->value();
	}
	
	public function arrayValueForRelationship(string $relationshipName): array
	{
		if (empty($this->relationships[$relationshipName]))
			return array();
		$rel = $this->relationships[$relationshipName];
		return $rel->valueAsArray();
	}

	public function valueForKeyPath(string $keypath)
	{
		$result = '';
		$parts = explode(".", $keypath);
		$count = count($parts);
		$currentEntity = $this;
	
		foreach ($parts as $i => $part)
		{
			if (! $part || $currentEntity == null)
				break;
		
			if ($i == $count-1)
				$result = $currentEntity->field($part);
		
			else
				$currentEntity = $currentEntity->valueForRelationship($part);
		}
		return $result;
	}

	public function relationshipForName(string $relationshipName): ?BLRelationship {
        return $this->relationships[$relationshipName] ?? null;
	}

	public function setValueForInverseRelationship(string $sourceKey, string $destKey, $value): void
	{
		$reflectionObject = new ReflectionObject($value);
		$className = $reflectionObject->getName();
		foreach ($this->relationships as $name => $rel)
		{
			if ($rel->sourceKey() == $sourceKey && $rel->destKey() == $destKey && $rel->destinationClassName() == $className)
			{
				$rel->addObject($value, false);
				break;
			}
		}
	}
	
	public function runtimeKey(): string
	{
		if (! $this->runtimeKey)
		{
			$names = $this->primaryKeyNameArray();
			$values = array();
			foreach ($names as $key)
			{
				if (empty($this->vars[$key]))
				{
					$this->runtimeKey = uniqid("blgen|");
					return $this->runtimeKey;
				}
				$values[] = $this->vars[$key];
			}
			$this->runtimeKey = implode("|", $values);
		}
		return $this->runtimeKey;
	}
	
	public function setRuntimeKey($value): void
	{
		if ($this->runtimeKey)
		{
			if (debugLogging() > 0)
			{
				dumpStack("## WARNING: overriding existing runtime key!");
			}
			trigger_error("## WARNING: overriding existing runtime key!");
		}
		$this->runtimeKey = $value;
	}

	public function validateForSave(): void
	{
		
	}
	
	public function awakeFromFetch(): void
	{
		
	}
	
	public function cacheTTL(): int {
		return 60;
	}
    
    public function isNew(): bool {
        return $this->isNew;
    }

	public function save(): void
	{
		$this->validateForSave();
		
		$insertID = $this->dataSource->save($this->tableName(), $this->primaryKeys, $this->vars, $this->tableEncoding(), $this->binaryAttributes(), $this->readOnlyAttributes(), $this->pkNames());
		$pks = $this->primaryKeyNameArray();
		if ($insertID && $insertID != 0 && sizeof($pks) == 1)
		{
			$this->primaryKeys = array();
			$this->primaryKeys[$pks[0]] = $insertID;
			$this->vars[$pks[0]] = $insertID;
			if (debugLogging() > 2) {
				debugln("setting pk ".$pks[0]." to $insertID");
			}
		}
		if ($this->runtimeKey)
			$this->runtimeKey = null;
	}

	public function delete(): bool
	{
		if ($this->primaryKeys) {
			$this->dataSource->delete(primaryKeys:$this->primaryKeys, tableName:$this->tableName());
			return true;
		}
		return false;
	}
	
	static public function sampleEntityForName(string $name): BLGenericRecord
	{
		require_once ROOT."/Entities/$name.php";
		$class = new ReflectionClass($name);
		return $class->newInstance();
	}
	
	public function dataSource(): BLDataSource {
		return $this->dataSource;
	}
	
	/*
		$className: The php class name of the correpsonding entity that is fetched.
		$qualifier: A BLQualifier to be applied to the search.
		$additionalParams: An indexed array of parameters targetted at the specific object store you are using.
		$dataSource: The target object store for the search. Leave as null to use your default object store.
	*/
	static public function find(string $className, ?BLQualifier $qualifier = null, ?array $order = null, array $additionalParams = array(), ?BLDataSource $dataSource = null): array
	{
		if ($dataSource == null)
			$dataSource = BLDataSource::defaultDataSource();
		
		require_once(ROOT."/Entities/$className.php");
		
		$class = new ReflectionClass($className);
		$sample = $class->newInstance($dataSource);
		if ($sample instanceof BLGenericRecord == false) {
			throw new Exception("BLGenericRecord::find error: $className does not extend BLGenericRecord!");
		}
		$tableName = $sample->tableName();
		if (! isset($additionalParams["encoding"]))
			$additionalParams["encoding"] = $sample->tableEncoding();
		if (! isset($additionalParams["tableName"]))
			$additionalParams["tableName"] = $tableName;
		
		if (is_array($order) && count($order) > 0)
		{
			$hints = array();
			foreach ($order as $key => $direction) {
				$hints[$key] = $sample->sortHintForAttribute($key);
			}
			$additionalParams["sortHints"] = $hints;
		}
		
		$result = $dataSource->find($qualifier, $order, $additionalParams);
		if (empty($result))
		{
			unset($className, $qualifier, $result);
			return array();
		}

		$foundSet = array();
		foreach ($result as $row)
		{
			$record = $class->newInstance($dataSource);
			if (debugLogging() > 3)
				debugln("instating new record");
			foreach ($row as $key => $value)
			{
				if (debugLogging() > 3)
					debugln("$key=$value");
				$record->vars[$key] = $value;
			}
            $record->isNew = false;

			$record->primaryKeys = array();
			$pks = $record->primaryKeyNameArray();
			foreach ($pks as $pkName)
			{
				if (! isset($row[$pkName]))
				{
					debugln("Error for setting pk '$pkName' on entity $className: field not set");
					continue;
				}
				if ($row[$pkName] === 0)
					$row["$pkName"] = "0";
				$record->primaryKeys["$pkName"] = $row["$pkName"];
				$record->awakeFromFetch(); // give the entity a chance to examine and modify any of its fetched values.
			}
			
		 	$foundSet[] = $record;
		}
		unset($className, $qualifier, $result);
		return $foundSet;
	}
	
	static public function findSingle(string $className, ?BLQualifier $qualifier = null, array $additionalParams = array(), ?BLDataSource $dataSource = null): ?BLGenericRecord
	{
		if (safeValue($additionalParams, "limit") == "") {
			$additionalParams["limit"] = 1;
		}
		$found = BLGenericRecord::find($className, $qualifier, null, $additionalParams, $dataSource);
		if ($found && count($found) > 0) {
			return $found[0];
		}
		return null;
	}
            
	static public function recordMatchingKeyAndValue(string $entity, string $key, $value, ?BLDataSource $dataSource = null): ?BLGenericRecord
	{
		$qual = new BLKeyValueQualifier($key, OP_EQUAL, $value);
		$found = BLGenericRecord::find($entity, $qual, null, array("limit" => 1), $dataSource);
		return sizeof($found) == 1 ? $found[0] : null;
	}
	
	public function asDictionary(): array
	{
		return [
		    "type" => "BLGenericRecord", 
            "entity" => $this->className(), 
            "vars" => $this->vars, 
            "primaryKeys" => $this->primaryKeys, 
            "tempKey" => $this->runtimeKey()
		];
	}
	
	static public function restoreFromDictionary(array $dict, ?BLDataSOurce $dataSource = null): BLGenericRecord
	{
		if (! safeValue($dict, "type") == "BLGenericRecord")
		{
			throw new Exception("BLGenericRecord::restoreFromDictionary error: dictionary given is not from a generic record!");
		}
		$record = BLGenericRecord::newRecordOfType($dict["entity"], $dataSource);
		$record->vars = $dict["vars"];
		$record->primaryKeys = $dict["primaryKeys"];
		$record->runtimeKey = $dict["tempKey"];
		
		return $record;
	}
	
	static public function newRecordOfType(string $entityName, ?BLDataSource $dataSource = null, ?array $fields = null): BLGenericRecord
	{
		require_once(ROOT."/Entities/$entityName.php");
		$original = new ReflectionClass($entityName);
		$class = $original;
		$parents = [];
		while ($class = $class->getParentClass()) {
		    $parents[] = $class->getName();
		}
		if (! in_array("BLGenericRecord", $parents))
			throw new Exception("BLGenericRecord::newRecordOfType error: $entityName does not extend BLGenericRecord!");
			
		$instance = $original->newInstance($dataSource);
		if ($fields) {
			$instance->vars = array_merge($instance->vars, $fields);
		}
		return $instance;
	}
}
