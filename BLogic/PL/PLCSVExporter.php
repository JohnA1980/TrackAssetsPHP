<?php
/**
*
* BLogic
* The Business Logic Web Framework
* 
* @package		BLogic
* @subpackage	PL
* @version		5
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

class PLCSVExporter
{
    protected array $headers = [];
    protected array $field_map = [];
    protected ?array $rows = null;
    
    protected string $tmpPath = '';
    protected $fh;
    protected bool $headersWritten = false;
    
    
    /**
     * Create a new CSV Exporter.
     * 
     * @param $tmpPath A custom path to the temporary file that will be used to generate the CSV. If
     * set to NULL then the class will attempt to create a temporary file in the default system
     * temp directory.
     */
    public function __construct(string $tmpPath = '')
    {
        $this->tmpPath = $tmpPath ? $tmpPath : tempnam(sys_get_temp_dir(), 'CSVExp');
    }
    
    public function __destruct()
    {
        $this->closeFH();
        if (! str_starts_with(haystack:$this->tmpPath, needle:'php://') && file_exists($this->tmpPath))
            unlink($this->tmpPath);
    }
    
    protected function closeFH(): void
    {
        if ($this->fh) {
            fflush($this->fh);
            fclose($this->fh);
            $this->fh = null;
        }
    }
    
    /**
     * Set a map for the exporter, which is series of column headers and keypaths
     * that will be used to automatically build the CSV from one or more generic records
     * passed into the class at a later stage.
     * 
     * @param $fieldMap An associative array where the column headers are the array keys and 
     * the keypaths are array values.
     * 
     * @return The CSV exporter object.
     */
    public function map(?array $fieldMap = null): PLCSVExporter
    {
        if ($this->headersWritten) {
            throw new Exception('PLCSVExporter: can not set headers or field map after they have already been output.');
        }
        if ($fieldMap) {
            $this->field_map = $fieldMap;
            $this->headers = array_keys($fieldMap);
            return $this;
        }
        return $this;
    }
    
    /**
     * Map a column header to a set keypath that will be used to acquire the corresponding value
     * from each record.
     * 
     * @param $header The column header.
     * @param $keypath The corresponding keypath that will be called on each generic record.
     * 
     * @return The CSV exporter object.
     */
    public function add_map(string $header, string $keypath): PLCSVExporter
    {
        if ($this->headersWritten) {
            throw new Exception('PLCSVExporter: can not set headers or field map after they have already been output.');
        }
        $this->field_map[$header] = $keypath;
        $this->headers[] = $header;
        
        return $this;
    }
    
    /**
     * Get or set the column headers for the exporter. Passing in nothing or null
     * will return the current headers, otherwise passing in an array will set the
     * headers.
     * 
     * @throws Exception if the headers have already been output.
     * 
     * @return The CSV exporter object when setting headers, otherwise the current header array.
     */
    public function headers(?array $headers = null): PLCSVExporter|array
    {
        if ($this->headersWritten) {
            throw new Exception('PLCSVExporter: can not set headers or field map after they have already been output.');
        }
        if ($headers) {
            $this->headers = $headers;
            if (count($this->field_map) > 0) {
                debugln("## WARNING: subsequent call to headers after the field map was set. This may upset your column order if it is not intentional", 1);
            }
            return $this;
        }
        return $this->headers;
    }
    
    protected function fh()
    {
        if (! $this->fh)
        {
            $this->fh = fopen($this->tmpPath, 'rw+');
            if (! $this->headersWritten) {
                $this->writeHeaders();
            }
        }
        return $this->fh;
    }
    
    protected function writeHeaders(): void
    {
        fputcsv($this->fh, $this->headers);
        $this->headersWritten = true;
    }
    
    /**
     * Add a series of values as the next row in the CSV.
     * 
     * @param $row An associative array where the keys are the column headers that correspdong to the
     * set headers, and the values are the row to be output.
     * 
     * @throws Exception if the headers have not been set.
     * 
     * @return The CSV exporter object.
     */
    public function add_raw_row(array $row): void
    {
        if (count($this->headers) == 0) {
            throw new Exception('PLCSVExporter: tried to add raw row prior to the headers being set.');
        }
        fputcsv($this->fh(), $row);
    }
    
    /**
     * Add a single generic record to the CSV. 
     * 
     * @param $record The record to write out.
     * 
     * @throws Exception If no field map has been set.
     * 
     * @return The CSV exporter object.
     */
    public function add_record(BLGenericRecord $record): PLCSVExporter
    {
        if (count($this->field_map) == 0) {
            throw new Exception('PLCSVExporter: tried to add record before the field map was provided.');
        }
        $keypaths = array_values($this->field_map);
        $values = $record->fields(...$keypaths);
        $this->add_raw_row($values);
        
        return $this;
    }
    
    /**
     * Add multiple generic records to the CSV.
     * 
     * @param $genericRecords The array of records to add.
     * 
     * @see add_record() for possible exceptions that may be thrown.
     * 
     * @return The CSV exporter object.
     */
    public function add_records(array $genericRecords): PLCSVExporter
    {
        foreach ($genericRecords as $r) {
            $this->add_record($r);
        }
        
        return $this;
    }
    
    /**
     * Produce the CSV and return the contents as a string.
     * 
     * @return A string containing the compiled CSV.
     */
    public function export(): string 
    {
        $out = '';
        if ($this->fh) {
            $pos = ftell($this->fh);
            rewind($this->fh);
            $out = fread($this->fh, $pos);
        }
        return $out;
    }
    
    // Converting the object to a string will return the compiled CSV.
    public function __tostring(): string {
        return $this->export();
    }
}
