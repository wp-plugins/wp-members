<?php
/**
 * WP-Members Email Functions
 *
 * Generates emails sent by the plugin.
 * 
 * This file is part of the WP-Members plugin by Chad Butler
 * You can find out more about this plugin at http://rocketgeek.com
 * Copyright (c) 2006-2016 Chad Butler
 * WP-Members(tm) is a trademark of butlerblog.com
 *
 * @package WP-Members
 * @subpackage WP-Members Email Functions
 * @author Chad Butler
 * @copyright 2006-2016
 *
 * Functions Included:
 * - wpmem_inc_regemail
 * - wpmem_notify_admin
 * - wpmem_mail_from
 * - wpmem_mail_from_name
 */


if ( ! function_exists( 'wpmem_inc_regemail' ) ):
/**
 * Builds emails for the user.
 *
 * @since 1.8
 *
 * @uses wp_mail
 *
 * @global object $wpmem                The WP_Members object.
 * @global string $wpmem_mail_from      The email from address.
 * @global string $wpmem_mail_from_name The email from name.
 * @param int     $user_ID              The User's ID.
 * @param string  $password             Password from the registration process.
 * @param string  $toggle               Toggle indicating the email being sent (newreg|newmod|appmod|repass).
 * @param array   $wpmem_fields         Array of the WP-Members fields (defaults to null).
 * @param array   $fields               Array of the registration data (defaults to null).
 */
function wpmem_inc_regemail( $user_id, $password, $toggle, $wpmem_fields = null, $field_data = null ) {

	global $wpmem;

	/**
	 * Determine which email is being sent.
	 *
	 * Stored option is an array with keys 'body' and 'subj'.
	 */
	switch ( $toggle ) {

	case 0: 
		// This is a new registration.
		$arr = get_option( 'wpmembers_email_newreg' );
		$arr['toggle'] = 'newreg';
		break;
		
	case 1:
		// Registration is moderated.
		$arr = get_option( 'wpmembers_email_newmod' );
		$arr['toggle'] = 'newmod';
		break;

	case 2:
		// Registration is moderated, user is approved.
		$arr = get_option( 'wpmembers_email_appmod' );
		$arr['toggle'] = 'appmod';
		break;

	case 3:
		// This is a password reset.
		$arr = get_option( 'wpmembers_email_repass' );
		$arr['toggle'] = 'repass';
		break;
		
	case 4:
		// This is a password reset.
		$arr = get_option( 'wpmembers_email_getuser' );
		$arr['toggle'] = 'getuser';
		break;

	}

	// Get the user ID.
	$user = new WP_User( $user_id );

	// Userdata for default shortcodes.
	$arr['user_id']       = $user_id;
	$arr['user_login']    = stripslashes( $user->user_login );
	$arr['user_email']    = stripslashes( $user->user_email );
	$arr['blogname']      = wp_specialchars_decode( get_option ( 'blogname' ), ENT_QUOTES );
	$arr['exp_type']      = ( defined( 'WPMEM_EXP_MODULE' ) && $wpmem->use_exp == 1 ) ? get_user_meta( $user_id, 'exp_type', true ) : '';
	$arr['exp_date']      = ( defined( 'WPMEM_EXP_MODULE' ) && $wpmem->use_exp == 1 ) ? get_user_meta( $user_id, 'expires',  true ) : '';
	$arr['wpmem_msurl']   = $wpmem->user_pages['profile'];
	$arr['reg_link']      = esc_url( get_user_meta( $user_id, 'wpmem_reg_url', true ) );
	$arr['do_shortcodes'] = true;
	$arr['add_footer']    = true;
	$arr['disable']       = false;

	// Apply filters (if set) for the sending email address.
	global $wpmem_mail_from, $wpmem_mail_from_name;
	add_filter( 'wp_mail_from',      'wpmem_mail_from'      );
	add_filter( 'wp_mail_from_name', 'wpmem_mail_from_name' );
	$default_header = ( $wpmem_mail_from && $wpmem_mail_from_name ) ? 'From: ' . $wpmem_mail_from_name . ' <' . $wpmem_mail_from . '>' : '';

	/**
	 * Filters the email headers.
	 *
	 * @since 2.7.4
	 *
	 * @param mixed  $default_header The email headers.
	 * @param string $arr['toggle']  Toggle to determine what email is being generated (newreg|newmod|appmod|repass|admin).
	 */
	$arr['headers'] = apply_filters( 'wpmem_email_headers', $default_header, $arr['toggle'] );

	// Handle backward compatibility for customizations that may call the email function directly.
	if ( ! $wpmem_fields ) {
		$wpmem_fields = $wpmem->fields; //get_option( 'wpmembers_fields' );
	}

	/**
	 * Filter the email.
	 *
	 * This is a new and more powerful filter than was previously available for
	 * emails. This new filter passes the email subject, body, user ID, and several
	 * other settings and parameters for use in the filter function. It also passes
	 * an array of the WP-Members fields, and an array of the posted registration
	 * data from the register function.
	 *
	 * @since 2.9.7
	 *
	 * @param array $arr          An array containing email body, subject, user id, and additional settings.
	 * @param array $wpmem_fields An array of the WP-Members fields.
	 * @param array $field_data   An array of the posted registration data.
	 */
	$arr = apply_filters( 'wpmem_email_filter', $arr, $wpmem_fields, $field_data );

	//If emails are not disabled, continue the email process.
	if ( ! $arr['disable'] ) {

		// Legacy email filters applied.
		switch ( $arr['toggle'] ) {

		case 'newreg':
			// This is a new registration.
			/**
			 * Filters the new registration email.
			 *
			 * @since 2.7.4
			 *
			 * @param string $arr['body'] The body content of the new registration email.
			 */
			$arr['body'] = apply_filters( 'wpmem_email_newreg', $arr['body'] );
			break;

		case 'newmod':
			// Registration is moderated.
			/**
			 * Filters the new moderated registration email.
			 *
			 * @since 2.7.4
			 *
			 * @param string $arr['body'] The body content of the moderated registration email.
			 */
			$arr['body'] = apply_filters( 'wpmem_email_newmod', $arr['body'] );
			break;

		case 'appmod':
			// Registration is moderated, user is approved.
			/**
			 * Filters the reset password email.
			 *
			 * @since 2.7.4
			 *
			 * @param string $arr['body'] The body content of the reset password email.
			 */
			$arr['body'] = apply_filters( 'wpmem_email_appmod', $arr['body'] );
			break;

		case 'repass':
			// This is a password reset.
			/**
			 * Filters the approved registration email.
			 *
			 * @since 2.7.4
			 *
			 * @param string $arr['body'] The body content of the approved registration email.
			 */
			$arr['body'] = apply_filters( 'wpmem_email_repass', $arr['body'] );
			break;
			
		}

		// Get the email footer if needed.
		$foot = ( $arr['add_footer'] ) ? get_option ( 'wpmembers_email_footer' ) : '';

		// If doing shortcode replacements.
		if ( $arr['do_shortcodes'] ) {

			// Setup default shortcodes.
			$shortcd = array(
				'[blogname]',
				'[username]',
				'[password]',
				'[email]',
				'[reglink]',
				'[members-area]',
				'[user-profile]',
				'[exp-type]',
				'[exp-data]',
			);

			// Replacement values for default shortcodes.
			$replace = array(
				$arr['blogname'],
				$arr['user_login'],
				$password,
				$arr['user_email'],
				$arr['reg_link'],
				$arr['wpmem_msurl'],
				$arr['wpmem_msurl'],
				$arr['exp_type'],
				$arr['exp_date'],
			);

			// Setup custom field shortcodes.
			foreach ( $wpmem_fields as $field ) {
				$shortcd[] = '[' . $field[2] . ']'; 
				$replace[] = ( is_array( $field_data ) && 'y' == $field[4] ) ? $field_data[ $field[2] ] : get_user_meta( $user_id, $field[2], true );
			}

			// Do replacements for subject, body, and footer shortcodes.
			$arr['subj'] = str_replace( $shortcd, $replace, $arr['subj'] );
			$arr['body'] = str_replace( $shortcd, $replace, $arr['body'] );
			$foot = ( $arr['add_footer'] ) ? str_replace( $shortcd, $replace, $foot ) : '';
		}

		// Append footer if needed.
		$arr['body'] = ( $arr['add_footer'] ) ? $arr['body'] . "\r\n" . $foot : $arr['body'];

		// Send the message.
		wp_mail( $arr['user_email'], stripslashes( $arr['subj'] ), stripslashes( $arr['body'] ), $arr['headers'] );

	}

	return;

}
endif;


if ( ! function_exists( 'wpmem_notify_admin' ) ):
/**
 * Builds the email for admin notification of new user registration.
 *
 * @since 2.3
 *
 * @uses wp_mail
 *
 * @global object $wpmem                The WP_Members object.
 * @global string $wpmem_mail_from      The email from address.
 * @global string $wpmem_mail_from_name The email from name.
 * @param int     $user_ID              The User's ID.
 * @param array   $wpmem_fields         Array of the WP-Members fields (defaults to null).
 * @param array   $fields               Array of the registration data (defaults to null).
 */
function wpmem_notify_admin( $user_id, $wpmem_fields, $field_data = null ) {

	global $wpmem;

	// WP default user fields.
	$wp_user_fields = array(
		'user_login',
		'user_nicename',
		'user_url',
		'user_registered',
		'display_name',
		'first_name',
		'last_name',
		'nickname',
		'description',
	);

	// Get the user data.
	$user = get_userdata( $user_id );
	
	// Get the email stored values.
	$arr  = get_option( 'wpmembers_email_notify' );

	// Userdata for default shortcodes.
	$arr['user_id']       = $user_id;
	$arr['user_login']    = stripslashes( $user->user_login );
	$arr['user_email']    = stripslashes( $user->user_email );
	$arr['blogname']      = wp_specialchars_decode( get_option ( 'blogname' ), ENT_QUOTES );
	$arr['user_ip']       = ( is_array( $field_data ) ) ? $field_data['wpmem_reg_ip'] : get_user_meta( $user_id, 'wpmem_reg_ip', true );
	$arr['reg_link']      = esc_url( get_user_meta( $user_id, 'wpmem_reg_url', true ) );
	$arr['act_link']      = get_bloginfo ( 'wpurl' ) . "/wp-admin/user-edit.php?user_id=".$user_id;
	$arr['exp_type']      = ( defined( 'WPMEM_EXP_MODULE' ) && $wpmem->use_exp == 1 ) ? get_user_meta( $user_id, 'exp_type', true ) : '';
	$arr['exp_date']      = ( defined( 'WPMEM_EXP_MODULE' ) && $wpmem->use_exp == 1 ) ? get_user_meta( $user_id, 'expires',  true ) : '';
	$arr['do_shortcodes'] = true;
	$arr['add_footer']    = true;
	$arr['disable']       = false;

	// Builds an array of the user data fields.
	$field_arr = array();
	foreach ( $wpmem_fields as $meta ) {
		if ( $meta[4] == 'y' ) {
			$name = $meta[1];
			if ( ! in_array( $meta[2], wpmem_get_excluded_meta( 'email' ) ) ) {
				if ( ( $meta[2] != 'user_email' ) && ( $meta[2] != 'password' ) ) {
					if ( $meta[2] == 'user_url' ) {
						$val = esc_url( $user->user_url );
					} elseif ( in_array( $meta[2], $wp_user_fields ) ) {
						$val = esc_html( $user->$meta[2] );
					} else {
						$val = ( is_array( $field_data ) ) ? esc_html( $field_data[ $meta[2] ] ) : esc_html( get_user_meta( $user_id, $meta[2], true ) );
					}
					$field_arr[ $name ] = $val;
				}
			}
		}
	}
	$arr['field_arr'] = $field_arr;

	// Apply filters (if set) for the sending email address.
	global $wpmem_mail_from, $wpmem_mail_from_name;
	add_filter( 'wp_mail_from',      'wpmem_mail_from'      );
	add_filter( 'wp_mail_from_name', 'wpmem_mail_from_name' );
	$default_header = ( $wpmem_mail_from && $wpmem_mail_from_name ) ? 'From: ' . $wpmem_mail_from_name . ' <' . $wpmem_mail_from . '>' : '';

	/** This filter is documented in email.php */
	$arr['headers'] = apply_filters( 'wpmem_email_headers', $default_header, 'admin' );

	// Handle backward compatibility for customizations that may call the email function directly.
	if ( ! $wpmem_fields ) {
		$wpmem_fields = $wpmem->fields;
	}

	/**
	 * Filters the address the admin notification is sent to.
	 *
	 * @since 2.7.5
	 *
	 * @param string The email address of the admin to send to.
	 */
	$arr['admin_email'] = apply_filters( 'wpmem_notify_addr', get_option( 'admin_email' ) );

	/**
	 * Filter the email.
	 *
	 * This is a new and more powerful filter than was previously available for
	 * emails. This new filter passes the email subject, body, user ID, and several
	 * other settings and parameters for use in the filter function. It also passes
	 * an array of the WP-Members fields, and an array of the posted registration
	 * data from the register function.
	 *
	 * @since 2.9.8
	 *
	 * @param array $arr              An array containing email body, subject, user id, and additional settings.
	 * @param array $wpmem_fields     An array of the WP-Members fields.
	 * @param array $arr['field_arr'] An array of the posted registration data.
	 */
	$arr = apply_filters( 'wpmem_notify_filter', $arr, $wpmem_fields, $field_data );

	// If emails are not disabled, continue the email process.
	if ( ! $arr['disable'] ) {

		// Split field_arr into field_str.
		$field_str = '';
		foreach ( $arr['field_arr'] as $key => $val ) {
			$field_str.= $key . ': ' . $val . "\r\n";
		}

		// Get the email footer if needed.
		$foot = ( $arr['add_footer'] ) ? get_option ( 'wpmembers_email_footer' ) : '';

		// If doing shortcode replacements.
		if ( $arr['do_shortcodes'] ) {

			// Setup default shortcodes.
			$shortcd = array(
				'[blogname]',
				'[username]',
				'[email]',
				'[reglink]',
				'[exp-type]',
				'[exp-data]',
				'[user-ip]',
				'[activate-user]',
				'[fields]',
			);

			// Replacement values for default shortcodes.
			$replace = array(
				$arr['blogname'],
				$arr['user_login'],
				$arr['user_email'],
				$arr['reg_link'],
				$arr['exp_type'],
				$arr['exp_date'],
				$arr['user_ip'],
				$arr['act_link'],
				$field_str,
			);

			// Create the custom field shortcodes.
			foreach ( $wpmem_fields as $field ) {
				$shortcd[] = '[' . $field[2] . ']';
				$replace[] = ( is_array( $field_data ) && 'y' == $field[4] ) ? $field_data[ $field[2] ] : get_user_meta( $user_id, $field[2], true );
			}

			// Get the subject, body, and footer shortcodes.
			$arr['subj'] = str_replace( $shortcd, $replace, $arr['subj'] );
			$arr['body'] = str_replace( $shortcd, $replace, $arr['body'] );
			$foot = ( $arr['add_footer'] ) ? str_replace( $shortcd, $replace, $foot ) : '';
		}

		// Append footer if needed.
		$arr['body'] = ( $arr['add_footer'] ) ? $arr['body'] . "\r\n" . $foot : $arr['body'];

		/**
		 * Filters the admin notification email.
		 *
		 * @since 2.8.2
		 *
		 * @param string $arr['body'] The admin notification email body.
		 */
		$arr['body'] = apply_filters( 'wpmem_email_notify', $arr['body'] );

		// Send the message.
		wp_mail( $arr['admin_email'], stripslashes( $arr['subj'] ), stripslashes( $arr['body'] ), $arr['headers'] );
	}
}
endif;


/**
 * Filters the wp_mail from address (if set).
 *
 * @since 2.7
 *
 * @param  string $email
 * @return string $email
 */
function wpmem_mail_from( $email ) {
	global $wpmem_mail_from;
	$wpmem_mail_from = ( get_option( 'wpmembers_email_wpfrom' ) ) ? get_option( 'wpmembers_email_wpfrom' ) : $email;
	return $wpmem_mail_from;
}


/**
 * Filters the wp_mail from name (if set).
 *
 * @since 2.7
 *
 * @param  string $name
 * @return string $name
 */
function wpmem_mail_from_name( $name ) {
	global $wpmem_mail_from_name;
	$wpmem_mail_from_name = ( get_option( 'wpmembers_email_wpname' ) ) ? stripslashes( get_option( 'wpmembers_email_wpname' ) ) : $name;
	return $wpmem_mail_from_name;
}

// End of file.