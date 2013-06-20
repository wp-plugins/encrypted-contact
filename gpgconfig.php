<?php
 // gpgconfig.php : Include file for WEB ENCRYPTION EXTENSION
 // version 1.4.0

    $RECIPIENT = "your@email.com";
    $ALLOWATTACHMENTS = "no";

    //$SPAMCHECK = "yes";
    //$SLIDER = "yes";
    $ARCHIVE = "yes";
    //$QUOTA = 3;

    
    $GPGDIR = "/home/gpg";
    $KEYSELECTION = "yes";
    $KEYEXPORT = "yes";
    $SECRETKEYEXPORT = "yes";
    $KEYCREATION = "yes";
    $KEYSREADONLY = "no";
    //$KEYSREADONLY = "yes";
    $DELETESECRETKEY = "yes";

    // Encryption
    $ENCRYPTIONTEXTAREA = "encryptedmessage"; 
    $INPUT = "textarea";
    $INPUTNAME = "encryptedmessage";
    
    // Decryption
    $DECRYPTBIGFILES = "yes"; 
    //$DECRYPTIONINPUT = "textarea";
    //$DECRYPTIONTEXTAREA = "demotext"; 
    
    // Signature
    //$SIGINPUT = "textarea";
    //$SIGINPUTNAME = "demotext";
    //$SIGTEXTAREA = "demotext"; 

    // Verification
    //$VERIFYINPUT = "textarea";
    //$VERIFYINPUTNAME = "sentmessage";
    //$VERIFYTEXTAREA = "demotext"; 

/*
-----BEGIN PGP SIGNED MESSAGE-----
Hash: SHA1

*/

/************************************************************
* Copyright Kerry Linux, Ireland 2011-2013. (http://kerry-linux.ie)
*
* This file is part of the WEB ENCRYPTION EXTENSION (WEE)
* File     : gpgconfig.php
* Version  : 1.4.0
* License  : GPL-v3
* Signature: To protect the integrity of the source code, this program
*            is signed with the code signing key used by the copyright
*            holder, Kerry Linux.
* Date     : Sunday, 14 April 2013
* Contact  : Please send enquiries and bug-reports to opensource@kerrylinux.ie
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
*
**************************************************************/

// essential checks (do not remove!)

 if (! isset ($SECURE_CONNECTION)){
     $SECURE_CONNECTION = "yes";
 }

 if (! isset ($APACHE)){
     $APACHE = 48;
 }

 if($_SERVER['HTTPS'] != "on") {
     die ("<h3 class=error>This connection is not secured by SSL. Aborting.</h3>");
 }

 if ((! isset($AUTHREQUIRED)) or ($AUTHREQUIRED == "yes")) {
     require_once('kerrylinuxauth.php');
 }

 // the $GPGDIR may have changed as a result of user authentication in kerrylinuxauth.php

 if (! is_dir($GPGDIR)){
     die ("<p><h3 class=error>Directory $GPGDIR does not exist.</h3>");
 } else {
     if (fileowner($GPGDIR) != $APACHE){
          echo "<p>run: chown $APACHE $GPGDIR";
          die("<p><h3 class=error>Directory $GPGDIR is not owned by webserver user</h3>");

     } else {
          if (decbin(fileperms($GPGDIR)) != "100000111000000" ) {
               echo "<p>run: chmod 700 $GPGDIR";
               die  ("<p><h3 class=error>Directory $GPGDIR has insecure permissions.</h3>");
          }
     }
 }


// GLOBAL FUNCTIONS

function unix($command)
{
      // Executing a System Command with output
      $handle = popen("$command 2>&1", 'r');
      $text = fread($handle, 2000000);
      pclose($handle);
      return $text;
}

function unix2($command,$dir)
{
      // Executing a System Command with very large output but no input
      $rndhandle = fopen("/dev/urandom","r");
      $RND = fread($rndhandle,20);
      fclose($rndhandle);
      $FILENAME = $dir."/".sha1($RND);
      $handle = popen("$command > ".$FILENAME." 2> ".$FILENAME, 'r');
      $res = fread($handle, 20000000);
      pclose($handle);
      $handle = fopen($FILENAME, "r");
      $text = fread($handle,20000000);
      fclose($handle);
      // destroy content of the plain text file
      unix("dd if=/dev/zero of=".$FILENAME." bs=1 count=".strlen($RESULT));
      unix("sync");
      unix("rm ".$FILENAME);
      return $text;
}

function unixpipe($command,$input)
{
      // Executing a System Command with no output to STDOUT but reading $input
      $handle = popen("$command 2>&1", 'w');
      fwrite($handle, $input);
      pclose($handle);
}

if (! isset($INPUT)){
     $INPUT     = "textarea";
     $INPUTNAME = "message";
     $INPUTID   = "message";
}

if (! isset($ENCRYPTIONTEXTAREA)){
     $ENCRYPTIONTEXTAREA = "messagearea";
}

if (! isset($DECRYPTIONINPUT)){
     $DECRYPTIONINPUT     = "textarea";
     $DECRYPTIONINPUTNAME = "message";
     $DECRYPTIONINPUTID   = "message";
}

if (! isset($DECRYPTIONTEXTAREA)){
     $DECRYPTIONTEXTAREA = "messagearea";
}

if (! isset($SIGINPUT)){
     $SIGINPUT     = "textarea";
     $SIGINPUTNAME = "message";
     $SIGINPUTID   = "message";
}

if (! isset($SIGTEXTAREA)){
     $SIGTEXTAREA = "messagearea";
}

if (! isset($VERIFYINPUT)){
     $VERIFYINPUT     = "textarea";
     $VERIFYINPUTNAME = "message";
     $VERIFYINPUTID   = "message";
}

if (! isset($VERIFYTEXTAREA)){
     $VERIFYTEXTAREA = "messagearea";
}

if (! isset($KEYSREADONLY)){
     $KEYSREADONLY = "yes";
}

if (! isset($KEYCREATION)){
     $KEYCREATION = "no";
}

if (! isset($DELETESECRETKEY)){
     $DELETESECRETKEY = "no";
}

if (! isset($FLEXIBLE)){
     $FLEXIBLE = "no";
}

if (! isset($ADDPRE)){
     $ADDPRE = "no";
}

if (! isset($FORYOUREYESONLY)){
     $FORYOUREYESONLY = "no";
}

/*
-----BEGIN PGP SIGNATURE-----
Version: GnuPG v1.4.11 (GNU/Linux)

iQIcBAEBAgAGBQJRavheAAoJEG99+9BhwvVFWCwP/1NTz70/+OBkW82Uiv3gm8jo
aBvbepnn4OTBkBvArYwYDHhK16Y5PrUrYBAoA4roJk/4uK4j47yHM6GsKsqn5/8F
4qkd7lJ+6f8Rfl2ObOAcZEF+OR5qZGjReH+GP7CAsfv0solEdIg9uVGRn+Cos3v4
k3fuWmeqH3nsJpYaQ49L/2nsPFuCPjvYoL5DZQFuZ6NZ76HOxbke1A6b4Qo1fLeH
hlAYOQUyA/NLxn+5iMJaSsoNoKQcW5YQ+GBROniPjIys/tk4Md2KEQdGZ7uqXOvV
Rnq/dvPOPiVkWW5H6oZe/dXCqNv1i/BtIzMQbJAmFm6fJHaFSooKT5UQqx2P+pPk
Cu4oNirjfrmd3Cr4Upe6zkZXRxwObaWI0kpW8ycorOGk4RWCBJjhF0bYQ2o6YUyy
mNP+eZs9FjKeg++027nOH9CB5Rd0pYHT0MmKCMRGstVUa9AqkX9PyLblNf+udxaa
o/cxDG3boh9lSf6SUFfYSvAKqsWS1E1fUqQOgIV5oc475B/p6ErGEas19MS50Wf3
UEqIdio61blUL9rgkirTsJ5o2uiARZzG1FGT8OwfYq7m2N7sn5h5F5StWdOM4PAB
XpwYzfFjG5noNlc6haaSwDvTT0FgZotIGKT20nZCDR4iU/JRoRj/sMFXis8P5cCG
py0PCyae6Z3VkhCyk5B3
=PNjK
-----END PGP SIGNATURE-----
*/?>
