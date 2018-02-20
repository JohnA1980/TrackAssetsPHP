<?php
    /* 
        Set this to a short string of characters for html obfusication. 
        NOTE: The encryption key must remain consistant across all page
        reloads. Don't use a runtime randomiser like uniqid() and don't
        store differing keys in a user's session. Sessions eventually 
        can expire and you will end up with situations where the encrypted
        form data will be unreadable to the request-response handler.
    */
    define("ENC_KEY", "2kcjlkjasdfalkjow");
?>