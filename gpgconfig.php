<?php
 // gpgconfig.php : Include file for WEB ENCRYPTION EXTENSION
 // version 1.4.1-plugin

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
* Version  : 1.4.1-plugin
* License  : GPL-v3
* Signature: To protect the integrity of the source code, this program
*            is signed with the code signing key used by the copyright
*            holder, Kerry Linux.
* Date     : Monday, 24 June 2013
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


 if ((! isset($AUTHREQUIRED)) or ($AUTHREQUIRED == "yes")) {
     require_once('kerrylinuxauth.php');
 }

 // the $GPGDIR may have changed as a result of user authentication in kerrylinuxauth.php
 // don't present warnings about the missing $GPGDIR to users in a plugin

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
      unix("dd if=/dev/zero of=".$FILENAME." bs=1 count=".strlen($text));
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

if (! isset($REMOVEBR)){
     $REMOVEBR = "no";
}

/*
-----BEGIN PGP SIGNATURE-----
Version: GnuPG v1.4.11 (GNU/Linux)

iQIcBAEBAgAGBQJRyF5hAAoJEG99+9BhwvVFIfUP+gODVMGpDIgGftLfTHQlq/GP
OgajhtUd1M/O5mCJElCJX0IPRwDDvSUG7OyMnCiHy8NBbLRdClfE4KTNrYSW2gmn
cX3swFFfL/RKJJnO8oPdiAc8MhHPMxYgm3kbFHlfHBVMopFrH6lTxL1Rar+ipcHo
HiK7BgXRCwwEM4pN4gn84MFMBIecgr79LEOj6YaKkuPZCE3mgd7qxlJ6R0XcP/QJ
GXB0dzOqcuyb9C64D17EoZmY/DWTkGsdA0KlIWN40ZVnGOYpDarzMm2UixcdMZ5v
pMzydt+9HMP8mjS1ti8q192lQJZPHFkSywWrSoZ9tpuFmOkajTWeP7BLLqvNCo9t
gfhl/FSVxeVkbp2FS9LMoURpRPP/qkkh75O+9XeHMazNtpBxcN/QwCtFJIGK05eN
qxYNHatGnXQnzJIW6eNLUgkuXED+nxZUhkguzjJmjTOiSn/32ripZsYXHICM9eA0
KGRC9sgWfR/oHQTMld0Laplhf8ltemig1S7PR5l2R1wlgcABvprpSYft1n6QHQhT
Dl2AAFTqmF8mFifRiBIaMA5wgQTsYI9dtnawufvUw1iUzjgmlAkcPpyYiIjXFdqs
WTqLP0uNYw+2UNARUkQEddy51mCa6qxztAAkt6P0Pz6rbmVFEbhkw//zy/uNDQXA
2v1bhM1c4iSUtZwR7PWg
=5Ti+
-----END PGP SIGNATURE-----
*/?>
