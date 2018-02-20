<?php
    $hostname = php_uname("n");
    $developer_custom_settings_file = "_$hostname.settings.php";
    if (file_exists($developer_custom_settings_file)) {
        echo "A custom developer settings file for your computer already exists.\n";
        exit;
    }

    $contents = file_get_contents("/usr/local/sqonk/BLogic/Templates/Base/_CustomDevSettings.php");
    file_put_contents($developer_custom_settings_file, $contents);
?>