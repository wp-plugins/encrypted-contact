<html>
<head>
   <link rel="stylesheet" type="text/css" href="gpgstyle.css" >
</head>
<?php

/*
-----BEGIN PGP SIGNED MESSAGE-----
Hash: SHA1

*/

/************************************************************
* Copyright Kerry Linux, Ireland 2011-2013. (http://kerry-linux.ie)
*
* This file is part of the WEB ENCRYPTION EXTENSION (WEE)
* File     : kerrylinuxdecrypt.php
* Version  : 1.4.1
* License  : GPL-v3
* Signature: To protect the integrity of the source code, this program
*            is signed with the code signing key used by the copyright
*            holder, Kerry Linux.
* Date     : Saturday, 11 May  2013
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

if (! is_file("gpgconfig.php")){
          die ("Config file does not exist.");
}

require_once('gpgconfig.php');

if (! isset($DATADIR)){
          $DATADIR = "/none";
          $FILESDIR = "/none";
}

// use GET only when filename is given
if (strtoupper($_SERVER['REQUEST_METHOD']) != "POST") {
          //check if there is a query string, then abort except if files are to be handled.
          if (count($_GET) != 0){
                if ($INPUT != 'file') {
                      die ("Always use POST to prevent recording of query strings. Aborting ...");
                } else {
                      if (isset($_REQUEST['file'])) {
                            // touch legitimate files only !
                            if (isset($USERID)) {
                                  $FILESDIR = $DATADIR ."/". $USERID;
                            } else {
                                  $FILESDIR = $GPGDIR."/data";
                            }
                            $RELATIVEFILE = $_REQUEST['file'];
                            $FILE = $FILESDIR ."/". $RELATIVEFILE;
                            $FILE = str_replace('//','/',$FILE);
                      } else {
                            $FILE = "/none";
                      }
                }
          }
} else {
          if (isset($_REQUEST['file'])) {
                // touch legitimate files only !
                if (isset($USERID)) {
                       $FILESDIR = $DATADIR ."/". $USERID;
                } else {
                       $FILESDIR = $GPGDIR."/data";
                }
                $RELATIVEFILE = $_REQUEST['file'];
                $FILE = $FILESDIR ."/". $RELATIVEFILE;
                $FILE = str_replace('//','/',$FILE);
          }
}

if (! isset($RELATIVEFILE)){
          $RELATIVEFILE = "/none";
}

if (! is_dir($GPGDIR)){
          die ("GPG directory $GPGDIR does not exist.");
} else {
          $ERRORFILE = $GPGDIR."/gpgerrors";
}


if (! isset($DECRYPTBIGFILES)){
          $DECRYPTBIGFILES = "no";
}

if (! isset($PLAINRETURN)){
          $PLAINRETURN = "no";
}
$TEXT = "";
if (isset($_REQUEST[$DECRYPTIONTEXTAREA])) {
          $TEXT = $_REQUEST[$DECRYPTIONTEXTAREA];
          $TEXT = addslashes($TEXT);
}

if (isset($_REQUEST['secret'])) {
          $SECRET = $_REQUEST['secret'];
}

if (! isset($KEYSELECTION)){
          $KEYSELECTION = "no";
}

if (! isset($DECRYPTIONIFRAMENUMBER)){
          $DECRYPTIONIFRAMENUMBER = 0;
}

if (! isset($REPLACEFILE)){
          $REPLACEFILE = "no";
}

if (isset($_REQUEST['decryptionkey'])){
          $DECRYPTIONKEY = $_REQUEST['decryptionkey'];
}

echo "<body onload='javascript:gettext(\"".$DECRYPTIONINPUT."\");'>\n";
echo "<div class=decryption>\n";

if (isset($TEXT) && isset($SECRET))
{
          // perform decryption
          if (strlen($SECRET) > 0) {
               $ERRORFILE = $GPGDIR."/gpgerrors";
               unix("rm ".$ERRORFILE);
               echo "<h3>Decryption</h3>\n";

               // get random file name for plain text
               $rndhandle = fopen("/dev/urandom","r");
               $RND = fread($rndhandle,20);
               fclose($rndhandle);
               $FILENAME = $GPGDIR."/".sha1($RND);
               // $FILENAME will contain plain text data
               $CRYPTOGRAM = $GPGDIR."/".sha1($RND)."-encrypted.file";
               if ($DECRYPTBIGFILES == "yes"){
                    if ($INPUT == 'file') {
                         // decrypt a file
                         unix("touch ".$CRYPTOGRAM);
                         unix("chmod 600 ".$CRYPTOGRAM);
                         unix("cp \"".$FILE."\" ".$CRYPTOGRAM);
                         $SIZE = unix("wc -c ".$CRYPTOGRAM." | cut -f1 -d' ' ");
                         echo "<p>decrypting ".$SIZE." bytes ...<p>\n";
                         $ENC ="/usr/bin/gpg   --homedir ".$GPGDIR." --require-secmem  --batch  --no-tty --yes --logger-file ".$ERRORFILE." --passphrase ".$SECRET." --output ".$FILENAME." --decrypt ".$CRYPTOGRAM ;
                         echo unix($ENC);
                         unix("rm ".$CRYPTOGRAM);
                    } else {
                         $ENC ="/usr/bin/gpg   --homedir ".$GPGDIR." --require-secmem  --batch  --no-tty --yes --logger-file ".$ERRORFILE." --passphrase ".$SECRET." --output -  --decrypt > ".$FILENAME ;
                         unixpipe($ENC,$TEXT);
                    }
                    unix("chmod 600 ".$FILENAME);

                    $handle = fopen($FILENAME, "r");
                    $RESULT = fread($handle,20000000);
                    fclose($handle);

                    if ($INPUT != 'file') {
                         // destroy content of the plain text file
                         unix("dd if=/dev/zero of=".$FILENAME." bs=1 count=".strlen($RESULT));
                         unix("sync");
                         unix("rm ".$FILENAME);
                    }
               } else {
                    $ENC ="echo \"".$TEXT."\" | /usr/bin/gpg   --homedir ".$GPGDIR." --require-secmem  --batch  --no-tty --yes --logger-file ".$ERRORFILE." --passphrase ".$SECRET." --output - --decrypt" ;
                    $RESULT = unix($ENC);
               }

               $ERRORS = unix("cat ".$ERRORFILE);
               echo "<textarea class=error cols=75 rows=4>".$ERRORS."</textarea>\n";
               // check if decryption is successful
               $ERR1 = strpos($RESULT,'No such file or directory');
               $ERR2 = strpos($RESULT,'no valid OpenPGP data found');
               if (($ERR1 === false) && ($ERR2 === false) && (strlen($RESULT) > 0 )){
                    echo "<p><h3>".strlen($RESULT)." bytes decrypted</h3>";
                    if ($INPUT != 'file') {
                         echo "<h3>Plain Text</h3>";
                         echo "\n<center><textarea name=result cols=75 rows=15>\n";
                         echo stripslashes($RESULT);
                         echo "\n</textarea></center>\n";
                         echo "<p> <input type=button value='Use this message' onclick='javascript:update_inputfield(\"".$DECRYPTIONINPUT."\");'>\n";
                         echo "&nbsp;&nbsp;&nbsp;&nbsp;\n<input type=button value='Cancel' onclick='javascript:window.close();'>\n";
                    } else {
                         if ($REPLACEFILE == "yes") {
                              unix("cp ".$FILENAME." \"".$FILE."\"");
                         } else {
                              // strip .asc from filename
                              if (substr($FILE,-4)  == '.asc') {
                                   $FNAME = substr($FILE,0,-4);
                              } else {
                                   $FNAME = $FILE;
                              }
                              unix("cp ".$FILENAME." \"".$FNAME."\"");
                         }
                         // destroy content of the plain text file
                         unix("dd if=/dev/zero of=".$FILENAME." bs=1 count=".strlen($RESULT));
                         unix("sync");
                         unix("rm ".$FILENAME);
                         echo "<input type=button value='Close' onclick='javascript:window.close();'>\n";
                    }
                    echo "<p><center>powered by <a href=http://kerry-linux.ie>Kerry Linux Solutions</a></center><p>";
               } else {
                    echo "<h3 class=error>Decryption failed.</h3>";
                    echo "<center><input type=button value='Close' onclick='javascript:window.close();'></center>\n";
               }
          } else {
               echo "<h3 class=error>Please enter a passphrase.</h3>";
               echo "<p><center><input type=button value='Close' onclick='javascript:window.close();'></center>\n";

          }
} else {
     // prompt for a passphrase
     echo "<h3>Decryption</h3>\n";
     echo "<h3>Available secret keys</h3>\n";
     $Keys = unix("/usr/bin/gpg --homedir $GPGDIR --list-secret-keys --fingerprint");
     $List = explode ("\n", $Keys);
     if (count($List) < 2 ) {
           die ("<h3 class=error>No keys available. Aborting ...</h3>");
     }

     echo "<table class=keylist border=0 cellpadding=5>\n";
     $START = 0;
     foreach ($List as $Line){

          $START += 1;
          if (substr_count($Line, "sec ") == 1) {
               $START = 0;
               $SEC = $Line;
          }
          if ( $START == 1) {
               $FP = substr($Line,24);
          }
          if ( $START == 2) {
               $UID = htmlentities(substr($Line,4));
          }
          if ( $START == 3) {
               $SUB = $Line;
               echo "<tr><td class=keyid1>".$SEC."<br>".$SUB."</td>";
               echo "<td class=keyid2>".$UID."</td>\n";
               echo "</tr>\n";
          }
     }
     echo "</table>\n";

     echo "<form name=decryptform method=POST action=kerrylinuxdecrypt.php>\n";
     echo "<input type=hidden name=file value=\"".$RELATIVEFILE."\">\n";
     echo "<table class=keyselect border=0 cellpadding=5>\n";
     if (! isset($_REQUEST['secret'])) {
          echo "<tr><td class=input>Passphrase</td>";
          echo "<td class=input><input name=secret type=password size=25></td></tr>\n";
     } else {
          echo "<input name=secret type=hidden value=\"".$_REQUEST['secret']."\" >\n";
     }

     if ($INPUT != 'file') {
          echo "<tr><td colspan=2 class=text>\n<textarea class=text name=".$DECRYPTIONTEXTAREA." cols=75 rows=15>\n";
          if (isset($TEXT)){
               echo rawurlencode($TEXT);
          }
          echo "\n</textarea></td></tr>\n";
          echo "<tr><td colspan=2 class=input><center><input type=submit value='Decrypt this message'>";
          echo "&nbsp;&nbsp;&nbsp;&nbsp;\n";
          echo "<input type=submit value='Close' onclick='javascript:window.close();' ></center></td></tr>\n";
     } else {
          $FNAME = $FILE;
          if (isset($DATADIR)) {
               // strip directory name from filename
               $FNAME = substr($FNAME,strlen($DATADIR));
          }
          echo "<tr><td class=keyid2>File</td><td class=keyid2>".$FNAME."</td></tr>\n";
          echo "<tr><td colspan=2 class=input><center><input type=submit value='Decrypt file'>";
          echo "&nbsp;&nbsp;&nbsp;&nbsp;\n";
          echo "<input type=submit value='Close' onclick='javascript:window.close();' ></center></td></tr>\n";
     }
     echo "</table>\n";
     echo "</form>\n";
     echo "<p><center>powered by <a href=http://kerry-linux.ie>Kerry Linux Solutions</a></center><p>";
}

echo "\n</div>\n";
?>

<script type="text/javascript">

     function gettext(inputelement)
     {
          var text = "<?php echo $DECRYPTIONTEXTAREA; ?>";
          var content = "";
          var success = false;
          var element = "none";
          var idelement = "none";
          if ("<?php echo $FLEXIBLE; ?>" == "yes") {
               try {
                    element = window.opener.document.getElementsByName('inputselector')[0].value;
               }
               catch (e) {
                    element = "<?php echo $DECRYPTIONINPUTNAME; ?>";
               }
               try {
                    idelement = window.opener.document.getElementById('inputselector').value;
               }
               catch (e) {
                    idelement = "<?php echo $DECRYPTIONINPUTID; ?>";
               }
          }
          else {
               element = "<?php echo $DECRYPTIONINPUTNAME; ?>";
               idelement = "<?php echo $DECRYPTIONINPUTID; ?>";
          }

          if (<?php echo strlen($TEXT);?> == "0") {
               if (inputelement == "editor") {
                    try {
                         content = window.opener.document.getElementsByName(text)[0].value;
                         window.document.getElementsByName(text)[0].value = content;
                    }
                    catch (e) {
                         window.close();
                    }
               }
               else if (inputelement == "textarea") {
                         try {
                              content = window.opener.document.getElementsByName(element)[0].value;
                              success = true;
                         } catch (e) {}
                         try {
                              content = window.opener.document.getElementById(idelement).value;
                              success = true;
                         } catch (e) {}
                         if (success) {
                              window.document.getElementsByName(text)[0].value = content;
                         }
                         else {
                              window.close();
                         }
               }
               else if (inputelement == "div") {
                         try {
                              content = window.opener.document.getElementsByName(element)[0].innerHTML;
                              success = true;
                         } catch (e) {}
                         try {
                              content = window.opener.document.getElementById(idelement).innerHTML;
                              success = true;
                         } catch (e) {}
                         if (success) {
                              window.document.getElementsByName(text)[0].value = content;
                         }
                         else {
                              window.close();
                         }
               }
               else if (inputelement == "iframe") {
                    try {
                         var fwin = window.opener.frames[<?php echo $DECRYPTIONIFRAMENUMBER; ?>];
                         content = fwin.document.getElementsByTagName('body')[0].innerHTML;
                         try {
                              if ("<?php echo $REMOVEBR; ?>" == "yes") {
                                   content = content.replace(/<br>/g,"");
                              }
                         } catch (e) {}
                         window.document.getElementsByName(text)[0].value = content;
                    } catch (e) {
                         window.document.write("The input element does not exist. Check the configuration.");
                    }
               }
          }
     }


     function update_inputfield(inputelement)
     {
          var text = "<?php echo $DECRYPTIONTEXTAREA; ?>";
          var plain = "<?php echo $PLAINRETURN; ?>";
          var content = window.document.getElementsByName("result")[0].value;
          if ("<?php echo $FORYOUREYESONLY; ?>" == "yes") {
               return false;
          }
          if ("<?php echo $ADDPRE; ?>" == "yes") {
               content = "\n<pre>\n" + content +  "\n</pre>\n";
          }

          var element = "none";
          var idelement = "none";
          if ("<?php echo $FLEXIBLE; ?>" == "yes") {
               try {
                    element = window.opener.document.getElementsByName('inputselector')[0].value;
               }
               catch (e) {
                    element = "<?php echo $DECRYPTIONINPUTNAME; ?>";
               }
               try {
                    idelement = window.opener.document.getElementById('inputselector').value;
               }
               catch (e) {
                    idelement = "<?php echo $DECRYPTIONINPUTID; ?>";
               }
          }
          else {
               element = "<?php echo $DECRYPTIONINPUTNAME; ?>";
               idelement = "<?php echo $DECRYPTIONINPUTID; ?>";
          }

          if (inputelement == "textarea") {
               try {
                    window.opener.document.getElementsByName(element)[0].value = content;
               } catch (e) {
                    window.opener.document.getElementById(idelement).value = content;
               }
          }
          else if (inputelement == "div") {
               try {
                    window.opener.document.getElementsByName(element)[0].value = content;
               } catch (e) {
                    window.opener.document.getElementById(idelement).innerHTML = content;
               }
          }
          else if (inputelement == "editor"){
               if (plain == "yes"){
                    window.opener.document.write(content);
               }
               else {
                    window.opener.document.getElementsByName(text)[0].value = content;
               }
          }
          else if (inputelement == "iframe"){
               var fwin = window.opener.frames[<?php echo $DECRYPTIONIFRAMENUMBER; ?>];
               fwin.document.getElementsByTagName('body')[0].innerHTML = content;

          }
          else{
               // nothing
          }
          window.close();
     }
</script>

<!--
-----BEGIN PGP SIGNATURE-----
Version: GnuPG v1.4.11 (GNU/Linux)

iQIcBAEBAgAGBQJRjiLEAAoJEG99+9BhwvVFancP/j6IDtPmIE8h8nBWU6sNmlPV
Mbk0lok7sIBh3JIOYX2Ts5SbwPkoBLaau0I87VLf4DLrPV8WJ2QXvaXTY8U1gwht
u9eW5WzZerP24QnkCTPiM832Gv4njbU2LeiE4t2b/qMOQt4ZGbfHoeleOUY8TWyN
mMw7vTRfR/P/uLTSalaAzbfdrNWrO7ou+Os4gnnDniqXb2HjQcy0FVCPkQ7Zer8A
EUh7oxfzsk0ZYBFvOD17Vf/X2I9mbs34h5kM99APy5WQKLNtQT6Ej4UwyXuqkoyW
2VdBJ7Od6towiqf7B903u+7kbYPZsRNVGRook+9hpsb+4UKXLpmk7sqUvHP8vgP6
Ul6Rdh4pf7n7CweIad1pMUPf2K4RrAFU8j7+Y0YlfOuC6G6M2ejtXrZ4PGpjy6+B
u8XWC7XvpeIFbITs2UpCCLYrPK4AUG01ZrWggQIwPaNuqKaI4aqb5p/IK0Dc9xKg
6oK+p6Hq5QQ1rgKWJ/7bz7Z7s4vf8VDn4mQ8fih4D/7FkfAtyczQGCsuoSPurOXv
QcD/51OOZUkcG/EeayAyAjgtyBdE2av3cZprO2K2kreVNWZJFMm3Wt/lqhzu3DAd
affkho1yPWsF/8STbXWAlQ4OZTQqgdYNIzcUXSyUnambgUlZ3TcD7kqOu3mP/HFO
tidUbQVn6W0ylKk7hti/
=U2yE
-----END PGP SIGNATURE-----
-->
</body>
</html>
