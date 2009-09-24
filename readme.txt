=== WP-Members ===
Contributors: cbutlerjr
Donate link: http://butlerblog/wp-members/
Tags: access, community, content, login, password, register, registration, security, users, membership
Requires at least: 1.5
Tested up to: 2.8.4
Stable tag: 2.1.1

This is a plugin to restrict content to be viewable by registered members. 

== Description ==

WP-Members is a plugin to restrict WP content to be viewable by registered site members.  It is designed to work "out-of-the-box" with no modifications to your theme, but also to be scalable for those that want to customize the look and feel, or want to restrict only some content.

By default, WordPress allows all content to be "open" and viewable by anyone and allows the site owner to restrict specific content if desired by setting a password for the post.  WP-Members operates with the reverse assumption.  It restricts all content by default and allows the site owner to "unblock" content as desired.

The plugin adds fields to the registration process to include name, address, phone, and email.

WP-Members 2.0 is a quantum leap forward from the 1.x. It was rebuilt from the ground up to be easier to install and allow more scalability.  Unlike the previous 1.x versions, 2.0 is designed to use the WP users and usermeta tables.  This allows it to be compatible with other login based plugins/addons such as a forum.  NOTE: upgrading from WP-Members 1.x requires the use of a migration script to get your users into the WordPress format.  Download the migration script at http://butlerblog/wp-members/


== Installation ==

WP-Members 2.0 was designed to run "out-of-the-box" with no modifications to your WP installation necessary.  There are also optional features that, if implemented, will require you to add some php to your theme.

Basic Install:

1. Upload `wp-members.php` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

That's it.

Locking down your site:

* To begin restricting posts, you will need to be using the `<!--more-->` link.  Content above to the "more" split will display on summary pages (home, archive, category) but the user will be required to login to view the entire post.
* If you want to restrict comments from being viewed along with posts, add the following at the top your comments.php template file: `<?php if (!is_user_logged_in()) { $post->post_password = wpmem_generatePassword(); } ?>`
* To further protect comments, we recommend setting "Users must be registered and logged in to comment" under Options > General
* Also on the page Options > General, we recommend making sure "Anyone can register" is unchecked.  Although not required, this will prevent WP's native registration from colliding with WP-Members.
* If you want to display the user's login status, and the following function call to your template: `<?php wpmem_login_status(); ?>`
* To add the login box to the sidebar (if desired) - if no widget support, call the function `<?php wpmem_inc_sidebar(); ?>`.  If you do have widget support, you can just drag the WP-Members widget to your sidebar.
* If you want to have your users be able to edit their login information, add a page (not a post) with a slug of "members-area".  In the body of this page, place `<!--members-area-->` where you want WP-Members to display its content.  (You may place content before and after this is desired.)  This page will allow registered members to edit their information or change their password, and will display the registration form for new members.
* If you want to customize any of the user viewable output for the plugin, this begins at line 580 in the wp-members.php file.  This will allow you to better integrate the WP-Members plugin with the look and feel of your site's theme.  You can customize any of the raw HTML, HTML output, and the email content, but careful not to change any of the php variables or function calls.  We suggest making a backup of the file before editing.


== Frequently Asked Questions ==

= I activated the plugin and went to test it and it didn't block my post? =

Make sure you log out of the admin before you test.  If you are logged in as admin, you will be able to click through to view the post.  (To know if you are logged in, we suggest using the WP-Members login widget, included in the installation. However, you must have the widgets plugin and a widget enabled theme to use this feature.)

= How can I show the login status on the sidebar? = 

If your theme is widget enabled, activate the widgets plugin, then add the WP-Members widget to your sidebar.  If you do not have widgets, you can call the function by adding this to your sidebar: `<?php wpmem_inc_sidebar(); ?>`


== Changelog ==

= 2.1.1 =
* Udates for the 2.1.0 release that were not completed.
* updated variables for some function calls.
* changed `<--` to `&laquo;`
* eliminated unnecessary $table_prefix globals.
* updated some queries to better utilize the $wpdb class.
* custom fields admin is now managed as an array (cuts the lines of code by 75%, and makes way for user defined custom fields).