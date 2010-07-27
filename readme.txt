=== WP-Members ===
Contributors: cbutlerjr
Donate link: http://butlerblog/wp-members
Tags: authentication, community, content, login, password, register, registration, security, users, membership, access, block, permissions, members, 
Requires at least: 2.7
Tested up to: 3.0
Stable tag: 2.2.1

This is a plugin to restrict content to be viewable by registered members. 

== Description ==

WP-Members is a plugin to restrict WP content to be viewable by registered site members.  It also puts the registration process inline with your content rather than use the native WP registration process.  It is designed to work "out-of-the-box" with no modifications to your theme, but also to be scalable for those that want to customize the look and feel, or want to restrict only some content.

By default, WordPress allows all content to be "open" and viewable by anyone and allows the site owner to restrict specific content if desired by setting a password for the post.  WP-Members operates with the reverse assumption.  It restricts all content by default and allows the site owner to "unblock" content as desired.  New in version 2.2 is the ability to change the default settings of WP-Members.  For those that simply want utilize the member management features and possibly restrict some content, the default setting can easily be toggled to accomodate this.

The plugin adds fields to the registration process to include name, address, phone, and email.  New in version 2.2 is an admin panel to manage the fields that will be used (both WP native fields and teh additional WP-Members fields).  Setting which fields are required is also now manageble through the admin panel.

Version 2.2 is our biggest change since the move to 2.0.  WP-Members 2.x is a quantum leap forward from the 1.x. It was rebuilt from the ground up to be easier to install and allow more scalability.  Unlike the previous 1.x versions, 2.x is designed to use the WP users and usermeta tables.  This allows it to be compatible with other login based plugins/addons such as a forum.  NOTE: upgrading from WP-Members 1.x requires the use of a migration script to get your users into the WordPress format.  Download the migration script at http://butlerblog/wp-members/


== Installation ==

WP-Members 2.x is designed to run "out-of-the-box" with no modifications to your WP installation necessary.  There are also optional features that, if implemented, will require you to add some php to your theme.

Basic Install:

1. Upload `wp-members.php` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

That's it!  You are ready to begin using WP-Members.  Follow the instructions titled "Locking down your site" below.  A "Quick Start Guide" is available at http://butlerblog.com/wp-members.  A live example site is viewable at http://butlerblog.com/wpmembers.

(Upgrading from 2.1 or earlier: If you are running a previous version "out-of-the-box," you should be able to upgrade without any problems.  If you did any customization to which registration fields are used and/or required, you can simply set this in the new admin panel to match your current usage.  However, if made changes to the code to customize the fields beyond this, i.e. field names or types, you should not upgrade at this time.  Also, if you made code changes to the inline registration and login forms, you should download and compare the new code to your customizations before upgrading.)

Locking down your site:

* To begin restricting posts, you will need to be using the `<!--more-->` link.  Content above to the "more" split will display on summary pages (home, archive, category) but the user will be required to login to view the entire post.
* If you want to restrict comments from being viewed along with posts, add the following at the top your comments.php template file: `<?php if (!is_user_logged_in()) { $post->post_password = wpmem_generatePassword(); } ?>`
* To further protect comments, we recommend setting "Users must be registered and logged in to comment" under Settings > Discussion
* Also on the page Settings > General, we recommend making sure "Anyone can register" is unchecked.  Although not required, this will prevent WP's native registration from colliding with WP-Members.
* If you want to display the user's login status, and the following function call to your template: `<?php wpmem_login_status(); ?>`
* To add the login box to the sidebar (if desired) - if no widget support, call the function `<?php wpmem_inc_sidebar(); ?>`.  If you do have widget support, you can just drag the WP-Members widget to your sidebar.
* If you want to have your users be able to edit their login information, add a page (not a post) with a slug of "members-area".  In the body of this page, place `<!--members-area-->` where you want WP-Members to display its content.  (You may place content before and after this if desired.)  This page will allow registered members to edit their information or change their password, and will display the registration form for new members.  (If you are using the default permalinks, i.e. http://yoursite.com/?p=123, then you must be certain this page title is "Members Area". Check Settings > Permalinks to see your settings.)


== Frequently Asked Questions ==

= I activated the plugin and went to test it and it didn't block my post? =

Make sure you log out of the admin before you test.  If you are logged in as admin, you will be able to click through to view the post.  (To know if you are logged in, we suggest using the WP-Members login widget, included in the installation. However, you must have the widgets plugin and a widget enabled theme to use this feature.)

Also, be sure you are using the `<!--more-->` tag.  The blocking only takes place one single posts (or, optionally, pages).  Without this tag, a full post would display on your home page or on an archive/category page.

= How can I show the login status on the sidebar? = 

If your theme is widget enabled, activate the widgets plugin, then add the WP-Members widget to your sidebar.  If you do not have widgets, you can call the function by adding this to your sidebar: `<?php wpmem_inc_sidebar(); ?>`

= I'm really only using this to add user fields and have the login integrated into the site. I would rather that posts be unblocked by default.  How do I do that?

New in version 2.2 is the ability to change the settings for how WP-Members blocks content.  The default is to block posts and allow individual posts to be set to unblock at the post level.  If you change this to "no," then all posts will be viewable by default.  If you then have a post that you want blocked to registered members only, you can set the post to block at the post level.

= How do I block (or unblock) an individual post (or page)?

If you are using the default settings (as mentioned above), and you have a post that you want to be unblocked (viewable by any user, not just logged in users), on the Edit Post page add a Custom Field with the name "block" and set the value to "true" or "1" (either will work).  This post will be now be viewable by anyone.  If you have set WP-Members to unblock by default and you want to block an individual post, use a Custom Field with the name "unblock" and set the value to "true" or "1".

= How to I change the registration fields that are used and which are required?

These settings can be managed on the WP-Members admin panel found under Settings > WP-Members

= Where do I find the users registration information?

WP-Members was designed to fully integrate with WordPress to allow maximum compatiblity not only with WP, but also with other plugins that rely on WP user registration information, such as discussion forums, email newsletters, etc.  The user information is in the main WP users page under Users > Users, then click "Edit" under an individual user.  Any non-native WP fields (WP-Members custom fields) are added to the bottom of this page and are fully editable.  (Note: if you don't have any registered users yet, i.e. a clean install, these fields will not display until there is data in them.)


== Changelog ==

= 2.2.1 =
* Bug fix change password
* Merged registration and user update functions to eliminate redundancy
* Added nonce security to the options admin

= 2.2.0 =
The #1 request with the plugin is to simply be able to change the required fields.  I have greatly simplified the process so it can be managed from within the WP admin panel.  Additionally, I added the ability to determine what fields will show in the registration form (both native WP fields, and additional WP-Members fields).  Also, the error/dialog messages can now be managed from with the admin panel as well.
* Added new customization features and an admin panel
* Can set fields that will display
* Can set fields to be required
* Can manage error/dialog messages from admin

= 2.1.2 =
* Added fix to set new registrations as the default role

= 2.1.1 =
* Udates for the 2.1.0 release that were not completed.
* updated variables for some function calls.
* changed `<--` to `&laquo;`
* eliminated unnecessary $table_prefix globals.
* updated some queries to better utilize the $wpdb class.
* custom fields admin is now managed as an array (cuts the lines of code by 75%, and makes way for user defined custom fields).