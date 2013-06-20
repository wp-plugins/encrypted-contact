<?php
/*
-----BEGIN PGP SIGNED MESSAGE-----
Hash: SHA1

*/

/*
 * Plugin Name: Encrypted Contact
 * Plugin URI:  http://kerry-linux.ie/wordpress/encrypted-contact
 * Description: Encrypted Contact offers your website visitors a tool to protect their messages before they are sent to the website's owner via email.  This plugin encrypts messages arriving (via HTTPS) at the server using gnupg.
 * Version:     1.0
 * Author:      Kerry Linux
 * Author URI:  http://kerry-linux.ie
 * License:     GPL2 or later
 * Date:        June 11th 2013

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


                echo "<form method=post action=".$_SERVER['PHP_SELF'];
                if (isset($_SERVER[QUERY_STRING])) {
                     echo "?".$_SERVER[QUERY_STRING];
                }
                echo ">";

                echo "<h3 class=eclabel>Your Email Address:</h3> <input class=email type=email name=email size=15 ";
                if (isset($_REQUEST['email'])) {
                     echo " value=".$_REQUEST['email'];
                }
                echo ">";
                echo "&nbsp;<p>&nbsp;";
                echo "<h3 class=eclabel>Your (encrypted) Message:</h3>";
                echo "<textarea class=message name=encryptedmessage cols=20 rows=8>";
                if (isset($_REQUEST['encryptedmessage'])) {
                     echo $_REQUEST['encryptedmessage'];
                }
                echo "</textarea>\n";
                if (isset($_REQUEST['sendencryptedmessage']) && isset($_REQUEST['encryptedmessage']) && $_REQUEST['encryptedmessage'] != "") {
                       // store message if ARCHIVE is enabled
                       store_message($_REQUEST['email'], $_REQUEST['encryptedmessage']);
                       // send the message out
                       $RECIPIENT = send_email($_REQUEST['email'], $_REQUEST['encryptedmessage']);
                       if ($RECIPIENT != "") {
                            // display success
                            echo "<table border=0 cellpadding=5><tr><td class=green bgcolor=lightgreen height=30 width=200>";
                            echo "<h3 align=center>Your message has been sent to<br><i>".$RECIPIENT."</i></h3>";
                            echo "</td></tr></table>\n";
                       } else {
                            echo "<table border=0 cellpadding=5><tr><td class=red bgcolor=#ffcccc height=30 width=200>";
                            echo "<h3 align=center>Your message has not been sent.</h3>";
                            echo "</td></tr></table>\n";
                       }
                } else {
                       display_slider();
                       // display buttons
                       $HTTPS_URL = str_replace('http','https',WP_PLUGIN_URL);
                       echo "<input type=button class=\"encryptbutton\" name=encrypt title=\"Protect your message before it is sent\" value=\"Encrypt\" onclick='javascript:window.open(\"".$HTTPS_URL."/encrypted-contact/kerrylinuxencrypt.php\",\"encrypt\",\"top=100, left=200, height=600, width=800, resizable=yes, scrollbars=yes, menubar=no, addressbar=no, status=yes\");'>";
                       echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
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

add_action ( 'widgets_init', 'ec_register_widgets' );
add_action ('wp_head','ec_styles');
/*
-----BEGIN PGP SIGNATURE-----
Version: GnuPG v1.4.11 (GNU/Linux)

iQIcBAEBAgAGBQJRt0jMAAoJEG99+9BhwvVFcIIQAIbwyc49h+kbU39/XCnIACqO
Es1XUwC2gahCk2c3TMvC6lLyq3mxd0WtnlaQHSr/pB5+6AQrIHRmBHRLK9u0ZVeO
1LbTLNUNhTqUVt+1IH5bR2GmnRBvaVuydx/CY8zmDUQaZbfsXDMT731Hq5XSsuG7
PC4zKHyHgdNUOrjZ8Oou8aRYugJH7u7xdb99BX4YzIjIDchbUyjQasM8itOEJ38Q
QPbWnMawIbX5XbB7JEj1+zkv70LoO4B8MAizoiDzJlcWLu8eZ65xezG4TugOlK52
aZxur7tj1GrGAHKXEwsYLGtEFzC5vjP1ar/uJV2iXnCjJVBY/flcVAqEp7VLXueP
9gDbUC60SkJWZZ1BdiCRgIjUH7xdVk/Ul3FXM5LgG9QplktTe5/l197Q3mRge3h5
GqHiPujYBBCOhYxoEjLjBhdlT1ULmqsfMfVNWHHf7M+bsxHeDFqvdjExh5TXJ9rP
2FLYnQklR2owEODEjG73mWxT+RW2kGTPnaWzuhgtDW4a5CnBx6UEuNztMjWGUE9Q
/R/XjvdO75C76W6E1Sn9mtIp5JPRSn7+oUt7aOAwT0BXKxrFq+dkFK6rS2LxpW5n
YnenkWr/g5u3W2kG3+e7fgGLv46ozRg9usF1/rl65mWNUF2gktgkePGFRSr6o5fO
j6IVCFvuq5Vj/WHnV8t0
=GBFh
-----END PGP SIGNATURE-----
*/?>
