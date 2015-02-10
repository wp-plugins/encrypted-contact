<?php
/*
-----BEGIN PGP SIGNED MESSAGE-----
Hash: SHA1

*/

/*
 * Plugin Name: Encrypted Contact
 * Plugin URI:  https://snderek.ie/wordpress/encrypted-contact
 * Description: Encrypted Contact offers your website visitors a tool to protect their messages before they are sent to the website's owner via email.  This plugin encrypts messages arriving (via HTTPS) at the server using gnupg.
 * Version:     1.3.0
 * Author:      Ralf Senderek
 * Author URI:  https://senderek.ie
 * License:     GPL2 or later
 * Date:        Monday, 10 November 2014

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

if ( ! defined( 'WP_CONTENT_URL' ) )
       define( 'WP_CONTENT_URL', WP_SITEURL . '/wp-content' );
if ( ! defined( 'WP_CONTENT_DIR' ) )
       define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
if ( ! defined( 'WP_PLUGIN_URL' ) )
       define( 'WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins' );
if ( ! defined( 'WP_PLUGIN_DIR' ) )
       define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );

$ec_plugin_dir = WP_PLUGIN_DIR.'/encrypted-contact/';
$EC_URL = WP_CONTENT_URL."/plugins/encrypted-contact";

include_once $ec_plugin_dir.'/admin/admin.php';
include_once $ec_plugin_dir.'/email.php';


class Encrypted_Contact_Widget extends WP_Widget {


        function Encrypted_Contact_Widget() {
                // Instantiate the parent object
                parent::__construct( false, 'Encrypted Contact' );
        }

        function widget( $args, $instance ) {
                // Widget output
                echo "<div align=left>";
                $EMAIL = checkinput($_REQUEST['email'],"noscript");
                $TEXT = checkinput($_REQUEST['encryptedmessage'],"noscript");

                echo "<form method=post action=".$_SERVER['PHP_SELF'];
                if (isset($_SERVER[QUERY_STRING])) {
                     echo "?".$_SERVER[QUERY_STRING];
                }
                echo ">";

                echo "<h3 class=eclabel>Your Email Address:</h3> <input class=email type=email name=email size=15 ";
                if (isset($_REQUEST['email'])) {
                     if ($_REQUEST['email'] != "") {
                          echo " value=".$EMAIL;
                     }
                }
                echo ">";
                echo "&nbsp;<p>&nbsp;";
                echo "<h3 class=eclabel>Your (encrypted) Message:</h3>";
                echo "<textarea class=message name=encryptedmessage>";
                if (isset($_REQUEST['encryptedmessage'])) {
                     echo $TEXT;
                }
                echo "</textarea>\n";
                if (isset($_REQUEST['sendencryptedmessage']) && isset($_REQUEST['encryptedmessage']) && $_REQUEST['encryptedmessage'] != "") {
                       // store message if ARCHIVE is enabled
                       store_message($EMAIL, $TEXT);
                       // send the message out
                       $RECIPIENT = send_email($EMAIL, $TEXT);
                       if ($RECIPIENT != "") {
                            // display success
                            echo "<table class=notice border=0 cellpadding=5><tr><td class=green>";
                            echo "<b>Your message has been sent to<br><i>".$RECIPIENT."</i></b>";
                            echo "</td></tr></table>\n";
                       } else {
                            echo "<table border=0 cellpadding=5><tr><td class=red> ";
                            echo "<b>Your message has not been sent.</b>";
                            echo "</td></tr></table>\n";
                       }
                } else {
                       display_slider();
                       // display buttons
                       $HTTPS_URL = str_replace('http:','https:',WP_PLUGIN_URL);
                       echo "<p>";
                       echo "<input type=button class=\"encryptbutton\" name=encrypt title=\"Protect your message before it is sent\" value=\"Encrypt\" onclick='javascript:window.open(\"".$HTTPS_URL."/encrypted-contact/wee-encrypt.php\",\"encrypt\",\"top=100, left=200, height=700, width=800, resizable=yes, scrollbars=yes, menubar=no, addressbar=no, status=yes\");'>";
                       echo "&nbsp;&nbsp;";
                       echo "<input type=submit class=sendmessage name=sendencryptedmessage title=\"Send your message as you see it now\" value=Send>";
                }
                echo "</form>";
                echo "</div><p>&nbsp;";
        }

        function update( $new_instance, $old_instance ) {
                // Save widget options
        }

        function form( $instance ) {
                // Output admin widget options form
        }
}

function ec_register_widgets() {
        register_widget( 'Encrypted_Contact_Widget' );
}

function ec_styles() {
         global $EC_URL;
         echo '<link rel="stylesheet" type="text/css" href="'.$EC_URL.'/ec.css">';

}

add_action ('widgets_init', 'ec_register_widgets' );
add_action ('wp_head','ec_styles');

/*
-----BEGIN PGP SIGNATURE-----
Version: GnuPG v1.4.11 (GNU/Linux)

iQIcBAEBAgAGBQJUYKOlAAoJEPv24sKOnJjdutwP/RuRrQ3Od0Y041X22idDKs5f
tS6vN5NfvCs7yrvgOAjhsmngLBovVnVb91FUSVrt3gFJgjI8daWWKIKcZ4PatE0G
5e4JgIGUbGbvwa6uMwN0nwv7oiFqNwAUyAY59Hpb5aD3sTeMTZam/3qIQKg5dDEd
BeznUgpEALkx3tQpQQp6/oZDGMX5U5Aexq6g4RowNj1IhOaH6P/3tnhP/pk+nj/b
N7x759/zXQICtoAbb9cWduugSfJWAwpE3G5e0RvAMobfzI1DiJuWiR29RY5hrmjV
O4ec3ydk35HTpdbNjEqUipmmZZt55UGzThHYCx5Exgx2kZO0PcgkBrFrsVvu8qMZ
Ed1pabE8yGyJHF0ErQS0ABdKlBv9NKsox4RMlSS7Sqp/nk7KycLsDe9qSoQyxDuw
LsfpB+e/wULRrIkrqyEzL2ZfiSDlQHN/AvJl8hMwkOW2Z/P/jbRo91peS1YkBI4d
tSyF8zesg/pQYaekOR46t14H17UKJSrE4aBf9W28dum8NffgK5yEm13b0pXGK8s6
54/GkcLjojZEW3JNCM5iRqozTUgusE35qEhnzJxsW4BE3iq1UR/EF6a/nHd2VZlX
fraH3efbWyYUVbAi8zrgl8Yn3LFHcvBOTDIiGvWtekrNJn3MzuEOL+nPiUjaOQ9I
j26sadUbrlNLhv87QloX
=2yTQ
-----END PGP SIGNATURE-----
*/?>
