<?php 
require_once BLOGIC."/BLogic.php"; 

	
class IncomeLineItemAttachmentView extends PLController 
{ 
	public IncomeLineItem $item;

	public function __construct($formData) 
	{ 
		parent::__construct($formData, "IncomeLineItemAttachmentView");
	} 

	public function setLineItem(IncomeLineItem $item): self 
	{
		$this->item = $item;
		return $this;
	}
} 

