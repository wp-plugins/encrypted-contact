<?php
// kerrylinuxauth.php for Wordpress
// application specific code that checks the validity of a login
// and returns a $USERID

//print_r($_SESSION);

$_SESSION['user_id'] = "demo";
if (isset($_SESSION['user_id'])) {
     $USERID = $_SESSION['user_id'];
     $GPGDIR = $GPGDIR."/".$USERID;
     if (! is_dir($GPGDIR)) {
          mkdir($GPGDIR,0700);
     }
}
else {
     die ("Not logged in.");
}
?>
