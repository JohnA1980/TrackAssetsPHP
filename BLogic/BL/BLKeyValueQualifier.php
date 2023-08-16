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

require_once dirname(__FILE__)."/BLQualifier.php";

define("OP_EQUAL", "equal");
define("OP_NOT_EQUAL", "not equal");
define("OP_GREATER", "greater than");
define("OP_LESS", "less than");
define("OP_GREATER_EQUAL", "greater than or equal");
define("OP_LESS_EQUAL", "less than or equal");
define("OP_CONTAINS", "contains");
define("OP_NOT_CONTAINS", "not like");
define("OP_EXACT_MATCH", "exact match of");
define("OP_EXACT_NOT_MATCH", "not exact match of");
define("NULL_VALUE", "__NULL");
define("OP_IN", "in");
define("OP_NOT_IN", "not in");
define("OP_BETWEEN", "between");

/*
	A qualifier that holds a left hand value, right hand value and an operator. Depending on the object store used, the left hand and right hand
	values may have different meanings.
*/
class BLKeyValueQualifier extends BLQualifier
{
	protected string $leftHand;
	protected ?string $operator;
	protected ?string $rightHand;

	public function __construct(string $leftHand, ?string $operator = null, string|int|float|null $rightHand = null)
	{
		$this->leftHand = $leftHand;
		$this->operator = $operator;
		$this->rightHand = $rightHand;
	}
	
	public function decodeJSON(string $jsonArray): BLQualifier
	{
		$this->leftHand = $jsonArray["leftHand"];
		$this->operator = $jsonArray["operator"];
		$this->rightHand = $jsonArray["rightHand"];
	}

	public function toString(): string
	{
        $qualString = "(".$this->leftHand." ".$this->operator." ";
        $qualString .= is_array($this->rightHand) ? '("'.join('","', $this->rightHand).'")' : $this->rightHand;
        return $qualString;
	}

	public function leftHand(): string {
		return $this->leftHand;
	}

	public function rightHand(): ?string {
		return $this->rightHand;
	}
	
	public function operator(): ?string {
		return $this->operator;
	}
}
