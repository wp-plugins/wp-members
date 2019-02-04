=== WP-Members Membership Plugin ===
Contributors: cbutlerjr
Tags: access, authentication, content, login, member, membership, password, protect, register, registration, restriction, subscriber
Requires at least: 4.0
Tested up to: 5.0
Stable tag: 3.2.5.1
License: GPLv2

== Description ==

The WP-Members membership plugin turns your WordPress site into a membership site. Restrict premium content, create custom registration fields, and more.

=== Membership Sites. Simplified. ===

You need a membership site, but you want to focus on your business, not mastering a plugin. WP-Members is simple to use, easy to set up, yet flexible in every way imaginable.

The plugin restricts selected WordPress content to registered site members. WP-Members puts the registration process on the site front end so it is part of your content instead of the native WP login page. WP-Members requires no modifications to your theme while remaining scalable for users who want to customize the look and feel, or want to restrict only some content. It is a great tool for sites offering premium content to subscribers and is adaptable to a variety of applications.

__Simple to install and configure - yet customizable and scalable!__

= Features: =

* Restrict or hide posts, pages, and custom post types
* User login, registration, and profile integrated into your theme
* Login widget
* Create custom registration and profile fields
* Notify admin of new user registrations
* Hold new registrations for admin approval
* Create post excerpt teaser content automatically
* [Shortcodes for login, registration, content restriction, and more](https://rkt.bz/docssc)
* Create powerful customizations with [more than 120 action and filter hooks](https://rkt.bz/hooks)
* [A library of API functions for extensibility](https://rkt.bz/api)

WP-Members allows you to restrict content as restricted or hidden, limiting access to registered users.

A full Users Guide is [available here](https://rkt.bz/docs). The guide outlines the installation process, and also documents how to use all of the settings.

Get support along with all of the plugin's premium extensions in one [cost saving Pro Bundle!](https://rkt.bz/pro)

= Premium Support =

Premium support subscribers have access to priority email support, examples, tutorials, and code snippets that will help you extend and customize the base plugin using the plugin's framework. [Visit the site for more info](https://rkt.bz/getsupport).

= Premium Extensions =

The plugin has several premium extensions for additional functionality. You can purchase any of them individually, or get them all for a significant discount in the Pro Bundle.

* [Advanced Options](https://rkt.bz/advanced) - adds additional settings to WP-Members for redirecting core WP created URLs, redirecting restricted content, hiding the WP toolbar, and more! Also includes integrations with popular plugins like WooCommerce, BuddyPress, bbPress, ADF, Easy Digital Downloads, and The Events Calendar.
* [Download Protect](https://rkt.bz/downloadprotect) - Allows you to restrict access to specific files, requiring the user to be logged in to access.
* [Invite Codes](https://rkt.bz/invitecodes) - set up invitation codes to restrict registration to only those with a valide invite code.
* [MailChimp Integration](https://rkt.bz/mailchimp) - add MailChimp list subscription to your registation form.
* [Memberships for WooCommerce](https://rkt.bz/fR) - Sell memberships through WooCommerce.
* [PayPal Subscriptions](https://rkt.bz/paypal) - Sell restricted content access through PayPal.
* [Security](https://rkt.bz/security) - adds a number of security features to the plugin such as preventing concurrent logins, registration form honey pot (spam blocker), require passwords be changed on first use, require passwords to be changed after defined period of time, require strong passwords, block registration by IP and email, restrict specified usernames from being registered.
* [Text Editor](https://rkt.bz/te) - Adds an editor to the WP-Members admin panel to easily customize all user facing strings in the plugin.
* [User List](https://rkt.bz/userlist) - Display lists of users on your site. Great for creating user directories with detailed and customizable profiles.
* [User Tracking](https://rkt.bz/usertrack) - Track what pages logged in users are visting and when.
* [WordPass Pro](https://rkt.bz/3O) - Change your random password generator from gibberish to word-based passwords (can be used with or without WP-Members).

Get support along with all of the plugin's premium extensions in one [cost saving Pro Bundle!](https://rkt.bz/pro)


== Installation ==

WP-Members is designed to run "out-of-the-box" with no modifications to your WP installation necessary. Please follow the installation instructions below. __Most of the support issues that arise are a result of improper installation or simply not reading/following directions__.

= Basic Install: =

The best way to begin is to review the [Initial Setup Video](https://rkt.bz/videos). There is also a complete [Users Guide available](https://rkt.bz/docs) that covers all of the plugin's features in depth.

1. Upload the `/wp-members/` directory and its contents to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress&reg;

You are ready to begin using WP-Members. Now follow the instructions titled "Locking down your site" below.

= Locking down your site: =

* To restrict posts, you will need to use the `<!--more-->` link in your posts. Content above to the "more" split will display on summary pages (home, archive, category) but the user will be required to login to view the entire post. You may also use the plugin's auto excerpt setting to create post excerpts automatically. If you do not use the "more" tag or the auto excerpt setting, full post content is going to show on archive templates, unless the post is marked as hidden.
* To begin restricting pages, change the plugin default setting for pages to be blocked. Unlike posts, the `<!--more-->` link is not necessary in the blocking of pages, but __must__ be used if you have the "show excerpts" setting turned on for pages.
* To protect comments, we recommend setting "Users must be registered and logged in to comment" under Settings > Discussion.
* On the Settings > General page, it is recommended that you uncheck "Anyone can register". While not required, this will prevent WP's native registration from colliding with WP-Members, especially if you are using any of the WP-Members additional registration fields.
* Under Settings > Reading, "For each article in a feed, show" is recommended to be set to "Summary."  WordPress installs with full feed settings by default. If you don't change this, your feeds will show full content.

= Additional Setup Information =

There are also some special pages that can be created with simple shortcodes:

* A User Profile page where registered members can edit their information and change/reset their password: [wpmem_profile]
* A Registration page available for those that need a specific URL for registrations (such as email marketing or banner ad landing pages). Note: this is strictly optional as a registration form can also be included by default on blocked content pages: [wpmem_form register]
* A Login page. This is also an optional page as the login form is included by default on blocked content. But if you need a specific login page, this can be created with a simple shortcode: [wpmem_form login]
* [And more shortcodes are available](https://rkt.bz/docssc)!

Powerful cusotmizations can be constructed with [over 120 action and filter hooks](https://rkt.bz/hooks), as well as user accessible functions.


== Frequently Asked Questions ==

The FAQs are maintained at https://rocketgeek.com/plugins/wp-members/docs/faqs/


== Upgrade Notice ==

WP-Members 3.2.0 is a major update. See changelog for important details. Minimum WP version is 4.0.
WP-Members 3.2.5 is primarily a feature update, with some fixes. See changelog for details.

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

= 3.2.5.1 =

* Fixes bug in 3.2.5 with the [wpmem_field] shortcode not displaying correctly.
* Reintroduces WPMEM_DEBUG constant (which is used in outside extensions).

= 3.2.5 =

* Fix user profile (admin/user) issue with tos field not displaying.
* Fix [wpmem_logged_in] shortcode to pass product attribute.
* Fix [wpmem_field] shortcode, checks if field is set to avoid undefined index error.
* Fix do_excerpt() if post object is not set as an object.
* Fix logic for displaying hidden posts based on product access.
* Added message dialog to display in Customizer.
* Added HTML5 "required" attribute to TOS checkbox.
* Added redirect_to attribute to logout link.
* Added $tag parameter to wpmem_{$tag}_link and wpmem_{$tag}_link_str filters.
* Added id parameter to wpmem_register_form_args filter.
* Added wpmem_email_send_args filter.
* Added wpmem_is_user() function to API.
* Added wpmem_do_shortcode() utility function in API.
* Added wpmem_export_fields filter to user export function.
* Added label attribute to field shortcode.
* Added user profile tabs (jquery ui tabs).
* Updated wpmem_form_date() API function.
* Updated check product access to handle product as an array.
* Updated to make a nonce a default for the registration form (reduces possibility of spam registrations).
* Updated form field creation, $value is now optional.
* Moved textdomain to load in main class file.
* Removed possibility of using reCAPTCHA v1 which is totally obsolete.
* Removed widget status message ("you are not logged in") for logged out state.

= 3.2.4 =

* Added deactivate users both bulk and single in Users > All Users.
* Added id attribute for form labels.
* Added wpmem_format_date() API function.
* Added label tags to multipe checkbox and radio group items.
* Added assigned product(s) column to users and posts screens.
* Updated membership product object structure.
* Updated load priority to run later for jquery loginout script.
* Removed query_vars filter.
* Deprecated wpmem_a_activate_user(), use wpmem_activate_user() instead.
* Deprecated wpmem_a_deactivate_user(), use wpmem_deactivate_user() instead.
* Relocated install file to /inc/ directory.
* Moved methods out of core.php, deprecated file.
* Fixed issue with default stylesheet setting caused by moving install file.

= 3.2.3 =

* Bug fix in user export that caused usernames to be dropped.
* Bug fix to allow admins to edit their own profile.
* Bug fix for jquery with regards to select2, only load if products are enabled.
* Added email API.
* Added product attribute to [wpmem_logged_in] shortcode.
* Added wpmem_force_ssl() API function.
* Added wpmem_set_as_logged_in() API function.
* Added filters to remove posts marked hidden from previous/next links.
* Updated user login function to use WP script, facilitates login with username OR email, removes wpmem_login_fields filter, changes to wp_safe_redirect().
* Updated password change for maintaining login state.
* Moved wpmem_fields(), wpmem_form_label(), and wpmem_form_field() to api-forms.php.
* Moved wpmem_user_has_role(), wpmem_user_has_meta(), wpmem_is_user_activated(), wpmem_user_data(), wpmem_update_user_role(), and wpmem_user_has_access() to api-users.php.
* Moved wpmem_do_excerpt(), wpmem_texturize(), wpmem_get_excluded_meta(), wpmem_use_ssl(), wpmem_write_log(), wpmem_load_dropins(), wpmem_array_insert(), and wpmem_get_sub_str() to api-utilities.php.
* Moved wpmem_wp_reserved_terms() to admin API.
* Deprecated wpmem_check_activated() and wpmem_use_ssl().
* Removed obsolete functions wpmem_enqueue_style(), wpmem_convert_tag(), wpmem_no_reset(), and wpmem_user_profile_multipart(). 
* Applied wpmem_force_ssl() to stylesheet in case it needs to load securely (even if the setting is saved as http://).
* Implemented change in the native WP registration form processing to allow values of "0" to be interpreted as string literals. Previously could be interpreted as boolean when being saved.

= 3.2.2 =

* Fixed bug in 3.2.1/3.2.2 for user activation when user creates password at registration and is activated from the user profile.
* Fixed a 3.2 upgrade issue, verifies username field is properly added to field settings array.
* Fixed issue with user product verification where only expiration products were validated.
* Fixed logic in form field builder so multiselect will accept a custom class. 
* Added select2 support for setting product access in the post editor.
* Removed duplicate API function wpmem_current_postid() (use wpmem_current_post_id()).
* Replaced sanitize_html_class() with WP_Members_Forms::sanitize_class() so variables may contain multiple classes.

= 3.2.1 =

* Fixed duplicate ID in login form.
* Fixed user profile update for excluded fields.
* Fixed native WP registration, excluded WP-Members username field in form validation.
* Fixed update post when block status is not changed.
* Rebuilt user interface for post restriction metabox to make it more intuitive.
* Changed status column in All Posts to show all block statuses, not just those opposite the default.
* Changed "clickable" attribute for field shortcode default to false.
* Added wpmem_user_export_header and wpmem_user_export_row filter for export.

= 3.2.0 =

* Tested and compatible with Gutenberg.
* Changed default address meta fields to WooCommerce billing meta keys.
* Removed language packs that install from wordpress.org (de_DE, hu_HU, ja, nl_NL, pt_BR, ru_RU, and sv_SE).
* All remaining user facing strings in get_text() added wp-members textdomain.
* Added locale as a parameter for localization filters.
* Added wpmem_register_hidden_rows filter.
* Added "post_to" key for wpmem_register_form_args.
* Rebuild of user export function. User export now uses fputcsv.
* Updates/code improvement for enqueueing styles.
* Updated widget, added widget_title filter before wpmem_widget_title, documented wpmem_widget_id filter, added instance and id_base parameters.
* Updated empty $fields check to not rewrite fields.
* Deprecated wpmem_inc_status().
* Deprecated wpmem_do_sidebar().
* Deprecated wpmem_create_formfield(), use wpmem_form_field() instead.
* Deprecated a number of non-user callable functions.
* Eliminated capability-based load for admin files.
* Maintain user login state when password is changed.
* Added wpmem_get_sub_str() string manipulation utility.
* Updated login form redirect_to to account for query vars.
* Fixes issue with login status if logout url is encoded (sprintf() has too few arguments).
* Added Membership Products Custom Post Type.
* Added "Hide Post" option for blocking content (currently only by post meta _wpmem_block).
* Removed several outdated stylesheets from selection (still packaged with download for users who may use them).
* Added wpmem_update_user_role() API function.
* Added wpmem_display_message() API function.
* Added wpmem_user_has_access() API function.
* HTML5 update - form anchor tags changed from "name" to "id".
* HTML5 update - form id tags set default value (can still be filtered).
* HTML5 update - removed "align" attribute (captcha, link-text).
* HTML5 update - added placeholder, pattern, and title attribute support for password fields.
* Improved the add a field dialog to adjust required inputs depending on field type.
* Added placeholder, rows, and cols attribute support for textarea field settings.
* Moved remaining core functions to appropriate object classes, left wrappers for most.
* Added new email class and shortcode class to replace previous functions/files.
* Added link_span_before/link_span_after wrapper for login link text.
* Updated "TOS" text to accept a custom label (priority to filtered label with wpmem_tos_link_txt).
* Updated all processing involving "TOS" to better exclude the field on profile update (if saved value equals the field's checked value).
* Fixed a bug that caused WP native fields to be set as non-native when labels were updated in the fields tab.
* Added Customizer control to display login, register, and widget forms when using the Customizer (easier to work on custom CSS).
* Added login/out menu item.

= 3.1.9 =

* Security enhancements.
* Improved user search in Users > All Users.
* Fully deprecated obsolete reCAPTCHA v1. If v1 is selected as a setting, it will be automatically changed to v2. (v1 users check your API keys for compatibility.)
* Removed obsolete comments_template.php.
* Set image field to display "medium" image in dashboard/admin user profile. Admin profile image display links to media editor.
* Added default format to date field shortcode to date format set in WP settings.
* Added format attribute to date field shortcode for custom date formatting.
* Added User ID as an optional column for the Users > All Users screen.
* Deprecated wpmem_user_profile(), wpmem_profile_update(), wpmem_user_profile_multipart(), wpmem_admin_fields(), wpmem_admin_update().
* Rebuild of dashboard profile functions incorporated into new user profile object class.
* Fields tab bulk action now checks for both page and tab (rather than just tab) to prevent namespace collisions.
* Removed $requested_redirect_to argument from login_redirect filter hook.
* Removed height property of button_div for generic stylesheets.
* Fixed user edit shortcode to display updated user data without page refresh.
* Fixed password change so that login state is maintained after password update.
* Fix for multiple checkbox and multiple select field types if "comma" was selected as the delimiter (previously, this would break the field settings array option assembly).
* Improvements on field manager add/edit field screen.
* Improvements to multiple checkbox and radio groups: better handling of non-value selections and span wrapper for group separators.

= 3.1.8 =

* Added new native registration handling to accommodate WooCommerce 3.0.
* Added support for user file upload on admin/dashboard profile.
* Added meta_key/meta_value attribute to [wpmem_logged_in] shortcode.
* Added wpmem_user_has_meta() API function.
* Updated post editor shortcode button javascript to include new(er) shortcodes.
* Fixed WP-Members user profile shortcode to only display logged in content (by shortcode) if the wpmem_a 'action' is not set (i.e. logged in content only displays if page is in "member links" state).
* Fixed register link in login form to prevent link from displaying if login form is displayed along with the registration form (standard configuration).
* Improved multisite support, including revisions to load all admin dependencies for administrator role, not just super admin. Dependency load role requirement is filterable.
* Improved line break stripping process in form builder to allow for line breaks in textarea fields.
* Improved field shortcode for textarea fields to display line breaks.
* Deprecated old wpmem_do_sc_pages() function, updated wpmem_sc_user_profile().
* Removed $_SERVER['REQUEST_URI'] from all admin tab form action attributes. Replaced with new wpmem_admin_form_post_url() API function.
* Fields Tab: ground-up rebuild to utilize WP_List_Table and jquery sorting UI.
* Fields Tab: improved field add/edit screen: return link at bottom of page, display current field upon successful form submission, success message include return link.
* Fields Tab: added support for HTML5 field types: number, date.
* Fields Tab: added support for HTML5 field attributes: placeholder, pattern (regex), title, min, max.
* Corrects an issue with user profile display/update of textarea fields that are not the WP default bio field.
* Added display=raw attribute handling to textarea fields to allow display without converting line breaks to HTML br tags.
* Fixes issue with User Profile form heading not using the get_text() value.
* Improves logic for displaying register link in login form if login page is not set.

= 3.1.7 =

* API updates: added wpmem_is_user_activated().
* API updates: wpmem_is_reg_page() added default of current page ID.
* API updates: wpmem_current_url() added check for query string.
* API updates: Added wpmem_fields filter to wpmem_fields().
* API updates: Added wpmem_current_post_id() and wpmem_form_label() functions.
* API updates: Added new [wpmem_avatar], [wpmem_login_link], and [wpmem_register_link] shortcodes.
* API updates: Added filter option in user export to decode html entities in user fields.
* API updates: Added wpmem_get_action action hook when wpmem_a is loaded.
* All admin dependencies and actions/filters loaded in admin api object.
* Corrected issue in forms function were RS Captcha had empty div wrapper variable.
* Removed deprecated functions wpmem_load_admin_js(), wpmem_test_shortcode(), and wpmem_generatePassword().
* Moved remaining deprecated functions to new inc/deprecated.php.
* Added successful registration message on page if nonce validates (for reg redirects).
* Added User object class, handling login, logout, register.
* Added buffering to login form and widget to allow do_login action results to be displayed (such as 3rd party login captcha).
* Added support for WP's login_redirect filter (loads before wpmem_login_redirect).
* Added a div wrapper for post restricted message.
* Added initial form support for HTML5 number input, & min, max, title, and pattern attributes (placeholder support was added in 3.1.6).
* Updated wpmem_admin_update() to exit if there is no user ID.
* Updated admin notification email to translate field labels.
* Updated login form links and filters to a single process (was one for each).
* Updated WP Registration finalize process.
* Moved form building functions to forms object class.
* Deprecated wpmem_register_fields_arr filter (Use wpmem_fields instead).
* Removing the wpautop() function is now optional rather than default.
* Fixed load fields error checking, install function now correctly returns defaults.
* Changed password reset and password change to use wp_set_password() for improved performance with caching.