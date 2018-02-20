<?php
    require "../Public/settings.php";
    
    if (count($argv) < 2) {
        echo "setuser <login> <desired password>\n";
        exit;
    }
    
    $login = safeValue($argv, 1);
    $password = safeValue($argv, 2);
    
    if (! $login || ! $password) {
        echo "Both a login and password are needed.\n";
        exit;
    }
    
    $user = BLGenericRecord::recordMatchingKeyAndValue("Contact", "login", $login);
    if ($user) {
        echo "updating user..\n";
    }
    else {
        echo "creating new user..\n";
        $user = BLGenericRecord::newRecordOfType("Contact");
    }
    
    $user->vars["login"] = $login;
    $user->vars["password"] = pwEncrypt($password);
    try {
        $user->save();
    }
    catch (Exception $error) {
        print $error->getMessage();
    }
    
    echo "done\n";
?>