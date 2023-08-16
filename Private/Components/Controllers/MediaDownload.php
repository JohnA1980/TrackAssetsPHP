<?php 
require_once BLOGIC."/BLogic.php"; 
require_once ROOT."/Components/Controllers/SessionController.php";
 
class MediaDownload extends PLController 
{ 
	public function __construct($formData) 
	{ 
		parent::__construct($formData);
		setUseHTML(false);
	} 
	
	public function appendToResponse(): void
	{
	    $params = url_params();
		if (count($params) < 3) {
			$this->output_http_response(status:400, message:'invalid request');
            return;
		}
		
		$entity = strip_tags(trim($params[0]));
		$token = $params[1];
		$file = $params[2];
		$res = safeValue($params, 2, "full");
		
		if (strpos($entity, "/") !== false || strpos($entity, ".") !== false) {
			$this->output_http_response(status:400, message:'invalid request');
            return;
		}
		
		if (! file_exists(ROOT."/Entities/$entity.php")) {
			$this->output_http_response(status:400, message:'invalid image type');
			return;
		}
		
		// validate user can access the requested post.
		$file = BLGenericRecord::recordMatchingKeyAndValue($entity, "token", $token);
		if (! $file) {
			$this->output_http_response(status:400, message:'invalid request');
            return;
		}
					
		$path = ($res == "reduced") ? $file->safeLowresPath() : $file->highresPath();
		$name = $file->field("fileName");
		
        $this->output_file(mimeType:$file->field('mimeType'), path:$path, filename:$name);
	}
	
	public function handleRequest(): ?PLController
	{
		if ($page = parent::handleRequest()) {
            $this->output_http_response(status:403, message:'You were denied access.');
		}
		else if ($error = SessionController::validate($this)) {
			$this->output_http_response(status:403, message:$error);
		}
        return null;
	}
} 

