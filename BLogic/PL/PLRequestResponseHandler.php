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

require_once dirname(__FILE__)."/PLController.php";

/*
	This main entry point into BLogic from your web site. This method gathers
	the form data from the correct source and works out which component class
	to use, along with the correct action to call on that class.
*/
function handleRequest(string $defaultPage)
{
    $client_ip = safeValue($_SERVER, "REMOTE_ADDR");
    if (banningEnabled() && $client_ip && ipIsBanned($client_ip)) {
        debugln("rejecting $client_ip, too much suspicious acitivity.");
        http_response_code(403);
        echo "Forbidden.";
        exit;
    }
    
    global $bl_url_args, $bl_allowed_urls;
    
    if (! defined("BL_DEFAULT_PAGE_ID"))
        define("BL_DEFAULT_PAGE_ID", "mpage");
    if (! defined("BL_DEFAULT_ACTION_ID"))
        define("BL_DEFAULT_ACTION_ID", "maction"); 

	if (debugLogging() > 2)
	{
		debugln("================\nNew Request Response Loop");
		$start = microtime(true);
	}
	$shouldURLDecode = false;	
		
	$method = strtolower($_SERVER['REQUEST_METHOD']);
	switch ($method)
	{
		case 'get':
			$formData = &$_GET;
			$shouldURLDecode = true;
			break;
		case 'post':
			$formData = &$_POST;
			break;
		case 'put':
		case 'delete':
			parse_str(file_get_contents('php://input'), $put_vars);
			$formData = $put_vars;
			break;
		default:
			$formData = array();
			break;
	}
	
	if (debugLogging() > 2)
	{   
		foreach ($formData as $key => $value) {
			debug("$key = ");
			debugln($value);
		}
	}
    
    $pageName = BLStringUtils::strip(safeValue($formData, "page", ''));
    if (! empty($pageName)) {
        $pageName = doDecrypt($pageName);
    }
            
    if (! defined("NO_PERMA_LINKS"))
    {
        $uri = $_SERVER["REQUEST_URI"];
        $uri_parts = explode('?', $uri);
        if ((count($uri_parts) == 2) && ($method == 'get')) {
            parse_str($uri_parts[1], $formData);
        }
        
        $url = $uri_parts[0];
        $parts = preg_split('/\/' . basename(domainName()) . '\/?/', $url);

		debugln("perma-link part count for $uri ".count($parts), 2);
        //if ((count($parts) > 1) && (strpos($uri, '.') === FALSE)) 
        if ((count($parts) > 0) && (strpos($uri, '.') === FALSE)) 
        {
		   debugln("examining permalink path...", 2);
           $bl_url_args = explode('/', trim($parts[count($parts)-1], '/'));
           $urlPageName = trim(array_shift($bl_url_args));
           if (empty($pageName) && ! empty($urlPageName)) 
           {
               // If setting page name from URL, make sure it is allowed
               if (isset($bl_allowed_urls) && count($bl_allowed_urls) > 0) 
			   {
				   // white list is engaged so in the interests of simplicity 
				   // transform the page name (aka. route) to lowercase so we 
				   // don't have case sensitivity issues.
				   $urlPageName = strtolower($urlPageName);
				   $pfound = false;
				   foreach ($bl_allowed_urls as $key => $value) {
					   if (strtolower($key) == $urlPageName) {
					   		$pageName = $value;
							$pfound = true;
							debugln("allowed page = $pageName", 2);
							break;
					   }
				   }
                   if (! $pfound) {
                       debugln("attempt to go to invalid perma-link: $urlPageName");
                       if ($client_ip) {
                           debugln("logged source IP of bad perma-link attempt: $client_ip");
                           logBadIPAttempt($client_ip);
                       }
                       if (DEPLOYED)
							goToNotFoundPage();
					   else {
							trigger_error("attempt to go to invalid perma-link: $urlPageName");
					   }
                   }
               } else {
                   //If no 'allowedUrls' array exists, then all are allowed
                   $pageName = $urlPageName;
				   debugln("url page = $pageName", 2);
               }
           }
        } else {
            $bl_url_args = array();
        }
    }
    else
        $bl_url_args = array();
	
	debugln("ip address: ".safeValue($_SERVER, "REMOTE_ADDR"), 2);        
    debugln("request page: $pageName", 2);
    if ($pageName == "") {
        $pageName = $defaultPage;
    }
    debugln("page=$pageName", 2);
	
	if (banningEnabled() && pageIsBanned($pageName)) {
		// block any page name put on the ban list, either by the auto-banner or by the developer.
		debugln("page '$pageName' is on the banned list!");
        if ($client_ip) {
            debugln("logged source IP of bad page attempt: $client_ip");
            logBadIPAttempt($client_ip);
        }
		sleep(2);
        http_response_code(400);
        header("HTTP/1.0 400 Bad Request");
        print "Bad Request.";
		exit;
	}

	if (preg_match("/[^a-zA-Z0-9]/", $pageName) || strlen($pageName) > 256) {
		// block obviously illegal or false page names.
		debugln("Bad page name: $pageName");
        if (banningEnabled()) {
            logBadPageAttempt($pageName);
            if ($client_ip) {
                debugln("logged source IP of bad page attempt: $client_ip");
                logBadIPAttempt($client_ip);
            }
        }
            
		goToNotFoundPage();
	}

	// Load and instantiate the class for the component.
	$startingComponent = PLController::componentWithName($pageName, $formData);
	if (! $startingComponent) {
		goToNotFoundPage();
		return null;
	}
	
	/* Give the component a chance to re-direct to another component before the main 
	 processing happens. This can be useful in various situations such as security 
	 checks that fail. This can be done by overriding the handleRequest() method
	 in your component and returning an instance of some other component.
	*/
            
	$component = letComponentHandleRequest($startingComponent);
	if (debugLogging() > 1)
	{
		$scname = $startingComponent ? $startingComponent->className() : "";
		$cname = $component ? $component->className() : "";
        debugln("starting component: $scname, final: $cname");
	}
	if ($startingComponent === $component || $component == null)
	{
		/* Now that we know the component being used has not be changed, extract from the form data 
		 the action to be called on the component. The action can be
		 any method which takes no parameters. An action will usually perform some kind of logic,
		 or process the form data from this request, and work out what to present to the user next. 
		 
		 If the action returns nothing or null then this method proceeds to ask the component to render
		 to the html page. An action can, if it chooses to, return another component instance which will
		 be used in place to render to the html. In this fashion you can process a request on the existing
		 page and then forward the user onto another location as desired. 
		*/	
		
		$shouldDecrypt = true;
		$action = BLStringUtils::strip(safeValue($formData, "action", ''));
		if ($action != "")
		{
			if ($shouldURLDecode)
			{
				if (debugLogging() > 0)
					debugln("url decoding $action");
				$action = urldecode($action);
			}
			
			if (banningEnabled() && actionIsBanned($action)) {
				debugln("action '$action' is on the banned list!");
				sleep(2);
                http_response_code(400);
                header("HTTP/1.0 400 Bad Request");
                print "Bad Request.";
				exit;
			}
			
			if ($shouldDecrypt) 
			{
				debugln("decoding action '$action'", 2);
				$action = doDecrypt($action); // action is encrypted
				debugln("action=$action", 2);
			}
			
			if (! preg_match("/[a-zA-Z0-9]$/", $action)  || strlen($action) > 256) 
			{
                if (banningEnabled())
                    logBadActionAttempt($action);
				debugln("Bad action name: $action");
                if ($client_ip) {
                    debugln("logged source IP of bad action attempt: $client_ip");
                    logBadIPAttempt($client_ip);
                }
                goToNotFoundPage();
				return;
			}

			try
			{
				list($ok, $newComponent) = $component->callMethodIfExists($action);
				if ($ok && $newComponent)
				{
					// Allow the new component to re-divert if need be.
					$component = letComponentHandleRequest($newComponent, $formData);
				}
				else if (! $ok)
				{
					debugln("Component ".$component->className()." does not have an action named '$action'");
                    if (banningEnabled())
                        logBadActionAttempt($action);
				}
			}
            catch (ReflectionException $re) {
                debugln($re->getMessage());
                if (! DEPLOYED) {
					$cclass = new ReflectionClass($component);
					trigger_error("Component ".$cclass->getName()." does not have an action named $action ".$re->getMessage());
                }
                if (banningEnabled())
                    logBadActionAttempt($action);
            }
			catch (Exception $methodError)
			{
                debugln($methodError->getMessage());
				$cclass = new ReflectionClass($component);
				trigger_error("Component ".$cclass->getName()." does not have an action named $action ".$methodError);
			}
		}
		else if (debugLogging() > 1) {
			debugln(safeValue($_SERVER, "REMOTE_ADDR").": no action given");
		}
	}
	else if (debugLogging() > 1)
		debugln("startingComponent differs after handleRequest");
	
	if (debugLogging() > 2)
	{
		$time = (microtime(true)-$start)." seconds";
		debugln("================\nEnd Request Response Loop - $time");
		//closeDebug();
	}	
		
	// Return the component that will be used to render to the html.
	return $component;
}

/*
	This function works recursively, which means if one component decides to divert to another
	component, the resulting component is also given a chances to handle the request and
	re-divert to somewhere else. This does mean you could end up in an infinite redirect chain
	if you are not careful.
*/
function letComponentHandleRequest(?PLController $component)
{
	if ($component)
	{
        debugln("letComponentHandleRequest: ".$component->className(), 2);
		$newComponent = $component->handleRequest();
		if ($newComponent)
		{
			$component = letComponentHandleRequest($newComponent);
		}
	}
	return $component;
}
