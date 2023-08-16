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

class BLDataSource
{
	static protected ?BLDataSource $defaultDataSource = null;
	
	static protected array $registeredDataSources = [];
	
	static public function setDefaultDataSource(?BLDataSource $store)
	{
		BLDataSource::$defaultDataSource = $store;
	}
	
	static public function defaultDataSource(): ?BLDataSource
	{
		return BLDataSource::$defaultDataSource;
	}
	
	public function symbolForOperator(string $operator): string
	{
		return "";
	}
	
	static public function dataSource(string $name): ?BLDataSource
	{
		return safeValue(BLDataSource::$registeredDataSources, $name, null);
	}
	
	static public function registerDataSource(BLDataSource $ds, string $name, bool $makeDefault = false)
	{
		if (isset(BLDataSource::$registeredDataSources[$name])) {
			debugln("## WARNING: overwriting previously assigned datasource with name '$name'", 1);
		}
		BLDataSource::$registeredDataSources[$name] = $ds;
		if ($makeDefault) {
			BLDataSource::setDefaultDataSource($ds);
		}
	}
    
    static public function all(): array {
        return self::$registeredDataSources;
    }
}

class BLDataSourceException extends Exception
{
	protected $dataSource;
	
	public function __construct($message, $code, $dataSource)
	{
		parent::__construct($message, $code);
		$this->dataSource = $dataSource;
	}
}
