=== QRCodes ===
Contributors: holyhope
Donate link:
Tags: qrcodes, qrcode, flash, barcode, generator, multisite, multiblog, footer, print, navigation, mobile, phone
Requires at least: 4.1
Tested up to: 4.1
Stable tag: trunk
License: GPLv2 or later.
License URI: http://www.gnu.org/licenses/license-list.html#GPLCompatibleLicenses

QRCode add images that visitor can flash with their favorites applications.
Choose where to display and when (ex: only on printed page).

== Description ==

QRCodes is a plugin very usefull, when visitor print pages, it add qrcodes which redirect to the url. So people who read your posts (and pages) can find easily your website.

It automatically generate qrcode for wordpress posts and pages (they are cached and your site will still be as fast as before you install this plugin).
Moreove, it add qrcodes to all other visited pages.

Help your visitor and everyone to find and reach your website.

== Requirement ==

QRCodes requires :

* A valid [*QRCode PHP library*](http://sourceforge.net/projects/phpqrcode/ 'SourceForge Project') installation.

== Installation ==

1. Install QRCode PHP Library (see below).
2. Extract plugin to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress. If you have a multisite installation it may take few minutes.
3. Enjoy your qrcodes by printing home page !

= QRCode PHP =

1. Download [QRCode PHP library](http://sourceforge.net/projects/phpqrcode/ 'SourceForge Project').
2. Install it (extract it) in a folder accessible by your WebServer (such as /path/to/your/wordpress/folder/library/qrcode).
3. In your `wp-content.php`, juste before `/* That's all, stop editing! Happy blogging. */`, add following lines:
 ``//QRCode PHP library
 define( 'qrcodes_LIB_PATH', '/path/to/your/wordpress/folder/library/qrcode' );``

= Optionnal =

You can, by adding constants in `wp-config.php` specify path :

* to store QRCodes by adding ``define( 'QRCODES_BASEDIR', '/path/to/qrcodes' );``
* to access QRCodes by adding ``define( 'QRCODES_BASEURL', 'http://your-domain.com/path/to/qrcodes' );``

== Screenshots ==

1. General plugin settings.
2. Settings of the library (resolution and correction level).
3. Set positions and size of qrcodes in different media query.
4. Manage your website in a multisite installation so QRcodes ref to a specific url (with shortcodes) or let administrators set it by themself.
5. Manage media query in the network admin panel for multisite installation or in normal admin for normal one.
7. Exemple of qrcode in classique navigation.
6. Exemple of qrcode in printed page (same page of 6th screenshot, different position).

== Changelog ==

= 1.2 =

* Fix qrcodes generation.
* Use now correctly [Settings API](http://codex.wordpress.org/Settings_API 'wordpress.org').
* Upgrade media query management interface.
* Use *postbox* and *nav-tab* style for admin pages.
* Move admin files to */admin* folder
* Add a default value for media query at plugin activation:
	* add print medium placed at the top right of pages.
	* Set option autoload to true (decrease load time) for few options.

= 1.1 =

* Fix save settings.
* Add *requirement* section in readme.txt.
* Add ``[user-id]``, ``[blog-id]``, ``[current-url]`` shortcodes so you can use in qrcodes url (ex: *http//domain.com/qrcodes?redirect=[current-url]*).
* add many options:
	* Generate all qrcodes for all blog.
	* Add and manage media query (active or not and qrcodes position).
	* Manage qrcodes redirection for blogs through network administration.
	* Manage qrcodes redirection through blog's administration.

= 1.0 =

* First release.
* Generate 404 QRCodes on [`wpmu_new_blog` hook](https://codex.wordpress.org/Plugin_API/Action_Reference/wpmu_new_blog).
* Display .qrcode on print media by default, but you can add other [Media queries](http://www.w3.org/TR/css3-mediaqueries/#media0).
* Generate QRCode on [`save_post` hook](http://codex.wordpress.org/Plugin_API/Action_Reference/save_post) and save them.
* Delete saved QRCode on [`delete_post` hook](http://codex.wordpress.org/Plugin_API/Action_Reference/delete_post).
* Generate QRCode on the go during [`get_header` hook](http://codex.wordpress.org/Plugin_API/Action_Reference/get_header) for other page.
* Create QRCode folder in `QRCODES_BASEDIR` if defined, or by default `/uploads/qrcodes`.
* Delete all QRCodes on plugin deactivation ([`register_deactivation_hook`](http://codex.wordpress.org/Function_Reference/register_deactivation_hook)).
* It is actually displayed on the top right corner, but more options will come.

== Frequently Asked Questions ==

No questions yet. It will coming soon.
Please tell me what's wrong with that plugin and what would you have in future version.

== Upgrade Notice ==

* Nothing special.

== Planned works ==

* Embed [*QRCode PHP library*](http://sourceforge.net/projects/phpqrcode/ 'SourceForge Project').
* Add options to set a cache timeout.
* Presentation of plugin through *wp-pointer*.
* Add *screen meta* to show constantes informations and library version.
* Fix *#wpadminbar* element over *.qrcodes* images.
* Set *FAQ* in *readme.txt*.
* Make an index of all qrcodes generated per site, so we can remove them when the site is deleted.
