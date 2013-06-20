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
* File     : kerrylinuxencrypt.php
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
                  if ($INPUT != 'file'){
                       die ("Always use POST to prevent recording of query strings. Aborting ...");
                  } else {
                       if (isset($_REQUEST['file'])){
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

$TEXT = "";
if (isset($_REQUEST[$ENCRYPTIONTEXTAREA])) {
          $TEXT = $_REQUEST[$ENCRYPTIONTEXTAREA];
          // quote newlines and " and ' to preserve them
          $TEXT = addslashes($TEXT);
}

if (! isset($KEYSELECTION)){
          $KEYSELECTION = "no";
}

if (! isset($IFRAMENUMBER)){
          $IFRAMENUMBER = 0;
}

if (! isset($SHOWTEXTAREA)){
          $SHOWTEXTAREA = "no";
}

if (! isset($REPLACEFILE)){
          $REPLACEFILE = "no";
}

if (isset($_REQUEST['encryptionkey'])){
          $ENCRYPTIONKEY = $_REQUEST['encryptionkey'];
}

echo "<body onload='javascript:gettext(\"".$INPUT."\");'>\n";
echo "<div class=encryption>\n";

if (isset($ENCRYPTIONKEY) && isset($TEXT) )
{
          if (strlen($ENCRYPTIONKEY) > 0) {
               // perform encryption
               echo "<h3>Encryption with key:<br> \"".htmlentities($ENCRYPTIONKEY)."\"</h3>\n";
               // get a random filename for plain text file and encrypted result
               // this is necessary to prevent overwriting by other users
               $rndhandle = fopen("/dev/urandom","r");
               $RND = fread($rndhandle,20);
               fclose($rndhandle);
               $PLAINTEXT = $GPGDIR."/".sha1($RND);
               $FILENAME = $PLAINTEXT.".encrypted";
               unix("touch ".$FILENAME);
               if ($INPUT == 'file'){
                    // encrypt a file
                    unix("touch ".$PLAINTEXT);
                    unix("chmod 600 ".$PLAINTEXT);
                    unix("cp \"".$FILE."\" ".$PLAINTEXT);
                    $SIZE = unix("wc -c ".$PLAINTEXT." | cut -f1 -d' ' ");
                    echo "encrypting ".$SIZE." bytes ...";
                    $ENC = " /usr/bin/gpg -a --homedir ".$GPGDIR." --cipher-algo AES --yes  --logger-file ".$ERRORFILE." --recipient \"".$ENCRYPTIONKEY."\" --always-trust -o ".$FILENAME." -e ".$PLAINTEXT;
                    echo unix($ENC);
                    // destroy content of the plain text file
                    $SIZE = unix("wc -c ".$PLAINTEXT." | cut -f1 -d' ' ");
                    //echo "\nOverwriting ".$SIZE." bytes plain text data\n";
                    unix("dd if=/dev/zero of=".$PLAINTEXT." bs=1 count=".$SIZE);
                    unix("sync");
                    unix("rm ".$PLAINTEXT);
               } else {
                    echo strlen($TEXT)." bytes plain text given";
                    $ENC = " /usr/bin/gpg -a --homedir ".$GPGDIR." --cipher-algo AES --yes  --logger-file ".$ERRORFILE." --recipient \"".$ENCRYPTIONKEY."\" --always-trust -e > ".$FILENAME;
                    unixpipe($ENC,$TEXT);
               }

               $handle = fopen($FILENAME, "r");
               $RESULT = fread($handle,20000000);
               fclose($handle);

               if ($INPUT != 'file'){
                    unix("rm ".$FILENAME);
               }
               // check if encryption is successful
               $ERR = strpos($RESULT,'BEGIN PGP MESSAGE');
               if (! $ERR === false){
                   echo "<h3>".strlen($RESULT)." bytes encrypted data</h3>";
                   if ($INPUT != 'file') {
                        if ($SHOWTEXTAREA == "yes"){
                             $RESULT = "\n<textarea name=".$ENCRYPTIONTEXTAREA." cols=75 rows=20>\n".$RESULT."\n</textarea>\n";
                        }

                        echo "<center><textarea name=result cols=75 rows=15>\n\n";
                        echo htmlentities($RESULT);
                        echo "\n</textarea></center>\n";
                        echo "<p> <input type=button value='Use this message' onclick='javascript:update_inputfield(\"".$INPUT."\");' >\n";
                        echo "&nbsp;&nbsp;&nbsp;&nbsp;\n<input type=button value='Cancel' onclick='javascript:window.close();'>\n";
                   } else {
                        if ($REPLACEFILE == "yes") {
                             unix("cp ".$FILENAME." \"".$FILE."\"");
                        } else {
                             // leave plaintext file intact
                             unix("cp ".$FILENAME." \"".$FILE.".asc\"");
                        }
                        unix("rm ".$FILENAME);
                        echo "<input type=button value='Close' onclick='javascript:window.close();'>\n";
                   }
               }
               else {
                    echo "<h3 class=error>Encryption failed.</h3>";
                    echo "<p>\n<center><input type=button value='Close' onclick='javascript:window.close();'></center>\n";
               }
          }
          else {
               echo "<h3 class=error>Encryption failed. Please select a public key</h3>";
               echo "<p>\n<center><input type=button value='Close' onclick='javascript:window.close();'></center>\n";
          }
}
else {
          echo "<h3>Encryption</h3>\n";
          // select a public key from the keyring
          if (! isset($ENCRYPTIONKEY)) {
               if ($KEYSELECTION == "yes") {
                    echo "<h3>Please choose one of the following public keys</h3>\n";
                    $Keys = unix("/usr/bin/gpg --homedir $GPGDIR --list-keys");
                    $List = explode ("\n", $Keys);
                    if (count($List) < 2 ) {
                          die ("<h3 class=error>No keys available. Aborting ...</h3>");
                    }

                    echo "<div class=keylist><table class=keylist border=0 cellpadding=5>\n";
                    foreach ($List as $Line){
                         $KEYID = htmlentities(trim(substr($Line,4)));
                         if ( substr_count($Line, "pub ") == 1){
                               echo "<tr><td class=keyid1>".$KEYID."</td>";
                         }
                         if ( substr_count($Line, "uid ") == 1){
                              echo "<td class=keyid2><a href='javascript:selectkey(\"".$KEYID."\");'>".$KEYID."</a></td></tr>\n";
                         }
                    }
                    echo "</table></div><p>\n";
                    if (isset($FILE)){
                         echo "<form name=encryptform method=GET action=kerrylinuxencrypt.php>\n";
                         echo "<input type=hidden name=file value=\"".$RELATIVEFILE."\">\n";
                    } else {
                         echo "<form name=encryptform method=POST action=kerrylinuxencrypt.php>\n";
                    }
                    echo "<table class=keyselect border=0 cellpadding=5>\n";
                    echo "<tr><td class=input>Key</td> <td class=input><input name=encryptionkey type=text size=40></td></tr>\n";
                    if ($INPUT != "file"){
                         echo "<tr><td colspan=2 class=text><textarea class=text name=".$ENCRYPTIONTEXTAREA." cols=75 rows=15>\n";
                         if (isset($TEXT)){
                              echo $TEXT;
                         }
                         echo "\n</textarea>\n</td></tr>\n";
                         echo "<tr><td colspan=2 class=text><center><input type=submit value='Encrypt this message'>";
                    } else {
                         $FNAME = $FILE;
                         if (isset($DATADIR)) {
                              // strip directory name from filename
                              $FNAME = substr($FNAME,strlen($DATADIR));
                         }
                         echo "<tr><td class=keyid2>File</td><td class=keyid2>".$FNAME."</td></tr>\n";
                         echo "<tr><td colspan=2 class=text><center><input type=submit value='Encrypt file'>";
                    }
                    echo "&nbsp;&nbsp;&nbsp;&nbsp;\n";
                    echo "<input type=submit value='Cancel' onclick='javascript:window.close();' ></center></td></tr>\n";
                    echo "</table>\n";
                    echo "</form>\n";
               }
               else {
                    echo "<h3 class=error>No encryption key selected. Encryption impossible.</h3>";
                    echo "<p><center><input type=submit value='Close' onclick='javascript:window.close();' ></center>\n";
               }
          } else {
               echo "<p><center><input type=submit value='Close' onclick='javascript:window.close();' ></center>\n";
          }
}

echo "\n</div>\n";
?>

<script type="text/javascript">

     function gettext(inputelement)
     {
          var content = "";
          var success = false;
          var text = "<?php echo $ENCRYPTIONTEXTAREA; ?>";
          var element = "none";
          var idelement = "none";
          if ("<?php echo $FLEXIBLE; ?>" == "yes") {
               try {
                    element = window.opener.document.getElementsByName('inputselector')[0].value;
               }
               catch (e) {
                    element = "<?php echo $INPUTNAME; ?>";
               }
               try {
                    idelement = window.opener.document.getElementById('inputselector').value;
               }
               catch (e) {
                    idelement = "<?php echo $INPUTID; ?>";
               }
          }
          else {
               element = "<?php echo $INPUTNAME; ?>";
               idelement = "<?php echo $INPUTID; ?>";
          }

          if (<?php echo strlen($TEXT);?> == "0") {
               if (inputelement == "editor" ) {
                    try {
                         content = window.opener.<?php echo $INPUTNAME; ?>.getData();
                         window.document.getElementsByName(text)[0].value = content;
                    }
                    catch (e) {
                          window.document.write("The input element <?php echo $INPUTNAME; ?> does not exist. Check the configuration.");
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
                         if (success){
                              window.document.getElementsByName(text)[0].value = content;
                         }
                         else {
                              window.document.write("The input element does not exist. Check the configuration.");
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
                         if (success){
                              window.document.getElementsByName(text)[0].value = content;
                         }
                         else {
                              window.document.write("The input element does not exist. Check the configuration.");
                         }
               }
               else if (inputelement == "iframe") {
                    try {
                         var fwin = window.opener.frames[<?php echo $IFRAMENUMBER; ?>];
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
               else if (inputelement == "file") {
               }
               else {
                         window.document.write("Unknown input element. Please check the configuration.");
               }
          }
     }

     function selectkey(key)
     {
          window.document.getElementsByName("encryptionkey")[0].value = key;
     }

     function update_inputfield(inputelement)
     {
          var content = window.document.getElementsByName("result")[0].value;
          var element = "none";
          var idelement = "none";
          if ("<?php echo $ADDPRE; ?>" == "yes") {
               content = "\n<pre>\n" + content +  "\n</pre>\n";
          }

          if ("<?php echo $FLEXIBLE; ?>" == "yes") {
               try {
                    element = window.opener.document.getElementsByName('inputselector')[0].value;
               }
               catch (e) {
                    element = "<?php echo $INPUTNAME; ?>";
               }
               try {
                    idelement = window.opener.document.getElementById('inputselector').value;
               }
               catch (e) {
                    idelement = "<?php echo $INPUTID; ?>";
               }
          }
          else {
               element = "<?php echo $INPUTNAME; ?>";
               idelement = "<?php echo $INPUTID; ?>";
          }

          if (inputelement == "textarea" ) {
               try {
                    window.opener.document.getElementsByName(element)[0].value = content;
               } catch (e) {
                    window.opener.document.getElementById(idelement).value = content;
               }
          }
          else if (inputelement == "div" ) {
               try {
                    window.opener.document.getElementsByName(element)[0].value = content;
               } catch (e) {
                    window.opener.document.getElementById(idelement).innerHTML = content;
               }
          }
          else if (inputelement == "editor"){
               window.opener.<?php echo $INPUTNAME; ?>.setData(content);
          }
          else if (inputelement == "iframe"){
               var fwin = window.opener.frames[<?php echo $IFRAMENUMBER; ?>];
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

iQIcBAEBAgAGBQJRjhkgAAoJEG99+9BhwvVFdR4P/Andis14FV+0gn34gfR3IVRm
+fiQsruCrYWyU/lqPmHc+VfXKsvk2MQSaE5s1uIM2A2xCSnAvTyay/o7b4hlcuF1
Duqd3A+sM/WHB21ZzBamrdfujHG+ZyegMATAn4p9hkOl6P98u9g+0GzIct4D6kV6
mH0fGQMQCyBCPWWmkRWGobB1cJu0jEzkdO9U65+gRzPW0DNCyZg9Dunb5IFb5WI1
2/U36mrOz0V5cvb2qNg7l+QTEfKTcSjYM2txp091tOmF6yL/22x+nVyEdsCtgOqZ
I6vcFe+VJwlYwZvKxn8sJTMgEvYVJhjkNiZ6I3nvnr1hDoacBlVQIfQXPZaY7k6e
OczHfLbYpCNak0vOELPZY9pt5MTN4/5P7qsakDHpmgtbbexfHtBSzevFd06ecLzD
VN0aSwpoVR73abUU20UF9ePYkXTRplqi55MsBbtFpdWvG9QGlRy5oUyLqO1oFuXM
DCmBECcXr5g6sXuZs3rboLIcGs4ZbNvbhF2BJLVcl4GyCBgg8dC9sypwkWFxhQhW
pnZF4eZJId1Xr4Hr5vj14W1WrnhS8+USKRyFayeZR4QolKORdJl0n2VCcQpe06E+
CRC96V0uUmL6E84FfyvW40Y0uQFVs/l6GRhnDuJjJRZZQxk7dt4Qrayaze1HjZt2
zF5+u/jtblwjJE63VTkn
=bI/I
-----END PGP SIGNATURE-----
-->
</body>
</html>
