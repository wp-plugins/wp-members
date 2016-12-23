<?php

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

		<div class="inner-sidebar">
			<?php wpmem_a_meta_box(); ?>
			<?php wpmem_a_rss_box(); ?>
		</div> <!-- .inner-sidebar -->

		<div id="post-body">
			<div id="post-body-content">
				<div class="postbox">
                    <div style="width:20%;max-width:300px;min-width:200px;padding:10px;margin:10px;border:#c4c4c4 1px solid;float:right;">
                        <h4><a href="http://rkt.bz/3O">WordPass</a></h4>
                        <p>Default random passwords can be difficult to for users to use.  WordPass simplifies this process by using words to create passwords. Passwords will be generated in the style of 2*Kayak29, 2Bigcranium2#, or %36POTATOE6.
                        </p>
                        <p>This plugin works with WordPress as well as with any plugin that uses the WordPress password generation function.</p>
                        <p><strong><a href="http://rkt.bz/3O">Try WordPass Free!</a></strong></p>
                     </div>
					<h3><?php _e( 'About WP-Members', 'wp-members' ); ?></h3>
					<div class="inside">
                    	<p>WP-Members is a WordPress membership plugin that is simple to use but incorporates a powerful framework for customization.
                        A simple installation can be up and running in just a few minutes. Using the plugin's API, filters, and actions, the plugin can
                        also be fully customized without touching the main plugin files.</p>
                        <p>I would encourage you to join the premium support site as a paid support subscriber.  Not only does this provide access to 
                        subscriber-exclusive tutorials on customizing the plugin, a members only forum, priority direct email support, and a host of 
                        exclusive plugin extensions, it also supports the ongoing development of the plugin.  The only way this plugin can continue to 
                        be actively developed and supported is through <b><i>your</i></b> support.  So if you like the plugin and find it integral 
                        to your site and/or business, please consider joining today!  (And if you're already a premium support subscriber - Thank You!  
                        You make this plugin possible.)</p>
                        <p>Introduced in 2006, WP-Members was the first WordPress Membership plugin and through support of the WP community it continues to grow
                        and be developed.  Why put your trust in an unknown?  WP-Members has a 10 year track record of active development and support.</p>
                        <p><strong><a href="http://rkt.bz/KB">Plugin Documentation</a></strong> |
                        <strong><a href="http://rkt.bz/about">Premium Support</a></strong> | 
                        <strong><a href="http://rkt.bz/join">Join Today!</a></strong></p>
                    </div>
                    <h3>Premium Extensions</h3>
                    <div class="inside">
                    	<p>These are some of the popular extensions that are <strong>available to premium support subscribers</strong>:</p>
                        <p><strong><a href="http://rkt.bz/0h">Advanced Options</a></strong><br />
                        This exclusive extension adds a host of extra features to the plugin that are as simple to set up as checking a box. Hides the dashboard,
                        override WP default URLs for login and registration, disable certain WP defaults, change WP-Members defaults, notify admin on user profile
                        update, integrate with other popular plugins like WooCommerce, BuddyPress, and ACF (Advanced Custom Fields), and more.  See a list
                        of available settings here.
                        <p><strong><a href="http://rkt.bz/r3">PayPal Subscriptions</a></strong><br />
                        Start charging for access to your membership site.  Easy to set up and manages user expiration dates.  Uses basic PayPal IPN (Instant
                        Payment Notification).</p>
                        <p><strong><a href="http://rkt.bz/3b">MailChimp Integration</a></strong><br />
                        Integrate MainChimp newsletter signup directly into your WP-Members registration form and allow users to subscribe/unsubscribe
                        from their profile.</p>
                        <p><strong><a href="http://rkt.bz/fh">User List</a></strong><br />
                        Provides a configurable shortcode to create user/member directories.  The extension allows you to set defaults for the shortcode and
                        to override any of those defaults with shortcode parameters for an unlimited number of lists.</p>
                        <p><strong><a href="http://rkt.bz/C6">Registration Blacklist</a></strong><br />
                        Do you need to block certain IPs or email addresses from registering?  How about restricting users from trying to register certain
                        usernames like "admin" or "support"?  This extension allows you to block IPs, emails, and restrict username selection.  It can also
                        be used to block registration from wildcard email domains such as *@hotmail.com or *.ru</p>
                        <p><strong><a href="http://rkt.bz/6k">And More!</a></strong><br />
                        Invitation codes, email as username, salesforce integration, and more.  View a full list of available extensions here.  
                        Or signup as a support subscriber and start taking your WP-Members installation to the next level today!</p>
                </div>
            </div>
        </div>
    </div><?php
}