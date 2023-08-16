<?php
	require_once BLOGIC."/BLogic.php";
	
		
	class SessionController extends PLController
	{
	    protected $rowFlipper;
		public function __construct($formData, $componentName)
		{
			parent::__construct($formData, $componentName);
		}
		
		/*
			Examine the time elapsed between this request and the previous one. If it's found to be beyond
			a certain time frame then destory the session and log the user out.
		*/
		public function handleRequest(): ?PLController 
		{
			$lastRequest = safeValue($_SESSION, "LAST_REQUEST");
			if ($lastRequest == "")
				$lastRequest = 0;
			$timedOut = (time()-$lastRequest) > 3600; // 1 hour idle timeout
			$error = "";
			
			if ($timedOut)
			{
				$error = "For security reasons your session timed out as it was idle for too long. Please log in again.";
			}
			else if (empty($_SESSION["userID"]) || ! isset($_SESSION["SERVER_GENERATED_SID"]))
			{
				$error = "Your session was deemed to be invalid. Please log in again.";
			}
			
			if ($error)
			{
                if (session_id() != "") {
        			session_unset();
        			session_destroy();
                }
				$_SESSION["errorMessage"] = $error;
                header("location: Login");
			}
			$_SESSION["LAST_REQUEST"] = time();	

			return null;
		}
		
		protected $user;
		
		public function user()
		{
			if (! $this->user && isset($_SESSION["user"]))
			{
				$this->user = BLGenericRecord::restoreFromDictionary($_SESSION["user"]);
			}
			return $this->user;
		}
		
		public function logout()
		{
            if (session_id() != "") {
    			session_unset();
    			session_destroy();
            }
			header("location: Login");
		}

        public function rowFlipper(){
            $this->rowFlipper = !$this->rowFlipper;
            return $this->rowFlipper;
        }
	}
?>