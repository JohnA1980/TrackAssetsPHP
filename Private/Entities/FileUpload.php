<?php 
//
// FileUpload.php
// 
// Created on 2017-05-13 @ 03:44 pm.
 
require_once BLOGIC.'/BLogic.php'; 
 
class FileUpload extends BLGenericRecord 
{ 
	public function __construct($dataSource = null) 
	{ 
		parent::__construct($dataSource); 
	} 
 
	public function tableName(): string { 
		return 'FileUpload'; 
	} 
	 
	public function pkNames(): string|array { 
		return 'id'; 
	}

	public function readOnlyAttributes(): array {
		return ['id'];
	}	
	
	public function isImage(): bool {
		return BLStringUtils::startsWith($this->field('mimeType'), 'image/');
	}
	
	public function hasImageData(): bool {
		return ($this->field('width') != '' && $this->field('height') != '');
	}
	
	public function isPlayableVideo(): bool {
		return BLStringUtils::startsWith($this->field('mimeType'), 'video/');
	}
	
	public function highresPath(): string {
		return $this->folderPath().'/'.$this->vars['id'].'.'.$this->vars['fileExtension'];
	}
	
	public function hasHighres(): bool 
    {
		$path = getcwd().'/'.$this->highresPath();
		return file_exists($path);
	}
	
	public function lowresPath(): string {
		return $this->folderPath().'/'.$this->vars['id'].'_thumb.'.$this->vars['fileExtension'];
	}
	
	protected function folderPath(): string {
		return ROOT.'/Persistence/Uploads';
	}
	
	public function hasLowres(): bool 
    {
		$path = getcwd().'/'.$this->lowresPath();
		return file_exists($path);
	}
	
	public function safeLowresPath(): string 
    {
		if ($this->hasLowres())
			return $this->lowresPath();
		return $this->hasHighres() ? $this->highresPath() : 'images/noImage_thumb.jpg';
	}
	
	public function url(bool $low = false): string 
    {
		$parts = [
			DOWNLOAD_ROUTE,
            $this->className(),
			$this->field('token'),
			$this->field('id')
		];
		if ($low) {
			$parts[] = 'reduced';
		}
		return implode('/', $parts);
	}
    
    public function delete(): bool 
    {
		$this->removeFile();
        return parent::delete();
    }
	
	public function removeFile(): void
	{
		$cwd = getcwd();
        $path = '$cwd/'.$this->highresPath();
        if (file_exists($path))
            unlink($path);
        $path = '$cwd/'.$this->lowresPath();
        if (file_exists($path))
            unlink($path);
	}
} 

