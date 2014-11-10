=== Plugin Name ===
Contributors: Senderek Web Security
Donate link: https://senderek.ie/donate
Tags: secure contact, encrypted message, encryption, confidential, decryption, gpg, gnupg, linux, commercial website
Requires at least: 2.9.2
Tested up to: 4.0
Stable tag: 1.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Encrypted Contact offers your website visitors a tool to protect their messages before they are sent 
to the website's owner via email.

== Description ==

As an online professional or business owner you need to offer your website visitors a
secure way to contact you. Unprotected email is not enough, even if someone tries
to contact you for the first time.

The Encrypted Contact plugin is the most secure way to enable secure messages for your website
visitors and it even allows the website owner to read (i.e. to decrypt) protected messages
online by using the admin panel of WordPress.

This plugin uses the well-established Web Encryption Extension, an open source extension
for web applications that has been released under GPL-3 in 2011. All encryption is done
on the server with the tested standard tool GnuPG, so sending protected messages is 
both secure and easy to use. Using Encrypted Contact on the website makes sure that 
the website owner can stay in touch with visitors, even if he is on the road, because
everything he needs is stored on the server. Total device independence is assured. Neither
the website user nor the owner has to install anything on his computer, smartphone or
tablet, except a browser, to use the plugin securely.




== Installation ==

1. Have your website https-ready, make sure your visitors can reach your site via 
   https://your-site.com.

2. Create a safe place for your encryption key by making the directory `/home/gpg`. 
   Change the ownership to the web server user and remove all permissions except for the 
   owner.

   as root run the following commands:

   mkdir /home/gpg
   chown apache /home/gpg    (you may replace apache with your web server user)
   chmod 700 /home/gpg

3. Upload the plugin zip-file `encrypted-contact.tgz` to your `/wp-content/plugins/` 
   directory and extract the plugin files. The files will be stored in a separate directory
   `encrypted-contact`.

4. Log into your admin panel and activate the new plugin through the `plugins` menu.

5. Place the contact form in some place like the sidebar through the `widgets` menu, where 
   the new widget `Encrypted Contact` will show up.

6. Log into your admin panel and create a new key pair for you via the `Key Management` 
   button.  Alternatively you can upload an existing private key via the Key Management tool.

7. Decide whether or not you will store the messages (encrypted or not) on the server. 
   Set the Archive Messages select box to `yes`. A subdirectory `messages` will be created 
   automatically inside the safe place for your encryption keys and copies of all messages 
   will be archived here before the are sent out via email.
   You can read these messages and even decrypt them online using the admin panel.

== Frequently Asked Questions ==

= Do I need a Linux server for Encrypted Contact?  =

Yes, because the software relies on the operation system capabilities of Linux to work 
securely.  It also requires an installation of GnuPG on the server, which is usually 
present already.

= Do I need full control over my server to install Encrypted Contact? =

Not necessarily. But you have to trust the system administrators, because the encryption is
done on the server and can be intercepted there. In order to perform the installation
you need a safe place for the encryption key(s), which is located outside the web server 
tree.
The default installation assumes that you use the directory "/home/gpg" for this purpose.
You need to ask your system administrator to create this directory for you and to make it
writeable for the web server process only. That means, if you cannot use a safe place for 
your encryption keys with restrictive access permissions, your encryption will refuse to 
work.  This is not a bug but a desired performance of Encrypted Contact.

Once your server's sysadmin has created such a directory for you, and its name matches the
setting for $GPGDIR in the file "gpgconfig.php", then all key management can be done via
the admin panel without any further help from the sysadmin.

If you like to read more about the desirable server environment for encryption, have a
look at [this article] (https://senderek.ie/articles/what-is-a-secure-server.php).

= Why do I need to enable HTTPS on my server before I can use Encrypted Contact? =

Because, if you don't you trick your website visitors into entering confidential messages
into a form that transfers these messages insecurely, i.e. unencrypted to your server.
And your website visitors will not even be sure their messages will arrive a the server
you call yours. Under these circumstances it is pointless to encrypt something on the server
that has arrived insecurely. Encrypted Contact will check, if the message has arrived
via https, and it will refuse to work, if not.

HTTPS is a basic requirement, if you are serious about the security of your website.

== Screenshots ==


== Changelog ==

= 1.0 =
This is the first stable version of Encrypted Contact, released Monday, 10th June 2013.

== Upgrade Notice ==

= 1.0 =
Security upgrade necessary.

= 1.3 =
Latest version includes security upgrade.

== Arbitrary section ==

The plugin uses the Web Encryption Extension in its current version. You can download the
latest version of WEE from the 
[download page] (https://senderek.ie/downloads) .
It is possible to replace the scripts for encryption, decryption and key management in your
plugin directory with the original files from this download, without breaking anything. 
Just make sure you preserve the content of the configuration file gpgconfig.php, where
your recipient address is being stored.

In order to comply with the conditions of the *Detailed Plugin Guidelines* we have
removed the powered by Senderek Web Security link from all these scripts as a default.
If you wish to display this link on your website, you can replace the default files with
the original files you'll find in the directory `websecurity`.

All files from the Web Encryption Extension are code-signed by Senderek Web Security, so the 
signature verification will fail on the installed files because of the missing link, but it will
validate correctly on the files in the directory `websecurity`. Check the difference, you'll
find only one line of code.

