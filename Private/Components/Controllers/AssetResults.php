<?php 
	require_once BLOGIC."/BLogic.php"; 
	
	 
	class AssetResults extends PLController 
	{ 
		public function __construct($formData) 
		{ 
			$templateName = $this->templateNameBasedOnDevice("AssetResults", array());
			parent::__construct($formData, $templateName);
		} 
        
        protected $assets;
        
        public function assets()
        {
            if (! $this->assets)
            {
                /* FIX ME: I don't know exact what you're doing here. When I looked at the
                eomodel you have a many-to-many set up but it seems as though you are
                currently just loading a flat array for any user. 
                */
            	//echo "class is " . get_class($_SESSION["investmentPortfolioID"]);
            	//echo "this is the investmentPort: " . $_SESSION["investmentPortfolioID"];
            	
            	
                $quals = array();
				
                $investmentPortfolioID = $_SESSION["investmentPortfolioID"];
                
                if(!is_null($investmentPortfolioID)){
                	$quals[] = new BLKeyValueQualifier("investmentPortfolioID", OP_EQUAL, $investmentPortfolioID);
                }
                
                $quals[] = new BLKeyValueQualifier("deleted", OP_EXACT_MATCH, NULL_VALUE);
                
                if (! $this->formValueForKey("showDeleted"))
                    $quals[] = new BLKeyValueQualifier("deleted", OP_EXACT_MATCH, NULL_VALUE);
                $searchText = $this->formValueforKey("searchText");
                if ($searchText) {
                    $quals[] = new BLOrQualifier(array(
                       new BLKeyValueQualifier("name", OP_CONTAINS, "$searchText%"),
                       new BLKeyValueQualifier("address", OP_CONTAINS, "$searchText%") 
                    ));
                }
                
                $order = array("name" => ORDER_ASCEND);
                if (count($quals) == 0) {
                    $qual = null;
                }   
                else {
                   $qual = count($quals) > 1 ? new BLAndQualifier($quals) : $quals[0]; 
                }
                
                $this->assets = BLGenericRecord::find("Asset", $qual, $order);
            }
            return $this->assets;
        }
	} 
?>
