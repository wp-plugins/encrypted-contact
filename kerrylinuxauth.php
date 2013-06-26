<?php
// kerrylinuxauth.php for Wordpress
// application specific code that sets the key directory to admin


if (is_dir($GPGDIR)) {
     $GPGDIR = $GPGDIR."/admin";
     if (! is_dir($GPGDIR)) {
          mkdir($GPGDIR,0700);
     }
     if (! is_dir($GPGDIR."/messages")) {
          mkdir($GPGDIR."/messages",0700);
     }
}

?>
