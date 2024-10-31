=== PassClip Auth for WordPress ===
Contributors: Passlogy
Tags: login, security, 2FA, two factor authentication, otp
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Donate link:
Requires at least: 4.5
Tested up to: 5.3.2
Stable tag: 1.0.6
Requires PHP: 5.3.3


"PassClip Auth" provides strong and easy authentication.
"PassClip Auth for WordPress" is the plugin to launch PassClip Auth to WordPress sites easily.
The login process is applicable to both administrators and subscribers.
*This plugin connects to the external server in order to authenticate.

== Description ==
You need strong password to protect your site. However, how do you remember it or is it really strong?
"PassClip Auth" provides really strong password that is also easy to remember.
Once you make your "pattern", you can get your password using "PassClip". And the password will change every 30 seconds(at the shortest).<br>


= Get and sign up for PassClip =
1. Go to <a href="https://www.passclip.com/">the page about PassClip</a> and install PassClip on your smart phone.
2. Activate your PassClip by registering your "pattern" and email address.

= Sign up for PassClip Auth(PCA) =
1. Input PassClip Code "paauth" in your PassClip. That makes a new slot in your PassClip.
2. Go to <a href="https://member.passclip.com/member/ui/">PassClip Auth member's page</a> and log in with your email address and password which the slot shows you.
3. Make your "PassClip Code". And then you get your "PassClip Auth app service id(PCA app service id)". You need both "code" and "id" to use this plugin.

= How to apply PassClip Auth to your site =
1. Install and activate this plugin to your WordPress.
2. Go to PassClip Auth Options Setting from the menu.
3. Input the PassClip Auth app service id(PCA app service id), PassClip Code and other items in the setting page and click the "Save Change" button.


= How to log in to WordPress site with PassClip Auth =
1. Users register PassClip Code of your site in their PassClip. That makes a new slot to get password to log in to your site.
2. Show the password in PassClip (tap the new slot).
3. In login form of your site, users enter email address and password in the slot. (<strong>Users do not need general WordPress password.</strong>)
4. Click the "Log in" button.


== Installation ==
= Installation =
1. Unzip the downloaded plugin zip file.
2. Upload `passclip-auth-plugin` directory and its contents to the `/wp-content/plugins/` directory.
3. Activate the plugin through the 'Plugins' menu in WordPress.


== Frequently Asked Questions ==
= Although I entered the correct password, I can not log in to wordpress after activation of this plugin. =
Check the connection to the PassClip Auth server from your WordPress server.<br>
If the connection has no ploblem, be sure to input your email address which you registered with the PassClip Code.


== Screenshots ==
1. Plugin options setting panel. All you need to do is input your "PCA app service id" and "PassClip Code".
2. Login page. The activated plugin modifies your login page and your site is protected by PassClip Auth.
3. Each time you log in, you need to get a new password from PassClip. Only you can know your password from this picture.
The correct password will change every 30 seconds(at the shortest).
4. PassClip Auth member site. You can log in with your PassClip and you can make your PassClip Code here.


== Changelog ==
= 1.0.4 =
Fixed for the multisite.
Added filters.

= 1.0.3 =
Fixed translation.

= 1.0.2 =
Fixed for the multisite.
Added filters.
Added widget.
Added Simple Setting.

= 1.0.1 =
Fixed language files and some urls.

= 1.0.0 =
Released.
