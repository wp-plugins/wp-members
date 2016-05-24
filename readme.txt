=== WP-Members: Membership Framework ===
Contributors: cbutlerjr
Tags: access, authentication, content, login, member, membership, password, protect, register, registration, restriction, subscriber
Requires at least: 3.1
Tested up to: 4.5.2
Stable tag: 3.1.1
License: GPLv2

WP-Members&trade; is a free membership management framework for WordPress&reg; that restricts content to registered users.

== Description ==

WP-Members&trade; is the original membership plugin for WordPress&reg;.  Perfect for newsletters, premium content sites, clubs/associations, and more!  

The plugin restricts selected WordPress&reg; content to registered site members.  WP-Members&trade; puts the registration process on the site front end so it is inline with your content rather than the native WP login page.  WP-Members&trade; requires __no__ modifications to your theme while remainging scalable for users who want to customize the look and feel, or want to restrict only some content.  It is a great tool for sites offering premium content to subscribers, and is adaptable to a variety of applications.

Simple to install and configure - yet customizable and scalable!

[youtube http://www.youtube.com/watch?v=x4MEoRLSY_U]

= Features: =

* Block posts, pages, both, or none by default
* Block individual posts/pages
* User login, registration, and profile integrated into your theme
* Sidebar login widget
* Create custom registration and profile fields
* Set which fields display and which are required
* Notify admin of new user registrations
* Hold new registrations for admin approval
* Setting to automatically create post excerpts
* More than 100 action and filter hooks for extensibility

By default, WordPress&reg; allows all content to be "open" and viewable by anyone and allows the site owner to restrict specific content if desired by setting a password for the post.  WP-Members&trade; operates with the reverse assumption.  It restricts all posts by default but allows the site owner to "unblock" content as desired.  WP-Members&trade; also offers the ability to change these default plugin settings.  For those that simply want to utilize the member management features and possibly restrict some content, the default setting can easily be toggled to block or unblock pages and/or posts by default.  No matter what the default setting, individual posts or pages can be set to be blocked or unblocked at the article level, overriding the default setting.

The plugin installs with additional registration fields including name, address, phone, and email. Using the WP-Members&trade; admin panel, you can also create your own custom registration fields and delete existing ones.  Changing the field order is simple with a drag-and-drop interface.  All of the registration process is inline with your theme and content rather than using the WordPress&reg; login page.  This offers you a premium content site with a professional and branded look and feel.  It also provides an opportunity for the user to register without leaving the page to do so - less clicks = more conversions.

There are also some special pages that can be created with simple shortcodes:

* A User Profile page where registered members can edit their information and change/reset their password: [wpmem_profile]
* A Registration page available for those that need a specific URL for registrations (such as email marketing or banner ad landing pages).  Note: this is strictly optional as a registration form can also be included by default on blocked content pages: [wpmem_form register]
* A Login page.  This is also an optional page as the login form is included by default on blocked content.  But if you need a specific login page, this can be created with a simple shortcode: [wpmem_form login]
* [And more shortcodes are available](http://rocketgeek.com/plugins/wp-members/users-guide/shortcodes/)!

The plugin runs on a framework with over 100 action and filter hooks so you can fully customize your implementation.

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

WP-Members 3.1.1 is primarily a feature update release (see changelog). This update does make a db settings change.
WP-Members 3.1.0 is a major verion release, please review the changelog.

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

= 3.1.1 =

* Added downgrade function (currently runs on deactivation) to allow for version rollbacks.
* Added new dialogs functions to admin api, allows for custom dialogs to be added.
* Added $user_id and $row to wpmem_admin_profile_field and wpmem_user_profile_field filters.
* Added new api class and api functions.
* Added [wpmem_loginout] shortcode.
* Added support for new field types to native registration and users > add new (except file/image fields).
* Updated users > add new so that user can be activated when added.
* Updated [wpmem_logged_in] shortcode so that content is not shown on on a user profile page action.
* Updated email settings to only save new from/name if changed.
* Updated admin warning messges.
* Updated multiselect and multicheckbox fields to allow user selected delimiter (defaults to pipe "|").
* Fixes issue with profile update when file/image field is required.
* Fixes image field edit in fields tab to include file type.
* Fixes attribution setting for 3.0 settings array.
* Fixes for PHP7.

= 3.1.0 =

This package contains several fixes, some new filters, new field types and other functional improvements.

* Some general code cleanup, reviewing inline documentation and comments.
* Fixed issue for sidebar with redirect_to parameter set in widget settings.
* Fixed issue for custom error messages and email comparison error for profile update (so that errors show in form update state and not on links page).
* Fixed main options tab where checkbox may not display correct setting if unchecked.
* Fixed translation issue for required field error where all of the message except the field name was translated.
* Fixed issue for register shortcode page where if a user has registered, and is logging in but the login fails, display the login error message.
* Fixed register shortcode redirect parameter.
* Fixed confirm_password to bypass sanitize_text_field (which breaks password comparison if certain characters are used).
* Added logic so that user_pages are not blocked (login, register, user_profile).
* Added after_wrapper array value for wpmem_{$page}_links_args filters
* Added a new admin api class, utilities object class, and forms object class.
* Added user facing strings as an array in the main $wpmem object class.
* Added wpmem_default_text_strings filter for user facing text strings.
* Added new wpmem_sidebar_status_args filter hook.
* Added new container in main object for email from and from name settings.
* Added file upload functions.
* Added new field types: multiple checkbox, multiple select, radio, file, image, email, url.
* Added "values" key to the register form rows array to hold possible values (i.e. select, multiple select, multiple checkbox, and radio group) and the actual value to be in the "value" key.
* Added the ability for dropdown/select fields to have a default value other than the first value.
* Added filter wpmem_user_upload_dir for filtering the location of user uploaded files.
* Added wpmem_register_form_rows_admin and wpmem_register_form_rows_profile filter hooks.
* Deprecated use of wpmem_chk_qstr() function, use add_query_arg() instead.
* Deprecated use of get_currentuserinfo() (deprecated in WP 4.5), use wp_get_current_user() instead.
* Email function updates, added 'footer' as an array value in the main wpmem_email_filter filter.
* Changed install to set email/confirm_email and user_url as HTML5 field types "email" and "url" (now supported).
* Changed get_action call from init action to template_redirect action.
* Changed username in register form from log to user_login to match wp native registration form.
* Changed [wp-members page="user-profile"] shortcode to [wpmem_profile] (old shortcode will still work).
* Removed redirect parameter from register shortcode in shortcode menu.
* Removed kubrick stylesheet from selector (still packaged with download, shows as custom url if used).
* Changed all _update_ functions in install package to _upgrade_.
* Fixes an issue with PayPal extension where users may be set to pending if moderated registration is enabled after the user already has an expiration date.
* Update wpmem_do_sidebar to use use add_query_arg() if on a search query.

= 3.0.9 =

* Added Custom Post Type support.
* Added wpmem_member_links_args and wpmem_register_links_args filters.
* Added $link parameter to wpmem_forgot_link_str and wpmem_reg_link_str filters (gives just the link as an available parameter).
* Added new wpmem_sb_reg_link_str and wpmem_sb_forgot_link_str filters (same format as above).
* Added [email] and [user-profile] shortcodes to the new user registration email.
* Added label_text key to wpmem_register_form_rows filter.
* Added new auto excerpt settings, can now be set by post type.
* Added new auto excerpt features including new wpmem_auto_excerpt_args filter.
* Added forgot username retrieveal link (included on forgot password reset form).
* Added wpmem_username_link and wpmem_username_link_str for filtering forgot username retrieval link.
* Added new upgrade process to WP_Members object class.
* Fixed handling of post bulk actions to keep current screen (if one is used).
* Fixed handling of updates to the user pages in the options tab.
* Fixed handling of empty post object in is_blocked() function.
* Improved email functions to eliminate get_user_meta() calls when not needed.

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