<?php
/**
*
* BLogic
* The Business Logic Web Framework
* 
* @package		BLogic
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

$dir = dirname(__FILE__);

// include all utility classes and general methods.
foreach (glob("$dir/Utils/*.php") as $filename) {
	require_once $filename;
}

// Buissiness Logic related classes.

require_once "$dir/BL/BLQualifiers.php";
require_once "$dir/BL/BLDataSource.php";
require_once "$dir/BL/BLCSVDataSource.php";
require_once "$dir/BL/BLGenericRecord.php";
require_once "$dir/BL/BLToOneRelationship.php";
require_once "$dir/BL/BLFromOneRelationship.php";
require_once "$dir/BL/BLToManyRelationship.php";
require_once "$dir/BL/BLFlattenedRelationship.php";
require_once "$dir/BL/BLFilteredToManyRelationship.php";

// Presentation logic relatec classes.

require_once "$dir/PL/PLController.php";
require_once "$dir/PL/PLDisplayGroup.php";
require_once "$dir/PL/PLTemplate.php";
require_once "$dir/PL/PLEmail.php";
require_once "$dir/PL/PLCSVExporter.php";

require_once "$dir/Errors.php";
