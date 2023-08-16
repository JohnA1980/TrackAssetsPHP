<?php
/**
*
* BLogic
* The Business Logic Web Framework
* 
* @package		BLogic
* @subpackage	Utils
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

$bl_apc_autocaching = false;
$bl_autoLoadReverseToManys = false;
$bl_appName = "My Website";
$bl_domainName = "mysite.com";
$bl_notFoundPage = null;


function setAppInfo(string $name, string $url)
{
	global $bl_appName;
	global $bl_domainName;
	$bl_appName = $name;
	$bl_domainName = $url;
}

function setAppName(string $name)
{
    global $bl_appName;
    $bl_appName = $name;
}

function appName(): string
{
	global $bl_appName;
	return $bl_appName;
}

function app_name(): string
{
    return appName();
}

function domainName(bool $noHTTP = false): string
{
	global $bl_domainName;
    
    $url = $bl_domainName;
    if ($noHTTP && strpos($url, "http://") !== false) {
        $url = substr($url, 7);
    }
    else if ($noHTTP && strpos($url, "https://") !== false) {
        $url = substr($url, 8);
    }
	return $url;
}

function domain_name(bool $noHTTP = false): string
{
    return domainName($noHTTP);
}

function setDomainName(string $url): void
{
    global $bl_domainName;
    $bl_domainName = $url;
}

function routingParams(): array
{
    return urlParams();
}

function urlParams(): array
{
    global $bl_url_args;
    if (! isset($bl_url_args) || ! is_array($bl_url_args))
        $bl_url_args = array();
    return $bl_url_args;
}

function url_params(): array
{
    return urlParams();
}

function isAJAX(array $formData): bool
{
    $header = strtolower(safeValue($_SERVER, 'HTTP_X_REQUESTED_WITH', ''));
    $form = strtolower(safeValue($formData, 'X.REQUESTED.WITH', ''));
    return ($header == 'xmlhttprequest' || $form == 'xmlhttprequest');
}

function setNotFoundPage(?string $pageName)
{
    global $bl_notFoundPage;
    $bl_notFoundPage = $pageName;
}

function notFoundPage(): string
{
    global $bl_notFoundPage;
    return $bl_notFoundPage ?? '';
}

function not_found_page(): string
{
    return notFoundPage();
}

function goToNotFoundPage(int $statusCode = 404): void
{
    global $bl_notFoundPage;

    if ($bl_notFoundPage) {
        $url = domainName()."/$bl_notFoundPage";
        header("location: $url", $statusCode);
    }
    else {
        throw new Exception("Bad page/action name");
    }
}

function enforce_ssl(): void
{
	header("strict-transport-security: max-age=600");
	$https = trim(safeValue($_SERVER, "HTTPS", safeValue($_SERVER, "HTTP_USESSL", safeValue($_SERVER, "HTTP_X_FORWARDED_PROTO"))));
	if ($https == "" || $https == "off" || $https == "http")
	{
		$host = safeValue($_SERVER, "HTTP_HOST");
		$redirect = "https://$host".$_SERVER['REQUEST_URI'];
		header('HTTP/1.1 301 Moved Permanently');
	    header("Location: $redirect");
	}
}
