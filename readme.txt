=== WP-Members: Membership Framework ===
Contributors: cbutlerjr
Tags: access, authentication, content, login, member, membership, password, protect, register, registration, restriction, subscriber
Requires at least: 3.1
Tested up to: 4.4.0
Stable tag: 3.0.8
License: GPLv2

WP-Members&trade; is a free membership management framework for WordPress&reg; that restricts content to registered users.

== Description ==

WP-Members&trade; is a membership plugin for WordPress&reg;.  Perfect for newsletters, premium content sites, and more!  The plugin restricts selected WordPress&reg; content to registered site members.  WP-Members&trade; puts the registration process on the site front end so it is inline with your content (and thus your branded theme) instead of the native WP login page.  WP-Members&trade; works with no modifications to your theme, but it is scalable for users that want to customize the look and feel, or want to restrict only some content.  It is a great tool for sites offering premium content to subscribers, and is adaptable to a variety of applications.

[vimeo https://vimeo.com/84961265]

= Features: =

* Block posts, pages, both, or none by default
* Block individual posts/pages
* Login/Registration is inline with content rather than the WP login page
* User registration and profile integrated into your theme
* Sidebar login widget
* Create custom registration and profile fields
* Set which fields display and which are required
* Notify admin of new user registrations
* Hold new registrations for admin approval
* Automatically create post excerpts
* Show excerpt on pages/posts for better SEO
* Optional CAPTCHA for registration
* More than 80 action and filter hooks for extensibility

By default, WordPress&reg; allows all content to be "open" and viewable by anyone and allows the site owner to restrict specific content if desired by setting a password for the post.  WP-Members&trade; operates with the reverse assumption.  It restricts all posts by default but allows the site owner to "unblock" content as desired.  WP-Members&trade; also offers the ability to change the default plugin settings.  For those that simply want to utilize the member management features and possibly restrict some content, the default setting can easily be toggled to block or unblock pages and/or posts by default.  No matter what the default setting, individual posts or pages can be set to be blocked or unblocked at the article level, overriding the default setting.

The plugin installs with additional registration fields including name, address, phone, and email. Using the WP-Members&trade; admin panel, you can also create your own custom registration fields and delete existing ones.  Changing the field order is simple with a drag-and-drop interface.  All of the registration process is inline with your theme and content rather than using the WordPress&reg; login page.  This offers you a premium content site with a professional and branded look and feel.  It also provides an opportunity for the user to register without leaving the page to do so - less clicks = more conversions.

There are also some special pages that can be created with simple shortcodes:

* A User Profile page where registered members can edit their information and change/reset their password.  
* A Registration page available for those that need a specific URL for registrations (such as email marketing or banner ad landing pages).  Note: this is strictly optional as a registration form can also be included by default on blocked content pages.  
* A Login page.  This is also an optional page as the login form is included by default on blocked content.  But if you need a specific login page, this can be created with a simple shortcode.
* And more!

The plugin runs on a framework with over 80 action and filter hooks so you can fully customize your implementation.

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

The plugin has 80 filter and action hooks.  For a list of hooks and a description of their use, see [this page](http://rocketgeek.com/plugins/wp-members/users-guide/filter-hooks/)

The plugin's premium support site has __loads__ of tips, tricks, and sample code for you to make maximum use out of the plugin.  [Get more information here](http://rocketgeek.com/about/site-membership-subscription/).  Members of the premium support site also have access to premium add-on modules.


== Frequently Asked Questions ==

The FAQs are maintained at http://rocketgeek.com/plugins/wp-members/users-guide/faqs/


== Other Notes ==

= Statement regarding the name WP-Members&trade; =

WP-Members&trade; is a trademark of butlerblog.com.

There are a number of commercial vendors offering products called WP-Members&trade; or a derivative thereof.  Most of these products are neither free, nor are all of them open source.  The original plugin hosted here has been publicly available since 2006 and in no way associated with any of these vendors.  Tagging your support request in the wordpress.org forums attaches it to this plugin.  If you are seeking support for one of these commercial products, you should seek support from the vendor.  If you got it from a site other than [here](http://wordpress.org/extend/plugins/wp-members) then it isn't WP-Members&trade;.

An [official statement is available here](http://butlerblog.com/regarding-wp-members).

= Regarding RocketGeek.com =

Premium priority support is available at the plugin's site [RocketGeek.com](http://rocketgeek.com).  A site membership includes priority support, members-only forum access, plugin extensions, and a custom code snippet library.  [RocketGeek.com](http://rocketgeek.com) is the only site officially managed for this plugin's support.


== Upgrade Notice ==

WP-Members 3.0.8 is a feature release, see release notes.
WP-Members 3.0.0 is a major version release. Please review the changelog: http://rkt.bz/v30

== Screenshots ==

1. The default when viewing a blocked post - the plugin will deliver a login screen and registration form in place of blocked content (this default can be changed to other options).

2. Admin Panel - Options Tab - the various option settings for the plugin.

3. Admin Panel - Fields Tab - the plugin field manager allows you to manage (or delete) the installed extra fields and field order, and also add your own custom fields.

4. Admin Panel - Dialogs Tab - the major dialogs that the plugin uses for error and other messages can be edited in the plugin's admin panel.

5. Admin Panel - Emails Tab - all of the emails that are sent by the plugin can be edited in the admin panel.

6. Posts > All Posts - The plugin adds a column to the list of posts and pages to display if a post or page is unblocked or blocked (the opposite of whatver you have set for the plugin's default in the options tab).

7. Posts > Edit Post - The plugin adds a meta box to the post/page editor allowing you to set an individual post to be blocked or unblocked (the opposite of whatver your default setting is).

8. Responsive forms.


== Changelog ==

= 3.0.8 =

* Added process for forgotten username retrieval.
* Removed last remaining instances of extract function.
* Updated settings for special pages (login|register|user-profile) to store only the numeric primary key ID. This will eliminate the need to update these settings if the site is moved (from a dev to live site, for example).  Legacy full URL settings will still be compatible without needing to be updated, but will be automatically updated when main options are saved.

= 3.0.7 =

* Fix for use of display_name on profile update.
* Fix for newer installs (post WP 4.0) where WPLANG is not defined and reCAPTCHA is used.
* Fix in wpmem_form shortcode to skp if no additional tag exists.
* Fix to plugin_basename.
* Changes in core to use fields from WP_Members class (preparing for new form field process).
* Reviews and updates to code standards and inline documentation.
* Fix for password reset (typo in object name checking for moderated registration)
* Fix for PayPal extension (http://rkt.bz/r3); added logic to avoid errors if the PayPal extension is disabled but the main option setting remained turned on.

= 3.0.6 =

* Updates to localization function - documented plugin_locale filter, wpmem_localization_file filter, and improved load_textdomain logic.
* Added /lang domain path to plugin header.
* Fixed a bug in the user export function that broke CSV columns when moderated registration was turned on.
* Improved current page retrieval in wpmem_redirect_to_login() function.
* Fixed admin enqueued scripts (post/page screen hook did not load from new location).

= 3.0.5 =

* Updated wpmem_pwd_change and wpmem_pwd_reset action hooks to include password as a parameter.
* Stylesheet updates for 2015, 2014, and generic (both float and no float).
* Fix to TinyMCE shortcode button, should now load button on new post/page editor.
* Added [WP-Members] to the TinyMCE shortcode button for clarity as to what it is.
* Moved admin js and css files to /admin/js/ and /admin/css/
* Moved admin class files to /admin/includes/
* Updated and verified all directories contain an index.php file to prevent directory browsing.

= 3.0.4 =

* Reintroduced the global action variable $wpmem_a for backward compatibility with certain add-ons, most notably the WP-Members MailChimp extension ( see http://rkt.bz/3b ). Users of this extension should upgrade.  This variable had been replaced with the new WP-Members object class introduced in 3.0. However, users of older extensions and those that may have customziations with logic may be using this variable, so rather than force updating and upgrading, it is being added back in.
* Change to the priority of functions hooked to the the_content filter. Lowering the priority should better integrate the plugin with various builder plugins and other processes that currently filter the_content after WP-Members since the content will now be filtered later in the process. This also should improve situations where in the past the on-the-fly texturization shortcode for the WP-Members forms might remain unparsed.

= 3.0.3 =

* Bug fix recaptcha v2 decode json response on validation.
* Bug fix typo in $wpmem object name in admin/user-profile.php.
* Bug fix message string variable in wpmem_msg_dialog_arr filter.
* Fix register form shortcode redirect_to parameter.
* Admin forms now use submit_button() function to generate submit button.
* Changed localization to load on init action which will allow for more flexibility with filtering custom language files.
* Added wpmem_localization_file and wpmem_localization_dir filters.
* Localization checks for file in /wp-content/ language directory first, then loads plugin default.

= 3.0.2 =

* Added reCAPTCHA version 2 as an option. v1 will remain available for now, to be fully deprecated later.
* Fixed widget to use __construct for WP 4.3 compatibility.
* Added error checking for WP reserved names when adding new custom fields.
* Added wpmem_wp_reserved_terms filter for overriding reserved terms list.
* Added trim whitespace to password reset and password change form validation.

= 3.0.1 =

* Fixed use of wp_signon() for ssl.
* Fixed [wpmem_msurl] email shortcode.
* Fixed admin js and css load (removed unnecessary slash).
* Fixed autoexcerpt to use setting from object and not wpmemembers_autoex option.
* Added filter to remove comments array if content is blocked.

= 3.0.0 =

This release makes significant changes to the plugin's main options in the way they are stored. While care has been taken to make sure that you can roll back to a previous version, you may need to resave settings in the plugin's main options tab when attempting to roll back. It is advised that you test this update prior upgrading an existing install on a production site.

If you have any custom functions hooked to filters and actions that call any file includes directly from the plugin, please note that several file names have changed.

* New settings and new plugin class WP_Members.
* New settings now natively support Custom Post Types, both setting defaults and individual post blocking.
* Settings constants removed in favor of using the $wpmem object class.
* Added new|edit toggle to wpmem-register_data filter hook.
* wpmem_settings_loaded action added.
* Securify comments changed to use comments_open instead of comments_template (with a blank template).
* New wpmem_securify_comments filter hook to customize whether comments load or not.
* Registration clear form button defaults to false.
* Removed wp_set_auth_cookie from login function; it is already done as part of wp_signon.
* Post meta box title filter is now for all post types wpmem_admin_{post type}_meta_title.
* New filter for Really Simple Captcha folder location: wpmem_rs_captcha_folder.
* New shortcodes [wpmem_form] added.
* Shortcode dropdown selector added to tinymce toolbar in Post/Page editor.
* Added redirect_to as a function argument if calling wpmem_logout() directly.

= 2.9.9 =
* Code standards in wp-members-email.php
* Rebuilt admin notification email to follow new regular email structure with more filters in wp-members-email.com
* Added $toggle to headers filter that is used in both emails so that headers could be filtered based on the email being sent (i.e. sending plain text for admin notification vs html for others. in wp-members-email.php
* Added redirect_to parameter as a widget entry in wp-members-sidebar.php
* Corrected flaws in error checking for adding new fields in /admin/tab-fields.php
* Added functions for updating user_status in wp_users table in /admin/users.php
* Fixed get_user_meta 'true' error in wp-members-core.php, users.php, /admin/users.php, /admin/user-profile.php
* Added dummy comments template to protect comments without post password.
* Added new action for deactivting a user (opposite of wpmem_user_activated).
* Added check to remove password, confirm_password, and confirm_email from export (data for these was already skipped, but the field space was there).
* Added wpmem_status_msg_args and wpmem_login_links_args filters.
* Corrected Really Simple Captcha, added field wrapper and should not display on user profile edit.

= 2.9.8 =
* Fixed bug in settings update that caused the stored version number to be erased.
* Fixed bug with new email function that causes the wpmem_email_newreg not to apply any filtered changes to the email body.
* Major updates to wpmem_block logic, changing to universal _wpmem_block from two separate metas (block & unblock).
* Fixed bug in the page bulk action that caused the result to end up on the posts page.
* Added wpmem_use_ssl utility function.
* Added use of wpmem_use_ssl function to reCAPTCHA to load correctly (previously handled ssl directly).
* Added use of wpmem_use_ssl function for default input text for custom register page, user profile page, and custom stylesheet settings inputs.
* Added new redirect_to parameter to login page shortcode.
* Fixed checkbox for checked by default on the add new user screen.
* Fixed "admin only" fields to display on the add new user screen.
* Added underscores parameter to the fields shortcode to strip underscores. Defaults to off.
* Updated excerpt logic to not show excerpts on multipage posts if not the first page.
* Added new 2015 stylesheets (currently available, but subject to changes/updates)

= 2.9.7 =
* Fixed comparison for the checkbox CSS class in wpmem_create_formfield.
* Corrected wp native registration function for use on localized sites.
* Rebuilt export function, merges the two previous functions into one (export selected and export all) and will allow for calling custom exports.
* Rebuilt user email function.
* Added default "from" headers to email.
* Added new filter wpmem_export_args.
* Added new filter wpmem_email_filter.
* Added a redirect_to parameter to the registration form similar to the login.
* Fixed plugin admin page load for multisite, if user has theme options edit capabilities.

= 2.9.6 =
* Updated admin.js to show/hide custom url fields for User Profile page, Register page, and Stylesheet settings.
* Updated options panel to show/hide custom url fields mentioned above.
* Updated admin.js to show/hide checkbox and dropdown extra settings when adding a custom field in Fields.
* Updated fields panel to show/hide extra fields mentioned above.
* Updated reCAPTCHA to automatically change language if the language is (1) set as WPLANG and (2) used by reCAPTCHA.
* Added error checking if Really Simple CAPTCHA is enabled but not installed.
* Updated registration function for improved operation when used with popular cache plugins.

= 2.9.5 =

* Added support for Really Simple CAPTCHA (Really Simple CAPTCHA must also be installed).
* Added support for custom field meta keys as shortcodes in emails.
* Added support for default permalinks when using wpmem_logout shortcode.
* Improved admin notification email to skip metas defined as excluded meta.
* Fixed activation function for activations from user profile omitting passwords (see 2.9.4 bug fix for moderated password registration).

= 2.9.4 =

* Bug fix for moderated password registration (changes in 2.9.3 introduced a bug that caused moderated registration to send a blank password).
* Bug fix for premium PayPal Subscription extension showing expired users, improved user query.
* Fixed user profile update so that wp_update_user is called only once.
* Added [wpmem_logged_out] shortcode to show content to logged out users. Same as [wp-members status="out"] shortcode but can be used on blocked content without changing security status.
* Removed checkbox for adding password and confirmation fields to the User Screen since these are not stored data.

= 2.9.3 =

* Fixed a backend user profile update and create new user issue introduced with some 2.9.2 code improvements. The issue caused the additional fields not to save.
* Added a confirm password and confirm email field to the default install, as well as automatic form validation when used.
* Updated all functions that store/read user data to skip these fields as there is not need to store them, they are simply form validation fields.
* Improved error checking in the admin Fields tab when attempting to add an option name that already exists.
* Added separate registration validation for multisite (refers to WP-Members front end registration only). Multisite has different username requirements and the existing error message was valid, the wording did not fit well for multisite making it confusing. The multisite validation returns the WP error message rather than a custom error message. I may be updating other validation messages in the plugin to utilize this method (while allowing them to be filtered for customization).
* Added a separate install for multisite.
* Updated the template for all localization files (some strings still need translation).

= 2.9.2 =

* Added user ID parameter to wpmem_login_redirect filter.
* Added new action hooks
* Added logout shortcode
* Added wpmem_msg_dialog_arr filter
* Improvements to registration function
* Admin panel updates for smaller screens
* Added bulk block/unblock for posts and pages

= 2.9.1 =

This is primarily a cleanup and fix update with a few new features.

* Added WP-Members registration fields to WordPress Users > Add New screen.
* Fixed wpmem_test_shortcode error for TOS.
* Plugin options tab - lists notify address for notify admin setting
* Updated default password change success message - removed need to re-login string.
* Make dropdown values in wpmem_create_formfield function translatable strings for localization
* Changed "logout" to "log out"
* Update to register function to check for unset values for WP native fields.
* Moved the path constants to be defined earlier.
* Added $action parameter to most of the login form filters, allows more direct filtering based on form state (login, password reset, password change).

= 2.9.0 =

This is a major update focusing on upgrades to the form building functions, but also includes a number of other changes and improvements.

Major updates

* New form building functions include new hooks and a more customizable form building process.
* Form functions moved from wp-members-dialogs.php to new file forms.php
* Sidebar login form also rebuilt in the same way the login and register forms were changed.
* Legacy (old table based) forms completely removed in 2.9
* Updates to error and dialog messages, removed unnecessary html tags

Changes in wp-members-core.php

* updated calling of wpmem_test_shortcode, now it works like has_shortcode, put off deprecating at this time.
* updated shortcode to include tos page, allow for new tags (wpmem_field, wpmem_logged_in) (added new shortcode calls in wp-members.php), and accept id attribute for fields. Added $tag argument, can use shortcode_atts_{$shortcode} filter
* moved wpmem_test_shortcode to utilities.php
* added new action hooks: wpmem_pwd_change and wpmem_pwd_reset
* added new filter hook: wpmem_regchk

Changes in wp-members.php

* a pretty major overhaul of this file. Moved all but four declarations that weren't already in functions into the init function. Only two constants are declared before the function. This initialization comes after the theme is setup, so pre-initilization needs, such as loading pluggable functions can be declared in the theme's functions.php file. Pluggable functions do not need to be loaded only from the wp-members-pluggable.php file.
* The file name of the wp-members-pluggable.php file is loaded in a filter hook, wpmem_plugins_file, so you could call it something else or load it from another location.
* New action hooks: wpmem_pre_init, wpmem_after_init, wpmem_pre_admin_init, wpmem_after_admin_init
* New filter hook: wpmem_settings

Miscellaneous Changes

* Updates to the html for some of the admin to better fit the new WP admin layout. Old html was compatible, but the new works better.
* Updates to the options tab to better group options
* Updates to native (wp-login.php) registration to include require field indication
* Review of output, localized a few missed strings
* Implementation of changes in localization of field names. English values are now stored in the db (except for custom fields that would be whatever language the user creates the field as). Fields are then translated when displayed, rather than stored as translated strings.
* Updated user profile to fix some issues with checkbox and required fields in users.php
* Updated user export to include wp_users table fields user_url, user_nicename, and display_name fields
* Code cleanup in wpmem_block function
* Updated autoexcerpt function
* New filter hooks for post editor meta box titles: wpmem_admin_post_meta_title, wpmem_admin_page_meta_title
* Some updates to existing stylesheets
* Added new stylesheets, including two with non-floated elements. Generic, non-floated stylesheet new default for fresh installs