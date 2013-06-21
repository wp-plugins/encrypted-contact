<?php
// kerrylinuxauth.php for WordPress ADMIN section
// application specific code that checks the validity of a login

$USERID = "";
foreach ($_COOKIE as $FIELD) {
    $pos = strpos($FIELD,'|');
    if ($pos !== false) {
         $USERID = substr($FIELD,0,$pos);
    }
}

//echo "<h3>".$USERID."</h3>";

if (isset($USERID) && ($USERID == "admin")) {
     $GPGDIR = $GPGDIR."/".$USERID;
     if (! is_dir($GPGDIR)) {
          mkdir($GPGDIR,0700);
     }
}
else {
     die ("Admin is not logged in.");
}
?>
