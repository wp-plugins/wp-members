=== WP-Members ===
Contributors: cbutlerjr
Donate link: http://butlerblog/wp-members/
Tags: authentication, community, content, login, password, register, registration, security, users, membership, access, block, permissions, members, 
Requires at least: 2.7
Tested up to: 3.0.1
Stable tag: 2.3.0

This is a plugin to restrict content to be viewable by registered members. 

== Description ==

WP-Members is a plugin to restrict WP content to be viewable by registered site members.  It also puts the registration process inline with your content rather than using the native WP registration process.  It is designed to work "out-of-the-box" with no modifications to your theme, but it is scalable for those that want to customize the look and feel, or want to restrict only some content.  It is a great tool for sites offering premium content to subscribers, and is adaptable to a variety of applications.

= Features: =

* Can block posts, pages, both, or none by default
* Can override the default block setting at the individual post/page level
* Login/Registration inline with content rather than the WP login page
* Can set fields that will display in the registration form
* Can set fields to be required
* Notify admin of new user registrations
* Hold new registrations for admin approval
* Turn registration off completely (for admins that want to control registrations in some other way)
* Show excerpt on pages/posts for better SEO

By default, WordPress allows all content to be "open" and viewable by anyone and allows the site owner to restrict specific content if desired by setting a password for the post.  WP-Members operates with the reverse assumption.  It restricts all content by default and allows the site owner to "unblock" content as desired.  New in version 2.2 is the ability to change the default settings of WP-Members.  For those that simply want utilize the member management features and possibly restrict some content, the default setting can easily be toggled to block or unblock pages and/or posts by default.  No matter what the default setting, individual posts or pages can be set to be blocked or unblocked as well.

The plugin adds custom fields to the registration process to include name, address, phone, and email.


== Installation ==

WP-Members 2.x is designed to run "out-of-the-box" with no modifications to your WP installation necessary.  There are also optional features that, if implemented, will require you to add some php to your theme.

= Basic Install: =

1. Upload `wp-members.php` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

That's it!  You are ready to begin using WP-Members.  Follow the instructions titled "Locking down your site" below.  A "Quick Start Guide" is available at http://butlerblog.com/wp-members.  A live example site is viewable at http://butlerblog.com/wpmembers.

(Upgrading from 2.1 or earlier: If you are running a previous version "out-of-the-box," you should be able to upgrade without any problems.  If you did any customization to which registration fields are used and/or required, you can simply set this in the new admin panel to match your current usage.  However, if made changes to the code to customize the fields beyond this, i.e. field names or types, you should not upgrade at this time.  Also, if you made code changes to the inline registration and login forms, you should download and compare the new code to your customizations before upgrading.)

= Locking down your site: =

* To begin restricting posts, you will need to be using the `<!--more-->` link.  Content above to the "more" split will display on summary pages (home, archive, category) but the user will be required to login to view the entire post.
* If you want to restrict comments from being viewed along with posts, add the following at the top your comments.php template file: `<?php if (!is_user_logged_in() && !get_post_custom_values('unblock')) { $post->post_password = wpmem_generatePassword(); } ?>`
* To further protect comments, we recommend setting "Users must be registered and logged in to comment" under Settings > Discussion
* Also on the page Settings > General, we recommend making sure "Anyone can register" is unchecked.  Although not required, this will prevent WP's native registration from colliding with WP-Members.
* If you want to display the user's login status, and the following function call to your template: `<?php wpmem_login_status(); ?>`
* To add the login box to the sidebar (if desired) - if no widget support, call the function `<?php wpmem_inc_sidebar(); ?>`.  If you do have widget support, you can just drag the WP-Members widget to your sidebar.
* If you want to have your users be able to edit their login information, add a page (not a post) with a slug of "members-area".  In the body of this page, place `<!--members-area-->` where you want WP-Members to display its content.  (You may place content before and after this if desired.)  This page will allow registered members to edit their information or change their password, and will display the registration form for new members.  (If you are using the default permalinks, i.e. http://yoursite.com/?p=123, then you must be certain this page title is "Members Area". Check Settings > Permalinks to see your settings.)

= Additional Settings and Information = 

A "Quick Start" guide is available at the plugin's homepage: http://butlerblog.com/wp-members

This guide outlines the installation process, and also documents how to use all of the settings.


== Frequently Asked Questions ==

= I activated the plugin and went to test it and it didn't block my post? =

Make sure you log out of the admin before you test.  If you are logged in as admin, you will be able to click through to view the post.  (To know if you are logged in, we suggest using the WP-Members login widget, included in the installation. However, you must have the widgets plugin and a widget enabled theme to use this feature.)

Also, be sure you are using the `<!--more-->` tag.  The blocking only takes place one single posts (or, optionally, pages).  Without this tag, a full post would display on your home page or on an archive/category page.

= How can I show the login status on the sidebar? = 

If your theme is widget enabled, activate the widgets plugin, then add the WP-Members widget to your sidebar.  If you do not have widgets, you can call the function by adding this to your sidebar: `<?php wpmem_inc_sidebar(); ?>`

= I'm really only using this to add user fields and have the login integrated into the site. I would rather that posts be unblocked by default.  How do I do that? = 

New in version 2.2 is the ability to change the settings for how WP-Members blocks content.  The default is to block posts and allow individual posts to be set to unblock at the post level.  If you change this to "no," then all posts will be viewable by default.  If you then have a post that you want blocked to registered members only, you can set the post to block at the post level.

= How do I block (or unblock) an individual post (or page)? = 

If you are using the default settings (as mentioned above), and you have a post that you want to be unblocked (viewable by any user, not just logged in users), on the Edit Post page add a Custom Field with the name "block" and set the value to "true" or "1" (either will work).  This post will be now be viewable by anyone.  If you have set WP-Members to unblock by default and you want to block an individual post, use a Custom Field with the name "unblock" and set the value to "true" or "1".

= How to I change the registration fields that are used and which are required? = 

These settings can be managed on the WP-Members admin panel found under Settings > WP-Members

= Where do I find the users registration information? = 

WP-Members was designed to fully integrate with WordPress to allow maximum compatiblity not only with WP, but also with other plugins that rely on WP user registration information, such as discussion forums, email newsletters, etc.  The user information is in the main WP users page under Users > Users, then click "Edit" under an individual user.  Any non-native WP fields (WP-Members custom fields) are added to the bottom of this page and are fully editable.  (Note: if you don't have any registered users yet, i.e. a clean install, these fields will not display until there is data in them.)

= Users are not being emailed their passwords, what is wrong? =

WP-Members uses the native WP function wp_mail to email passwords. This is the same function the WP uses if you are using the WP registration process. If it’s not configured properly or for some other reason not working, neither will WP-Members’ registration process.

You can test this process by creating a new user via the WP admin panel. Go to Users > Add New in the menu and create a new user. Make sure when you do this “Send this password to the new user by email” is checked. If you do not get an email, then wp_mail is not working. If that is the case, you are probably going to have to do some troubleshooting to fix it. Try the WP support forums for this: http://wordpress.org/tags/wp_mail


== Upgrade Notice ==

WP-Members 2.3.0 has updates the API calls for the use of the WP-Members sidebar login widget.  Double check your sidebar after upgrading to determine if you need to reapply the widget.  



== Changelog ==

= 2.3.0 =
Adds a number of features put off from the 2.2 release

* option to notify admin of new user registrations
* option to hold new registrations for admin approval
* option to turn registration off (for admins that want to control registrations in some other way)
* option to show excerpt on pages/posts
* updated widget calls to wp_register_sidebar_ (register_sidebar_ is deprecated)
* updated certain API calls known to be deprecated
* broke out email related functions to separate file; only loads when needed
* broke out core and dialog functions to separate files

= 2.2.2 =
This release is all code-side cleanup to make the plugin more efficient:

* Rewrote _securify function to remove redundancies
* Merged Login, Password Change, and Password Reset forms into _login_form
* Added uninstall/delete process to empty settings from database on plugin deletion
* Broke out install and admin scripts to separate file only loaded when needed
* Continued improvement of admin functions

= 2.2.1 =
* Change password bug fix
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