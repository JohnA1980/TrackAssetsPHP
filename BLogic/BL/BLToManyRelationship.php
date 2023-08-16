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

class BLToManyRelationship extends BLRelationship
{
	protected bool $isFetching = false;
	protected BLQualifier|array|null $additionalQualifier = null;
	protected ?array $sortOrder = null;
	protected ?BLDataSource $dataSource = null;

	public function __construct(string $name, BLGenericRecord $owner, string $destinationClassName, ?string $sourceKey = null, ?string $destKey = null, bool $ownsDestination = false, ?BLQualifier $additionalQualifier = null, ?array $sortOrder = null, ?BLDataSource $dataSource = null)
	{
        if (! $sourceKey) {
            $sourceKey = $owner->pkNames();
            if(is_array($sourceKey)) {
                $sourceKey = join(',', $sourceKey);
            }
        }
        if (! $destKey) {
            $destKey = lcfirst($owner->tableName()).'ID';
        }
		parent::__construct($name, $owner, $destinationClassName, $sourceKey, $destKey, $ownsDestination);
		$this->additionalQualifier = $additionalQualifier;
		$this->sortOrder = $sortOrder;
		$this->dataSource = $dataSource;
		if (! $this->dataSource)
			$this->dataSource = $this->owner->dataSource;
	}
	
	public function dataSource(): ?BLDataSource {
		return $this->dataSource;
	}
	
	public function count(): int
	{
	    if ($this->fetchedValue)
	    {
	        return count($this->fetchedValue);
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
		if (! $this->fetchedValue && ! $this->isFetching && $this->owner->primaryKeys)
		{
			$refl = new ReflectionObject($this->owner);
			$ownerName = $refl->getName();
			debugln("fetching toMany relationship: ".$this->name." from entity: $ownerName", 2);
			if (debugLogging() > 3)
				dumpStack();
			
			$this->isFetching = true;
			if (empty($this->owner->vars[$this->sourceKey]))
				return null;
				
			global $bl_apc_autocaching;
			debugln("bl_apc_autocaching: $bl_apc_autocaching", 3);
				
			$cacheName = "Relationship-".$this->name."For$ownerName-".$this->owner->vars[$this->sourceKey];
			$cachedValue = $bl_apc_autocaching ? apcu_fetch($cacheName) : null;
			if (is_array($cachedValue))
			{
			    debugln("-- using cached records for key: $cacheName", 3);
				$items = array();
				foreach ($cachedValue as $cachedItem)
				 	$items[] = BLGenericRecord::restoreFromDictionary($cachedItem);
				$this->fetchedValue = $items;
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
				$this->fetchedValue = BLGenericRecord::find($this->destinationClassName, $qual, $this->sortOrder, array(), $this->dataSource);

				$cache = array();
				$ttl = 60;
				foreach ($this->fetchedValue as $item)
				{
					if (! $ttl)
						$ttl = $item->cacheTTL();
					$item->setValueForInverseRelationship($this->destKey, $this->sourceKey, $this->owner);
					$cache[] = $item->asDictionary();
				}
				if ($bl_apc_autocaching) {
				    debugln("-- caching records for key: $cacheName", 3);
				    apcu_store($cacheName, $cache, $ttl);
				}
				unset($quals, $qual);
			}
			$this->isFetching = false;
		}

		return $this->fetchedValue;
	}

	public function save(): void
	{
		if ($this->fetchedValue)
		{
			foreach ($this->fetchedValue as $item)
			{
				if (! $item->primaryKeys || $this->ownsDestination)
				{
					// attach the item to the parent via the foreign key if it's not already set.
					if (empty($item->vars[$this->destKey()]))
						$item->vars[$this->destKey()] = $this->owner->vars[$this->sourceKey];
					$item->save();
				}
			}
		}
	}

	public function addObject(BLGenericRecord $value): void
	{
		if ($this->owner->primaryKeys)
		 	$this->value();
        
		else if (! $this->fetchedValue)
		{
			$this->fetchedValue = array();
			global $bl_autoLoadReverseToManys;
			if ($bl_autoLoadReverseToManys)
			{
				if (debugLogging() > 2)
					dumpStack("loaded reverse toMany ".$this->name." from toOne");
				else if (debugLogging() > 1)
					debugln("loaded reverse toMany ".$this->name." from toOne");
				
                $this->value();
			}
		}
			
		if (! in_array($value, $this->fetchedValue))
		{
			$this->fetchedValue[] = $value;
		}
	}
}
