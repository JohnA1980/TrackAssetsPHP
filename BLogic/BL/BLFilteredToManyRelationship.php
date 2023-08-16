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

/*
	Use this class to define a filtered subset of an existing relationship. As all relationships
	must have unique joins and names this allows you to create additional links between
	tables that work with only a portion of the related data.
*/
class BLFilteredToManyRelationship extends BLRelationship
{
	protected string $existingToManyRelationshipName;
	protected BLQualifier $additionalQualifier;
	protected ?array $sortOrder;
	protected bool $isFetching = false;
	
	public function __construct(string $name, BLGenericRecord $owner, string $existingToManyRelationshipName, BLQualifier $additionalQualifier, ?array $sortOrder = null)
	{
		parent::__construct($name, $owner, $owner->relationshipForName($existingToManyRelationshipName)->destinationClassName(), uniqid(), uniqid(), false);
        
		$this->existingToManyRelationshipName = $existingToManyRelationshipName;
		$this->additionalQualifier = $additionalQualifier;
		$this->sortOrder = $sortOrder;
	}
	
	public function count(): int
	{
	    if ($this->fetchedValue)
	    {
	        return count($this->fetchedValue);
	    }
	    $existingRel = $this->owner->relationshipForName($this->existingToManyRelationshipName);
	    $quals = array(new BLKeyValueQualifier($existingRel->destKey, OP_EQUAL, $this->owner->vars[$existingRel->sourceKey]));
		if ($this->additionalQualifier)
		{
			if (is_array($this->additionalQualifier))
				$quals = array_merge($quals, $this->additionalQualifier);
			else
				$quals[] = $this->additionalQualifier;
		}
		$qual = new BLAndQualifier($quals);
		
		return $this->dataSource->countForQualifier($existingRel->destinationClassName, $qual);
	}
	
	public function value()
	{
		if (! $this->fetchedValue && ! $this->isFetching && $this->owner->primaryKeys)
		{
			if (debugLogging() > 2)
			{
				$refl = new ReflectionObject($this->owner);
				$ownerName = $refl->getName();
				debugln("fetching filtered toMany relationship: ".$this->name." from entity: $ownerName");
			}
			$existingRel = $this->owner->relationshipForName($this->existingToManyRelationshipName);
			if (debugLogging() > 3)
			{
				dumpStack();
			}
			$this->isFetching = true;
			if (empty($this->owner->vars[$existingRel->sourceKey]))
				return null;
			$quals = array(new BLKeyValueQualifier($existingRel->destKey, OP_EQUAL, $this->owner->vars[$existingRel->sourceKey]));
			if ($this->additionalQualifier)
			{
				if (is_array($this->additionalQualifier))
					$quals = array_merge($quals, $this->additionalQualifier);
				else
					$quals[] = $this->additionalQualifier;
			}
			$qual = new BLAndQualifier($quals);
			$this->fetchedValue = BLGenericRecord::find($existingRel->destinationClassName, $qual, $this->sortOrder, array(), $existingRel->dataSource());
			
			// NOTE: no inverse relationships are set for this class
			
			unset($quals, $qual);
			$this->isFetching = false;
		}
		return $this->fetchedValue;
	}
	
	public function addObject(BLGenericRecord $value): void
	{
		trigger_error("attempted to add object to filtered relationship", E_USER_WARNING);
	}
	
	public function save(): void
	{
		trigger_error("you can not call save on a filtered relationship!", E_USER_WARNING);
	}
}
