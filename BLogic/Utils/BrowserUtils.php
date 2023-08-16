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

function is_iPad(string $agent = ''): bool
{
	if (! $agent)
		$agent = safeValue($_SERVER, "HTTP_USER_AGENT");
	return (bool)strpos($agent, "iPad");
}

function is_iPhone(string $agent = ''): bool
{
	if (! $agent)
		$agent = safeValue($_SERVER, "HTTP_USER_AGENT");
	return (bool)strpos($agent, "iPhone");
}

function is_Android(string $agent = ''): bool
{
	if (! $agent)
		$agent = safeValue($_SERVER, "HTTP_USER_AGENT");
    $apos = strpos($agent, "Android");
    return ($apos !== false);
}

// NOTE: there is no real distinguishing features between Android tablets and phones,
// they come in all sizes so the browser check is the same.
function is_Android_Phone(string $agent = ''): bool
{
    return is_Android($agent);
}

function is_Android_Tablet(string $agent = ''): bool
{
    return is_Android($agent);
}

function is_MSSurface(string $agent = ''): bool
{
	if (! $agent)
		$agent = safeValue($_SERVER, "HTTP_USER_AGENT");
    return (is_InternetExplorer($agent) && strpos($agent, 'Touch') !== false);
}

function is_InternetExplorer(string $agent = ''): bool
{
	if (! $agent)
		$agent = safeValue($_SERVER, "HTTP_USER_AGENT");
	return ($agent && ( 
        (strpos($agent, 'MSIE') !== false) ||
        (strpos($agent, 'Windows') !== false && strpos($agent, 'Trident') !== false)
    ));
}

function usingIE(string $agent = ''): bool
{
	if (! $agent)
		$agent = safeValue($_SERVER, "HTTP_USER_AGENT");
	return is_InternetExplorer($agent);
}

function is_Safari(string $agent = ''): bool
{
	if (! $agent)
		$agent = safeValue($_SERVER, "HTTP_USER_AGENT");
	return ($agent && (strpos($agent, 'Safari') !== false));
}

function is_Chrome(string $agent = ''): bool
{
	if (! $agent)
		$agent = safeValue($_SERVER, "HTTP_USER_AGENT");
	return ($agent && (strpos($agent, 'Chrome') !== false));
}

function is_Firefox(string $agent = ''): bool
{
	if (! $agent)
		$agent = safeValue($_SERVER, "HTTP_USER_AGENT");
	return ($agent && (strpos($agent, 'Firefox') !== false));
}

function is_Mac(string $agent = ''): bool
{
	if (! $agent)
		$agent = safeValue($_SERVER, "HTTP_USER_AGENT");
    return ($agent && (strpos($agent, 'Mac') !== false));
}

function is_Windows(string $agent = ''): bool
{
	if (! $agent)
		$agent = safeValue($_SERVER, "HTTP_USER_AGENT");
    return ($agent && (strpos($agent, 'Windows') !== false));
}
