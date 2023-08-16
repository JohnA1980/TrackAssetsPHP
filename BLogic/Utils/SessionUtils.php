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

define("BL_SESSION_TOKEN", "BLSESSIONTOKEN");
define("BL_SESSION_IP", "clientIP");

function sessionID(): string {
    return session_id();
}

function init_session(): void {
    session_start();
}

function sessionKeysExist(...$keys): bool
{
    foreach ($keys as $key) {
        if (! array_key_exists(array:$_SESSION, key:$key))
            return false;
    }
    return true;
}

function sessionValueForKey($key, $defaultValue = null)
{
    return safeValue($_SESSION, $key, $defaultValue);
}

function setSessionValueForKey($value, $key): void
{
    $_SESSION[$key] = $value;
}

function storeSessionValues(array $keyedArray): void
{
    foreach ($keyedArray as $key => $value)
        $_SESSION[$key] = $value;
}

function removeSessionValueForKey(string $key): void
{
    unset($_SESSION[$key]);
}

function removeSessionKeys(...$keys): void
{
    foreach ($keys as $key) 
		removeSessionValueForKey($key);
}

function regenSessionID(): void
{
	session_regenerate_id(true);
}

function regen_session_id(): void
{
	regenSessionID();
}

function update_session_csrf(): void
{
	$token = sessionValueForKey("SERVER_GENERATED_SID");
	if (! $token) {
		regen_csrf();
	}
	else if (valid_session_csrf()) {
		setcookie(BL_SESSION_TOKEN, $token, time()+60*60*24*5, '/');
	}
}

function regen_csrf()
{
	$bytes = openssl_random_pseudo_bytes(32);
	$token = password_hash($bytes, PASSWORD_DEFAULT);
	setSessionValueForKey($token, "SERVER_GENERATED_SID");
	
	$ip = getenv("REMOTE_ADDR");
	setSessionValueForKey($ip, BL_SESSION_IP);
	setcookie(BL_SESSION_TOKEN, $token, time()+60*60*24*5, '/');
}

function valid_session_csrf(): bool
{
	$sessionToken = sessionValueForKey("SERVER_GENERATED_SID");
	$cookieToken = safeValue($_COOKIE, BL_SESSION_TOKEN);
	$formToken = safeValue($_POST, "csrfToken");

	if (! is_string($sessionToken) or ! is_string($cookieToken) or ! hash_equals($sessionToken, $cookieToken))
	{
		dumpStack("CSRF mismatch: session ($sessionToken) vs cookie ($cookieToken)");
		return false;
	}
	else if (count($_POST) > 0 and (! is_string($formToken) or ! hash_equals($sessionToken, $formToken)))
	{
		dumpStack("CSRF mismatch: session ($sessionToken) vs form ($formToken)");
		return false;
	}
	else if ((DEPLOYED or TESTING) and ! defined('CSRF_NO_IP_MATCH') and getenv("REMOTE_ADDR") != sessionValueForKey(BL_SESSION_IP))
	{
		$ip = getenv("REMOTE_ADDR");
		$sip = sessionValueForKey(BL_SESSION_IP);
		dumpStack("CSRF IP mismatch: request ($ip) vs session ($sip)");
		return false;
	}
	return true;
}

function destroy_session(): void
{
    destroySession();
}

function destroySession(): void
{	
	if (sessionValueForKey("SERVER_GENERATED_SID")) {
		setcookie(BL_SESSION_TOKEN, "", 1);
		setcookie(BL_SESSION_TOKEN, false);
		unset($_COOKIE[BL_SESSION_TOKEN]);
	}
	
	session_unset();
	session_destroy();       
}
