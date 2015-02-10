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
* Copyright Ralf Senderek, Ireland 2011-2016. (http://senderek.ie)
*
* This file is part of the WEB ENCRYPTION EXTENSION (WEE)
* File     : wee-export.php
* Version  : 3.1.0
* License  : GPL-v3
* Signature: To protect the integrity of the source code, this program
*            is signed with the code signing key used by the copyright
*            holder, Ralf Senderek.
* Date     : Tuesday, 10 February 2015
* Contact  : Please send enquiries and bug-reports to opensource@senderek.ie
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
          $KEYID = checkinput($_REQUEST['keyid'], "noscript");
}

if (isset($_REQUEST['keytype'])) {
          $KEYTYPE = checkinput($_REQUEST['keytype'], "noscript");
}

echo "<div class=keys>\n";
if ((isset($_SESSION['csrf-token'])) && (isset($_REQUEST['csrf-token']))){
           if ($_SESSION['csrf-token'] != $_REQUEST['csrf-token']){
                die ("CSRF attack");
           }
} else {
           die ("CSRF attack");
}


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

echo "\n</div>\n";

?>

<!--
-----BEGIN PGP SIGNATURE-----
Version: GnuPG v1.4.11 (GNU/Linux)

iQIcBAEBAgAGBQJU2ftbAAoJEPv24sKOnJjdSEAP+gLEUchA9DM6MaMc0gfXfkTz
qDa0PG92PufqwyHPWinS5qJzfmzHRdRTyTKdKl61MFzMmVTt1wBgDCqmM76vmvJM
3ym+lRGZDV5TdcO3yfExAPPuefFlDncT/w8cKiRezN6NTp84oelllh13R0GEp07g
h2qy68gYs9Jd+gCgH+uHHr+Xurxp+R/hjb7G+Bug7w3avH05iNLdEtrgujQyNlWT
tKkrjcHBcESW68IxJeR/i/DHnArU0VGCSNbFCDYMLh2Od9g/Jo5CcFO68dxwFHqo
4UDYhOCrMmV5mDNVpaLnfw07RaYscmwPz9mqzhFfqHt9wVVvEg+MmxBQAbDFh+kV
1JPwT8BolMqjqo29Jae6z1FB5Mk+rEnKGYnGBtW4YNB2HRhjvnfYozT4DgUBr8lr
kuvwKDgUROKDpI3vimHslVliEnv+6n4h0el+arTWt4p/aL/ws0fYBOMZmpxnUdf0
m0MRFjsBbhzdglgD5LuBHAgcvsZ167JmHywDpuN3Qi1OAlvaVe+hmx4mx05J62XC
CkbS58Se+YYLD5hgJBQxNmrU4xJpRKWQh7MYvw1nSxoG4u7/dF3zqgmDgqLTnHrJ
NzAk2V1TINNr8p3ClqBwQiD41lzYkF9VLhueyjUi9KdoUcyebamiJYhIWI2zkZUP
o17zYbUNSGYFavBoPavk
=tilR
-----END PGP SIGNATURE-----
-->
</body>
</html>
