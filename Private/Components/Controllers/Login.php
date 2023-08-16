<?php 
	require_once BLOGIC."/BLogic.php"; 
	
	 
	class Login extends PLController 
	{ 
		public $errorMessage;
		
		public function __construct($formData) 
		{ 
			parent::__construct($formData, "Login");
			
			addJS("js/prototype.js");
			addJS("js/scriptaculous/scriptaculous.js");
            addJS("js/ajax.js");
			
			$_SESSION["test"] = "yes";
		} 
		
		public function appendToResponse(): void
		{
			global $useHTML;
			if ($useHTML) // only render the page when not called from ajax.
				parent::appendToResponse();
		}
		
		public function handleRequest(): ?PLController
		{
			$page = parent::handleRequest();
			if (! $page)
			{
                if (safeValue($_SESSION, "SERVER_GENERATED_SID") == "yes" && 
                    safeValue($_SESSION, "userID") != "") {
    					$this->nextPage();
                }
                
                $error = safeValue($_SESSION, "errorMessage");
                if ($error) {
                    $this->errorMessage = $error;
                    unset($_SESSION["errorMessage"]);
                }
			}
			return $page;
		}
		
        public function renderComponent(): void
        {
        }
		
		protected function nextPage()
		{
			header("location: AssetList");
		}
		
		public function validateLogin()
		{
			global $useHTML;
			$useHTML = false;
			ob_clean();
			ob_start();
			if (! isset($_SESSION["test"]))
			{
                $result = array("error" => 1, "message" => "Sorry your browser does not appear to have cookies enabled. Please ensure you're browser is set to accept coookies before continuing.");
                echo json_encode($result);
				return;
			}
			
			// 1 second delay to help prevent brute force bots.
			sleep(1);
			
			$login = $this->formValueForKey("login"); 
			if ($login == "")
			{
                $result = array("error" => 1, "message" => "The login you entered is not valid.");
                echo json_encode($result);
				return;
			} 
			$password = $this->formValueForKey("password");
			if ($password == "")
			{
                $result = array("error" => 1, "message" => "The password you entered is not valid.");
                echo json_encode($result);
				return;
			}
			
			$quals = array(
				new BLKeyValueQualifier("login", OP_EQUAL, $login),
				new BLKeyValueQualifier("password", OP_EQUAL, $password),
			);
			$result = BLGenericRecord::find("User", new BLAndQualifier($quals), null, array("limit" => 1));
			
			if (sizeof($result) == 1)
			{
				$user = $result[0];
				
				session_regenerate_id(true);
				$_SESSION["SERVER_GENERATED_SID"] = "yes";
				$_SESSION["LAST_REQUEST"] = time();
				$_SESSION["userID"] = $user->vars["userID"];
				$_SESSION["user"] = $user->asDictionary();
				
				$_SESSION["investmentPortfolioID"] = $user->firstPortfolioID();
				unset($_SESSION["test"]); // clear the test var.
				
				if ($this->formValueForKey("keepSignedIn") == 1)
				{
					$_SESSION["keepSignedIn"] = "yes";
					session_set_cookie_params(259200);
				}
				
				$result = array("error" => 0);
                echo json_encode($result);
			}
			else
			{
                // output to ajax call.
				$result = array("error" => 1, "message" => "Password And/Or Login incorrect.");
                echo json_encode($result);
			}
		}
		
		public function sendPasswordReminder()
		{
			global $useHTML;
			$useHTML = false;
			ob_clean();
			ob_start();
			
			// 1 second delay to help prevent brute force bots.
			sleep(1);
			
			$emailStr = substr($this->formValueForKey("email"), 0, 100); 
			if ($emailStr == "" || invalidEmail($emailStr))
			{
				$result = array("error" => 1, "message" => "Your account is not currently set up to be able to log in. Please contact your regional administrator.");
                echo json_encode($result);
				return;
			}
			
			$user = BLGenericRecord::recordMatchingKeyAndValue("User", "email", $emailStr);
			if ($user)
			{
				$email = new PLEmail();
				$email->addPlainTextPart("Your password is ".$user->vars["password"]);
				$email->send($user->vars["email"], "server@".domainName(), "noreply@".domainName(), appName()." Password Reminder");
				
                // output to ajax call.
				$result = array("error" => 0);
                echo json_encode($result);
			}
			else
			{
                // output to ajax call.
				$result = array("error" => 1, "message" => "That email address doesn't exist in our system!");
                echo json_encode($result);
			}
		}
	} 
?>
