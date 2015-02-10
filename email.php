<?php 
/*
-----BEGIN PGP SIGNED MESSAGE-----
Hash: SHA1

*/

/*
Function Module for encrypted-contact.php
*/

 /************************************************************
 * Copyright Ralf Senderek, Ireland 2014. (https://senderek.ie)
 *
 * This file is part of the WEB ENCRYPTION EXTENSION (WEE)
 * File     : email.php
 * Version  : 1.3.0
 * License  : GPL-v2
 * Signature: To protect the integrity of the source code, this program
 *            is signed with the code signing key used by the copyright
 *            holder, Ralf Senderek.
 * Date     : Monday, 10 November 2014
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

 include_once('gpgconfig.php');

 if ( ! defined( 'WP_CONTENT_URL' ) )
        define( 'WP_CONTENT_URL', WP_SITEURL . '/wp-content' );
 $EC_URL = WP_CONTENT_URL."/plugins/encrypted-contact";

 if ((! isset($GPGDIR)) || (! isset($APACHE)) && (isset($RECIPIENT))) {
       $message = "There is something wrong with your WEE configuration.\n(encrypted-contact WP plugin)";
       $subject = "GPGDIR or webserver permissions are misconfigured";
       send_alert($RECIPIENT, $subject, $message);
 }

 if (isset($_REQUEST["slider"])){
       $SLIDERVALUE = $_REQUEST["slider"];
 }

 if (isset($_REQUEST["random"])){
       $RANDOM = $_REQUEST["random"];
 }

 if (! isset($ARCHIVE)) {
       $ARCHIVE = "no";
 }

 if (! isset($QUOTA)) {
       $QUOTA = 1;
 }

 if (! isset($SPAMCHECK)) {
       $SPAMCHECK = "no";
 }

 if (! isset($SLIDER)) {
       $SLIDER = "no";
 }

 if (! isset($ALLOWATTACHMENTS)) {
       $ALLOWATTACHMENTS = "no";
 }

 // get settings from database and overwrite defaults from gpgconfig.php
 $R = get_option('recipientemail');
 if ( $R !== FALSE ){
      $RECIPIENT = $R;
 }
 $S = get_option('showslider');
 if ( $S !== FALSE ){
      $SLIDER = $S;
 }
 $SPAM = get_option('spamcheck');
 if ( $SPAM !== FALSE ){
      $SPAMCHECK = $SPAM;
 }

 function check_email($e) {
      $E = "";
      $ALLOWED = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789.@_-";
      for ($i=0; $i < strlen($e); $i++) {
           if (strpos($ALLOWED, $e[$i])) {
                $E = $E.$e[$i];
           }
      }
      return $E;
 }

 function send_alert($recipient, $subject, $alert) {
      mail($recipient,"ALERT [".$subject."]",$alert);
 }

 function is_valid($email) {
      if ((strlen($email) > 6) and (strlen($email) < 100)) {
           if (( substr_count($email, "@") == 1) and (substr_count($email, ".") > 0)){
                return true;
           }
      }
      return false;
 }

 function spamcheck($MESSAGE) {

      /* a message is considered spam if any of these conditions hold:
      empty message
      more than 4 links in a message
      less than 250 characters per link
      more than one slash per 30 characters
      any href in a message
      ALL SPAM WILL SIMPLY BE IGNORED.
      */

      $HTTP = substr_count(strtolower($MESSAGE), "http");
      $LEN = strlen($MESSAGE);
      $HREF = (int) substr_count(strtolower($MESSAGE),"href");
      $SLASH = (int) substr_count(strtolower($MESSAGE),"/");
      //echo $HTTP. " " . $HREF. " ". $SLASH ;
      if (strlen($MESSAGE) == 0) { return false; }
      if ($HREF > 0) { return false; }
      if ($HTTP > 4) { return false; }
      if ($HTTP > 0){
          $LINKS = $LEN/$HTTP;
          if ($LINKS < 250) { return false; }
      }
      if ($SLASH > 0){
          $SL = $LEN/$SLASH;
          if ($SL < 30) { return false; }
      }
      return true;
 }

 function display_slider(){
      global $SLIDER, $EC_URL;
      if ($SLIDER == "yes") {
           echo "\n<script type=\"text/javascript\" src=\"".$EC_URL."/html5slider.js\"></script>\n";
           echo "Prove you're human, <br>please move the slider near ";
           echo "\n<script type=\"text/javascript\">\n";
           echo "<!--\n";
           echo "    var rand = Math.round(Math.random()*10);\n";
           echo "    document.write(rand*10);\n";
           echo "    document.write(\"<input type=hidden name=random value=\");\n";
           echo "    document.write(rand*10+\">\");\n";
           echo "//-->\n";
           echo "</script>\n";
           echo " <br> 0 <input type=range name=slider min=0 max=100 value=0 style='width:120px'> 100<p>\n";
      }
 }

 function slidercheck() {
      global $SLIDERVALUE, $RANDOM;
      $MAXDIFF = 10;
      $DIFF = (int) $RANDOM - (int) $SLIDERVALUE;
      if (abs($DIFF) > $MAXDIFF) {
           return false;
      }
      return true;
 }


 function checks_ok($MESSAGE) {
      global $SLIDER, $SPAMCHECK;

      if (($SLIDER == "yes") and ! slidercheck()) {
           return false;
      }
      if (($SPAMCHECK == "yes") and ! spamcheck($MESSAGE)) {
           return false;
      }

      // other checks here
      return true;
 }


 function store_message($email,$message) {
      global $GPGDIR, $APACHE, $QUOTA, $RECIPIENT;
      $DIR = $GPGDIR."/messages";

      if (! is_dir($DIR)){
           mkdir($DIR,0700);
           $M = "ERROR: \nMessage directory $DIR does not exist.\n";
           $M = $M . "run: mkdir $DIR\n";
           $M = $M . "run: chown $APACHE $DIR\n";
           $M = $M . "run: chmod 700 $DIR\n";
           send_alert($RECIPIENT, "Misconfigured WP plugin", $M);
           return "";
      }
      if (fileowner($DIR) != $APACHE){
           $M = "ERROR: \nMessage directory $DIR has insecure permissions.\n";
           $M = $M . "run: chown $APACHE $DIR\n";
           $M = $M . "run: chmod 700 $DIR\n";
           send_alert($RECIPIENT, "Misconfigured WP plugin", $M);
           return "";
      }
      else{
           if (decbin(fileperms($DIR)) != "100000111000000" ) {
                $M = "ERROR: \nMessage directory $DIR has insecure permissions.\n";
                $M = $M . "run: chmod 700 $DIR\n";
                send_alert($RECIPIENT, "Misconfigured WP plugin", $M);
           return "";
           }
      }

      $USED = unix("du -s ".$DIR." | cut -f1  ");

      if (diskfreespace($DIR) < doubleval(0.2 * $QUOTA * 1048576)){
           // free disk space is less than 20 percent of quota.
           $M = "Your file system is going to fill up. There are only ".strval(diskfreespace($DIR))." bytes left.";
           send_alert($RECIPIENT, "free disk space is running out", $M);
      }

      if (doubleval($USED * 1024) > doubleval(0.8 * $QUOTA * 1048576) ) {
           // more than 80 percent of QUOTA has been used.
           $M = "Your stored messages are going to reach your quota. \nThere are currently ".strval($USED * 1024)." bytes stored. \nPlease delete unused messages to free disk space.";
           send_alert($RECIPIENT, "delete messages", $M);
      }
      else {
           // write message to disk
           $i = -1;
           $H = opendir($DIR);
           while (readdir($H)) {
                $i++;
           }
           if ($i < 10){
                $FNAME = "message_00".$i;
           }
           elseif ($i < 100){
                $FNAME = "message_0".$i;
           }
           else{
                $FNAME = "message_".$i;
           }
           if (is_file($DIR."/".$FNAME)){
                $j = 1;
                while (is_file($DIR."/".$FNAME.".".$j)){
                      $j++;
                }
                $FNAME = $FNAME.".".$j;
           }

           //$MSG = "Name: ".$name."\n";
           $MSG = "";
           $MSG = $MSG."Email: ".$email."\n";
           $MSG = $MSG."Time: ".date("r")."\n\n";
           $MSG = $MSG.$message."\n\n";
           if (checks_ok($message)){
                $FILE = fopen($DIR."/".$FNAME,"w");
                fwrite($FILE,$MSG);
                fclose($FILE);
           }
      }
 }

function send_email($EMAIL, $MESSAGE) {
     global $RECIPIENT;
     if (isset($EMAIL) and is_valid($EMAIL) and checks_ok($MESSAGE)){

          $MESSAGE = str_replace("\r",'',$MESSAGE);
          if ($ARCHIVE == "yes") {
                store_message($EMAIL, $MESSAGE);
          }

          if (mail($RECIPIENT,"Direct Contact [".$EMAIL."]",$MESSAGE)){
               return $RECIPIENT;
          }
          else {
               echo "<h3 class=error>Sorry, we cannot send your email out.</h3>";
               return "";
          }
      }
      else {
          if (checks_ok($MESSAGE)) {
               echo "<h3 class=error>Please provide a valid email address.</h3>";
               return "";
          }
      }
}

/*
-----BEGIN PGP SIGNATURE-----
Version: GnuPG v1.4.11 (GNU/Linux)

iQIcBAEBAgAGBQJUYKNJAAoJEPv24sKOnJjdjLEP+QEcesBYsSCqvt2m5QG/MsRN
d631Whmtpgl3qIJclPVJeO8f+B/0mQsYeEoBE2sikn/jopW5itJAJDj75ecylRb9
Sfnsm22tuizMTRLjzA7WN+pqIqntCMWrCU9f0Dh3+QAGncynj/jcx/ZGlk1h43lR
NHAGMCk4N5XfNoCUvV4YqVaXf4BhBmLRv6DfPzRqo8XWb1oiXkUonD7evmV2Oc0m
30vFlDneYThzuT0jhNICrY1KwnXNwDuOGfKwLnUbeZpyOVAHQcl/MCPWVrKaGt7b
ihfgKJkRLSuirN6pAbN5DNfGWfWMiXKF0ST+RArhgPtKG/TdaGlSwmRdXfzvX52j
euumzxi+1/YVaKYPngRh8LY4YLBRv1A3BY1j669gJKoDG34nZvbn2cEgsdq4uOmC
GK1CUqOLcdM/Srax0RF/ZGRdjxFCJGuzeHYwIOZqjHSB+iIoSX28htahV7Fq55b9
7uGpBrCIZTJ069Dc8WBzUkKqhOsL3pTuLK1KywjkV9W4s0m1+Fze0aZ7FtOf0ydt
8K+VzJ85ZLpe5LwXcm2hFrk5CB+/WcU2VP1TMFHAyyP9duyVBdTF8v/EJBv3OqYK
U2a7kL2tbuGmUJBNqwzaz/6uJ5r3nNYlrQvMnNJdmJ2D2RoGPBmKhN/c1o4lUC2/
JNJIw1QR/e0pFx5uK6Tc
=csIA
-----END PGP SIGNATURE-----
*/?>
