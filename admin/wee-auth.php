<?php
// wee-auth.php for Wordpress
// application specific code that checks the validity of a login
// and returns a $USERID

session_start();


/*
* Thanks to Keith Makan <k3170makan@gmail.com> for his code review that
* led to this improvement
*/
if (! isset($_SESSION['admin-login'])) {
     if (isset($_COOKIE['PHPSESSID'])){
           if ( ! $_SESSION['admin-login'] == sha1($_COOKIE['PHPSESSID']) )   
                 die("Not logged in.");
     }
}

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
