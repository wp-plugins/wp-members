<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

/**
 * Creates the About tab.
 *
 * @since 3.1.1
 *
 * @param  string      $tab The admin tab being displayed.
 * @return string|bool      The about tab, otherwise false.
 */
function wpmem_a_about_tab( $tab ) {
	if ( $tab == 'about' ) {
		// Render the about tab.
		return wpmem_a_build_about_tab();
	} else {
		return false;
	}
}


/**
 * Adds the About tab.
 *
 * @since 3.1.1
 *
 * @param  array $tabs The array of tabs for the admin panel.
 * @return array       The updated array of tabs for the admin panel.
 */
function wpmem_add_about_tab( $tabs ) {
	return array_merge( $tabs, array( 'about' => __( 'About WP-Members', 'wp-members' ) ) );
}


function wpmem_a_build_about_tab() { ?>
	<div class="metabox-holder has-right-sidebar">
		<div class="post-body"><div class="post-body-content">
		<div class="postbox"><div class="inside">
			<div style="width:20%;max-width:300px;min-width:200px;padding:10px;margin:10px;float:right;">
				<?php wpmem_a_meta_box(); ?>
				<?php wpmem_a_rating_box(); ?>
				<?php wpmem_a_rss_box(); ?>
				<div class="postbox"><div class="inside">
				<h4><a href="http://rkt.bz/3O">WordPass</a></h4>
				<p>Default random passwords can be difficult to for users to use.  WordPass simplifies this process by using words to create passwords. Passwords will be generated in the style of 2*Kayak29, 2Bigcranium2#, or %36POTATOE6.
				</p>
				<p>This plugin works with WordPress as well as with any plugin that uses the WordPress password generation function.</p>
				<p><strong><a href="http://rkt.bz/3O">Try WordPass Free!</a></strong></p>
				</div></div>
			 </div>
			<h2><?php _e( 'About WP-Members', 'wp-members' ); ?></h2>
				<p>WP-Members is a WordPress membership plugin that is simple to use but incorporates a powerful API for customization.
				A simple installation can be up and running in minutes. Yet, using the plugin's API, filters, and actions, the plugin can
				be customized without touching the main plugin files.</p>
				<p>Introduced publicly in 2006, WP-Members was the first WordPress Membership plugin and through support of the WP community it continues to grow
				and be developed.  <strong>Why put your trust in an unknown?  WP-Members has a <?php echo date('Y') - date('Y', strtotime('2006-01-01')); ?> year track record of active development and support.</strong></p>
				<p><strong><a href="https://rkt.bz/KB">Plugin Documentation</a></strong> |
				<strong><a href="https://rkt.bz/join">Premium Support &amp; Extensions</a></strong></p>
			<h2>Priority Support</h2>
				<p>If you want to make the most out of WP-Members, subscribing to Priority Support is a great way to do that. You'll not only get priority email support, but also a member-only forum  
			and access to the member's only site with a code library of tutorials and customizations. You can also subscribe to the WP-Members Pro Bundle to get everything Priority Support has to offer
			PLUS all of the premium extensions as well.<br /><br />
			<a href="https://rkt.bz/join"><strong>Check out the Premium Support options here</strong></a>.<br /><strong>NEW!! <a href="https://rkt.bz/121">One-on-one Consulting Now Available</a>!</strong></p>
			<h2>Premium Extensions</h2>
				<table>
					<tr>
						<td><a href="https://rkt.bz/0h"><img src="https://rocketgeek.com/wp/wp-content/uploads/2018/01/advanced_options-80x80.png" /></a></td>
						<td><strong><a href="https://rkt.bz/0h">Advanced Options</a></strong><br />
				This exclusive extension adds a host of additional features to the plugin that are as simple to set up as checking a box. Hides the dashboard,
				override WP default URLs for login and registration, disable certain WP defaults, change WP-Members defaults, notify admin on user profile
				update, integrate with other popular plugins like WooCommerce, BuddyPress, and ACF (Advanced Custom Fields), and more.  See a list
				of available settings here.</td>
					</tr>
					<tr>
						<td><a href="https://rkt.bz/UF"><img src="https://rocketgeek.com/wp/wp-content/uploads/2018/01/download_protect-80x80.png" /></a></td>
						<td><strong><a href="https://rkt.bz/UF">Download Protect</a></strong><br />Adds file restriction to the core WP-Members functionality. Restrict file downloads to registered users and track download activity.</td>
					</tr>
					<tr>
						<td><a href="https://rkt.bz/IP"><img src="https://rocketgeek.com/wp/wp-content/uploads/2018/01/invitation_codes-80x80.png" /></a></td>
						<td><strong><a href="https://rkt.bz/IP">Invite Codes</a></strong><br />Create invite codes for registration. Use to track sign-ups, or require a valid invite code in order to register.</td>
					</tr>
					<tr>
						<td><a href="https://rkt.bz/C6"><img src="https://rocketgeek.com/wp/wp-content/uploads/2018/01/wpmembers_security-80x80.png" /></a></td>
						<td><strong><a href="https://rkt.bz/C6">Security</a></strong><br />Set password expirations, require strong passwords, restrict concurrent logins, block specific IPs or email addresses from registering, restrict usernames like "admin" or "support" from being registered.  This extension allows you to block IPs, emails, and restrict username selection.</td>
					</tr>
					<tr>
						<td><a href="https://rkt.bz/cA"><img src="https://rocketgeek.com/wp/wp-content/uploads/2018/01/wpmembers_editor-80x80.png" /></a></td>
						<td><strong><a href="https://rkt.bz/cA">Text String Editor</a><br />Provides a simple way to edit all of the plugin's user-facing text strings. Includes text that is used in the various forms, custom messages, form headings, etc.</strong></td>
					</tr>
					<tr>
						<td><a href="https://rkt.bz/fh"><img src="https://rocketgeek.com/wp/wp-content/uploads/2018/01/wpmembers_userlist-80x80.png" /></a></td>
						<td><strong><a href="https://rkt.bz/fh">User List</a></strong><br />Provides a configurable shortcode to create user/member directories.  The extension allows you to set defaults for the shortcode and to override any of those defaults with shortcode parameters for an unlimited number of lists.</td>
					</tr>
					<tr>
						<td><a href="https://rkt.bz/aU"><img src="https://rocketgeek.com/wp/wp-content/uploads/2018/01/wpmembers_user_tracking-80x80.png" /></a></td>
						<td><strong><a href="https://rkt.bz/aU">User Tracking</a></strong><br />Tracks site usage by registered logged in users. Review what pages a user is viewing, download data as CSV.</td>
					</tr>
					<tr>
						<td><a href="https://rkt.bz/3b"><img src="https://rocketgeek.com/wp/wp-content/uploads/2017/12/mailchimp_logo-80x80.png" /></a></td>
						<td><strong><a href="https://rkt.bz/3b">MailChimp Integration</a></strong><br />Integrate MainChimp newsletter signup directly into your WP-Members registration form and allow users to subscribe/unsubscribe from their profile.</td>
					</tr>
					<tr>
						<td><a href="https://rkt.bz/r3"><img src="https://rocketgeek.com/wp/wp-content/uploads/2017/12/paypal_PNG22-80x80.png" /></a></td>
						<td><strong><a href="https://rkt.bz/r3">PayPal Subscriptions</a></strong><br />Start charging for access to your membership site.  Easy to set up and manages user expiration dates.  Uses basic PayPal IPN (Instant Payment Notification).</td>
					</tr>
					<tr>
						<td><a href="https://rkt.bz/r3"><img src="https://rocketgeek.com/wp/wp-content/uploads/2018/01/salesforce-80x80.png" /></a></td>
						<td><strong><a href="https://rkt.bz/r3">Salesforce Integration</a></strong><br />Integrates Salesforce Web-to-Lead with the WP-Members registration form data.</td>
					</tr>
				</table>
			</div>
		</div>
	</div></div></div><?php
}