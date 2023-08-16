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

require_once dirname(__FILE__)."/PLTemplate.php";

define("BL_LOCATION_FORM_DATA", "bl_locFormData");


abstract class PLController
{
	protected ?PLTemplate $template = null;
	protected array $formData;
	protected array $runtimeMethods = array();
	protected array $runtimeVars = array();
    protected ?FormBuilder $form; // form object used for streamlined form building.
    
	static public string $componentROOT;
	static public bool $stripSlashes = false; // set this to true if magic quotes is on and causing pain.

	public function __construct(array $formData, ?string $templateName = null, bool $runKeyPathFix = true)
	{
		if ($templateName)
		{
			$this->bindTemplate($templateName);
		}
		$this->formData = $formData;
		if ($runKeyPathFix)
			$this->fixKeyPaths();
	}
    
	public function appendToResponse(): void
	{
		if (debugLogging() > 2)
			$start = microtime(true);
		if ($this->template)
			echo $this->template->fetch();
		if (debugLogging() > 2)
		{
			$time = (microtime(true)-$start)." seconds";
			debugln("== Component completed rendering in $time");
		}
	}
    
	/*
		Typically you would override this if you needed some kind of safe-guard to prevent further
		processing of the component, by redirecting output to something else.
	*/
	public function handleRequest(): ?PLController
	{
        $locData = sessionValueForKey(BL_LOCATION_FORM_DATA);
        if ($locData) 
        {
			if (debugLogging() > 1) {
				debugln($this->className().": restoring cached component.");
			}
            removeSessionValueForKey(BL_LOCATION_FORM_DATA);
            try {
                $page = @unserialize($locData);
                if ($page instanceof PLController) {
                    return $page;
                }
                else {
                    debugln("## WARNING: attempted to load cached location page that was not a PLController!");
                }
            }
            catch (Exception $error) {
                debugln("## WARNING: unserialise of cached page failed.");
                debugln($locData);
                debugln($error);
            }
        }
		return null;
	}
	
	/*
		Use basic HTTP Auth to authenticate access.

		If you are running PHP in CGI/fCGI mode then the method will look for the presence
		of an externally set authentication key which can be set via htaccess with:

			RewriteEngine on
			RewriteRule .* - [E=REMOTE_USER:%{HTTP:Authorization},L]

		 If you set directive in htaccess to something else other than REMOTE_USER then don't
		 forget to adjust the $cgiOverride variable when you call authenticate().
	*/
	protected function authenticate(string $login, string $password, ?string $realm = null, string $cgiOverride = 'REMOTE_USER'): bool
	{
		$challengeUser = safeValue($_SERVER, "PHP_AUTH_USER");
		$challengePass = safeValue($_SERVER, "PHP_AUTH_PW");
		if (! isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER["REDIRECT_$cgiOverride"])) 
		{
			$header = base64_decode(substr($_SERVER["REDIRECT_$cgiOverride"], 6));
			if (strpos($header, ':') !== false)
		   		list($challengeUser, $challengePass) = explode(':', $header);
		}
		
		if ($challengeUser != $login || $challengePass != $password)
		{
			ob_clean();
			if (! $realm)
				$realm = $this->className();
			header("WWW-Authenticate: Basic realm='$realm'");
			return false;
		} 
		return true;
	}
	
	public function templateForName(string $templateName): ?PLTemplate
	{
		$cr = $this->componentROOT();
		$placesToCheck = array($cr, "$cr/Views");
		
		$templatePath = null;
		foreach ($placesToCheck as $folder)
		{
			$path = "$folder/$templateName.html";
			if (file_exists($path)) {
				$templatePath = $path;
				break;
			}           
			$path = "$folder/$templateName.tpl";
			if (file_exists($path)) {
				$templatePath = $path;
				break;
			}
		}
		
		return ($templatePath) ? new PLTemplate($templatePath) : null;
	}

	public function componentROOT(): string
	{
		return PLController::$componentROOT;
	}
	
	public function className(): string
	{
		return get_class($this);
	}
	
	// @deprecated still works but was built pre-responsive design era.
	protected function templateNameBasedOnDevice(string $baseName, array $supportedDevicesForComponent): string
	{
		$name = $baseName;
		if (is_iPad() && in_array("iPad", $supportedDevicesForComponent))
			$name = $baseName."_iPad";
		else if (is_iPhone() && in_array("iPhone", $supportedDevicesForComponent))
			$name = $baseName."_iPhone";
		else if (is_Android_Tablet() && in_array("AndroidTablet", $supportedDevicesForComponent))
			$name = $baseName."_AndroidTablet";
		else if (is_Android_Phone() && in_array("AndroidPhone", $supportedDevicesForComponent))
			$name = $baseName."_AndroidPhone";

		debugln("choosing template $name", 2);

		return $name;
	}
	
	protected function bindTemplate(string $templateName): void
	{
		$this->template = $this->templateForName($templateName);
        if (! $this->template) {
            debugln("ERROR template for name '$templateName' null");
            dumpStack();
        }
		$this->template->set("controller", $this);
        
        $this->form = new FormBuilder($this);
        $this->template->set("form", $this->form);
	}
    
    // basic SEO methods.
    public function metaKeywords(): string
    {
        return "";
    }
    
    public function metaDescription(): string
    {
        return "";
    }
    
    public function conocialURL(): string
    {
        return domainName()."/".$this->className();
    }
    
    public function pageTitle(): string
    {
        return "";
    }

	public function doNothing()
	{
		// Allows you to safely set action buttons to this method which does nothing.
	}
	
	public function pageWithName(string $pageName, ?array $formData = null): ?PLController
	{
		if (! is_array($formData))
		{
			$formData = $this->formData;
		}
		return PLController::componentWithName($pageName, $formData);
	}
    
    static public function hasComponentWithName(string $pageName): bool
    {
		$cr = PLController::$componentROOT;
		$controllerPath = "$cr/Controllers/$pageName.php";
		if (! file_exists($controllerPath))
		{
			$pageName = basename($pageName);
			$controllerPath = "$cr/Controllers/$pageName.php";
		}
        if (! file_exists($controllerPath))
		{
			$controllerPath = "$cr/$pageName.php";
		}
        return file_exists($controllerPath);
    }
	
	static public function componentWithName(string $pageName, array $formData): ?PLController
	{
		try
		{
			$cr = PLController::$componentROOT;
			$controllerPath = "$cr/Controllers/$pageName.php";
			if (! file_exists($controllerPath))
			{
				$pageName = basename($pageName);
				$controllerPath = "$cr/Controllers/$pageName.php";
			}
            if (! file_exists($controllerPath))
			{
				$controllerPath = "$cr/$pageName.php";
			}
			if (! file_exists($controllerPath))
			{
                if (banningEnabled())
                    logBadPageAttempt($pageName);
                if (notFoundPage()) {
                    goToNotFoundPage();
                } else {
                    debugln("** ERROR: could not find component with name: $pageName, $controllerPath", 1);
	                trigger_error("** ERROR: pageWithName: can not find $pageName");
                }
                return null;
            }

			debugln("loading controller $controllerPath", 2);
			require_once($controllerPath);
			$class = new ReflectionClass(basename($pageName));
				
			return $class->newInstance($formData);
		}
		catch (Exception $error)
		{
			trigger_error($error);
			if (debugLogging() > 1)
				debugln($error);
		}
		return null;
	}
    
    /*
        When 'name' is not passed in the component will store a copy of itself in the session
        and restore itself after page refresh.
    */
    public function goToLocation(string $name = "", array $parameters = null): void
    {
        $domain = domainName();
        if (! $name)
        {
			debugln("goToLocation: serialising page data.", 2);
            setSessionValueForKey(serialize($this), BL_LOCATION_FORM_DATA);

            $name = "$domain/".$this->locationName();
        }
        else if (! BLStringUtils::startsWith($name, $domain))
        {
            $name = "$domain/$name";
        }
        if ($parameters)
        {
            $name .= "/".implode('/', $parameters);
        }
        debugln("goToLocation: $name", 1);
        header("location: $name");
        exit;
    }
	
    public function callMethodIfExists(string $requestedName): array
    {
        $args = [];
        if (BLStringUtils::contains($requestedName, '-')) {
            [$requestedName, $id] = explode('-', $requestedName);
            $args[] = $id;
        }
        
        $object = new ReflectionObject($this);
        $obj = $this;
        $methods = get_class_methods($object->getName());
        if (BLStringUtils::contains($requestedName, "."))
        {
            // keypath style method call. Calls a chain of methods delimetered by a '.'
        
			$newKey = str_replace(['...', '..'], ['_', '._'], implode('.', explode('_', $requestedName)));
        
            $parts = explode(".", $newKey);
            $count = count($parts);
            foreach ($parts as $i => $m) 
			{
                if ($i == 0 && ! in_array($m, $methods)) 
                    break;
    		
    			$method = $object->getMethod($m);
                debugln("calling $m on ".$object->getName(), 1);
                $obj = $method->invoke($obj, ...$args);
			
                if ($i == $count-1) 
                    return array(true, $obj);
            
                else if (! is_object($obj)) {
                    debugln("null or non-object returned from call_method on $m ($requestedName)");
                    return array(false, $obj);
                }
            
                // shift the reference object to the result for the next method call.
                $object = new ReflectionObject($obj);
            }
        }
        else
        {
			// Standard one-shot method call.
            debugln("looking for method: $requestedName", 1);
    		if (in_array($requestedName, $methods))
    			return array(true, $object->getMethod($requestedName)->invoke($obj, ...$args));
        }
    
        return array(false, null);
    }
    
    // Override this if the perma-link is customised.
    public function locationName(): string
    {
        return $this->className();
    }

	public function setFormValueForKey($value, string $key): void
	{
		if (debugLogging() > 3)
			debugln("setting form key $key to '$value'");
		$this->formData[$key] = $value;
	}

	public function formValueForKey(string $key, $default = '')
	{
		return safeValue($this->formData, $key, $default);
	}
	
	/**
	 * Returns a set of form values in one call. If you need to escape HTML on a key then you 
	 * must use formValueForKey separately.
	 */
	public function formValueForKeys(...$fields): array
	{
		return array_map(function($field) {
			return $this->formValueForKey($field);
		}, $fields);
	}
    
    /**
     * Clear out all or part of the submitted form data from the controller.
     * @keypath: limit data removal to keys which begin with the value passed in.
     */
    function clearFormData(string $keypath = ''): void
    {
        foreach ($this->formData as $key => $value) {
            if (! $keypath or str_starts_with(haystack:$key, needle:$keypath)) 
                unset($this->formData[$key]);
        }
    }
	
	public function hasSubmittedFormValueKey(string $key): bool
	{
		return isset($this->formData[$key]);
	}

	public function formValueForKeyPath(string $keyPath, bool $encryptFreshValues = false, bool $escapeHTML = false, string $encoding = "UTF-8")
	{
		$parts = explode(".", $keyPath);
		$last = count($parts)-1;
		$existingValue = safeValue($this->formData, $keyPath);
	
		if (count($parts) < 2 or $existingValue)
			return $existingValue;
	
		else
		{
			$current = $this;
			foreach ($parts as $i => $part)
			{
				if (! is_object($current)) 
					return null;
			
				if ($i == $last)
					return $current->field($part);

				else if ($i == 0) 
					[$_, $current] = $current->callMethodIfExists($part);
			
				else
				{
					if (! $current instanceof BLGenericRecord)
					{
						debugln("form_value_for_keypath: current is not a generic record!");
						debugln($current);
						break;
					}
				
					$current = $current->relationshipForName($part)->value();
				}
			}
			debugln("form_value_for_keypath: value for $keyPath was empty as path could not be resolved!", 3);
		}
	}
	
	// this function does nothing to the submitted data other than create new inline records accordingly to store them in.
	protected function processFormValueKeyPathsForNewObjects(): void
	{
		foreach ($this->formData as $key => $value)
		{
			if (strpos($key, ".") === false || strpos($key, "@") === false)
				continue;
			
			if (debugLogging() > 3)
				debugln("processing $key");

			$parts = explode(".", $key);
			$count = sizeof($parts);
			$current = $this;
			$relationship = null;
			$i = 0;

			foreach ($parts as $part)
			{
				if (debugLogging() > 3)
					debugln("part = $part");
				if ($i == $count-1)
				{
					if (strpos($part, "@") === 0 && $relationship != null)
					{
						// keypath is for a record being edited inline on a form. 
						// record.toManyRelationship.@dataKey:pkValue
						if (! $current)
							$current = array();
							
						$searchSet = explode(":", $part);
						if (sizeof($searchSet) < 1)
						{
							if (debugLogging() > 0)
								debugln("ERROR: '$part' is not a valid inline key value set!");
							trigger_error("'$part' is not a valid inline key value set!");
							die();
						}
						$valueKey = substr($searchSet[0], 1);
						$pkValue = sizeof($searchSet) > 1 ? $searchSet[1] : "";
						$destClassName = $relationship->destinationClassName();
						if (strpos($pkValue, "blgen|") !== false)
						{
							if (debugLogging() > 3)
								debugln("creating new record for $value");
							$current = BLGenericRecord::newRecordOfType($destClassName, $relationship->owner()->dataSource);
							$current->vars[$valueKey] = $value;
							$relationship->addObject($current);
							debugln("adding $value to relationship for ".$relationship->owner()->runtimeKey());
							// sets a consistant key so any other fields for the same inline record will be stored to this instance.
							$current->setRuntimeKey($pkValue);
						}
					}
					else // final part
					{
						$current->vars[$part] = $value;
					}
						
					break;
				}
				else if ($i == 0)
				{
					$currentClass = new ReflectionObject($current);
					$method = $currentClass->getMethod($part);
					$current = $method->invoke($current);
					if (! $current)
						dumpStack("WARNING: first part of keypath was null!");
				}
				else
				{
					if ($current == null)
					{
						debugln("current is null!");
						break;
					}
					if (! is_object($current) && debugLogging() > 3)
						debugln("## current is: $current");
					$relationship = $current->relationshipForName($part);
					$current = $relationship->value();
				}
				$i++;
			}
		}
	}
	
    
	public function processFormValueKeyPathsForSave(array $whitelist = []): void
	{
        try 
		{
			$wildcards = [];
			if (count($whitelist) > 0)
			{
				// scan the whitelist for anyway wildcard inclusions.
				foreach ($whitelist as $el) {
					if (BLStringUtils::endsWith($el, '.*'))
						$wildcards[] = substr($el, 0, -1);
				}
			}
		
			foreach ($this->formData as $key => $value)
			{
                if ($key === 'PHP.SESSION.UPLOAD.PROGRESS') {
                    continue;
                }
				debugln("processing $key", 3);
			
				$widcard_pass = false;
				if (strpos($key, '.') !== false && count($wildcards) > 0)
				{
					// If the current key starts with any of the wildcards found in the
					// whitelist then it passes the test following after.
					foreach ($wildcards as $wc) {
						if (BLStringUtils::startsWith($key, $wc)) {
							$widcard_pass = true;
							break;
						}
					}
				}
			
				if (strpos($key, '.') === false || BLStringUtils::startsWith($key, ".") || 
					(count($whitelist) > 0 && ! in_array($key, $whitelist) && ! $widcard_pass)
					) 
				{
					continue;
				}

				$parts = explode(".", $key);
				$last = count($parts)-1;
				$current = $this;
				foreach ($parts as $i => $part)
				{
					debugln("part = $part", 4);
					if ($i == $last)
						$current->setValueForField($value, $part); // final part
				
					else if ($i == 0)
						[$_, $current] = $current->callMethodIfExists($part);					
					
					else
					{
						if (! $current instanceof BLGenericRecord)
						{
							debugln("current is is not a generic record!");
							debugln($current);
							break;
						}
											
						$current = $current->relationshipForName($part)->value();
					}
				}
			}
        } 
		catch (Exception $e) {
            dumpStack($e->getMessage());
        }
	}

	// ===============
	// = fixKeyPaths =
	// ===============
	/*
		This method allows you to automatically construct your editing fields using key paths. However, for this to work
		it translates all '_' back to '.'.
		The problem is the form data also transforms spaces to '_', so make sure you don't use spaces in your field names!
		
		'..' will be transformed into ._
		'...' will be transformed into _
	*/
	protected function fixKeyPaths(): void
	{
        if(! isset($this->formData)) {
            return;
        }
		foreach ($this->formData as $key => $value)
		{
			if (PLController::$stripSlashes && is_string($value))
				$this->formData[$key] = stripslashes($value);
			if (strpos("_", $key) == -1)
				continue;
			
			$parts = explode("_", $key);
			$newKey = implode(".", $parts);
			$newKey = str_replace("...", "_", $newKey);
			$newKey = str_replace("..", "._", $newKey);
			unset($this->formData[$key]);
			$this->formData[$newKey] = $value;
			debugln("changed keyPath '$key' to '$newKey'", 4);
		}
	}

	public function allFormValuesWhoseKeysStartWith(string $prefix, bool $stripPrefix = false): array
	{
		$array = [];
		$len = strlen($prefix);
		foreach ($this->formData as $key => $value)
		{
			if (str_starts_with(haystack:$key, needle:$prefix))
			{
                if ($stripPrefix) 
                    $key = substr($key, $len);
            
				$array[$key] = $value;
			}
		}
		return $array;
	}
	
	/* 
		We use a different method for rendering the component when it comes to ajax to prevent the 
	 	inherritance of the class methods from rendering at the wrong level. Any ajax components
		should override this.
	*/
	public function renderComponent(): void
	{
		$this->appendToResponse();
	}

	public function asString(): string
	{
		return ($this->template) ? $this->template->fetch() : "";
	}
    
    // output a HTTP response code and echo a message out, then exit immediately.
    public function output_http_response(int $status, string $message): void
    {
        http_response_code($status);
        print $message;
    }
    
    public function output_json(array $array): void
    {
        // setUseHTML(false);
        header("Content-type: text/json");
        if (! isset($array['csrf']))
            $array['csrf'] = sessionValueForKey('transactionID');
        if (! isset($array['transactionID']))
            $array['transactionID'] = sessionValueForKey('transactionID');
        print json_encode($array);
    }
    
    public function output(string $mimeType, string $data, ?string $filename = null): void
    {
       //  setUseHTML(false);
        header("Content-type: $mimeType");
        header("Content-length: ".strlen($data));
        if ($filename) {
            header("Content-Disposition: attachment; filename=$filename");
        }
        print $data;
    }
    
    public function output_file(string $mimeType, string $path, ?string $filename = null): void
    {
        // setUseHTML(false);
        header("Content-type: $mimeType");
        header("Content-length: ".filesize($path));
        if ($filename) {
            header("Content-Disposition: attachment; filename=$filename");
        }
		$fh = fopen($path, "rb");
		fpassthru($fh);  
        fclose($fh);
    }
	
	public function serializedFormData(bool $alwaysUseFresh = false): string
	{
		$archivedData = ($alwaysUseFresh) ? null : $this->formValueForKey("previousPage");
		if (! $archivedData)
			$archivedData = base64_encode(json_encode($this->formData));
		return $archivedData;
	}

	public function archivePreviousFormData(bool $alwaysUseFresh = false): void
	{
		$archivedData = $this->serializedFormData($alwaysUseFresh);
		$this->form->hidden("previousPage", ["value" => $archivedData]);
	}

	public function restorePreviousFormData(?string $correctPageName = null, ?array $archivedData = null): void
	{
        if (! $archivedData)
		    $archivedData = $this->formValueForKey("previousPage"); // fall back to old style hidden field.
		if ($archivedData)
		{
			$this->formData = json_decode(base64_decode($archivedData), true);
			if ($correctPageName)
				$this->formData["page"] = $correctPageName;
			else
				unset($this->formData["page"]);
			unset($this->formData["action"]);
		}
	}
}
