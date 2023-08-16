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

class PLTemplate 
{
    protected array $vars; // Holds all the template variables
    protected ?string $file = null;

    public function __construct(?string $file = null) 
    {
        $this->file = $file;
    }

    public function set(string $name, $value): void
    {
        $this->vars[$name] = $value;
    }
    
	/* 
		Load and return an html file contained anywhere within the 'views' folder.
	*/
	public function partial(string $templateName): PLTemplate
	{
		debugln("fetching partial: $templateName", 3);
		$partial = $this->vars['controller']->templateForName($templateName);
        $partial->set('controller', $this->vars['controller']);
        $partial->set('form', $this->vars['form']);
        return $partial;
	}
    
	// escape and print string to the output.
	public function esc(string $string, string $encoding = 'UTF-8'): void
	{
		print htmlspecialchars($string, ENT_QUOTES | ENT_HTML401, $encoding);
	}

    public function fetch(?string $file = null): string
    {
        $file ??= $this->file;
        
        // automatically include all public class variables of the controller.
		if ($controller = safeValue($this->vars, 'controller')) 
		{
			$obj = new ReflectionObject($controller);
			$props = $obj->getProperties(ReflectionProperty::IS_PUBLIC);
			foreach ($props as $prop) 
			{
				$name = $prop->getName();
				$value = $prop->getValue($controller);
			
				// Automatically escape all strings unless prefixed with a '_'.
				if (BLStringUtils::is_stringable($value) && ! BLStringUtils::startsWith($name, '_'))
					$value = BLStringUtils::strip($value);

				$this->set($name, $value);
			}
		}

        extract($this->vars); // Extract the vars to local namespace
        ob_start();      
		try {
	        include($file);                
	        $contents = ob_get_contents(); 
		}              
        finally {
        	ob_end_clean();
        }
                    
        return $contents;              
    }
    
    public function render(): void
    {
        print $this->fetch();
    }
}

