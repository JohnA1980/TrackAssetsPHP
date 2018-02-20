<?php
    // ================
    // = Page Routing =
    // ================
    
    /* BLogic routing is very simple and doesn't need to be configured before you can start using it.
       As long as you have URL Rewriting rules enabled (typically under a .htaccess file) it will
       automatically route directly to a requested component name. 
       The array below is used as whitelist to increase security or add alternative naming conventions to URLs. Once it has one or more entries only those specific entries may be accessed.  
    
        Once inside the component called you can use routingParams() or urlParams() to retrieve an array
        of any items in the '/' delimitered URL that were part of the request. It is left to sovereignty of each component to decide and dictate the required order and meaning of anything in this array.
    
        Examples: 
            - The URL: http://www.mysite.com/ProductDetails/34
    
        In the URL above the routing system would load the 'ProductDetails' component. From the component, calling routingParams() would return an array [ 34 ]. Most likely being the product identifier the component would fetch the matching product from the database and display it's details. 
    
            - The URL: http://www.mysite.com/Articles/2016/Mining
    
            and with $bl_allowed_urls set to:
    
            $bl_allowed_urls = array(
                "Articles" => "PostList"
            )
    
        In this example a request to this URL would use the $bl_allowed_urls map to load the 'PostList' component. From the component, calling routingParams() would return an array [ 2016, Mining ]. The component would likely be programmed to use both of these items as search qualifiers to fetch related posts from the database. The meaning of these two values is entirely unknown to BLogic. The component being loaded is the only one that can attribute their nessesity and meaning.
    */

    // BLogic Routing. Leave empty to allow direct access to any component.
    $bl_allowed_urls = array(
        // "MyRoute" => "MyComponent" // e.g. http://www.mysite.com/MyRoute will load MyComponent
    );
?>
<?php /** ADDED BY FEATURE TEMPLATE: file_upload **/ ?>
<?php
	// ******* file_upload feature. 
	
	if (count($bl_allowed_urls) > 0)
	{
		// Whatever you want the URL to appear as for uploads and downloads should be set here.
		define("UPLOAD_ROUTE", "Upload");
		define("DOWNLOAD_ROUTE", "Download");

		$bl_allowed_urls = array_merge($bl_allowed_urls, [
			UPLOAD_ROUTE => "MediaUpload",
			DOWNLOAD_ROUTE => "MediaDownload"
	 	]);
	}
	else
	{
		// This allows uploading and downloading to automatically work if custom routes have not been configured.
		define("UPLOAD_ROUTE", "MediaUpload");
		define("DOWNLOAD_ROUTE", "MediaDownload");
	}
?>