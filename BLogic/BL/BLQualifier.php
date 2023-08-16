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

abstract class BLQualifier
{
	abstract public function toString(): string;
	
	/*static public function jsonDecode(string $jsonArray): BLQualifier
	{
		$type = $jsonArray->{"type"};
		if ($type == "BLKeyValueQualifier")
		{
			return new BLKeyValueQualifier($jsonArray->{"leftHand"}, $jsonArray->{"operator"}, $jsonArray->{"rightHand"});
		} 
		else if ($type == "BLAndQualifier")
		{
			$and = new BLAndQualifier(null);
			$and->decodeJSON($jsonArray);
			return $and;
		}
		else
		{
			$or = new BLOrQualifier(null);
			$or->decodeJSON($jsonArray);
			return $or;
		}
	}*/
    
    public function __tostring(): string {
        return $this->toString();
    }
}
