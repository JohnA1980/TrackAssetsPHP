<?php
require_once ROOT.'/Entities/FileUpload.php';

class LineItemAttachment extends FileUpload {

    protected function folderPath(): string {
		return parent::folderPath()."/lineItemFiles";
	}

}