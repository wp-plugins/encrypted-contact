<?php
/*
-----BEGIN PGP SIGNED MESSAGE-----
Hash: SHA1

*/

/*
 * Admin functions for Encrypted Contact ver 1.3.1

 ******************************************************************************
 * Copyright Ralf Senderek, Ireland 2014. (https://senderek.ie)
 *
 * This file is part of the WEB ENCRYPTION EXTENSION (WEE)
 * File     : admin.php
 * Version  : 1.3.1
 * License  : GPL-v2
 * Signature: To protect the integrity of the source code, this program
 *            is signed with the code signing key used by the copyright
 *            holder, Ralf Senderek.
 * Date     : Tuesday, 10 February 2015
 * Contact  : Please send enquiries and bug-reports to opensource@senderek.ie
 *******************************************************************************

Copyright 2014 Ralf Senderek, Ireland (email: opensource@senderek.ie)

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

session_start();

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

function ec_admin_options() {
     global $MESSAGEDIR;
     if ( ! current_user_can('activate_plugins') ){
           wp_die( __( 'You do not have sufficient permissions to manage plugins for this site.' ) );
     } else {
           $_SESSION['admin-login'] = sha1($_COOKIE['PHPSESSID']);
     }
     ?>
     <div class="wrap">
     <h2>Administration of Encrypted Contacts</h2>
     <p>
     <?php
     $HTTPS_URL = str_replace('http:','https:',WP_PLUGIN_URL);
     echo "<input type=button class=\"keybutton\" name=\"Key Management\" value=\"Key Management\" onclick='javascript:window.open(\"".$HTTPS_URL."/encrypted-contact/admin/wee-keys.php\",\"keys\",\"top=100, left=200, height=700, width=800, resizable=yes, scrollbars=yes, menubar=no, addressbar=no, status=yes\");'>";
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

           echo "<td><input type=button class=\"button mainaction\" name=Decrypt value=Decrypt onclick='javascript:window.open(\"".$HTTPS_URL."/encrypted-contact/admin/wee-decrypt.php\",\"keys\",\"top=100, left=200, height=700, width=800, resizable=yes, scrollbars=yes, menubar=no, addressbar=no, status=yes\");'>";
           echo "</td></tr>";

           echo "<tr><td colspan=5><textarea name=storedmessage cols=70 rows=15>\n";
           if (isset($_REQUEST['selectfilename']) && isset($_REQUEST['filename']) && $_REQUEST['filename'] != "") {
                $CONTENT = unix("cat ".$MESSAGEDIR."/".checkinput($_REQUEST['filename'],"noscript"));
                echo $CONTENT;
           }
           echo "</textarea>\n</td>";
           echo "</tr>\n</table><p>\n";
      }

}

/*
-----BEGIN PGP SIGNATURE-----
Version: GnuPG v1.4.11 (GNU/Linux)

iQIcBAEBAgAGBQJU2fsKAAoJEPv24sKOnJjddSgP/2KST9CnOvZqFnrldW1asb3X
Klqg1bt6xJZrHzNcEKCNxgkkEoaq8+QequLX4syfLl/bmW9yGzkwSto0075Bx4n9
cFKKXnVLVvrmANvW1VhnRoe/Uf9YjyIQZ2gPYXmiQHh8Qakhc7GE6kva7+WBoy3i
ilZFcT/y+PViPDcskhQqCRSUGWJW/55xQATZ15l06Oposjl7xtnm8CkvQtV5sQCz
6+MHrL7hVANgJ95wJai0bvbN4+kd04FejGKDh1Xhb4erNlnfBsyRyJoT8gUwTRQ/
T69I53XF3K1CIe/eYGtMqQrY3VtHcSo9bmhxNQP4Q8GnMqxjp/OZTsPj2bZIoS9U
KgWuljx2ai1YoPRikPu6tsNa4k+Ph9ZGKXWGL1H0JU5UsyO+YRLIXJ0snrL1Re2A
DA6L6JY+PseyzyP+cr6vFyYqd1lAnw5ML2wyGpuDXpeCEHY8654tLfNRGVySUQhd
OnpKPV0CwDM3crVBOVjBW6GLyH/nYWWgIqjfhgqDwT8P83BJrzoxpWIOAPpoNnph
z5+L6j9KMWqJ2oFlnWSG9/ZauCcWcljK9fEfuQGsM/VOjIDsytAhQgwzRrm/Euk/
Ba5rpur942xNz2C5cPYukwOUDiqYRDbaB94dRAGLzASfXadAEpxNbxmw3BjQdUiN
+RXEoTxV3bzaArkqdOo8
=gaD/
-----END PGP SIGNATURE-----
*/?>
