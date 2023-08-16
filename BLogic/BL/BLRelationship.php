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

abstract class BLRelationship
{
	protected $fetchedValue = null;
	protected bool $ownsDestination = false;
	protected BLGenericRecord $owner;
	protected $sourceKey;
	protected $destKey;
	protected $destinationClassName;
	protected $name;
	
	public abstract function value();
	public abstract function save(): void;
	public abstract function addObject(BLGenericRecord $value);
	
	public function __construct(string $name, BLGenericRecord $owner, string $destinationClassName, string $sourceKey, string $destKey, bool $ownsDestination)
	{
		$this->owner = $owner;
		$this->sourceKey = $sourceKey;
		$this->destKey = $destKey;
		$this->destinationClassName = $destinationClassName;
		$this->name = $name;
		$this->ownsDestination = $ownsDestination;
	}
	
	public function fault(): void
	{
		unset($this->fetchedValue);
		$this->fetchedValue = null;
	}
	
	public function clearCache(): void
	{
		$this->fetchedValue = null;
		
		$refl = new ReflectionObject($this->owner);
		$ownerName = $refl->getName();
		$cacheName = "Relationship-".$this->name."For$ownerName-".$this->owner->vars[$this->sourceKey];
		apcu_delete($cacheName);
	}
	
	public function destKey(): string {
		return $this->destKey;
	}
	
	public function sourceKey(): string {
		return $this->sourceKey;
	}
	
	public function name(): string {
		return $this->name;
	}
			
	public function owner(): BLGenericRecord {
		return $this->owner;
	}
	
	public function destinationClassName(): string {
		return $this->destinationClassName;
	}
	
	public function addObjectToBothSides(BLGenericRecord $otherObject): void
	{
		$this->addObject($otherObject);
		$otherObject->setValueForInverseRelationship($this->destKey, $this->sourceKey, $this->owner);
	}
	
	public function valueAsArray(): array
	{
		$value = $this->value();
		if (! $value)
			$value = array();
		else if (! is_array($value))
			$value = array($value);
		return $value;
	}
}
