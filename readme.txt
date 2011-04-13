=== WP-Members ===
Contributors: cbutlerjr
Donate link: http://butlerblog/wp-members/
Tags: authentication, community, content, login, password, register, registration, restriction, security, users, membership, access, block, permissions, members, 
Requires at least: 3.0
Tested up to: 3.0.1
Stable tag: 2.5.0

This is a plugin to restrict content to be viewable by registered members. 

== Description ==

WP-Members is a plugin to make your WordPress blog a membership driven site.  Perfect for newsletters, private blogs, premium content sites, and more!  The plugin restricts selected WP content to be viewable by registered site members.  Unlike other registration plugins and WordPress itself, it puts the registration process inline with your content (and thus your branded theme) instead of the native WP login page.  WP-Members works "out-of-the-box" with no modifications to your theme, but it is fully scalable for those that want to customize the look and feel, or want to restrict only some content.  It is a great tool for sites offering premium content to subscribers, and is adaptable to a variety of applications.

= Features: =

* Can block posts, pages, both, or none by default
* Can override the default block setting at the individual post/page level
* Login/Registration inline with content rather than the WP login page
* User registration and member information management integrated into your theme
* Sidebar login widget
* Can set fields that will display in the registration form
* Can set fields to be required
* Notify admin of new user registrations
* Hold new registrations for admin approval
* Turn registration off completely (for admins that want to control registrations in some other way)
* Show excerpt on pages/posts for better SEO
* Optional CAPTCHA for registration

By default, WordPress allows all content to be "open" and viewable by anyone and allows the site owner to restrict specific content if desired by setting a password for the post.  WP-Members operates with the reverse assumption.  It restricts all content by default but allows the site owner to "unblock" content as desired.  WP-Members also offers the ability to change the default plugin settings.  For those that simply want utilize the member management features and possibly restrict some content, the default setting can easily be toggled to block or unblock pages and/or posts by default.  No matter what the default setting, individual posts or pages can be set to be blocked or unblocked as well.

The plugin adds custom fields to the registration process to include name, address, phone, and email.  All of the registration process is inline with your theme and content rather than using the WordPress login page.  This offers you a premium content site with a professional and branded look and feel.

There are also some special pages available.  There is a Members Area where registered members can edit their information and change/reset their password.  Although a registration form is included in place of blocked content, there is a registration page available for those that need a specific URL for registrations (such as email marketing or banner ads).  NEW in 2.4, you can now also establish a specific login page as well.


== Installation ==

WP-Members is designed to run "out-of-the-box" with no modifications to your WP installation necessary.  There are also optional features that, if implemented, will require you to add some php to your theme.

= Basic Install: =

1. Upload all files in the `/wp-members/` directory to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

You are ready to begin using WP-Members.  Now follow the instructions titled "Locking down your site" below.  A "Quick Start Guide" is available at http://butlerblog.com/wp-members.  A live example site is viewable at http://butlerblog.com/wpmembers.

(Upgrading from 2.1 or earlier: If you are running a previous version "out-of-the-box," you should be able to upgrade without any problems.  If you did any customization to which registration fields are used and/or required, you can simply set this in the new admin panel to match your current usage.  However, if you have made changes to the code to customize the fields beyond this, i.e. field names or types, you will need to make some changes to the install script to customize the registration fields accordingly.  Also, if you made code changes to the inline registration and login forms, you should download and compare the new code to your customizations before upgrading.)

= Locking down your site: =

* To begin restricting posts, you will need to be using the `<!--more-->` link.  Content above to the "more" split will display on summary pages (home, archive, category) but the user will be required to login to view the entire post.
* If you want to restrict comments from being viewed along with posts, add the following at the top your comments.php template file: `<?php if (!is_user_logged_in() && !get_post_custom_values('unblock')) { $post->post_password = wpmem_generatePassword(); } ?>`  IMPORTANT: If you copy/paste this code, make sure there are NO line breaks.  Also, make sure you do not put this inside another set of php open/close tags (<?php ... ?>)
* To further protect comments, we recommend setting "Users must be registered and logged in to comment" under Settings > Discussion
* Also on the page Settings > General, we recommend making sure "Anyone can register" is unchecked.  Although not required, this will prevent WP's native registration from colliding with WP-Members, especially if you are using any of the WP-Members additional registration fields.
* Under Settings > Reading, we recommend that "For each article in a feed, show" is set to "Summary."


= Additional Settings and Information = 

A "Quick Start" guide is available at the plugin's homepage: http://butlerblog.com/wp-members

The guide outlines the installation process, and also documents how to use all of the settings.

* If you want to display the user's login status, and the following function call to your template: `<?php wpmem_login_status(); ?>`
* To add the login box to the sidebar (if desired) you can just drag the WP-Members widget to your sidebar. If your theme has no widget support, call the function `<?php wpmem_inc_sidebar(); ?>`.
* If you want to have your users be able to edit their login information, add a page (not a post) with a slug of "members-area".  In the body of this page, place `<!--members-area-->` where you want WP-Members to display its content.  (You may place content before and after this if desired.)  This page will allow registered members to edit their information or change their password, and will display the registration form for new members.  (If you are using the default permalinks, i.e. http://yoursite.com/?p=123, then you must be certain this page title is "Members Area". Check Settings > Permalinks to see your settings.)
* If you would like to have a page to direct users for registrations, WP-Members now offers you a registration page.  Similar to the "members area" page setup, create a page (not a post) with a slug of "register".  In the body of the page, put the placeholder `<!--reg-area-->` (Just like the members area, if you are using default permalinks, you must title this page "Register".)
* New in 2.5, you may now use shortcodes to call the Members Area and Register pages rather than the above method.  This is desireable for those that are using default permalinks of if you need a different page slug. In order to use a shortcode to display the Members Area, Registration, or the login form, insert the following shortcode into the page with the parameter for what page you are calling:
	[wp-members page="members-area"]
	[wp-members page="register"]
	[wp-members page="login"]
* New in 2.5 is reCAPTCHA support for the initial registration process.  If you use reCAPTCHA, you will need a valid API key.  The CAPTCHA form will not display if you have not entered a valid API key in the WP-Members settings.  You can request an API key here: http://www.google.com/recaptcha (Note: the settings tab for reCAPTCHA will only show if you've turned this option on in the settings tab.)
* New in 2.5 is the addition of a Terms of Service checkbox.  This is turned on by default in new installations.  If you don't need it, you can turn it off.  If you use the TOS checkbox, there is a place for you to insert your Terms of Service in the WP-Members Dialogs and Error Messages.  This text is used to generate a link for the user to read the TOS in a popup.  The TOS content can be HTML; in fact, it is recommended for sizeable TOS documents that you use <h1>, <h2>, <p>, etc.



== Frequently Asked Questions ==

= I activated the plugin and went to test it and it didn't block my post? =

Make sure you log out of the admin before you test.  If you are logged in as admin, you will be able to click through to view the post.  (To know if you are logged in, we suggest using the WP-Members login widget, included in the installation. However, you must have the widgets plugin and a widget enabled theme to use this feature.)

Also, for posts, be sure you are using the `<!--more-->` tag.  The blocking only takes place on single posts.  Without this tag, a full post would display on your home page or on an archive/category page.

For pages, the `<!--more-->` tag is not required, but can be used for excerpts if you have them enabled.

Double check your settings for both posts and pages. The default installation is to block posts by default but not pages.

= How can I show the login status on the sidebar? = 

If your theme is widget enabled, activate the widgets plugin, then add the WP-Members widget to your sidebar.  If you do not have widgets, you can call the function by adding this to your sidebar: `<?php wpmem_inc_sidebar(); ?>`

= I'm really only using this to add user fields and have the login integrated into the site. I would rather that posts be unblocked by default.  How do I do that? = 

WP-Members gives you the ability to change the settings for how the plugin blocks content.  The default setting is to block posts and allow individual posts to be set to unblock at the post level.  You can change this setting so that all posts will be viewable by default.  If you then have a post that you want blocked to registered members only, you can set the post to block at the post level.

= How do I block (or unblock) an individual post (or page)? = 

If you are using the default settings (as mentioned above), and you have a post that you want to be unblocked (viewable by any user, not just logged in users), on the Edit Post page add a Custom Field with the name "block" and set the value to "true" or "1" (either will work).  This post will be now be viewable by anyone.  If you have set WP-Members to unblock by default and you want to block an individual post, use a Custom Field with the name "unblock" and set the value to "true" or "1".  Important: custom fields are case sensitive!  Be certain that you use all lowercase or it will not work.

= How to I change the registration fields that are used and which are required? = 

These settings can be managed on the WP-Members admin panel found under Settings > WP-Members

= Where do I find the users registration information? = 

WP-Members was designed to fully integrate with WordPress to allow maximum compatiblity not only with WP, but also with other plugins that rely on WP user registration information, such as discussion forums, email newsletters, etc.  The user information is in the main WP users page under Users > Users, then click "Edit" under an individual user.  Any non-native WP fields (WP-Members custom fields) are added to the bottom of this page and are fully editable.  (Note: if you don't have any registered users yet, i.e. a clean install, these fields will not display until there is data in them.)

New in 2.5: There is now a WP-Members bulk user edit panel where you can see a list of users, view key details such as email, phone, and country, as well as do bulk activations and exports.  This is found under the WP Users menu: Users > WP-Members.  For bulk user export, WP-Members keeps track of users that are exported so that you don't have to export the full user list just to get a few new subscribers, but you can also export the full list.

= Users are not being emailed their passwords, what is wrong? =

WP-Members uses the native WP function wp_mail to email passwords. This is the same function the WP uses if you are using the WP registration process. If it's not configured properly or for some other reason not working, neither will WP-Members' registration process.

You can test this process by creating a new user via the WP admin panel. Go to Users > Add New in the menu and create a new user. Make sure when you do this "Send this password to the new user by email" is checked. If you do not get an email, then wp_mail is not working. If that is the case, you are probably going to have to do some troubleshooting to fix it. Try the WP support forums for this: http://wordpress.org/tags/wp_mail

= Can I customize the way the login and registration forms look? =

Yes!  There are two classes available for customized CSS specifications - wpmem_login, wpmem_reg.  There is also and ID for error messages: wpmem_msg.

The wpmem_login and wpmem_reg classes wrap the login and registration tables.  The dialog messages that display on form validation or registration success are wrapped with the wpmem_msg ID.  This allows you to set things like cell spacing, padding, borders, backgrounds, and more via CSS.

= Can I customize the plugin =

It is not recommended. If you make any customized code changes, they will need to be reimplemented in the event of a plugin upgrade.


== Other Notes ==

= Statement regarding the name WP-Members =

WP-Members is a trademark of butlerblog.com.

There are a number of commercial vendors offering products called WP-Members or a derivative thereof.  These products are neither free, nor open source.  The original plugin hosted here has been publicly available since 2006 and in no way associated with any of these vendors.  Tagging your support request in the wordpress.org forums attaches it to this plugin.  If you are seeking support for one of these commercial products, you should seek support from the vendor.  If you paid for it, or you got it from a site other than http://wordpress.org/extend/plugins/wp-members or http://butlerblog.com/ , then it isn't WP-Members(tm).

An official statement is available here: http://butlerblog.com/regarding-wp-members


== Upgrade Notice ==

Care was taken in the building of version 2.5 to consider various upgrade scenarios.  Unless you have made customizations to the code itself, you should be able to upgrade without overriding any previous settings.  Some changes were made to the install that could affect users of the 2.4.0 public beta release, so if you are one of those users, please make sure you have a proper backup prior to upgrade so you can rollback if necessary.

WP-Members 2.3.x updates the API calls for the use of the WP-Members sidebar login widget.  Double check your sidebar after upgrading to determine if you need to reapply the widget.  (There is no change to this for upgrading from 2.3.0 to 2.3.1.)  


== Screenshots ==

Rather than bloat your plugin download with screenshots, we will be offering screenshots and videos at the plugin's homepage: http://butlerblog.com/wp-members


== Changelog ==

= 2.5.0 =
WP-Members 2.5 is essentially a pre-3.0 release and extention of the 2.4 release, which was only released as a public beta. In addition to the list below, see the list of features, improvements, and fixes for 2.4.0.

New Features
* added shortcode support for login only page
* added support for checkboxes in the registration fields
* added setting for members area/settings page url (so we can have "forgot password" link anywhere)
* added a "change your password" link in the email if members area/settings page url is set

Bug Fixes
* corrected a bug introduced in the 2.3.x widget update that caused the widget to be undraggable in certain instances

= 2.4.0 =
New Features
* added reCAPTCHA support for registration
* added Terms of Service (TOS) checkbox and popup
* added shortcode support for members area and register pages
* added custom user management panel for bulk user edits
* added user list export
* localization support (beginning)
* contextual help in admin panels (beginning)

Code Improvements
* updated the registration process so that unused fields are not put into the user_meta table
* updated emails for moderated registration to send user the url they signed up on
* capture user's IP address at registration
* sets logged in admin as activated (prevents admin from accidentally being locked out)
* added toggle to force clean install of settings
* improvements to uninstall process

Bug Fixes
* added stripslashes to dialogs (accommodates the use of apostrophes/quotation marks)
* fixed sidebar login for non-widget use (bug from 2.3.x)

= 2.3.2 =
Bug Fix Release
* fixed login failed message for sidebar widget
* fixed login failed message for members area page
* fixed email to include user_url field properly
* changed cell alignment for 'textarea' field type in reg form

= 2.3.1 =
Code Improvements

* updated deprecated call get_usermeta to get_user_meta, update_usermeta to update_user_meta
* completed update of deprecated call get_settings changed to get_option
* removed deprecated functions wpmem_register() and wpmem_update(), both of these are now handled by wpmem_registration
* $redirect_to in wpmem_login changed to $_POST
* fixed password reset link issue
* changed wp-members-admin.php to load for 'edit_users' capabilities, down from 'manage_options'. 
* changes to admin form posts for use with WP Multisite (still need additional testing with Multisite for full compatibility)

New Features

* added direct link to edit user in notify admin email
* added optional registration page

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
* eliminated unnecessary $table_prefix globals.
* updated some queries to better utilize the $wpdb class.
* custom fields admin is now managed as an array (cuts the lines of code by 75%, and makes way for user defined custom fields).