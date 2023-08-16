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

require_once dirname(__FILE__)."/BLRelationship.php";

class BLToOneRelationship extends BLRelationship
{
	protected bool $isFetching = false;
	protected ?BLQualifier $additionalQualifier = null;
	protected ?array $sortOrder = null;
	protected ?BLDataSource $dataSource = null;

	public function __construct(string $name, BLGenericRecord $owner, string $destinationClassName, ?string $sourceKey = null, ?string $destKey = null, bool $ownsDestination = false, ?BLQualifier $additionalQualifier = null, ?array $sortOrder = null, ?BLDataSource $dataSource = null)
	{
        if (!$sourceKey) {
            $sourceKey = $owner->pkNames();
            if (is_array($sourceKey)) {
                $sourceKey = join(',', $sourceKey);
            }
        }
        
        if (!$destKey) {
            $destKey = lcfirst($owner->tableName()).'ID';
        }
        
        parent::__construct($name, $owner, $destinationClassName, $sourceKey, $destKey, $ownsDestination);
		
        $this->dataSource = $dataSource;
        $this->additionalQualifier = $additionalQualifier;
        $this->sortOrder = $sortOrder;
        if (! $this->dataSource)
             $this->dataSource = $this->owner->dataSource;
	}
	
	public function count(): int
	{
	    if ($this->fetchedValue)
	    {
	        return 1;
	    }
	    
	    $quals = array(new BLKeyValueQualifier($this->destKey, OP_EQUAL, $this->owner->vars[$this->sourceKey]));
                if ($this->additionalQualifier)
                {
                        if (is_array($this->additionalQualifier))
                                $quals = array_merge($quals, $this->additionalQualifier);
                        else
                                $quals[] = $this->additionalQualifier;
                }
                $qual = new BLAndQualifier($quals);
		
		
                return $this->dataSource->countForQualifier($this->destinationClassName, $qual);
	}

	public function value()
	{
		if (! $this->fetchedValue && ! $this->isFetching/*  && $this->owner->primaryKeys */)
		{
			$refl = new ReflectionObject($this->owner);
			$ownerName = $refl->getName();
			if (debugLogging() > 2)
			{
				debugln("fetching toOne relationship: ".$this->name." from entity: $ownerName");
			}
			if (debugLogging() > 3)
			{
				dumpStack();
			}
			$this->isFetching = true;
			if (empty($this->owner->vars[$this->sourceKey]))
			{
				if (debugLogging() > 2)
					debugln("value for ".$this->sourceKey." is empty, returning null");
				return null;
			}
			
			global $bl_apc_autocaching;
			
			$cacheName = "Relationship-".$this->name."For$ownerName-".$this->owner->vars[$this->sourceKey];
			$cachedValue = $bl_apc_autocaching ? apc_fetch($cacheName) : null;
			if ($cachedValue)
			{
				debugln("using apc cache for relationship: ".$this->name, 3);
				$this->fetchedValue = BLGenericRecord::restoreFromDictionary($cachedValue);
			}
			else
			{
				$quals = array(new BLKeyValueQualifier($this->destKey, OP_EQUAL, $this->owner->vars[$this->sourceKey]));
				if ($this->additionalQualifier)
				{
				    if (is_array($this->additionalQualifier))
				    	$quals = array_merge($quals, $this->additionalQualifier);
				    else
				        $quals[] = $this->additionalQualifier;
				}
				$qual = new BLAndQualifier($quals);
		
				$params = array("limit" => 1);
				$found = BLGenericRecord::find($this->destinationClassName, $qual, null, $params, $this->dataSource);
				if (sizeof($found) > 0)
				{
					$this->fetchedValue = $found[0];
                    
                    global $bl_autoLoadReverseToManys;
                    if ($bl_autoLoadReverseToManys)
					    $this->fetchedValue->setValueForInverseRelationship($this->destKey, $this->sourceKey, $this->owner);
					
					if (sizeof($found) > 1 && debugLogging() > 1)
					{
						debugln("========= REFERENTIAL INTEGRITY ERROR: ========= ", 2);
						debugln("MORE THAN ONE!");
						dumpStack();
					}
					if ($bl_apc_autocaching) {
					    debugln("-- caching record for key: $cacheName", 3);
						apc_store($cacheName, $this->fetchedValue->asDictionary(), $this->fetchedValue->cacheTTL());
					}
				}
				else if (debugLogging() > 1)
				{
					// if we get nothing back and there's a value for the foreign key then we have ourselves a refirential integridy error.
					debugln("========= REFERENTIAL INTEGRITY ERROR ========= ");
					dumpStack();
				}
				unset($qual, $found);
			}
			$this->isFetching = false;
		}
		return $this->fetchedValue;
	}

	public function save(): void
	{
		if ($this->fetchedValue && (! $this->fetchedValue->primaryKeys || $this->ownsDestination))
		{
			$this->fetchedValue->vars[$this->destKey()] = $this->owner->vars[$this->sourceKey()];
			$this->fetchedValue->save();
		}
	}

	public function addObject(BLGenericRecord $value): void
	{
		$this->fetchedValue = $value;
	}
}
