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

class BLFlattenedRelationship extends BLRelationship
{
	protected string $joinRelationshipName;
	protected ?BLQualifier $additionalQualifier = null;
	protected ?array $joins = null;

	public function __construct(string $name, BLGenericRecord $owner, string $destinationClassName, string $sourceKey, string $destKey, string $joinRelationshipName, ?BLQualifier $additionalQualifier = null, ?array $sortOrder = null)
	{
		parent::__construct($name, $owner, $destinationClassName, sourceKey:$sourceKey, destKey:$destKey, ownsDestination:false);
        
        $this->additionalQualifier = $additionalQualifier;
		$this->sortOrder = $sortOrder;
		$this->joinRelationshipName = $joinRelationshipName;
	}
	
	public function count(): int
	{
	    if ($this->fetchedValue)
	    {
	        return count($this->fetchedValue);
	    }
	    
	    $joinRel = $this->owner->relationshipForName($this->joinRelationshipName);
	    return $joinRel->count();
	}

	public function value()
	{
		if (! $this->fetchedValue)
		{
			if (debugLogging() > 2)
			{
				$refl = new ReflectionObject($this->owner);
				$ownerName = $refl->getName();
				debugln("fetching flattened relationship: ".$this->name." from entity: $ownerName");
			}
			$this->joins = $this->owner->valueForRelationship($this->joinRelationshipName);
			if (! $this->joins)
			{
				if (debugLogging() > 2)
					debugln("joins are null, returning");
				return null;
			}
			$records = array();
			foreach ($this->joins as $join)
			{
				$toOne = new BLToOneRelationship("", $join, $this->destinationClassName, $this->sourceKey, $this->destKey, $this->ownsDestination, $this->additionalQualifier);
                if ($this->additionalQualifier && ($toOne->count() == 0)) {
                    continue;
                }
                $records[] = $toOne->value();
			}
			$this->fetchedValue = $records;
		}
		return $this->fetchedValue;
	}

	public function addObject(BLGenericRecord $value): void
	{
		if ($this->owner->primaryKeys)
			$this->value();
		else if (! $this->fetchedValue)
			$this->fetchedValue = array();
		$this->fetchedValue[] = $value;

		// create the entry in the join table
		$joinRel = $this->owner->relationshipForName($this->joinRelationshipName);

		require_once(ROOT."/Entities/$joinRel->destinationClassName.php");
		$class = new ReflectionClass($joinRel->destinationClassName);
		$join = $class->newInstance();

		if (! empty($this->owner->vars[$joinRel->sourceKey]))
			$join->vars[$joinRel->destKey] = $this->owner->vars[$joinRel->sourceKey];
		if (! empty($value->vars[$value->pkNames()]))
			$join->vars[$this->sourceKey] = $value->vars[$value->pkNames()]; // this should always be a singular string.
		$joinRel->addObject($join);
	}

	/* 	Currently only refers the save request onto the join table. This means you should
	 	not call the save on the join relationship if you are also calling save here.
	*/
	public function save(): void
	{
		$this->owner->relationshipForName($this->joinRelationshipName)->save();
	}
}
