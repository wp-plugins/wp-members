=== WP-Members: Membership Framework ===
Contributors: cbutlerjr
Tags: access, authentication, content, login, member, membership, password, protect, register, registration, restriction, subscriber
Requires at least: 3.6
Tested up to: 4.7
Stable tag: 3.1.8
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

WP-Members 3.1.8 is a major update. There are no database changes (rollback is possible). See changelog for important details. Minimum WP version is 3.6.

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