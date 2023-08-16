<?php
	ob_start();
	if (file_exists(".offline")) {
		// If the site is marked as down for maintenance then forward to the offline page
		// and go no further. 
		header("location: offline.html");
		exit;
	}
	require "settings.php";
	
    $useHTML = true;
	session_start();
	$component = handleRequest("Login");
if ($useHTML) :
?>
<!DOCTYPE html>
<html>
	<head>
		<title><?php echo appName(); ?> </title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <base href="<?php echo domainName(); ?>/" />
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
		<link rel="stylesheet" href="css/shared.css" type="text/css" media="screen" title="shared" charset="utf-8"/>
		<link rel="stylesheet" href="css/components.css" type="text/css" media="screen" title="shared" charset="utf-8"/>
		<?php foreach ($extraCSS as $css) : ?>
			<link rel="stylesheet" href="<?php echo $css; ?>" type="text/css" media="screen" charset="utf-8"/>
		<?php endforeach; ?>
		<script type="text/javascript" src="js/blogic.js"></script>
		<script type="text/javascript" src="js/general.js"></script>
		<?php foreach ($extraJS as $js) : ?>
			<script type="text/javascript" src="<?php echo $js; ?>"></script>
		<?php endforeach; ?>



		  
		
	</head>
	<body id="body">
		<?php $component->appendToResponse(); ?>
	</body>
</html>
<?php else : ?><?php $component->appendToResponse(); ?><?php endif; ?>