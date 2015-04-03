=== QRCodes ===
Requires at least: 4.1
Tested up to:      4.1
Contributors:      holyhope
Donate link:
License URI:       http://www.gnu.org/licenses/license-list.html#GPLCompatibleLicenses
Stable tag:        trunk
License:           GPLv2 or later.
Tags:              qrcodes, qrcode, flash, barcode, generator, multisite, multiblog, footer, print, navigation, mobile, phone

QRCodes add images that visitor can flash with their favorites applications.
Choose where to display and when (ex: only on printed page).

== Description ==

QRCodes is a plugin very usefull, when visitor print pages, it add qrcodes which redirect to the current url. So people who read your posts (and pages) can find easily your website.

It automatically generate qrcode for wordpress posts and pages (they are cached so your site will still be as fast as before you install this plugin).
Moreove, it add qrcodes on the go to all other visited pages.

Help your visitor and everyone to find and reach your website.

== Installation ==

1. Extract plugin to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress. If you have a multisite installation it may take few minutes.
3. Enjoy your qrcodes by printing home page!

= Optionnal =

You can, by adding constants in `wp-config.php` specify path:

* to store QRCodes by adding ``define( 'QRCODES_BASEDIR', '/path/to/qrcodes' );``
* to access QRCodes by adding ``define( 'QRCODES_BASEURL', 'http://your-domain.com/path/to/qrcodes' );``

You can also add following lines to specify a custom *QRCode PHP* library:

 ``//QRCode PHP library
 define( 'qrcodes_LIB_PATH', '/path/to/your/wordpress/folder/library/qrcode' );``

== Screenshots ==

1. General plugin settings.
2. Settings of the library (resolution and correction level).
3. Set positions and size of qrcodes in different media query.
4. Manage your website in a multisite installation so QRcodes ref to a specific url (with shortcodes) or let administrators set it by themself.
5. Manage media query in the network admin panel for multisite installation or in normal admin for normal one.
7. Exemple of qrcode in classique navigation.
6. Exemple of qrcode in printed page (same page of 6th screenshot, different position).

== Constants configuration ==

= QRCODES_LIB_PATH =

Install the [*QRCode PHP library*](http://sourceforge.net/projects/phpqrcode/ 'SourceForge Project') on your web server then define the constant in wordpress configuration file.

Default to `path_join( plugin_dir_path( QRCODES_INDEX_FILE ), 'phpqrcode' )`.

For that download library in your wordpress folder then add following line to *wp-config.php*.

`define( 'QRCODES_LIB_PATH', ABSPATH . '/library/qrcode' );`

= QRCODES_BASEDIR =

Define this constant to the directory of generated images in wordpress configuration file.

Default to `path_join( $uploads['basedir'], 'qrcodes' )`.

Make a directory named *qrcodes* in */wp-content/uploads* then add following line to *wp-config.php*.

`define( 'QRCODES_LIB_PATH', ABSPATH . '/library/qrcode' );`

= QRCODES_BASEURL =

Define this constant to the url to access to `QRCODES_BASEDIR` directory.

Default to `path_join( $uploads['baseurl'], 'qrcodes' )`.

Define `QRCODES_BASEDIR` constant then add following line to *wp-config.php*.

`define( 'QRCODES_BASEURL', '/wp-content/uploads/qrcodes' );`

== Changelog ==

= 1.3.3 =

* Fix postbox on admin page.
* Fix tooltip to autoclose when another is opened.

= 1.3.2 =

* Add tooltip in admin pages.
* Fix sources path in langage project.

= 1.3.1 =

* Format code to respect WordPress standard.
* Rename files to respect WordPress standard.


= 1.3 =

* Embed [*QRCode PHP library*](http://sourceforge.net/projects/phpqrcode/ 'SourceForge Project').
* Fix *#wpadminbar* element over *.qrcodes* images.
* Now qrcodes are indexed per site.
* Add qrcodes list per site in admin panel.
	* Single or massive delete in one throw.
	* Display data and path.
	* Show a previex of qrcodes.
* Add qrcodes-generate action triggered when a qrcode is generated.
* Add qrcodes-remove triggered when a qrcode is successfully removed.
* Date of generation is now a difference time from current time.
* Fix `[user-id]` shortcode functionality by setting it to *0* for unknown users.
* Fix qrcodes generation for all site (previously generated only for default site).
* Remove qrcodes of site on deletion.
* Remove qrcode of post on deletion.
* Add documentation in readme.txt about:
	* `QRCODES_LIB_PATH`
	* `QRCODES_BASEDIR`
	* `QRCODES_BASEURL`

= 1.2 =

* Fix qrcodes generation.
* Use now correctly [Settings API](http://codex.wordpress.org/Settings_API 'wordpress.org').
* Upgrade media query management interface.
* Use *postbox* and *nav-tab* style for admin pages.
* Add some screenshots.
* Move admin files to */admin* folder
* Add a default value for media query at plugin activation:
	* add print medium placed at the top right of pages.
	* Set option autoload to true (decrease load time) for few options.

= 1.1 =

* Fix save settings.
* Add *requirement* section in readme.txt.
* Add `[user-id]`, `[blog-id]`, `[current-url]` shortcodes so you can use in qrcodes url (ex: *http//domain.com/qrcodes?redirect=[current-url]*).
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

* Add help tab to admin pages.
* Presentation of plugin through *wp-pointer*.
* Set *FAQ* in *readme.txt*.
* Add a banner for [wordpress.org](http://wordpress.org).
* Add comments in code.
* Fix `[user-id]` shortcode by generate qrcode on the go for logged user.
* Fix settings error on *qrcodes-index* page.
* Use language wordpress folder.
