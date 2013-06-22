<?php
// kerrylinuxauth.php for Wordpress
// application specific code that sets the key directory to admin


if (! is_dir($GPGDIR)) {
     echo "There is no directory $GPGDIR to store the keys.\n\n";
     echo "Run the following commands as root :  mkdir $GPGDIR; chown apache $GPGDIR";
} else {
     $GPGDIR = $GPGDIR."/admin";
     if (! is_dir($GPGDIR)) {
          mkdir($GPGDIR,0700);
     }
     if (! is_dir($GPGDIR."/messages")) {
          mkdir($GPGDIR."/messages",0700);
     }
}

?>
