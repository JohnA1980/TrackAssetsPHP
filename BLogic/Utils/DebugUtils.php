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

$bl_logPath = "Debugging.log";
$bl_debugLogging = 0;
$bl_debugLoggingMaxFileSize = 5; // MB

define("__ECHO__", "__ECHO__");
define("__ERROR_LOG__", "ERROR_LOG");
define("__STDOUT__", "__STDOUT__");

function setLogPath(string $path, int $maxSize = 5): void
{
	global $bl_logPath;
	global $bl_debugLoggingMaxFileSize;
	
	$bl_logPath = $path;
	
	if (! is_int($maxSize))
	{
		trigger_error("setLogPath: maxSize value supplied is not an int!");
		die;
	}
	
	$bl_debugLoggingMaxFileSize = $maxSize;
}

function logPath(): string
{
	global $bl_logPath;
	return $bl_logPath;
}
	
function debug($data, ?int $level = null): void
{
	global $bl_debugLogging, $bl_logPath;
	if((isset($level)) && ($bl_debugLogging < $level)) {
		//Don't debug this data at current level
		return;
	}
	if (is_array($data))
		$data = var_export($data, true);
	
	$data = "[".date("d/m/Y h:i:s a")."] $data";
	
	if ($bl_logPath == __ERROR_LOG__)
	{
		error_log($data);
	}
    else if ($bl_logPath == __STDOUT__)
    {
        file_put_contents("php://stdout", $data.PHP_EOL);
    }
	else if ($bl_logPath != __ECHO__)
	{
		$debugLogOut = fopen($bl_logPath, "a");
		fwrite($debugLogOut, $data, strlen($data));
		fflush($debugLogOut);
		$closed = fclose($debugLogOut);
		if (! $closed)
			error_log("### DEBUG LOG FAILED TO CLOSE!!!");
	}
	else
	{
		echo "$data";
	}
}

function debugln($data, ?int $level = null): void
{
	global $bl_debugLogging;
	if((isset($level)) && ($bl_debugLogging < $level)) {
		//Don't debug this data at current level
		return;
	}
	if (is_array($data))
		$data = var_export($data, true);
	debug("$data\n");
}

function debugLogging(): int
{
	global $bl_debugLogging;
	return $bl_debugLogging;
}

function setDebugLogging(int $value): void
{
	global $bl_debugLogging;
	$bl_debugLogging = $value;
}

function dumpStack(string $message = ""): void
{
	$e = new Exception();
	debugln("$message: \n".$e->getTraceAsString());
}

function rollLogFileIfNeeded(): void
{
	global $bl_logPath;
	global $bl_debugLoggingMaxFileSize;
	
	if (file_exists($bl_logPath))
	{
		clearstatcache();
		$bytes = filesize($bl_logPath);
		if ($bytes !== false && ($bytes / 1024 / 1024) > $bl_debugLoggingMaxFileSize)
		{
			// rotate log
			$path = "$bl_logPath-".date("Y-m-d-h-m-s").".log.zip";
            error_log("rotating log file, compressing to $path");

			$zip = new ZipArchive();
			if ($zip->open($path, ZIPARCHIVE::CREATE) !== TRUE)
			{
				error_log("### ERROR rotating log!");
			}
			else
			{
				$zip->addFile($bl_logPath, basename($bl_logPath));
				$zip->close();
				file_put_contents($bl_logPath, "");
				error_log("log file rotated, compressed to $path");
			}
		}
	}
}
