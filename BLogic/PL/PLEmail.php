<?php
/**
*
* BLogic
* The Business Logic Web Framework
* 
* @package		BLogic
* @subpackage	PL
* @version		3.0
* @deprecated	Discontinued. Migrate to a modern third-party mail engine.
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

class PLEmail
{
	static protected $smtp_details;
	static protected $usePEAR = true;
	
	protected $parts = array();
	
	static public function setSMTPDetails($host, $authenticated = false, $port = 25, $user = "", $password = "", $authMethod = "LOGIN", $usePEAR = true)
	{
		PLEmail::$smtp_details = array(
			"host" => $host,
			"auth" => $authenticated,
			"port" => $port,
			"username" => $user,
			"password" => $password,
			"authMethod" => $authMethod,
		);
		PLEmail::$usePEAR = $usePEAR;
	}
	
	public function __construct()
	{
		debugln("## PLEmail relies on the ageing PEAR Mail library and has minor incompatibilities with some email servers, such as Microsoft Exchange. You should migrate all email for your app to an alternative 3rd party PHP mail engine, such as PHPMailer or SwiftMailer. This message will only continue to show when debug logging is set to > 0.");
	}
	
	public function addPlainTextPart($text)
	{
		$this->parts[] = array("mime" => "text/plain", "contents" => $text);
	}
	
	public function addHTMLPart($html, $plainTextAlternative = "")
	{
	    // If a plain text part is also provided then split the part into a multipart/alternative set, otherwise
	    // add the html as a single text part.
	    if ($plainTextAlternative)
		    $this->parts[] = array("mime" => "multipart/alternative", "contents" => array($html, $plainTextAlternative));
		else
		    $this->parts[] = array("mime" => "text/html", "contents" => $html);
	}
	
	public function addAttachmentFromPath($path, $fileName = null)
	{
		if (file_exists($path))
		{
			$data = chunk_split(base64_encode(file_get_contents($path)));
			if (! $fileName)
				$fileName = basename($path);
			$finfo = finfo_open(FILEINFO_MIME_TYPE);
			$mimeType = finfo_file($finfo, $path);
			finfo_close($finfo);
			
			$this->parts[] = array("mime" => $mimeType, "contents" => $data, "fileName" => $fileName);
		}
		else
		{
			trigger_error("## File $path could not be added to the email as it could not be found!");
			debugln("## File $path could not be added to the email as it could not be found!");
		}
	}
	
	// data array passed in will be encoded in base 64 and chunked
	public function addAttachmentFromData($data, $mimeType, $fileName)
	{
		$this->parts[] = array("mime" => $mimeType, "contents" => chunk_split(base64_encode($data)), "fileName" => $fileName);
	}
	
	protected function compiledBody($main_boundary, $lineEnding)
	{
		$compiledParts = array();
		
		$compiledParts = array("This is a MIME multipart message.");
		$charset = "utf-8";
		
		foreach ($this->parts as $part)
		{
			if ($part["mime"] == "text/plain")
			{
				$text = $part["contents"];
				$compiledParts[] = "Content-Type:text/plain; charset=\"$charset\"\nContent-Transfer-Encoding: 7bit\n\n$text\n\n";
			}
			else if ($part["mime"] == "text/html")
			{
			    $text = $part["contents"];
			    $compiledParts[] = "Content-Type:text/html; charset=\"$charset\"\nContent-Transfer-Encoding: quoted-printable\nContent-Disposition: inline\nMIME-Version: 1.0\n\n$text\n\n";
			}
			else if ($part["mime"] == "multipart/alternative")
			{
				$sub_boundary = "xxBoundary_".md5(rand())."x";
				$html = $part["contents"][0];
				$text = $part["contents"][1];
				$sub_parts = array(
					"Content-Type:text/plain; charset=\"$charset\"\nContent-Transfer-Encoding: 7bit\n\n$text\n\n",
					"Content-Type:text/html; charset=\"$charset\"\nContent-Transfer-Encoding: 7bit\n\n$html\n\n"
				);
				$compiledParts[] = "Content-Type:multipart/alternative; boundary=$sub_boundary\nContent-Transfer-Encoding: 7bit\n\n".implode("$lineEnding--$sub_boundary".$lineEnding, $sub_parts)."\n\n--$sub_boundary--\n\n";
			}
			else if (isset($part["fileName"]))
			{
				$mimeType = $part["mime"];
				$fileName = $part["fileName"];
				$data = $part["contents"];
				$compiledParts[] = "Content-Type: $mimeType; name=\"{$fileName}\"\nContent-Disposition: attachment; filename=\"$fileName\"\nContent-Transfer-Encoding: base64\n\n$data";
			}
		}
		
		return wordwrap(implode("$lineEnding--$main_boundary".$lineEnding, $compiledParts)."\n\n--$main_boundary--\n", 120);
	}
	
	/**
     * Sanitize an array of mail headers by removing any additional header
     * strings present in a legitimate header's value.  The goal of this
     * filter is to prevent mail injection attacks.
     *
     * @param array $headers The associative array of headers to sanitize.
     *
     * @access private
     */
    protected function _sanitizeHeaders($headers)
    {
        foreach ($headers as $key => $value) 
		{
            $headers[$key] = preg_replace('=((<CR>|<LF>|0x0A/%0A|0x0D/%0D|\\n|\\r)\S).*=i', null, $value);
        }
		return $headers;
    }
	
	public function send($to, $from, $reply_to, $subject, $useAuthentication = false, $additionalHeaders = null)
	{
		$main_boundary = "zzBoundary_".md5(rand())."x";
		
		$lineEnding = "\r\n";
		if (stristr(php_uname(), "WIN") || $useAuthentication)
			$lineEnding = "\r\n";
		
		$body = $this->compiledBody($main_boundary, $lineEnding);
		
		if (is_array($from))
		{
			$pure_from = $from["address"];
			$from = $from["name"]." <".$from["address"].">";
		}
		else
			$pure_from = $from;
		
		$headers = array(
			"From" => "$from",
			"Content-type" => "multipart/mixed; boundary=$main_boundary",
			"X-Mailer" => "PHP/".phpversion(),
			"Reply-To" => $reply_to,
			"Date" => date("D, d M Y H:i:s O"),
		);
        if ($additionalHeaders) {
            $headers = array_merge($headers, $additionalHeaders);
        }
		$headers = $this->_sanitizeHeaders($headers);
		
		debugln("### Sending email...");
		debugln("Subject: $subject");
		debugln("To: $to");
		
		if ($useAuthentication)
		{
			if (! PLEmail::$smtp_details)
			{
				trigger_error("Email could not be sent with authentication as authentication details are not set!");
				return;
			}
			
			debugln("## Sending via PEAR");
			require_once "Mail.php";
			
			if (function_exists("setErrorHandlingEnabled"))
				setErrorHandlingEnabled(false);
			$oldReporting = error_reporting(E_ERROR);

			$headers["To"] = $to;
			$headers["Subject"] = $subject;

			$smtp = Mail::factory('smtp', PLEmail::$smtp_details);
			
			$result = $smtp->send($to, $headers, $body);
			if (PEAR::isError($result))
			{
				debugln($result->getMessage());
				return false;
			}
			debugln("sent!");
			
			if (function_exists("setErrorHandlingEnabled"))
				setErrorHandlingEnabled(true);
			
			error_reporting($oldReporting);
			
			return true;
		}
		else
		{
			$headers_text = BLArrayUtils::implode_assoc($lineEnding, $headers, ": ");
			$sent = mail($to, $subject, $body, $headers_text);
			if ($sent)
				debugln("sent!");
				
			return $sent;
		}
	}
}
