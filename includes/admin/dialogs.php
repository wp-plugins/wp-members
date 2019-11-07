<?php
/**
 * WP-Members Admin Functions
 *
 * Handles functions that output admin dialogs to adminstrative users.
 * 
 * This file is part of the WP-Members plugin by Chad Butler
 * You can find out more about this plugin at https://rocketgeek.com
 * Copyright (c) 2006-2019  Chad Butler
 * WP-Members(tm) is a trademark of butlerblog.com
 *
 * @package WP-Members
 * @author Chad Butler
 * @copyright 2006-2019
 *
 * Functions included:
 * - wpmem_a_do_warnings
 * - wpmem_a_warning_msg
 * - wpmem_a_meta_box
 * - wpmem_a_rss_box
 * - butlerblog_dashboard_widget
 * - butlerblog_feed_output
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

/**
 * Outputs the various admin warning messages.
 *
 * @since 2.8.0
 * 
 * @param string $did_update     Contains the update message.
 * @param array  $wpmem_settings Array containing the plugin settings.
 */
function wpmem_a_do_warnings( $did_update ) {

	global $wpmem;

	/** This filter is documented in /includes/class-wp-members-admin-api.php */
	$dialogs = apply_filters( 'wpmem_dialogs', get_option( 'wpmembers_dialogs' ) ); 

	if ( $did_update != false ) {?>
		<div id="message" class="updated fade"><p><strong><?php echo $did_update; ?></strong></p></div><?php
	}

	/*
	 * Warning messages
 	 */

	// Are warnings turned off?
	$warnings_on = ( $wpmem->warnings == 0 ) ? true : false;
	
	// Is there an active warning?
	$warning_active = false;

	// Settings allow anyone to register.
	if ( get_option( 'users_can_register' ) != 0 && $warnings_on ) {
		wpmem_a_warning_msg( 'users_can_register' );
		$warning_active = true;
	}

	// Settings allow anyone to comment.
	if ( get_option( 'comment_registration' ) !=1 && $warnings_on ) {
		wpmem_a_warning_msg( 'comment_registration' );
		$warning_active = true;
	}

	// Rss set to full text feeds.
	if ( get_option( 'rss_use_excerpt' ) !=1 && $warnings_on ) {
		wpmem_a_warning_msg( 'rss_use_excerpt' );
		$warning_active = true;
	}

	// Holding registrations but haven't changed default successful registration message.
	if ( $warnings_on && $wpmem->mod_reg == 1 && $dialogs['success'] == $wpmem->get_text( 'success' ) ) {
		wpmem_a_warning_msg( 'success' );
		$warning_active = true;
	}

	// Haven't entered recaptcha api keys.
	if ( $warnings_on && $wpmem->captcha > 0 ) {
		$wpmem_captcha = get_option( 'wpmembers_captcha' );
		if ( 1 == $wpmem->captcha || 3 == $wpmem->captcha ) {
			if ( ! $wpmem_captcha['recaptcha']['public'] || ! $wpmem_captcha['recaptcha']['private'] ) {
				wpmem_a_warning_msg( 'wpmembers_captcha' );
				$warning_active = true;
			}
		}
	}
	
	// If there is an active warning, display message about warnings.
	if ( $warning_active ) {
		wpmem_a_warning_msg( 'warning_active' );
	}

}


/**
 * Assembles the various admin warning messages.
 *
 * @since 2.4.0
 * @since 3.1.0 Changed $msg argument to string.
 * 
 * @param string $msg The number for which message should be displayed.
 */
function wpmem_a_warning_msg( $msg ) {

	$strong_msg = $remain_msg = $span_msg = '';

	switch ( $msg ) {

	case 'users_can_register':
		$strong_msg = __( 'Your WP settings allow anyone to register - this is not the recommended setting.', 'wp-members' );
		$remain_msg = sprintf( __( 'You can %s change this here %s making sure the box next to "Anyone can register" is unchecked.', 'wp-members'), '<a href="options-general.php">', '</a>' );
		$span_msg   = __( 'This setting allows a link on the /wp-login.php page to register using the WP native registration process thus circumventing any registration you are using with WP-Members. In some cases, this may suit the users wants/needs, but most users should uncheck this option. If you do not change this setting, you can choose to ignore these warning messages under WP-Members Settings.', 'wp-members' );
		break;

	case 'comment_registration':
		$strong_msg = __( 'Your WP settings allow anyone to comment - this is not the recommended setting.', 'wp-members' );
		$remain_msg = sprintf( __( 'You can %s change this here %s by checking the box next to "Users must be registered and logged in to comment."', 'wp-members' ), '<a href="options-discussion.php">', '</a>' );
		$span_msg   = __( 'This setting allows any users to comment, whether or not they are registered. Depending on how you are using WP-Members will determine whether you should change this setting or not. If you do not change this setting, you can choose to ignore these warning messages under WP-Members Settings.', 'wp-members' );
		break;

	case 'rss_use_excerpt':
		$strong_msg = __( 'Your WP settings allow full text rss feeds - this is not the recommended setting.', 'wp-members' );
		$remain_msg = sprintf( __( 'You can %s change this here %s by changing "For each article in a feed, show" to "Summary."', 'wp-members' ), '<a href="options-reading.php">' , '</a>' );
		$span_msg   = __( 'Leaving this set to full text allows anyone to read your protected content in an RSS reader. Changing this to Summary prevents this as your feeds will only show summary text.', 'wp-members' );
		break;

	case 'success':
		$strong_msg = __( 'You have set WP-Members to hold registrations for approval', 'wp-members' );
		$remain_msg = __( 'but you have not changed the default message for "Registration Completed" under "WP-Members Dialogs and Error Messages."  You should change this message to let users know they are pending approval.', 'wp-members' );
		break;

	case 'wpmembers_captcha':
		$strong_msg = __( 'You have turned on reCAPTCHA', 'wp-members');
		$remain_msg = __( 'but you have not entered API keys.  You will need both a public and private key.  The CAPTCHA will not display unless a valid API key is included.', 'wp-members' );
		break;
		
	case 'warning_active':
		$strong_msg = __( 'You have active settings that are not recommended.', 'wp-members' );
		$remain_msg = __( 'If you will not be changing these settings, you can turn off these warning messages by checking the "Ignore warning messages" in the settings below.', 'wp-members' );
		break;

	}

	if ( $span_msg ) {
		$span_msg = ' [<span title="' . $span_msg . '">why is this?</span>]';
	}
	echo '<div class="error"><p><strong>' . $strong_msg . '</strong> ' . $remain_msg . $span_msg . '</p></div>';

}


/**
 * Assemble the side meta box.
 *
 * @since 2.8.0
 *
 * @global object $wpmem
 */
function wpmem_a_meta_box() {

	global $wpmem;
	
	?><div class="postbox">
		<h3><span>WP-Members Information</span></h3>
		<div class="inside">

			<p><strong><?php _e( 'Version:', 'wp-members' ); echo "&nbsp;" . $wpmem->version; ?></strong><br />
				<a href="https://rocketgeek.com/plugins/wp-members/quick-start-guide/"><?php _e( 'Quick Start Guide', 'wp-members' ); ?></a><br />
				<a href="https://rocketgeek.com/plugins/wp-members/users-guide/"><?php _e( 'Online User Guide', 'wp-members' ); ?></a><br />
				<a href="https://rocketgeek.com/plugins/wp-members/users-guide/faqs/"><?php _e( 'FAQs', 'wp-members' ); ?></a>
			<?php if( ! defined( 'WPMEM_REMOVE_ATTR' ) ) { ?>
				<br /><br /><a href="https://rocketgeek.com/about/site-membership-subscription/">Find out how to get access</a> to WP-Members private members forum, premium code snippets, tutorials, and add-on modules!
			<?php } ?>
			</p>

			<p><i>
			<?php _e( 'Thank you for using WP-Members', 'wp-members' ); ?>&trade;!<br /><br />
			<?php _e( 'A plugin developed by', 'wp-members' ); ?>&nbsp;<a href="http://butlerblog.com">Chad Butler</a><br />
			<?php _e( 'Follow', 'wp-members' ); ?> ButlerBlog: <a href="http://feeds.butlerblog.com/butlerblog" target="_blank">RSS</a> | <a href="http://www.twitter.com/butlerblog" target="_blank">Twitter</a><br />
			Copyright &copy; 2006-<?php echo date("Y"); ?><br /><br />
			Premium support and installation service <a href="https://rocketgeek.com/about/site-membership-subscription/">available at rocketgeek.com</a>.
			</i></p>
		</div>
	</div><?php
}


/**
 * Assemble the rocketgeek.com rss feed box.
 *
 * @since 2.8.0
 */
function wpmem_a_rss_box() {

	?><div class="postbox">
		<h3><span><?php _e( 'Latest from RocketGeek', 'wp-members' ); ?></span></h3>
		<div class="inside"><?php
		wp_widget_rss_output( array(
			'url'          => 'https://rocketgeek.com/feed/',  //put your feed URL here
			'title'        => __( 'Latest from RocketGeek', 'wp-members' ),
			'items'        => 4, //how many posts to show
			'show_summary' => 0,
			'show_author'  => 0,
			'show_date'    => 0,
		) );?>
		</div>
	</div><?php
}

/**
 * Adds the rating request meta box.
 *
 * @since 3.2.0
 */
function wpmem_a_rating_box() {
	?><div class="postbox">
		<h3><?php _e( 'Like WP-Members?', 'wp-members' ); ?></h3>
		<div class="inside"><?php echo sprintf( __( 'If you like WP-Members please give it a %s&#9733;&#9733;&#9733;&#9733;&#9733;%s rating. Thanks!!', 'wp-members' ), '<a href="https://wordpress.org/support/plugin/wp-members/reviews?rate=5#new-post">', '</a>' ); ?></div>
	</div><?php
}


/**
 * Add the dashboard widget.
 *
 * @since 2.8.0
 */
function butlerblog_dashboard_widget() {
	wp_add_dashboard_widget( 'dashboard_custom_feed', __( 'Latest from ButlerBlog', 'wp-members' ), 'butlerblog_feed_output' );
}


/**
 * Output the rss feed for the dashboard widget.
 *
 * @since 2.8.0
 */
function butlerblog_feed_output() {
	echo '<div class="rss-widget">';
	wp_widget_rss_output( array(
		'url'          => 'https://feeds.feedburner.com/butlerblog',
		'title'        => __( 'Latest from ButlerBlog', 'wp-members' ),
		'items'        => 5,
		'show_summary' => 0,
		'show_author'  => 0,
		'show_date'    => 1,
	) );
	echo "</div>";
}

// End of file.