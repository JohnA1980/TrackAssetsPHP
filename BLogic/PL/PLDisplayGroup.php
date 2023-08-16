<?php
/**
*
* BLogic
* The Business Logic Web Framework
* 
* @package		BLogic
* @subpackage	PL
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

require_once dirname(__FILE__)."/../BLogic.php";

class PLDisplayGroup
{
	protected $entityName;
	protected $qualifier;
	protected $order;
	protected $dataSource;
	protected $params;
	
	protected $entriesPerBatch;
	protected $controller;
	protected $formKey;
            
    protected $pageKey;
	
	public function __construct($entityName, $qualifier, $order, $entriesPerBatch, $parent, $key, $params = array(), $dataSource = null, $pageKey = 'p')
	{
		$this->entityName = $entityName;
		$this->qualifier = $qualifier;
		$this->order = $order;
		$this->params = $params;
		$this->dataSource = $dataSource ? $dataSource : BLDataSource::defaultDataSource();
	
		$this->controller = $parent;
		$this->formKey = $key;
        $this->pageKey = $pageKey;
		$this->entriesPerBatch = $entriesPerBatch;
	}
	
	public function dataSource()
	{
		return $this->dataSource;
	}
	
	protected $objects;

	public function objects()
	{
		if (! $this->objects)
		{
			$this->params["limit"] = $this->entriesPerBatch;
			$this->params["offset"] = $this->offset();
			$this->objects = BLGenericRecord::find($this->entityName, $this->qualifier, $this->order, $this->params, $this->dataSource());
		}
		return $this->objects;
	}
	
	protected $recordCount;
	
	public function objectCount()
	{
		if (! $this->recordCount)
		{
			$obj = BLGenericRecord::sampleEntityForName($this->entityName);
			$table = $obj->tableName();
			if (safeValue($this->params, ""))
				$table .= " $this->sqlTableIdentity";
			if ($this->params) {
				unset($this->params["limit"]);
				unset($this->params["offset"]);
			}
			$this->recordCount = $this->dataSource->countForQualifier($table, $this->qualifier, $this->params);
		}
		return $this->recordCount;
	}
	
	public function offset()
	{
		$offset = $this->controller->formValueForKey($this->formKey);
		if ($offset == "")
			$offset = 0;
		return $offset;
	}

	public function nextBatch()
	{
		$offset = $this->offset();
		if ($this->currentBatch() >= $this->batchCount())
			$offset = -$this->entriesPerBatch;
		$offset += $this->entriesPerBatch;
		$this->controller->setFormValueForKey($offset, $this->formKey);
	}
	
	public function requery() {
		$this->objects = false;
		return $this->objects();
	}

	public function previousBatch()
	{
		$offset = $this->offset();
		if ($this->currentBatch() <= 1)
			$offset = ($this->batchCount()-1)*$this->entriesPerBatch;
		else
			$offset -= $this->entriesPerBatch;
		if ($offset < 0)
			$offset = 0;
		$this->controller->setFormValueForKey($offset, $this->formKey);
	}

	public function batchCount()
	{
        return ceil($this->objectCount() / $this->entriesPerBatch);
	}

	public function currentBatch()
	{
		return ceil($this->offset() / $this->entriesPerBatch + 1);
	}
	
	public function entriesPerBatch()
	{
		return $this->entriesPerBatch;
	}
            
    public function setPage() { 
        $pageNo = safeValue($_GET, $this->pageKey);
        if($pageNo) {
            $this->offset = $this->entriesPerBatch * ($pageNo-1);
            $this->controller->setFormValueForKey($this->offset, $this->formKey);
        }
    }   
    
	public function batchArray()
	{
		$batches = [];
		$count = $this->batchCount();
		for ($i = 0; $i < $count; $i++)
		{
			$batch = $i * $this->entriesPerBatch();
			$batches[$batch] = $i+1;
		}
		return $batches;
	}       
}
