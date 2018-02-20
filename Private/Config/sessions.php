<?php
    /*  Clear out sessions older, than a day. Pass in seconds passed to change the period (default is 1 day).
        Ideally you should move this call into a cron job.:
        Example crontab: 1 01 * * * cd /path/to/php <web dir>/cron; ./session_cleanup.sh>> <webdir>/logs/session_cleanup.log; cd -;
    */
    cleanup_old_sessions(); 
?>