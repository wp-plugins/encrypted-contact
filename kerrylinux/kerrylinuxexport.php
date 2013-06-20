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
* File     : kerrylinuxexport.php
* Version  : 1.4.1
* License  : GPL-v3
* Signature: To protect the integrity of the source code, this program
*            is signed with the code signing key used by the copyright
*            holder, Kerry Linux.
* Date     : Saturday, 11 May 2013
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

// before anything else, check that data has arrived here via HTTPS
if ($_SERVER['HTTPS'] != "on") {
          die ("Use a secure HTTPS connection to the server. Aborting ...");
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

if (isset($_REQUEST['keyid'])) {
          $KEYID = $_REQUEST['keyid'];
}

if (isset($_REQUEST['keytype'])) {
          $KEYTYPE = $_REQUEST['keytype'];
}

echo "<div class=keys>\n";

if (isset($KEYID) && isset($KEYTYPE)) {
     if ($KEYEXPORT == "yes") {
          // Key export
          echo "<h3>Key Export</h3>\n";
          $EXPORT = "";
          if (($KEYTYPE == "secretkey") && ($SECRETKEYEXPORT == "yes")) {
               $EXPORT ="/usr/bin/gpg  --homedir ".$GPGDIR." --armor --logger-file ".$ERRORFILE." --output -  --export-secret-keys \"".$KEYID."\"" ;
          }

          if ($KEYTYPE == "publickey") {
               $EXPORT ="/usr/bin/gpg  --homedir ".$GPGDIR." --armor --logger-file ".$ERRORFILE." --output -  --export \"".$KEYID."\"" ;
          }
          if (! empty($EXPORT)) {
               $RESULT = unix2 ($EXPORT,$GPGDIR);
               // check if key export is successful
               $ERR1 = strpos($RESULT,'no signed data');
               $ERR2 = strpos($RESULT,'the signature could not be verified');
               if (($ERR1 === false) && ($ERR2 === false)){
                    echo "<textarea name=result cols=65 rows=15>\n";
                    echo $RESULT;
                    echo "\n</textarea>\n";
                    echo "\n<p><input type=button value='Close' onclick='javascript:window.close();'>\n";
               }
               else {
                    echo "<h3 class=error>Key export failed.</h3>";
                    echo "<input type=button value='Close' onclick='javascript:window.close();'>\n";
               }
          }
          else {
               echo "<h3 class=error>Key export is not allowed.</h3>";
               echo "<input type=button value='Close' onclick='javascript:window.close();'>\n";
          }
     }
     else {
          echo "<h3 class=error>Key export is not allowed.</h3>";
          echo "<input type=button value='Close' onclick='javascript:window.close();'>\n";

     }
}
else {
      echo "<h3 class=error>Key export failed.</h3>\n";
      echo "<input type=button value='Close' onclick='javascript:window.close();'>\n";
}

echo "<p><center>powered by <a href=http://kerry-linux.ie>Kerry Linux Solutions</a></center><p>";
echo "\n</div>\n";

?>

<!--
-----BEGIN PGP SIGNATURE-----
Version: GnuPG v1.4.11 (GNU/Linux)

iQIcBAEBAgAGBQJRjibBAAoJEG99+9BhwvVFKSIP/jY7ggpDWZ710ceFvt+OXrzV
cwL+9MXGL/AisIHKbYUwGHzEpyu119HI60YGxGf7Y0/VEoipmMeZrUDsL56AU9Dh
iWbnHzm2bPDC/DU9CSd81+riTXHOzHKvwLFdTUrQmKDmNTVnwxLV4kQuF08R6psp
qf1D6GjZdYqEMKZwBjyGBvZG7N9iG6oeILHISyxNI+QKDwKl1asxBKOuIso548L+
223aCUwz02l9kXhA7IhAnLlrmkIlM9HsIBzpl0EuXQ80giz3VMjjI3vVnSNwGVct
TJXpTZ3L7UBBTMFfLdjvbxu75YKUXVaZcATIFdQQYl7ivMqM0zTS7D8PuZ+FeJQt
XEJvmEqJDXsi7vswpQtt/jy6MAV2sWeTEST3MwB81s4IhIXIx7ZSpGRNsc8M3+/a
DyezUjw8B5B6dLLq8v6rr3F7Eg9k+zuXpfP8HumfXLjdXxDUq/detMRMr7xP0u4X
1RYxfD1HYHuKto6KeDf9SQBxiQvCsRzbubLMWCDucNsaZPn0xddcmhOC7WGyI6vN
YBYxetqAkpHmHkFqlpCiYdrKhlzwanrlNC/zkXa+9lAsT3R1VhXQ7NiCN2mKMFv6
dGSB1LxAbQPQ0IV55nbLjQkFlQhI7CPQxa1uJD6u/aGv0XcQwaTQaJiNuBsw0ejP
dnqogk5p3GRm6siWMsGW
=GA3X
-----END PGP SIGNATURE-----
-->
</body>
</html>
