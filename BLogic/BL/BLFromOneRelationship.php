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
 * */
require_once dirname(__FILE__) . "/BLToOneRelationship.php";

/**
 * Very simple class to differentiate from and to relationship.
 * This meaning each can have a simplified default constructor, although functionality is identical
 */
class BLFromOneRelationship extends BLToOneRelationship 
{
    public function __construct(string $name, BLGenericRecord $owner, string $destinationClassName, ?string $sourceKey = null, ?string $destKey = null, bool $ownsDestination = false, ?BLQualifier $additionalQualifier = null, ?array $sortOrder = null, ?BLDataSource $dataSource = null) 
	{
        if (! $sourceKey) {
            $sourceKey = lcfirst($destinationClassName) . 'ID';
        }
        
        if (! $destKey) {
            $destKey = 'id';
        }
        
        parent::__construct($name, $owner, $destinationClassName, $sourceKey, $destKey, $ownsDestination, $additionalQualifier, $sortOrder, $dataSource);
    }
}

