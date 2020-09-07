=== WP-Members Membership Plugin ===
Contributors: cbutlerjr
Tags: access, authentication, content, login, member, membership, password, protect, register, registration, restriction, subscriber
Requires at least: 4.0
Tested up to: 5.5
Stable tag: 3.3.6
License: GPLv2

== Description ==

The WP-Members membership plugin turns your WordPress site into a membership site. Restrict premium content, create custom registration fields, and more.

=== Membership Sites. Simplified. ===

You need a membership site, but you want to focus on your business, not mastering a plugin. WP-Members is simple to use, easy to set up, yet flexible in every way imaginable.

__Simple to install and configure - yet customizable and scalable!__

= Features: =

* Restrict or hide posts, pages, and custom post types
* Limit menu items to logged in users
* User login, registration, and profile integrated into your theme
* Create custom registration and profile fields
* Notify admin of new user registrations
* Hold new registrations for admin approval
* Create post excerpt teaser content automatically
* [Shortcodes for login, registration, content restriction, and more](https://rocketgeek.com/plugins/wp-members/docs/shortcodes/)
* Create powerful customizations with [more than 120 action and filter hooks](https://rocketgeek.com/plugins/wp-members/docs/filter-hooks/)
* [A library of API functions for extensibility](https://rocketgeek.com/plugins/wp-members/docs/api-functions/)

WP-Members allows you to restrict content as restricted or hidden, limiting access to registered users.

A full Users Guide is [available here](https://rocketgeek.com/plugins/wp-members/docs/). The guide outlines the installation process, and also documents how to use all of the settings.

= Support =

There is [freely available documentation on the plugin's support site](https://rocketgeek.com/plugins/wp-members/docs/). Your question may be answered there. If you need assistance configuring the plugin or have questions on how to implement or customize features, [premium support is available](https://rocketgeek.com/product/wp-members-plugin-support/).

You can get priority support along with all of the plugin's premium extensions in one [cost saving Pro Bundle!](https://rocketgeek.com/product/wp-members-pro-bundle/)

= Premium Support =

Premium support subscribers have access to priority email support, examples, tutorials, and code snippets that will help you extend and customize the base plugin using the plugin's framework. [Visit the site for more info](https://rocketgeek.com/plugins/wp-members/support-options/).

= Free Extensions =

* [Stop Spam Registrations](https://rocketgeek.com/product/stop-spam-registrations/) - Uses stopforumspam.com's API to block spam registrations.
* [Send Test Emails](https://rocketgeek.com/product/send-test-emails/) - A utility to send test versions of the plugin's emails.

= Premium Extensions =

The plugin has several premium extensions for additional functionality. You can purchase any of them individually, or get them all for a significant discount in the Pro Bundle.

* [Advanced Options](https://rocketgeek.com/plugins/wp-members-advanced-options/) - adds additional settings to WP-Members for redirecting core WP created URLs, redirecting restricted content, hiding the WP toolbar, and more! Also includes integrations with popular plugins like WooCommerce, BuddyPress, bbPress, ADF, Easy Digital Downloads, and The Events Calendar.
* [Download Protect](https://rocketgeek.com/plugins/wp-members-download-protect/) - Allows you to restrict access to specific files, requiring the user to be logged in to access.
* [Invite Codes](https://rocketgeek.com/plugins/wp-members-invite-codes/) - set up invitation codes to restrict registration to only those with a valide invite code.
* [MailChimp Integration](https://rocketgeek.com/plugins/wp-members-mailchimp-integration/) - add MailChimp list subscription to your registation form.
* [Memberships for WooCommerce](https://rocketgeek.com/plugins/wp-members-memberships-for-woocommerce/) - Sell memberships through WooCommerce.
* [PayPal Subscriptions](https://rocketgeek.com/plugins/wp-members-paypal-subscriptions/) - Sell restricted content access through PayPal.
* [Security](https://rocketgeek.com/plugins/wp-members-security/) - adds a number of security features to the plugin such as preventing concurrent logins, registration form honey pot (spam blocker), require passwords be changed on first use, require passwords to be changed after defined period of time, require strong passwords, block registration by IP and email, restrict specified usernames from being registered.
* [Text Editor](https://rocketgeek.com/plugins/wp-members-text-editor/) - Adds an editor to the WP-Members admin panel to easily customize all user facing strings in the plugin.
* [User List](https://rocketgeek.com/plugins/wp-members-user-list/) - Display lists of users on your site. Great for creating user directories with detailed and customizable profiles.
* [User Tracking](https://rocketgeek.com/plugins/wp-members-user-tracking/) - Track what pages logged in users are visting and when.
* [WordPass Pro](https://rocketgeek.com/plugins/wordpass/) - Change your random password generator from gibberish to word-based passwords (can be used with or without WP-Members).

Get support along with all of the plugin's premium extensions in one [cost saving Pro Bundle!](https://rocketgeek.com/product/wp-members-pro-bundle/)


== Installation ==

WP-Members is designed to run "out-of-the-box" with no modifications to your WP installation necessary. Please follow the installation instructions below. __Most of the support issues that arise are a result of improper installation or simply not reading/following directions__.

= Basic Install: =

The best way to begin is to review the [Initial Setup Video](https://rocketgeek.com/plugins/wp-members/docs/videos/). There is also a complete [Users Guide available](https://rocketgeek.com/plugins/wp-members/docs/) that covers all of the plugin's features in depth.

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
* [And more shortcodes are available](https://rocketgeek.com/plugins/wp-members/docs/shortcodes/)!

Powerful cusotmizations can be constructed with [over 120 action and filter hooks](https://rocketgeek.com/plugins/wp-members/docs/filter-hooks/), as well as user accessible functions.


== Frequently Asked Questions ==

The FAQs are maintained at https://rocketgeek.com/plugins/wp-members/docs/faqs/


== Upgrade Notice ==

WP-Members 3.3.0 is a major update. WP-Members 3.3.6 is a bug fix release. See changelog for important details. Minimum WP version is 4.0.


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

= 3.3.6 =

* Improved admin tab for captcha settings. You can now change the captcha type from the captcha tab (previously, you could only do this from the main options tab).
* Removed "pattern" attribute from number field type. HTML5 does not support this attribute for this input type.
* Fix issues with custom fields in admin/user dashboard profile. This involved a change to how fields were loaded for both display and validation (so that now it is a singular process).
* Fix undefined has_access() (replaced with API function) when renewing a membership.
* Fix issues with WooCommerce registration integration.
* Fix issue of undefined array keys if Really Simple Captcha is selected, but the plugin is not enabled.
* Fix issue that caused users to not be properly set when moderated registration is used along with BuddyPress and users are created manually.
* Fix issue in WP CLI wpmem settings command that caused error to be displayed when viewing content settings.

= 3.3.5 =

* Added optional new user validation link and password reset link (instead of sending password). This option will become the default setting in 3.4.0.
* Added optional login error message to fully utilize the WP login error.  This option will become the default setting in 3.4.0.
* Updated the default product restricted message to display required membership(s). This eliminates the custom message string "product_restricted" and replaces with two new ones: product_restricted_single and product_restricted_multiple. (Note this only affects the default message if no custom membership message is established in the membership properties).
* Added login/logout button to login/logout link api function wpmem_loginout() and shortcode [wpmem_loginout]. It will continue to display a hyperlink by default, but accepts arguments to display as a button. Also added ID and class options for link or button.
* Added [wpmem_login_button] to directly call the button format of [wpmem_loginout].
* Captcha options now have a static function that can be used to directly call captcha and validation.
* Fixed an issue where the Really Simple Captcha "not installed" error was returned as a string but evaluated as an array.
* Fixed an issue that caused the "membership" field selector/type to display as a text input in Users > Add New instead of a dropdown/select.
* Added user api functions wpmem_get_user_id(), wpmem_get_user_obj(), wpmem_get_users_by_meta().
* Added action hooks to membership product admin screen.
* Added wpmem_post_product filter to allow for filtering required products by post ID.
* Added wpmem_is_user_activated filter hook.
* wpmem_activate_user() now accepts a "notify" argument (defaults to true, set to false to not send a notification email).
* Added wpmem_get_users_by_meta(), wpmem_get_pending_users(), wpmem_get_activated_users(), and wpmem_get_deactivated_users().
* Added manage_options capability requirement for membership products custom post type.
* Updated WooCommerce registration handling.
* Added wpmem_is_reg_type(). Can be used withing wpmem_post_register_data to determine which registration type is being triggered.
* Added WP-CLI commands (see release announcement and documentation for more information on specific commands).
* Added support for hCaptcha (https://www.hcaptcha.com/).

= 3.3.4 =

* Updated pre_get_posts to merge post__not_in with any existing values. This will allow for better integration with other plugins (such as Search Exclude).
* Updated pre_get_posts to fire later (20) in case another plugin is adding values to be excluded. This will prevent any hidden posts from being dropped by the other plugin's process.
* Added wpmem_hidden_posts and wpmem_posts__not_in filters.
* Fixed logic in upload input type (image or file) to correct undefined variable ($file_type).
* Added function_exists check for wpmem_renew() (a PayPal extension function used in the core plugin).
* Fixed function name typo for wpmem_a_extend_user() (a PayPal extension function used in the core plugin).
* Updated product access shortcode error message to use the product_restricted message and changed the class to product_restricted_msg
* Updated CAPTCHA class for more flexibility (can now be implemented into API for calling directly in the login or other forms).
* Moved user export function from Admin API to User API.
* Fixed adding WP-Members fields to WooCommerce checkout.


= 3.3.3 =

* If WooCommerce is active, any standard WC user meta fields are removed from the WP-Members additional fields in the User Profile Edit (since they already display in the WC field blocks).
* When setting hidden posts, don't include posts from post types not set as handled by WP-Members. This prevents previously hidden posts from being included if the post type is no longer included for WP-Members.
* Set a default product for publishing posts/pages.
* Updated activation/deactivation processing so that a (admin) user cannot activate or deactivate themselves. Also, if a user has "edit_users" capability, they can log in without being activated.
* Load email "from" address with main settings rather than when email is sent. This corrects issues with Advanced Options extension, and also keeps the value loaded for use outside of WP-Members email function.
* WP 5.4 adds the wp_nav_menu_item_custom_fields action, so now WP-Members only loads its custom walker if WP is 5.3 or lower.
* Image file field type now shows an immediate preview when the "choose file" button is clicked and an image selected (both profile update and new registration).
* wpmem_login_failed_args updated to pass $args (similar to other _args filters in the plugin, now parses defaults).

= 3.3.2 =

* Added back shortcode menu item previously removed in 3.3.0.
* Added new handling in wpmem_is_blocked() for validating rest api requests.
* Added new wpmem_is_rest() function to the plugin's API, determines if the request is a rest request.
* Fixed issue with dropdown, mutliple select, and radio field types that allowed white space in resulting value.
* Fixed issue with register/profile update validation if email is removed via wpmem_fields filter hook.
* Fixed issue with prev/next post links to not show hidden posts if user is logged in but does not have a membership.
* Fixed issue with hidden posts when membership products are used. Hidden posts not assigned a membership remained hidden.
* Fixed issue with menus where logged in/logged out settings were not applied unless membership products were enabled.
* Moved wpmem_post_register_data action to fire hooked to user_register at priority 20. Changed email actions to fire at priority 25. See release announcement for more detail of implications.
* Code improvement to reCAPTCHA.
* Code improvement to excerpt generation.
* Code improvement to expiration date generation.
* Code improvement to hidden posts when using membership products.
* Code improvement changed user_register hook priority for post_register_data() to "9" to allow for custom meta fields to be available to any user_register function using the default priority (10) or later.

= 3.3.1 =

* Update membership product expiration to allow for a "no gap" expiration (i.e. renewal begins from expiration date, optional) or default (renewal begins from today or expiration date, whichever is later).
* Update user activation to use wp_set_password().
* Update display of membership product slugs to text (instead of a form input). They can't be edited.
* Added empty /inc/dialogs.php for customizations and plugins that try to include the legacy dialogs file.
* Updates to user profile screen which allows users with 'edit_users' capability (admins) to edit their own profile.
* Fixes a bug that caused the user profile display for a new user to say "reactivate" instead of "activate".
* Fixes a bug in the membership renewal that sets the individual date meta forward two periods instead of one.
* Fixes a bug in the hidden fields lookup that caused hidden posts with a membership limitation to not display to users who had a matching membership.
* Changed custom menu options to use wp_nav_menu_item_custom_fields action hook. This is a "made up" hook that is not actually part of WP. But somewhere along the line, some menu-focused plugins began using it in their custom walkers. By not using it in WP-Members, that caused some problems for users who also used one of those other plugins or themes. This updates shifts to use this "non-standard" action hook these other themes and plugins are using in order to apply some level of compatibility. 

Including all 3.3.0.x patches:
* Provides a workaround for a bug in the dialogs setting when custom dialogs are applied. This targets an issue specifically affecting users with the WP-Members Security extension installed. (3.3.0.4)
* Fixes a bug when products are enabled but no default is set. Allows for no default membership assigned at registration. (3.3.0.4)
* Fixes a bug that caused the post restricted message to display on the short form when it was in password reset and forgot username state. (3.3.0.4)
* Added wpmem_get_hidden_posts() API function to return an array of post IDs marked as hidden. (3.3.0.4)
* Fixes a shortcode issue for the [wpmem_logged_in] shortcode when using the meta_key attribute. 3.3.0 added a "compare" attribute, and with this addition, it broke the original default use of the shortcode. (3.3.0.3)
* Fixes an issue with registration/profile form fields when used in the profile. It was intended to introduce separate selection of fields for registration and profile update in 3.3.0. However, there is an issue that causes profile fields to both (1) not correctly display and (2) if they do, they do not update correctly. (3.3.0.2)
* Fixes issue when updating where the stylesheet selector indicates "use_custom" but the actual URL is to a default stylesheet. The problem can be corrected manually, but this fix applies the custom URL to the new standard setting for the defaults. (3.3.0.1)
* Fixes bug where any stylesheet other than the default reverts to the default ("no float"). This was due to the database version being wiped when settings were updated. This fix correctly applies the database version when updating settings. (3.3.0.1)
* Fixes bug when captcha is used (unknown validate() function). The validate() function should have been declared and used as a static method. This fix declares validate() as static and then uses it as such. (3.3.0.1)
* Fixes undefined string variable when successful registration is executed. (3.3.0.1)

= 3.3.0 =

* REMOVED [wp-members] shortcode tag. THIS TAG IS OBSOLETE WILL NO LONGER FUNCTION. See: https://rocketgeek.com/shortcodes/list-of-replacement-shortcodes/
* REMOVED tinymce button for shortcodes as no longer necessary with gutenberg.
* Deprecated wpmem_inc_login_args filter, use wpmem_login_form_defaults instead.
* Deprecated wpmem_inc_{$form}_inputs and wpmem_inc_{$form}_args filters, use wpmem_{$form}_form_defaults instead. (changepassword|resetpassword|forgotusername)
* Deprecated wpmem_sb_login_args filter, use wpmem_login_widget_args instead.
* Deprecated wpmem_msg_args and wpmem_msg_dialog_arr filters, use wpmem_msg_defaults instead.
* The following functions are deprecated, replacements should no longer be considered "pluggable":
  - wpmem_inc_registration() Use wpmem_register_form() instead ($heading argument obsolete).
  - wpmem_inc_changepassword()
  - wpmem_inc_resetpassword()
  - wpmem_inc_forgotusername()
  - wpmem_inc_recaptcha()
  - wpmem_build_rs_captcha()
* The following functions and filters are obsolete and have been removed:
  - wpmem_shortcode() (deprecated 3.1.2)
  - wpmem_do_sc_pages() (deprecated 3.1.8)
  - wpmem_admin_fields() (deprecated 3.1.9)
  - wpmem_admin_update() (deprecated 3.1.9)
  - wpmem_user_profile() (deprecated 3.1.9)
  - wpmem_profile_update() (deprecated 3.1.9)
  - wpmem_dashboard_enqueue_scripts() (deprecated 3.2.0 Use $wpmem->admin->dashboard_enqueue_script() instead.)
  - wpmem_sc_forms() (deprecated 3.2.0 Use $wpmem->shortcodes->forms() instead.)
  - wpmem_sc_logged_in() (deprecated 3.2.0 Use $wpmem->shortcodes->logged_in() instead.)
  - wpmem_sc_logged_out() (deprecated 3.2.0 Use $wpmem->shortcodes->logged_out() instead.)
  - wpmem_sc_user_profile (deprecated 3.2.0 Use $wpmem->shortcodes->profile() instead.)
  - wpmem_sc_user_count() (3.2.0 Use $wpmem->shortcodes->user_count() instead.)
  - wpmem_sc_loginout 3.2.0() (deprecated Use $wpmem->shortcodes->loginout() instead.)
  - wpmem_sc_fields() (deprecated 3.2.0 Use $wpmem->shortcodes->fields() instead.)
  - wpmem_sc_logout() (deprecated 3.2.0 Use $wpmem->shortcodes->logout() instead.)
  - wpmem_sc_tos() (deprecated 3.2.0 Use $wpmem->shortcodes->tos() instead.)
  - wpmem_sc_avatar() (deprecated 3.2.0 Use $wpmem->shortcodes->avatar() instead.)
  - wpmem_sc_link() (deprecated 3.2.0 Use $wpmem->shortcodes->login_link() instead.)
  - wpmem_register_fields_arr (obsolete 3.1.7, use wpmem_fields instead.)
  
IMPORTANT UPDATES/CHANGES

* Major filesystem changes. The directory structure has changed and several files
  moved/renamed/made obsolete. If you have ANY WP-Members customization that directly
  includes a file, that step is probably obsolete. The plugin has loaded most of the
  include files automatically since at least version 3.2, so this step has not been
  necessary for quite some time. However, this set of changes is more significant.
  (If you do not have code snippets using file includes from WP-Members, this most 
  likely will not affect you.)

* Updated registration function to hook to user_register, IMPORTANT: this 
  changes the order in which the user meta fields are saved, and also changes 
  when the email is sent. Email is now hooked to user_register, but can be 
  unloaded if necessary.
  
* Major overhaul of registration and login form, validation, and processing
  functions. Moved things into appropriate object classes (user, forms) and
  deprecated legacy functions and files (register.php, forms.php).
  
* Updated membership product meta and date format, IMPORTANT: this changes the 
  way the user product access information is stored (going from an array of 
  all memberships to individual meta for each) as well as the format (dates 
  are now unix timestamp). There is an update script that will run during 
  upgrade to handle this. For now, the legacy format is also maintained (so 
  consider this if customzizing any processing) so that rollback is possible.

* Updated wpmem_user_has_meta() to include a check by array when the field is 
  multiple checkbox or multiple select.

* Updated [wpmem_logged_in] shortcode to include an msg attribute to display a 
  message if the user does not have access to a specified product (product must
  be passed as attribute).
  
* Updated [wpmem_logged_in] shortcode to include a compare attribute. Possible
  values for "compare" are "=" and "!=" to restrict if the has a meta value or
  the meta value is "not equal to" respectively. Passing only meta_key/meta_value
  will still assume an "=" comparison.

* Updated register page shortcode [wpmem_form register] logged in state - if a 
  profile page is set, second link links to profile rather than "begin using 
  the site".

* Updated Users > All Users screen filters, removed "Not Activated" replaced
  with "Pending Activation". Filter now only shows users who have not been
  activated, no longer includes users who were deactivated.

* Major menus change - if you use the $wpmem->menus object directly, this is 
  now $wpmem->menus_clone (setting $wpmem->clone_menus remains the same).
  wpmem_menu_settings and wpmem_menus are now wpmem_clone_menu_settings and 
  wpmem_clone_menus. New menu handing has been introduced in the $wpmem->menus
  object and that will take the place of the cloned menu options.
  
* Updated the way stylesheets are handled. Added wpmem_get_suffix() API function to
  get the appropriate suffix for files (.min.css or .css) for both js and css. Also,
  minified all CSS files that were not previously minified. Note: you can no longer
  filter custom stylesheets into the plugin's dropdown selector (no one was using
  this feature as far as I am aware anyway). You *can* still filter the stylesheet
  being loaded as well as indicate the path of a custom stylesheet.

* Added reCAPTCHA v3 support.
* Added default membership product(s) at registration.
* Added membership product(s) for user export.
* Added support for selecting fields to display on the registration form or the profile form.
* Added wpmem_activate_user() and wpmem_deactivate_user() to user API.
* Added wpmem_user_sets_password() API function.
* Added wpmem_get_block_setting() API function.
* Added wpmem_set_user_status() API function.
* Added wpmem_export_users() as API function (function already existed, but the original has been moved to an object class, and the function has been included in the API).
* Added wpmem_sanitize_field() API function. This is a general utility that allows for different sanitization by type.
* Added wpmem_maybe_unserialize() API function. If result is serialized, it unserializes to an array, if an array, it sanitizes using wpmem_sanitize_array().
* Added wpmem_get_user_role() API function.
* Added wpmem_get_user_ip() API function.
* Added wpmem_get_user_meta() API function.
* Added wpmem_get_user_products() API function.
* Added wpmem_user_has_meta filter.
* Added wpmem_login_form_settings filter.
* Added wpmem_block_settings filter.
* Added wpmem_msg_settings filter.
* Added wpmem_sc_product_access_denied filter.
* Added wpmem_views_users filter.
* Added wpmem_dialogs filter.
* Added wpmem_query_where filter.
* Added wpmem_user_action.
* Added admin user class for handling Users > All users screen and user activation.
* Added user export class.
* Added "msg" attribute support for [wpmem_logged_in] when using the "membership" or "product" attributes.
* Replaced WPMEM_VERSION constant with $wpmem->version.
* Replaced WPMEM_PATH constant with $wpmem->path. WPMEM_PATH will still function for backward compatibility.
* Replaced WPMEM_URL constant with $wpmem->url.
* New folder structure being implemented
  - All admin js & css now load from /assets/ not /admin/ !!!

Other Improvements

* Changed load for WP-Members Admin API so that emails, dialogs, and tabs only load on the WP-Members settings screens (where they are used).
* Changed email "from" to only load if the WP-Members Email object is doing a send (user or admin). This saves an option load when not needed.
* Fixed an issue where a PHP notice was thrown if one of the User Pages (login/register/profile) was deleted but the setting not updated. Fixes the PHP notice issue, but also adds an admin notice to indicate the page was deleted, but the setting not updated. (This also adds a new admin notice function/process that can be expanded on later.)
* Fixed an issue with wpmem_user_has_access() that prevented proper results when used to check a specific user ID (other than the current user).

= 3.2.9 =

* Load jQuery UI and Select2 libraries locally (if they have not already been enqueued).
* Use (local) template file for default tos field link (can be customized and saved in theme folder).
* Added new sanitization functions to API: wpmem_sanitize_class() and wpmem_sanitize_array().
* Review and cleanup of form data sanitization.
* Improved file/image field upload file type validation.
* Fixed issue with loading legacy translation files.
* Added "membership" attribute to [wpmem_logged_in] shortcode (same as "product" attribute, they are interchangeable).
* Added datepicker for setting user expiration (when membership products are used).

= 3.2.8 =

* Include jQuery UI CSS and Select2 library locally.
* Updated default TOS to a template file.
* Added additional data sanitization.
* Fixed potential security issues to prevent CSRF (Cross Site Request Forgery).

= 3.2.7 =

* Fix console error from nonce by implementing different nonce names.
* Updated packaged POT file for legacy lanaguage files. Updated legacy language files to use new POT.
* Full removal of legacy language files that are available as language packs from https://translate.wordpress.org/projects/wp-plugins/wp-members/
* Added jQuery UI stylesheet (fixes issue with main options tab settings modal).
* Added wpmem_default_text filter.
* Deprecated wpmem_default_text_strings filter, use wpmem_default_text instead.
* Added translation capability to field placeholders and title attributes.
* Updated Customizer setting slug to avoid namespace collisions (added "wpmem_" stem).
* Updated instances of wp_redirect() to use wp_safe_redirect().
* Updated install for multisite to use wp_insert_site (wpmu_new_blog deprecated in WP 5.1).
* Added user screen filter to show activated users.

= 3.2.6 =

* Update to evaluate required fields as not null (instead of false).
* Added wpmem_tos_link_tag filter.
* Added $button_html parameter to wpmem_register_form_buttons filter.
* Added wpmem_serve_menu filter.
* Added nonce to short form (long form was added in 3.2.5).
* Password change function only can be fired if user is logged in.
* Added "all" argument to wpmem_user_data() to retrieve either all user meta or WP-Members fields only.
* Added $date argument to wpmem_set_user_product(). Allows specific date to be set using API.
* Added wpmem_admin_after_profile_table and wpmem_user_after_profile_table actions.
* get_user_products() returns empty array if no products (previously boolean).
* Rebuild of [wpmem_field] logic for field type. Combined multiple conditions into a single switch.
* Update password reset form - password field should be "text" class.
* Added membership field type for allowing selection of a membership at registration.
* Login form updated from "Username" to "Username or Email".
* Added $arr parameter to wpmem_login_form_rows filter.
* Added file's post ID to post_data array.

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