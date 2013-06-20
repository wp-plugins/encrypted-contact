<html>
<head>
   <link rel="stylesheet" type="text/css" href="gpgstyle.css" >
</head>
<body>
<?php

/*
-----BEGIN PGP SIGNED MESSAGE-----
Hash: SHA1

*/

/************************************************************
* Copyright Kerry Linux, Ireland 2011-2013. (http://kerry-linux.ie)
*
* This file is part of the WEB ENCRYPTION EXTENSION (WEE)
* File     : kerrylinuxkeys.php
* Version  : 1.4.1
* License  : GPL-v3
* Signature: To protect the integrity of the source code, this program
*            is signed with the code signing key used by the copyright
*            holder, Kerry Linux.
* Date     : Saturday 11 May 2013
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
*************************************************************/

//  check that data has arrived here via HTTPS
if ($_SERVER['HTTPS'] != "on") {
          die ("Use a secure HTTPS connection to the server. Aborting ...");
}

// use GET only without data
if (strtoupper($_SERVER['REQUEST_METHOD']) != "POST") {
          //check if there is a query string, then abort.
          if (count($_GET) != 0){
                  die ("Always use POST to prevent recording of query strings. Aborting ...");
          }
}

if (! is_file("gpgconfig.php")){
          die ("Config file does not exist.");
}

require_once('gpgconfig.php');

if (! is_dir($GPGDIR)){
          die ("GPG directory $GPGDIR does not exist.");
}
else {
          $ERRORFILE = $GPGDIR."/gpgerrors";
}

if (! isset($KEYEXPORT)) {
           $KEYEXPORT = "no";
}

if (! isset($SECRETKEYEXPORT)) {
           $SECRETKEYEXPORT = "no";
}

// FUNCTIONS

function listkeys ($gpghome, $type, $select, $export, $secretexport) {
     unix ("/usr/bin/gpg --homedir ".$gpghome." --update-trustdb");
     if ($type == "secret") {
          echo "<h3>Secret Keys</h3>\n";
          $KEYS = unix2("/usr/bin/gpg --homedir ".$gpghome." --list-secret-keys --fingerprint",$gpghome);
     }
     else {
          echo "<h3>Public Keys</h3>\n";
          $KEYS = unix2("/usr/bin/gpg --homedir ".$gpghome." --list-keys --fingerprint", $gpghome);
     }

     $List = explode ("\n", $KEYS);
     if (count($List) < 2 ) {
          die ("<h3 class=error>No keys available. Aborting ...</h3>");
     }

     echo "<table class=keylist border=0 cellpadding=5>\n";
     foreach ($List as $Line){

          $START += 1;
          if (( substr_count($Line, "pub ") == 1) or(substr_count($Line, "sec ") == 1)){
               $START = 0;
               $PUB = $Line;
          }
          if ( $START == 1) {
               $FP = substr($Line,24);
          }
          if ( $START == 2) {
               $UID = htmlentities(substr($Line,4));
          }
          if ( $START == 3) {
               $SUB = $Line;
               echo "<tr><td class=keyid1>".$PUB."<br><i>(".$FP.")<br>".$SUB."</td>";
               if ($select) {
                    if ($type == "secret"){
                         echo " <td class=keyid2><input type=radio name=keyid value=\"".$FP."\"></td> ";
                    }
                    else {
                         echo " <td class=keyid2><input type=radio name=keyid value=\"".$UID."\"></td> ";
                    }
               }
               echo "<td class=keyid2>".$UID."</td>\n";
               if ($export == "yes") {
                    if (($type == "secret") && ($secretexport == "yes")) {
                         echo "<td class=keyid2><a href=kerrylinuxkeys.php onclick='javascript:window.open(\"kerrylinuxexport.php?keytype=secretkey&keyid=".trim($UID)."\",\"export\",\"\");' ><img src=export.png alt=export border=0></a></td>";
                    }
                    if ($type == "public")  {
                         echo "<td class=keyid2><a href=kerrylinuxkeys.php onclick='javascript:window.open(\"kerrylinuxexport.php?keytype=publickey&keyid=".trim($UID)."\",\"export\",\"\");' ><img src=export.png alt=export border=0></a></td>";
                    }
               }
               echo "</tr>\n";
          }
     }
     echo "</table>\n";
}


function addkeys ($gpghome, $key) {
     $FILENAME = $gpghome."/keyfile";
     $handle = fopen($FILENAME, "w");
     fwrite($handle,$key);
     fclose($handle);
     $CMD ="/usr/bin/gpg --homedir ".$gpghome." --import ".$FILENAME;
     $RESULT = unix($CMD);
     $ERR1 = strpos($RESULT,'No such file or directory');
     $ERR2 = strpos($RESULT,'no valid OpenPGP data found');
     if (! $ERR2 === false){
           echo "<h3 class=error>Please enter your key in ascii format.</h3>";
     }
     if (($ERR1 === false) && ($ERR2 === false) && (strlen($RESULT) > 0 )){
           // success
           echo "<p><textarea name=result cols=65 rows=5>";
           echo $RESULT;
           echo "</textarea>\n";
           listkeys($gpghome,"public",false);
           listkeys($gpghome,"secret",false);
     }
     else {
           echo "<h3 class=error>Key import failed.</h3>";
     }
}


function removepubkey ($gpghome, $keyid) {

     $CMD ="/usr/bin/gpg --homedir ".$gpghome." --require-secmem --batch --no-tty --yes  --delete-key \"".trim($keyid)."\"";
     $RESULT = unix($CMD);
     $ERR1 = strpos($RESULT,'can\'t open');
     $ERR2 = strpos($RESULT,'not found:');
     $ERR3 = strpos($RESULT,'there is a secret key for public key');
     if (! $ERR1 === false){
           echo "<h3 class=error>Check file permissions on your keyring.</h3>";
     }
     if (! $ERR2 === false){
           echo "<h3 class=error>The key is not in your keyring.</h3>";
     }
     if (! $ERR3 === false){
           echo "<h3 class=error>You must remove the secret key first.</h3>";
     }
     if (($ERR1 === false) && ($ERR2 === false) && ($ERR3 === false)){
           // success
           if (strlen($RESULT) > 6 ) {
                echo "<p><textarea name=result cols=65 rows=5>";
                echo $RESULT;
                echo "</textarea>\n";
           }
           listkeys($gpghome,"public",false,"","");
     }
     else {
           echo "<h3 class=error>Removing a key failed.</h3>";
     }
}

function removeseckey ($gpghome, $keyid) {

     $keyid = str_replace(" ","",$keyid);
     $CMD ="/usr/bin/gpg --homedir ".$gpghome." --require-secmem --batch --no-tty --yes  --delete-secret-key \"".trim($keyid)."\"";
     $RESULT = unix($CMD);
     $ERR1 = strpos($RESULT,'can\'t open');
     $ERR2 = strpos($RESULT,'not found:');
     if (! $ERR1 === false){
           echo "<h3 class=error>Check file permissions on your keyring.</h3>";
     }
     if (! $ERR2 === false){
           echo "<h3 class=error>The key is not in your keyring.</h3>";
     }
     if (($ERR1 === false) && ($ERR2 === false) ){
           // success
           if (strlen($RESULT) > 6 ) {
                echo "<p><textarea name=result cols=65 rows=5>";
                echo $RESULT;
                echo "</textarea>\n";
           }
           listkeys($gpghome,"secret",false,"","");
     }
     else {
           echo "<h3 class=error>Removing a key failed.</h3>";
     }
}

function createkeys ($gpghome, $name, $email, $secret) {

     $name = htmlentities($name, ENT_QUOTES);
     $email = htmlentities($email, ENT_QUOTES);
     $secret = htmlentities($secret, ENT_QUOTES);

     $CMD ="/usr/bin/gpg --homedir ".$gpghome."  --gen-key --batch --logger-file ".$gpghome."/gpgerrors  << EOF\n";
     $CMD = $CMD."Key-Type: RSA\nKey-Length: 4096\nSubkey-Type: RSA\nSubkey-Length: 2048\nPassphrase: ".$secret."\nName-Real: ".$name."\nName-Email: ".$email."\nEOF\n";
     unix("find / > /dev/null &");
     $RESULT = unix($CMD);
     listkeys($gpghome, "secret", false,"","");
}


// MAIN
echo "<div class=keys>\n";
echo "<h2 class=title>Key Management for User $USERID</h2>\n";
if (! isset($_REQUEST['action'])) {
     echo "<form action=kerrylinuxkeys.php method=POST>\n";
     echo "<select name=action>\n";
     echo "  <option value=listpublic>List public keys  </option>\n";
     echo "  <option value=listsecret>List secret keys  </option>\n";
     if ($KEYSREADONLY != "yes") {
          echo "  <option value=addkeys>Add keys </option>\n";
          echo "  <option value=removepkey>Remove a public key  </option>\n";
          echo "  <option value=removeskey>Remove a secret key  </option>\n";
          echo "  <option value=createkeys>Create a new key pair  </option>\n";
     }
     echo "</select>\n";
     echo "&nbsp;&nbsp;&nbsp;&nbsp;<input type=submit class=button value=\"Do it now\">\n";
     echo "</form>\n";
}
else {
     if ($_REQUEST['action'] == "listpublic") {
           listkeys($GPGDIR, "public",false, $KEYEXPORT, $SECRETKEYEXPORT);
     }

     if ($_REQUEST['action'] == "listsecret") {
           listkeys($GPGDIR, "secret",false, $KEYEXPORT, $SECRETKEYEXPORT);
     }

     if ($_REQUEST['action'] == "addkeys") {
           if (isset($_REQUEST['keyblock'])) {
                if ($KEYSREADONLY != "yes") {
                     addkeys($GPGDIR, $_REQUEST['keyblock']);
                }
           }
           else {
                // create form
                echo "<form method=POST action=kerrylinuxkeys.php>\n";
                echo "<input type=hidden name=action value=addkeys>\n";
                echo "<h3>Adding New Keys</h3><p>";
                echo "<textarea name=keyblock cols=65 rows=15>\n";
                echo "Enter your keyblock here";
                echo "</textarea>\n";
                echo "<p><input type=submit class=button value=\"Add this key\">";
                echo "</form>\n";
           }
     }

     if ($_REQUEST['action'] == "removepkey") {

           if (isset($_REQUEST['keyid'])) {
                if ($KEYSREADONLY != "yes") {
                     removepubkey($GPGDIR, $_REQUEST['keyid']);
                }
           }
           else {
                // create form
                echo "<form method=POST action=kerrylinuxkeys.php>\n";
                echo "<input type=hidden name=action value=removepkey>\n";
                echo "<h3>Removing a Public Key</h3><p>";
                listkeys($GPGDIR,"public",true,"","");
                echo "<p><input type=submit class=button value=\"Remove this key\">";
                echo "</form>\n";
           }
     }

     if ($_REQUEST['action'] == "removeskey") {

           if (isset($_REQUEST['keyid'])) {
                if (($KEYSREADONLY != "yes") && ($DELETESECRETKEY == "yes" )) {
                     removeseckey($GPGDIR, $_REQUEST['keyid']);
                }
                else {
                     echo "\n<h3 class=error>Deleting secret keys is not allowed.</h3>\n";
                }
           }
           else {
                // create form
                echo "<form method=POST action=kerrylinuxkeys.php>\n";
                echo "<input type=hidden name=action value=removeskey>\n";
                echo "<h3>Removing a Secret Key</h3><p>";
                listkeys($GPGDIR,"secret",true,"","");
                echo "<p><input type=submit class=button value=\"Remove this key\">";
                echo "</form>\n";
           }
     }

     if ($_REQUEST['action'] == "createkeys") {
           if (isset($_REQUEST['keyname']) and ($_REQUEST['keyemail']) and ($_REQUEST['keysecret'])) {
                if (($KEYSREADONLY != "yes") and ($KEYCREATION == "yes")) {
                     createkeys($GPGDIR, $_REQUEST['keyname'], $_REQUEST['keyemail'], $_REQUEST['keysecret']);
                }
                else {
                     echo "\n<h3 class=error>Key creation is not allowed.</h3>\n";
                }
           }
           else {
                // create form
                echo "<form method=POST action=kerrylinuxkeys.php>\n";
                echo "<input type=hidden name=action value=createkeys>\n";
                echo "<h3>Creating A New Key Pair</h3><p>\n";
                echo "<p><table class=genkey>";
                echo "<tr><td class=label1>Key name</td><td class=label2> <input type=text name=keyname ></td></tr>\n";
                echo "<tr><td class=label1>Key email address</td><td class=label2> <input type=text name=keyemail ></td></tr>\n";
                echo "<tr><td class=label1>Secret passphrase</td><td class=label2> <input type=password name=keysecret ></td></tr>\n";
                echo "</td></tr></table>\n";
                echo "<h4>This process may take some time. Please be patient.</h4>\n";
                echo "<p><input type=submit class=button value=\"Create key pair now\">\n";
                echo "</form>\n";
           }
     }
     echo "<p><input type=button class=button value=\"<< Back\" onclick='javascript:window.history.back();'>\n";
}

echo "</div>";

?>

<!--
-----BEGIN PGP SIGNATURE-----
Version: GnuPG v1.4.11 (GNU/Linux)

iQIcBAEBAgAGBQJRjib1AAoJEG99+9BhwvVFt34P/iT/ZW74cAPpMkP39+vG0P8I
MFLWLZIPPpm/95sV2I4H11UHRmTBm6MHqXONmVXpMpPVXqzy/EYGH0zNKAuYWGmd
RHaqqDSzVoWEQZWMWKUI0zWLrZ5FueMm12WQi9Lo5r+6YdKh7/S212X8vJf1ipdL
7iRqEgKMPmPZ8Ob3JhUZj/zBqisjT1q85nXzC4cXPcaajphITYonzEIIgRuFemCa
m89qFxrP+B86UgvXNWlY8p7+xF2lMwAbOx/T53JMemciogCsHUHow/Mes0gYChgh
rXUJ9Ys+4YGFIZL9XYQnB07FoLkbl8Z69sZkITSegFVTcUf3XsrQ4uVkJcpcPckH
AT5z6EqenXYX21Bf3dVKET8v4uP07JyDi3NrhlP3h9WixUv9eXA3RnavUdhdmwbV
2kNbN0eiqyfwLJuVpCDebGhOH3r76+JgFAh825ANwNJgXnU7pRF2jyeEtJNNzhx7
t02MXe/FGJiu0zMmJngnSJTXjqhy6XCoSOTgDn0MgeN6taeUxMpG/nyb8VEi3kqZ
TVyHglc2z5mOL+tfRdvvhTrPNNRTMD+9n/PA22lEudidUf/Vfz/7ZTJfvVcFw+Vm
6VnS7/NDXOOEHvh2cayXyf4Casohf42Xsy7XHo53kW4LsNhkjPpi+HfM6wVxhoER
+yh2MLDt3aY19vTniq8T
=FR13
-----END PGP SIGNATURE-----
-->
</body>
</html>
