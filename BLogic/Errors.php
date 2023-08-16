<?php
	/**
	*
	* BLogic
	* The Business Logic Web Framework
	* 
	* @package		BLogic
	* @version		3.0
	* 
	* @license		GPLv3 see license.txt
	* @copyright	2010 Sqonk Pty Ltd.
	*
	* This file is distributed
	* on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either
	* express or implied. See the License for the specific language governing
	* permissions and limitations under the License.
	**/
	
	// ===================================================================================
	// = Error Handling - All errors will be diverted here if the handlers are installed =
	// ===================================================================================
	
	$bl_emailAddresses = array();
	$bl_errorReturnPage = "index.php";
	$bl_errorReportingEnabled = true;
	
	// =====================
	// = Public Interfaces =
	// =====================
	
	function installGeneralErrorHandlers($emails = array(), $errorPage = null)
	{
		global $bl_emailAddresses;
		$bl_emailAddresses = $emails ? $emails : array();
		
		global $bl_errorReturnPage;
		$bl_errorReturnPage = $errorPage;
		
		set_error_handler("_bl_handle_error");
		set_exception_handler("_bl_handle_exception");
	}
	
	function setErrorHandlingEnabled($on)
	{
		global $bl_errorReportingEnabled;
		$bl_errorReportingEnabled = $on;
	}
	
	
	// ====================
	// = Private Handlers =
	// ====================
	
	function _bl_handle_error($errorNo, $errMsg, $fileName, $lineNum) 
	{
		global $bl_errorReportingEnabled;
		if (! $bl_errorReportingEnabled || $errorNo == E_STRICT || $errorNo == 8192 || $errorNo == 2048 || strpos($errMsg, "PEAR") > 0 || strpos($errMsg, "Non-static method") > 0)
		 	return;
		
		$errorType = array(
			E_ERROR              => 'Error',
			E_WARNING            => 'Warning',
			E_PARSE              => 'Parsing Error',
			E_NOTICE             => 'Notice',
			E_CORE_ERROR         => 'Core Error',
			E_CORE_WARNING       => 'Core Warning',
			E_COMPILE_ERROR      => 'Compile Error',
			E_COMPILE_WARNING    => 'Compile Warning',
			E_USER_ERROR         => 'User Error',
			E_USER_WARNING       => 'User Warning',
			E_USER_NOTICE        => 'User Notice',
			E_STRICT             => 'Runtime Notice',
			E_RECOVERABLE_ERROR  => 'Catchable Fatal Error'
		);
		
		$reportComponents = array(
			"Date & Time" => date("Y-m-d H:i:s (T)"),
			"Error Number" => $errorNo,
			"Type" => $errorType[$errorNo],
			"Msg" => $errMsg,
			"Script Name" => $fileName,
			"Script Line Number" => $lineNum
		);		
		$report = BLArrayUtils::implode_assoc("\n", $reportComponents, ": ");
		
		dumpStack($report);
		
		$e = new Exception();
		$report .= "\n\n".$e->getTraceAsString();
		
		global $bl_emailAddresses;
		if (sizeof($bl_emailAddresses) > 0)
		{
			$email = new PLEmail();
			$email->addPlainTextPart($report);
			$email->send(implode(",", $bl_emailAddresses), "server@".domainName(true), "noreply@".domainName(true), appName()." Critical Error");
		}
						
		global $bl_errorReturnPage;
		if ($bl_errorReturnPage)
		{
		    if (session_id() != "")
		    {
		        session_unset();
    			session_destroy();
		    }
			ob_clean();
			header("Location: $bl_errorReturnPage", true, 500);
			die();
		}
	}
	
	function _bl_handle_exception($exception) 
	{
		$report = $exception->getMessage()."\n".$exception->getTraceAsString();
		
		debugln($exception->getMessage());
        debugln($exception->getTraceAsString());
		
		global $bl_emailAddresses;
		if (sizeof($bl_emailAddresses) > 0)
		{
			$email = new PLEmail();
			$email->addPlainTextPart($report);
			$email->send(implode(",", $bl_emailAddresses), "server@".domainName(true), "noreply@".domainName(true), appName()." Exception");
		}
 	 	
		global $bl_errorReturnPage;
		if ($bl_errorReturnPage)
		{
		    if (session_id() != "")
		    {
			    session_unset();
			    session_destroy();
			}
			ob_clean();
			header("Location: $bl_errorReturnPage", true, 500);
			die();
		}
	}
?>