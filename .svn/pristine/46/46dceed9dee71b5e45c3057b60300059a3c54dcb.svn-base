<?php	
	/* 
		These two arrays are globals that can be accessed by components to add
		extra css or javascript files to the page at runtime before the page
	 	is rendered. This then allows you to only add external files as required by each component. 
	 	
	 	An important factor to keep in mind is when transitioning from one component to another that you may
	 	accumulate redundant CSS and JS references. If this becomes an issue you can flush the arrays as needed.
	*/
	$extraCSS = array();
	$extraJS = array();
	
	function addJS($js_path)
	{
	    global $extraJS;
	    $extraJS[] = $js_path;
	}
	
	function addCSS($css_path)
	{
	    global $extraCSS;
	    $extraCSS[] = $css_path;
	}
	
	function flushJS()
	{
	    global $extraJS;
	    $extraJS = array();
	}
	
	function flushCSS()
	{
	    global $extraCSS;
	    $extraCSS = array();
	}
        
        /**
         * Convert and Entity array to a select input array, with simple value=>display mapping
         * @param type $entities - list of entities
         * @param type $valField - value field (default is id)
         * @param type $textField - display text field (default is title)
         * @return type - array of value=>display mappings, which can be passed to printSimpleSelect or printEditForm functions
         */
        function toSelectArray($entities, $valField='id', $textField='title') {
            $options = array();
            foreach ($entities as $entity) {
                $options[$entity->vars[$valField]] = $entity->vars[$textField];
            }
            return $options;
        }

?>