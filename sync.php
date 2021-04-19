<?php

// Use in the "Post-Receive URLs" section of your GitHub repo.

if ( $_GET['sync']==1 ) {
    $output = shell_exec( 'cd /home/hazrulaz/www/w4l2 && git reset --hard HEAD && git pull' );
    echo "results: ".$output;
}

?>
