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

class BLAndQualifier extends BLQualifier
{
	protected array $qualifiers;

	public function __construct(array $array)
	{
		$this->qualifiers = $array;
	}
	
	public function decodeJSON(string $jsonArray): BLQualifier
	{
		$quals = $jsonArray->{"subqualifiers"}->{"_buckets"};
		$this->qualifiers = array();
		foreach ($quals as $qual)
		{
			$type = $qual->{"type"};
			if ($type == "BLKeyValueQualifier")
				$this->qualifiers[] = new BLKeyValueQualifier($qual->{"leftHand"}, $qual->{"operator"}, $qual->{"rightHand"});
			else if ($type == "BLAndQualifier")
			{
				$and = new BLAndQualifier(null);
				$and->decodeJSON($qual);
				$this->qualifiers[] = $and;
			}
			else if ($type == "BLOrQualifier")
			{
				$or = new BLOrQualifier(null);
				$or->decodeJSON($qual);
				$this->qualifiers[] = $or;
			}
		}
	}

	public function toString(): string
	{
		$prepatedStatements = array();
		foreach ($this->qualifiers as $qual)
		{
			$prepatedStatements[] = $qual->toString();
		}
		return "(".implode(" AND ", $prepatedStatements).")";
	}

	public function subQualifiers(): array
	{
		return $this->qualifiers;
	}
}
