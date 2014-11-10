<?php
/*
-----BEGIN PGP SIGNED MESSAGE-----
Hash: SHA1

*/

/*
 * Admin functions for Encrypted Contact ver 1.3.0

 ******************************************************************************
 * Copyright Ralf Senderek, Ireland 2014. (https://senderek.ie)
 *
 * This file is part of the WEB ENCRYPTION EXTENSION (WEE)
 * File     : admin.php
 * Version  : 1.3.0
 * License  : GPL-v2
 * Signature: To protect the integrity of the source code, this program
 *            is signed with the code signing key used by the copyright
 *            holder, Ralf Senderek.
 * Date     : Monday, 10 November 2014
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

iQIcBAEBAgAGBQJUYKQzAAoJEPv24sKOnJjdj5AQAI6trrx/ahnL52Lw3Fy9OPwa
mt75jbVMGdhAUUO0LEdgHf1dYkB80uL+QluAUKAPY+GiFXQu37NO/K8koIF6rBZ8
NIPK1S1CziKRyTkCV3fnAGN+hITlcIFPLexCN2YyQOEvrV7uSMqCHetyyabvPePY
UO3p7mpukQadsZgjMrHBSWdLDRnZuFbI7dGYbccblmFHB62adWQLIWH/cFtnXQIn
zctuWAHU2w8B6VAWGFIwyIUGs58GXpSBqwL/JmxESf8btrUzh3dCAibmcYgHTVPn
uTIhl9JRZAN3tDpR8k4t6BBvTB8++k+euh5ZoGoV4NTl5+oooQmZLLdMes+Ua3+6
KX3oqUuwqXoBkDUcIFoC9XszO6vAcvAAVrMl5amQiahroL4vRfoKR6jjUfCLifhW
kxOj/ovNLy5Ff8SBOWRvBG/Uut3ItVk78UEGgO+d2UrSwyn5YtdsL55brbFEldmt
rtpCqgo/jaP244t++7OSmywUEuM9sKPry7X8ErUKkJ4qwlzpXberDzvdYkwO2s8t
4pqGWeZw9dO/pYckvy5a/YenjuozQ7sbnKs4GJ2oraqcl6NxA9GIl3xBN5eUGccM
PVfyucJVmvwFKIegUlISCxBjGkLlG0Lj2sGkI1BNEdePoseCVOsDf6CvAiyrhXuU
MWR0OIa4vXGy6CNELP7S
=jVP3
-----END PGP SIGNATURE-----
*/?>
