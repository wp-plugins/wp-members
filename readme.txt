=== WP-Members ===
Contributors: cbutlerjr
Tags: authentication, captcha, community, content, login, password, register, registration, restriction, security, user, users, membership, access, block, permissions, members
Requires at least: 3.1
Tested up to: 3.5.0
Stable tag: 2.8.1
License: GPLv2

WP-Members&trade; is a free membership management framework for WordPress&reg; that restricts content to registered users.

== Description ==

WP-Members&trade; is a plugin to make your WordPress&reg; blog a membership driven site.  Perfect for newsletters, premium content sites, and more!  The plugin restricts selected WordPress&reg; content to be viewable by registered site members.  WP-Members&trade; puts the registration process inline with your content (and thus your branded theme) instead of the native WP login page.  WP-Members&trade; works "out-of-the-box" with no modifications to your theme, but it is scalable for users that want to customize the look and feel, or want to restrict only some content.  It is a great tool for sites offering premium content to subscribers, and is adaptable to a variety of applications.

= Features: =

* Block posts, pages, both, or none by default
* Block individual posts/pages
* Login/Registration is inline with content rather than the WP login page
* User registration and profile integrated into your theme
* Sidebar login widget
* Create custom registration fields
* Set which fields display and which are required
* Notify admin of new user registrations
* Hold new registrations for admin approval
* Automatically create post excerpts
* Show excerpt on pages/posts for better SEO
* Optional CAPTCHA for registration
* Action and filter hooks for extensibility

By default, WordPress&reg; allows all content to be "open" and viewable by anyone and allows the site owner to restrict specific content if desired by setting a password for the post.  WP-Members&trade; operates with the reverse assumption.  It restricts all posts by default but allows the site owner to "unblock" content as desired.  WP-Members&trade; also offers the ability to change the default plugin settings.  For those that simply want to utilize the member management features and possibly restrict some content, the default setting can easily be toggled to block or unblock pages and/or posts by default.  No matter what the default setting, individual posts or pages can be set to be blocked or unblocked at the article level, overriding the default setting.

The plugin installs with additional registration fields including name, address, phone, and email. Using the WP-Members&trade; admin panel, you can also create your own custom registration fields and delete existing ones.  Changing the field order is simple with a drag-and-drop interface.  All of the registration process is inline with your theme and content rather than using the WordPress&reg; login page.  This offers you a premium content site with a professional and branded look and feel.  It also provides an opportunity for the user to register without leaving the page to do so - less clicks = more conversions.

There are also some special pages that can be created with simple shortcodes:

* A User Profile page where registered members can edit their information and change/reset their password.  
* A Registration page available for those that need a specific URL for registrations (such as email marketing or banner ad landing pages).  Note: this is strictly optional as a registration form can also be included by default on blocked content pages.  
* A Login page.  This is also an optional page as the login form is included by default on blocked content.  But if you need a specific login page, this can be created with a simple shortcode.
* And more!

The plugin runs on a framework with over 30 action and filter hooks so you can fully customize your implementation.

In addition to all of the features above, the plugin can be extended with premium add-on modules available from the support site rocketgeek.com.  Members of rocketgeek.com have access to support, examples, tutorials, and code snippets that will help you extend and customize the base plugin using the plugin's framework.  Some of the add-ons have their own hooks and shortcodes to further extend the plugin's extensibility.  [Visit the site for more info](http://rocketgeek.com/about/site-membership-subscription/).

= What the plugin does not do =

WP-Members does not automatically hide absolutely everything from view.  The default install of the plugin is designed to use "teaser" content to drive users to want to register.  If you want certain content or menu elements completely hidden, there are ways to do that with some customization between your theme and the plugin, but it is not automatic.


== Installation ==

WP-Members&trade; is designed to run "out-of-the-box" with no modifications to your WP installation necessary.  Please follow the installation instructions below.  __Most of the support issues that arise are a result of improper installation or simply not reading/following directions__.

= Basic Install: =

The best start is to follow the instructions in the [Quick Start Guide](http://rocketgeek.com/plugins/wp-members/quick-start-guide/).  There is also a complete [Users Guide available](http://rocketgeek.com/plugins/wp-members/users-guide/) that covers all of the plugin's features in depth.

1. Upload the `/wp-members/` directory and its contents to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress&reg;

You are ready to begin using WP-Members&trade;.  Now follow the instructions titled "Locking down your site" below.

NOTE: Please follow instructions for installation. The vast majority of people that have marked the plugin as "broken" in the plugin compatibility form simply did not read/follow installation instructions. If something is unclear, ask for assistance.

= Locking down your site: =

* To begin restricting posts, you will need to be using the `<!--more-->` link in your posts.  Content above to the "more" split will display on summary pages (home, archive, category) but the user will be required to login to view the entire post.  You may also use the plugin's auto excerpt setting to create post excerpts automatically.  If you do not use the "more" tag or the auto excerpt setting, full post content is going to show on archive templates.
* To begin restricting pages, change the plugin default setting for pages to be blocked. Unlike posts, the `<!--more-->` link is not necessary in the blocking of pages, but __must__ be used if you have the "show excerpts" setting turned on.
* To protect comments, we recommend setting "Users must be registered and logged in to comment" under Settings > Discussion
* Also on the page Settings > General, we recommend making sure "Anyone can register" is unchecked.  While not required, this will prevent WP's native registration from colliding with WP-Members&trade;, especially if you are using any of the WP-Members&trade; additional registration fields.
* Under Settings > Reading, we recommend that "For each article in a feed, show" is set to "Summary."  WordPress&reg; installs with full feed settings by default. If you don't change this, your feeds will show full content.


= Additional Settings and Information = 

A full Users Guide is [available here](http://rocketgeek.com/plugins/wp-members/users-guide/).  The guide outlines the installation process, and also documents how to use all of the settings.

= Plugin Extensibility =

WP-Members&trade; is designed to be an out-of-the-box usable plugin, but also have an extensible framework for maximum flexibility and customization.  For this purpose, there are a number of shortcodes, filters, and actions that can be used.

See [this page](http://rocketgeek.com/plugins/wp-members/users-guide/shortcodes/) for a list of shortcodes and their description.

The plugin has 40 filter and action hooks.  For a list of hooks and a description of their use, see [this page](http://rocketgeek.com/plugins/wp-members/users-guide/filter-hooks/)

The plugin's premium support site has __loads__ of tips, tricks, and sample code for you to make maximum use out of the plugin.  [Get more information here](http://rocketgeek.com/about/site-membership-subscription/).  Members of the premium support site also have access to premium add-on modules.


== Frequently Asked Questions ==

The FAQs are maintained at http://rocketgeek.com/plugins/wp-members/users-guide/faqs/


== Other Notes ==

= Statement regarding the name WP-Members&trade; =

WP-Members&trade; is a trademark of butlerblog.com.

There are a number of commercial vendors offering products called WP-Members&trade; or a derivative thereof.  These products are neither free, nor open source.  The original plugin hosted here has been publicly available since 2006 and in no way associated with any of these vendors.  Tagging your support request in the wordpress.org forums attaches it to this plugin.  If you are seeking support for one of these commercial products, you should seek support from the vendor.  If you got it from a site other than http://wordpress.org/extend/plugins/wp-members or http://butlerblog.com/ , then it isn't WP-Members&trade;.

An [official statement is available here](http://butlerblog.com/regarding-wp-members).


== Upgrade Notice ==

Security fix release - This update is comprised of security fixes and a couple of bug patches.

== Screenshots ==

Rather than bloat your plugin download with screenshots, we will be offering screenshots and videos at the plugin's homepage: http://rocketgeek.com


== Changelog ==

= 2.8.1 =

Security update release: 2.8.1 primarily closes some potential security holes.  This update is highly recommended.

Security Updates:

* Closed potential cross site scripting exploit
* Added nonces to front-side registration
* Updated nonces in admin form submission
* Security evaluation and updates to other areas

Feature Updates:

* Added dropdown option for User Profile (members-area) and Register page location
* Updated dropdown field to accommodate commas in the values (ex: 1,000)

Other Updates:

* Updated TOS shortcode to be case-insensitive for the shortcode parameter (TOS/tos)
* Begin deprecating members-area parameter to be replaced with user-profile
* Separated User Profile functions from wp-members-core.php file
* Applied post 2.8.0 patches and corrected missing files

= 2.8.0 =

New Feature release: 2.8.0 offers the beginning of a major rebuild of the admin panels with a few front-side features slipped in along the way.

Security Improvements:

* Added a dummy index.php file in all plugin directories.  This is a security improvement and disallows direct access to a directory (in case your server allows directory browsing).

Code Improvements:

* Added new constants WPMEM_DIR and WPMEM_PATH.  This will allow you to grab the directory of the plugin directly in action and filter functions.
* Broke up the admin file wp-members-admin.php into multiple files based on function.  These are all now moved into an /admin/ directory.
* Added the password field to the fields array in the registration function so that this can be used in the registration hooks and filters.
* Updated the logout process to use wp_clear_auth_cookie.  The previous wp_clearcookie was deprecated and was causing header errors in some instances.
* Improved the TOS shortcode. This should eliminate some of the parsing errors some users experienced in previous releases.

Admin Panel Updates:

* New admin look updates the tabs to the WP format.  This also was built to allow extensible tabs so you can hook in and create your own tabs and panels.
* Add field dialog was updated and improved.
* Added an admin process to edit existing fields.
* Added new option in the plugin options to load one of the predefined stylesheets from a dropdown.
* Block/Unblock post meta box added to the post/page editor – no need to use custom fields anymore (this feature actually updates the custom fields accordingly, so you can use custom fields if you want to).
* Added dropdown selector for preloaded stylesheets - no need to enter the location of the preloaded stylesheets to use them.

New Filters:

* wpmem_admin_tabs - allows developers to hook into the admin tabs to add additional tabs.
* wpmem_admin_style_list - allows developers to hook into the dropdown list of stylesheets to add additional stylesheets.
* wpmem_auto_excerpt - filters the automatically generated excerpt.  Allows you to customize a 'read more' link.
* wpmem_post_password - filters the automatically generated post password that blocks comments on blocked posts/pages.
* wpmem_forgot_link - filters the forgot password link that shows in the login forms.
* wpmem_reg_link - filters the register link that shows in the login forms.

Other Updates:

* Added a new pre-loaded stylesheet for Twenty Twelve theme.  New installs will default to this style.  Note: with the addition of the new style selector in the admin panel, you can easily toggle between the preloaded stylesheets.  You can also add your own using the new wpmem_admin_style_list filter, enter the URL location in the Custom Stylesheet field, or load one using wp_enqueue_scripts.


= 2.7.7 =

New Features:

* Added a new filter hook wpmem_securify.  This hook applies a filter to the $content variable at the end of the wpmem_securify function.  The primary reason for this hook is to be able to run filters on $content that would give you the ability to block content even if the user is logged in (the wpmem_block filter only works for non-logged in state).  This will bring in the ability to block users from content based on defined criteria such as content is for members of "group A" but the user is does not have access to "group A" content.
* Added wpmem_email_headers filter hook.  This will allow you to easily filter the headers for the email process of the plugin giving you the ability to send HTML email without modifying the plugin.
* Added wpmem_user_activated action hook.  This hook will give you the ability to run actions at the end of user activation.  For sites that moderate registration, this gives you the ability to hook in actions that you might not want to do before approval of the registration (such as would normally be done with wpmem_post_register_data).
* Added new shortcode for creating a user list/member directory.  __This shortcode requires installation of the premium add-on module WP-Members User List [available to rocketgeek.com members](http://rocketgeek.com/about/site-membership-subscription/)__. The shortcode has parameters for including a member search function as well as filter hooks for filtering the layout of the directory elements.
* Added new shortcode for protecting inline content with the __premium add-on module WP-Members PayPal Subscription [available to rocketgeek.com members](http://rocketgeek.com/about/site-membership-subscription/)__.

Bug Fix:

* Fixed a bug where the reCAPTCHA error messages do not display on the [shortcode pages "register" and "members-area"](http://rocketgeek.com/plugins/wp-members/users-guide/shortcodes/page-shortcodes/).

Code Improvement:

* Completed a rebuild of the login function wpmem_login.  Updated the cookie process to switch from [wp_setcookie](http://codex.wordpress.org/Function_Reference/wp_setcookie) (which is deprecated) to [wp_set_auth_cookie](http://codex.wordpress.org/Function_Reference/wp_set_auth_cookie). Also, the [wpmem_login_redirect hook](http://rocketgeek.com/plugins/wp-members/users-guide/filter-hooks/wpmem_login_redirect/) was moved to after the login credentials have been validated and the user is logged in.  This allows the hook to access user data without the need to validate the user within the filter.

= 2.7.6 =

This release has some new features and some code updates

* Added ability to use same redirect_to querystring that WP uses in the wp-login form.  This allows more seamless replacement of the wp-login.
* Added a new page shortcode for password reset/change [wp-members page="password"].  If the user is logged out, it works the reset forgotten password functionality.  If the user is logged in, it will offer the change password functionality.  These functions do also remain in the members-area page shortcode as well, but now can be placed in a stand-alone location as well.
* Added a new page shortcode for the user edit page [wp-members page="user-edit"].  This needs to be used if the user is logged in and can be used with the login status shortcode.
* Removed $content global from the page shortcode function.  This should correct the double form issue when used with plugins/themes that filter $content.
* Added do_shortcode to the page shortcode call.  This should allow the page shortcodes to be used in conjuction with other shortcodes on the page (although this is not necessarily recommended).
* Added translations for Russian, Slovak, and Hindi.
* Moved _OLD forms to wp-members-deprecated.php.  These forms can still be used, but will be deprecated in a future version.  It is highly recommended that users still using legacy forms begin converting to the _NEW forms.

= 2.7.5 =

This is a new feature release (see [release announcement](http://wp.me/p1zYcs-xf) for full details.)

* Added 5 new filter hooks
* Final deprecation of the old style shortcodes for special pages (<!--members-area-->, <!--reg-area-->). If you are using these you need simply need to update to the modern shortcodes such as [wp-members page="members-area"]
* Added a check for the error message variable immediately following the wpmem_pre_register_data and wpmem_pre_update_data action hooks.  This will allow for use of these hooks to include your own custom validation and still be able to return a relevant error message.
* Added a check to see if the TOS field is not being used and thus don't display it on the User Profile for the admin.
* Fixed a bug that showed the incorrect heading on the User Edit page when there is an empty field error.
* Added completely updated .pot file for translations
* Updated all .po/.mo translation files from the new .pot (still need some strings translated for some languages).
* Added all .po/.mo files to the download package.

= 2.7.4 =

This is a new feature release (see [release announcement](http://wp.me/p1zYcs-wQ) for full details.)

* Added 14 new filter hooks
* Added full user export function
* Force email in password reset to be non-case sensitive (changes to wpmem_reset_password in wp-members-core.php)
* Changed "Existing users Login" to "Existing Users Login" in wpmem_inc_login in wp-members-dialogs.php

= 2.7.3 =

This is a code improvement release (see [release announcement](http://wp.me/p1zYcs-wD) for full details.)

* Improved a number of functions in various files for improved functionality.
* Added p tag with class .noinput to the username field when updating profile, added property definition to the stylesheet as well to better align the username.
* Added css property to better align checkboxes in the reg form (a change to wp-members.css).
* Added a class to captcha, and a cooresponding css property in wp-members.css for top/bottom margin of captcha form.

New features (shortcodes and hooks):

* Added 'field' shortcode for displaying user data with a shortcode - currently considered experimental and subject to changes.
* Added wpmem_restricted_msg filter hook to filter the restricted post message.
* Added wpmem_login_failed filter hook to filter the login failed message (includes filtering display markup).
* Added wpmem_login_failed_sb filter hook to filter the login failed message in the sidebar (filters message only, not formatting).

= 2.7.2 =

This is primarily a bug fix release (see [release announcement](http://wp.me/p1zYcs-vW) for full details.)

* Fixed a bug where, when using moderated registration, updating a user's backend profile deactivates the user.
* Improved the login error message for login via wp-login.php.
* Added wpmem_pre_register_data action hook.
* Added wpmem_post_register_data action hook.
* Added wpmem_pre_update_data action hook.
* Added wpmem_post_update_data action hook.

= 2.7.1 =

This release contains some new features that didn't get completed for 2.7.0 and some fixes (see [release announcement](http://wp.me/p1zYcs-vq) for full details.)

New features:

* Deactivate/reactivate users
* Registration moderation added for user defined passwords
* Include an optional stylesheet based on TwentyEleven Theme
* Include an optional stylesheet based on Kubrick (for narrower content areas)
* Added wpmem_register_redirect action hook
* Added wpmem_login_redirect filter hook
* Added wpmem_logout_redirect filter hook
* Added wpmem_sidebar_status filter hook
* Added wpmem_register_links filter hook
* Added wpmem_member_links filter hook
* Added wpmem_login_links filter hook


Fixes:

* rebuilt default email install function
* skip password in [fields] shortcode (changes in wpmem_notify_admin)
* fixed widget sidebar div tag (changes in class widget_wpmemwidget) 
* fixed form field validation for invalid email still registered user (changes in wpmem_registration)
* fixed rememberme in login process (changes in wpmem_login)
* moved send from filters to just the wp-members email processes (changes in wpmem_inc_regemail && wpmem_notify_admin)


= 2.7.0 =

This is new feature release with the following features and improvements (see :

* Email messages can be customized via the plugin admin panel.
* Dropdown fields can be added via the field managment panel.
* Moved location of pluggable file load so that constants may be used without loading twice.
* Registration stores the url the user registered on for all registrations, not just if registration is moderated.
* Trim trailing whitespace on members area, register, and custom css urls.
* Added wp_specialchars_decode to blogname in emails to decode any special characters (such as &amp;) in the title.
* Registration function is now pluggable.
* Updated sidebar widget to be multi-widget compatible.
* TOS can be stored in dialogs OR be a WP page (set in dialogs with a shortcode).
* Plugin can be set up for users to select their own passwords at registration (cannot be used with moderated registration).