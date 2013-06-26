<?php
/*
-----BEGIN PGP SIGNED MESSAGE-----
Hash: SHA1

*/

/*
 * Admin functions for Encrypted Contact ver 1.0

 ******************************************************************************
 * Copyright Kerry Linux, Ireland 2013. (http://kerry-linux.ie)
 *
 * This file is part of the WEB ENCRYPTION EXTENSION (WEE)
 * File     : admin.php
 * Version  : 1.0
 * License  : GPL-v2
 * Signature: To protect the integrity of the source code, this program
 *            is signed with the code signing key used by the copyright
 *            holder, Kerry Linux.
 * Date     : Monday, 24 June 2013
 * Contact  : Please send enquiries and bug-reports to opensource@kerrylinux.ie
 *******************************************************************************

Copyright 2013 Kerry Linux, Ireland (email: opensource@kerry-linux.ie)

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/


add_action( 'admin_menu', 'ec_admin_menu' );
add_action( 'admin_head', 'admin_css' );

if ( ! defined( 'WP_PLUGIN_DIR' ) )
       define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );
if ( ! defined( 'WP_CONTENT_URL' ) )
       define( 'WP_CONTENT_URL', WP_SITEURL . '/wp-content' );
$EC_URL = WP_CONTENT_URL."/plugins/encrypted-contact";

$ec_plugin_dir = WP_PLUGIN_DIR.'/encrypted-contact/';

include_once $ec_plugin_dir.'/gpgconfig.php';
$MESSAGEDIR = $GPGDIR."/messages";

function ec_admin_menu(){
     add_menu_page('Encryption Page', 'Encryption', 'manage_options', 'encrypt_contact_handle','ec_admin_options');

     add_action('admin_init','register_ec_settings');
}

function admin_css()
{
     global $EC_URL;
     echo '<link rel="stylesheet" type="text/css" href="'.$EC_URL.'/admin.css">';
}

function register_ec_settings(){
     register_setting('ec-option-group','recipientemail');
     register_setting('ec-option-group','archivemessages');
     register_setting('ec-option-group','showslider');
     register_setting('ec-option-group','spamcheck');
}

function warning_no_keydirectory(){
     global $GPGDIR;
     echo "<table cellpadding=20><tr><td bgcolor=#ffdddd>";
     echo "<h3>There is no directory $GPGDIR to store the keys.\n";
     echo "Please, run the following commands as root :</h3><code> mkdir $GPGDIR <br>chown apache $GPGDIR<br>chmod 700 $GPGDIR </code>";
     echo "</td></tr></table><p>\n";
}

function warning_no_https(){
     echo "<table cellpadding=20><tr><td bgcolor=#ffdddd>";
     echo "<h3 class=error>This connection is not secured by HTTPS.</h3>\n";
     echo "<h3 class=error>Your website visitors may enter confidential information into your contact form that does not travel to your server securely.</h3>\n";
     echo "<h3 class=error>Encryption will not work unless you have made your web server https-ready.\n";
     echo "Please visit <a href=http://kerry-linux.ie/support/https-tutorial.php>this tutorial</a> for more details.</h3>\n";
     echo "</td></tr></table><p>\n";
}

function ec_admin_options() {
     global $MESSAGEDIR, $GPGDIR, $APACHE;

     if($_SERVER['HTTPS'] != "on") {
          warning_no_https();
     }

     if(! is_dir($GPGDIR)) {
          warning_no_keydirectory();
     } else {
          if (fileowner($GPGDIR) != $APACHE){
               echo "<p><h3 class=error>Directory $GPGDIR is not owned by webserver user</h3>";
               echo "run: <code>chown $APACHE $GPGDIR</code>\n";
          } else {
               if (decbin(fileperms($GPGDIR)) != "100000111000000" ) {
                    echo "<p><h3 class=error>Directory $GPGDIR has insecure permissions.</h3>";
                    echo "run: <code>chmod 700 $GPGDIR</code>\n";
               }
          }
     }

     ?>
     <div class="wrap">
     <h2>Administration of Encrypted Contacts</h2>
     <p>
     <?php


     $HTTPS_URL = str_replace('http','https',WP_PLUGIN_URL);
     echo "<input type=button class=\"keybutton\" name=\"Key Management\" value=\"Key Management\" onclick='javascript:window.open(\"".$HTTPS_URL."/encrypted-contact/admin/kerrylinuxkeys.php\",\"keys\",\"top=100, left=200, height=600, width=800, resizable=yes, scrollbars=yes, menubar=no, addressbar=no, status=yes\");'>";
     ?>
     <p>
     <h3>Settings</h3>

     <?php
     echo "<form method=post action=".$_SERVER['PHP_SELF'];
     if (isset($_SERVER[QUERY_STRING])) {
          echo "?".$_SERVER[QUERY_STRING];
     }
     echo ">";

     if ( is_admin() ) {
          settings_fields('ec-option-group');
          do_settings_fields('ec-option-group','');
          if (isset($_REQUEST['recipientemail'])){
               if (get_option('recipientemail') === FALSE) {
                    add_option('recipientemail',$_REQUEST['recipientemail']);
               } else {
                    update_option('recipientemail',$_REQUEST['recipientemail']);
               }
          }
          $RECIPIENT = get_option('recipientemail');

          if (isset($_REQUEST['archivemessages'])){
               if (get_option('archivemessages') === FALSE) {
                    add_option('archivemessages',$_REQUEST['archivemessages']);
               } else {
                    update_option('archivemessages',$_REQUEST['archivemessages']);
               }
          }
          $ARCHIVEMESSAGES = get_option('archivemessages');

          if (isset($_REQUEST['showslider'])){
               if (get_option('showslider') === FALSE) {
                    add_option('showslider',$_REQUEST['showslider']);
               } else {
                    update_option('showslider',$_REQUEST['showslider']);
               }
          }
          $SLIDER = get_option('showslider');

          if (isset($_REQUEST['spamcheck'])){
               if (get_option('spamcheck') === FALSE) {
                    add_option('spamcheck',$_REQUEST['spamcheck']);
               } else {
                    update_option('spamcheck',$_REQUEST['spamcheck']);
               }
          }
          $SPAMCHECK = get_option('spamcheck');
     }
     ?>


     <table border=0 cellpadding=5>
     <tr><!-- left -->
     <td><table border=0 cellpadding=10><tr>
          <td>Recipient's Email Address</td><td><input type=text name=recipientemail value="<?php echo get_option('recipientemail'); ?> ">
          </td>
          </tr>
          <tr>
          <td>Archive Messages</td>
          <td><select name=archivemessages>
          <?php
          if ( get_option('archivemessages') === FALSE ) {
               echo "<option value=yes >yes</option>\n";
               echo "<option value=no selected>no</option>\n";
          } else {
               if (get_option('archivemessages') == "yes"){
                    echo "<option value=yes selected >yes</option>\n";
                    echo "<option value=no >no</option>\n";
               } else {
                    echo "<option value=yes >yes</option>\n";
                    echo "<option value=no selected>no</option>\n";
               }
          }
          echo "</select></td></tr></table>\n";
          ?>
     </td><!-- right -->
     <td><table border=0 cellpadding=10><tr>
          <td>Show Slider</td>
          <td><select name=showslider>
          <?php
          if ( get_option('showslider') === FALSE ) {
               echo "<option value=yes >yes</option>\n";
               echo "<option value=no selected>no</option>\n";
          } else {
               if (get_option('showslider') == "yes"){
                    echo "<option value=yes selected >yes</option>\n";
                    echo "<option value=no >no</option>\n";
               } else {
                    echo "<option value=yes >yes</option>\n";
                    echo "<option value=no selected>no</option>\n";
               }
          }
          echo "</select></td></tr>\n";
          ?>

          <tr>
          <td>Activate Spam Check</td>
          <td><select name=spamcheck>
          <?php
          if ( get_option('spamcheck') === FALSE ) {
               echo "<option value=yes >yes</option>\n";
               echo "<option value=no selected>no</option>\n";
          } else {
               if (get_option('spamcheck') == "yes"){
                    echo "<option value=yes selected >yes</option>\n";
                    echo "<option value=no >no</option>\n";
               } else {
                    echo "<option value=yes >yes</option>\n";
                    echo "<option value=no selected>no</option>\n";
               }
          }
          echo "</select></td></tr></table>\n";
          ?>
     </td>
     </tr>
     </table>
     <input type=submit class=savechanges value="Save Changes">
     </form>
     </div>

     <?php
     if ( is_admin() && (get_option('archivemessages') == 'yes') ) {
           $FILENAME = message_001;
           echo "<table border=0><tr><td>";
           echo "<h3>Browse messages:</h3></td>\n<td>";
           echo "<form method=post action=".$_SERVER['PHP_SELF'];
           if (isset($_SERVER[QUERY_STRING])) {
                echo "?".$_SERVER[QUERY_STRING];
           }
           echo ">";

           $LIST = unix("ls ".$MESSAGEDIR." | sort -r");
           $FILES = explode("\n",$LIST);
           echo "<select name=filename>\n";
           foreach ($FILES as $FNAME) {
                echo "<option ";
                if (isset($_REQUEST['filename']) && ($_REQUEST['filename'] == $FNAME)) {
                      echo " selected";
                }
                echo ">".$FNAME."</option>\n";
           }
           echo "</select>\n";
           echo "&nbsp; <input type=submit name=selectfilename value=Show>\n";
           echo "</form></td>\n";

           echo "<td><input type=button class=\"button mainaction\" name=Decrypt value=Decrypt onclick='javascript:window.open(\"".$HTTPS_URL."/encrypted-contact/admin/kerrylinuxdecrypt.php\",\"keys\",\"top=100, left=200, height=600, width=800, resizable=yes, scrollbars=yes, menubar=no, addressbar=no, status=yes\");'>";
           echo "</td></tr>";

           echo "<tr><td colspan=5><textarea name=storedmessage cols=70 rows=15>\n";
           if (isset($_REQUEST['selectfilename']) && isset($_REQUEST['filename']) && $_REQUEST['filename'] != "") {
                $CONTENT = unix("cat ".$MESSAGEDIR."/".$_REQUEST['filename']);
                echo $CONTENT;
           }
           echo "</textarea>\n</td>";
           echo "</tr>\n</table><p>\n";
      }

}

/*
-----BEGIN PGP SIGNATURE-----
Version: GnuPG v1.4.11 (GNU/Linux)

iQIcBAEBAgAGBQJRyF/9AAoJEG99+9BhwvVF9qIP+wQuuxnjcOshkhAdGh0HcbMl
FphBci9gHcHuyUgPJMVMleQuHOEtOFpSdMUtfm0Uhlh7SOXxBzpvPM101729WlUH
EKpG5D0YB9/Hyi5H9b/ihDMllApJr+MUx4FCdisEhHP9l90mK/hfK29ZXZZ//Ru5
Sj9kyB5g2C00n4QQRjiP8DyetFM8lo7RSVwmmOjneSJ8dJmHVUyW+L8BxRYDtlr3
AuCywWwmvCM8kZxWrD4yXkALowW0YvCocV2pKaW98dTIkz8pU7WjIQqVRltYH1yB
SIbtkeg+Stqb7DPPLOJjzzE8/JrZdiSao0rcqOy13tPiAddAkqIQBdayennEJAmZ
BPVAlADkvJceivUmBwZQ8pUSyNfNNHlSVl7URfI0Mn8GReb4kCXXmCj94RQWo/dz
XFnIEmsPdH8dlaPRbJWiDDFxON+RcWpRj5WMjGae05fsryqfGGI0rRt2VADyKg5z
tu3Tk0Fs/zV6nvAwNYvqwiTA4mVjNaz68ZjthDsXbo5xisGoBTMccTw/zIBtxdic
PGoMsGp98qiWNS3Uyr+FJjR15iK+8253pJ3u0+cX4AYOzxCrcNVm5/DR1sESAolG
W7q1qjsaShFrdO6l4hZ+YPEa+nmyLPzdQFJnsyf/147MVp3njhOqUEQcskaes8Hs
HftTq9ofBIQuTmp5n8ej
=TNB8
-----END PGP SIGNATURE-----
*/?>
