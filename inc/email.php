<?php
/**
 * WP-Members Email Functions
 *
 * Generates emails sent by the plugin.
 * 
 * This file is part of the WP-Members plugin by Chad Butler
 * You can find out more about this plugin at http://rocketgeek.com
 * Copyright (c) 2006-2017 Chad Butler
 * WP-Members(tm) is a trademark of butlerblog.com
 *
 * @package WP-Members
 * @subpackage WP-Members Email Functions
 * @author Chad Butler
 * @copyright 2006-2017
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
 * @since 1.8.0
 * @since 2.7.4 Added wpmem_email_headers and individual body/subject filters.
 * @since 2.9.7 Major overhaul, added wpmem_email_filter filter.
 * @since 3.1.0 Can filter in custom shortcodes with wpmem_email_shortcodes.
 * @since 3.1.1 Added $custom argument for custom emails.
 *
 * @global object $wpmem                The WP_Members object.
 * @global string $wpmem_mail_from      The email from address.
 * @global string $wpmem_mail_from_name The email from name.
 * @param  int    $user_ID              The User's ID.
 * @param  string $password             Password from the registration process.
 * @param  string $toggle               Toggle indicating the email being sent (newreg|newmod|appmod|repass|getuser).
 * @param  array  $wpmem_fields         Array of the WP-Members fields (defaults to null).
 * @param  array  $fields               Array of the registration data (defaults to null).
 * @param  array  $custom               Array of custom email information (defaults to null).
 */
function wpmem_inc_regemail( $user_id, $password, $toggle, $wpmem_fields = null, $field_data = null, $custom = null ) {

	global $wpmem;

	// Handle backward compatibility for customizations that may call the email function directly.
	$wpmem_fields = wpmem_fields( $toggle );

	/*
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
		// This is a retrieve username.
		$arr = get_option( 'wpmembers_email_getuser' );
		$arr['toggle'] = 'getuser';
		break;
	
	case 5:
		// This is a custom email.
		$arr['subj']   = $custom['subj'];
		$arr['body']   = $custom['body'];
		$arr['toggle'] = $custom['toggle'];

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
	$arr['wpmem_msurl']   = esc_url( $wpmem->user_pages['profile'] );
	$arr['wpmem_reg']     = esc_url( $wpmem->user_pages['register'] );
	$arr['wpmem_login']   = esc_url( $wpmem->user_pages['login'] );
	$arr['reg_link']      = esc_url( get_user_meta( $user_id, 'wpmem_reg_url', true ) );
	$arr['do_shortcodes'] = true;
	$arr['add_footer']    = true;
	$arr['footer']        = get_option( 'wpmembers_email_footer' );
	$arr['disable']       = false;

	// Apply filters (if set) for the sending email address.
	$default_header = ( $wpmem->email['from'] && $wpmem->email['from_name'] ) ? 'From: "' . $wpmem->email['from_name'] . '" <' . $wpmem->email['from'] . '>' : '';

	/**
	 * Filters the email headers.
	 *
	 * @since 2.7.4
	 *
	 * @param mixed  $default_header The email headers.
	 * @param string $arr['toggle']  Toggle to determine what email is being generated (newreg|newmod|appmod|repass|admin).
	 */
	$arr['headers'] = apply_filters( 'wpmem_email_headers', $default_header, $arr['toggle'] );

	/**
	 * Filter the email.
	 *
	 * This filter passes the email subject, body, user ID, and several other
	 * settings and parameters for use in the filter function. It also passes an
	 * array of the WP-Members fields, and an array of the posted registration
	 * data from the register function.
	 *
	 * @since 2.9.7
	 * @since 3.1.0 Added footer content to the array.
	 *
	 * @param array $arr {
	 *     An array containing email body, subject, user id, and additional settings.
	 *
	 *     @type string subj
	 *     @type string body
	 *     @type string toggle
	 *     @type int    user_id
	 *     @type string user_login
	 *     @type string user_email
	 *     @type string blogname
	 *     @type string exp_type
	 *     @type string exp_date
	 *     @type string wpmem_msurl
	 *     @type string reg_link
	 *     @type string do_shortcodes
	 *     @type bool   add_footer
	 *     @type string footer
	 *     @type bool   disable
	 *     @type mixed  headers
	 * }
	 * @param array $wpmem_fields An array of the WP-Members fields.
	 * @param array $field_data   An array of the posted registration data.
	 */
	$arr = apply_filters( 'wpmem_email_filter', $arr, $wpmem_fields, $field_data );

	// If emails are not disabled, continue the email process.
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
		$foot = ( $arr['add_footer'] ) ? $arr['footer'] : '';

		// If doing shortcode replacements.
		if ( $arr['do_shortcodes'] ) {
			
			$shortcodes = array(
				'blogname'     => $arr['blogname'],
				'username'     => $arr['user_login'],
				'password'     => $password,
				'email'        => $arr['user_email'],
				'reglink'      => $arr['reg_link'],
				'members-area' => $arr['wpmem_msurl'],
				'user-profile' => $arr['wpmem_msurl'],
				'exp-type'     => $arr['exp_type'],
				'exp-data'     => $arr['exp_date'],
				'exp-date'     => $arr['exp_date'],
				'login'        => $arr['wpmem_login'],
				'register'     => $arr['wpmem_reg'],
			);

			// Add custom field shortcodes.
			foreach ( $wpmem_fields as $meta_key => $field ) {
				$val = ( is_array( $field_data ) && $field['register'] ) ? $field_data[ $meta_key ] : get_user_meta( $user_id, $meta_key, true );
				$shortcodes[ $meta_key ] = $val;
			}
			
			/**
			 * Filter available email shortcodes.
			 *
			 * @since 3.1.0
			 *
			 * @param array  $shortcodes
			 * @param string $toggle 
			 */
			$shortcodes = apply_filters( 'wpmem_email_shortcodes', $shortcodes, $arr['toggle'] );
			
			$shortcd = array();
			$replace = array();
			foreach ( $shortcodes as $key => $val ) {
				// Shortcodes.
				$shortcd[] = '[' . $key . ']';
				// Replacement values.
				$replace[] = ( 'password' == $key ) ? $password : $val;
			}

			// Do replacements for subject, body, and footer shortcodes.
			$arr['subj'] = str_replace( $shortcd, $replace, $arr['subj'] );
			$arr['body'] = str_replace( $shortcd, $replace, $arr['body'] );
			$foot = ( $arr['add_footer'] ) ? str_replace( $shortcd, $replace, $foot ) : '';
		}

		// Append footer if needed.
		$arr['body'] = ( $arr['add_footer'] ) ? $arr['body'] . "\r\n" . $foot : $arr['body'];
		
		// @todo The remainder is slated to be moved to an "email send" function.
		// Apply WP's "from" and "from name" email filters.
		add_filter( 'wp_mail_from',      'wpmem_mail_from' );
		add_filter( 'wp_mail_from_name', 'wpmem_mail_from_name' );

		// Send the message.
		wp_mail( $arr['user_email'], stripslashes( $arr['subj'] ), stripslashes( $arr['body'] ), $arr['headers'] );
		// @todo End of slated for move.

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
 * @global object $wpmem                The WP_Members object.
 * @global string $wpmem_mail_from      The email from address.
 * @global string $wpmem_mail_from_name The email from name.
 * @param  int    $user_id              The User's ID.
 * @param  array  $wpmem_fields         Array of the WP-Members fields (defaults to null).
 * @param  array  $field_data           Array of the registration data (defaults to null).
 */
function wpmem_notify_admin( $user_id, $wpmem_fields = null, $field_data = null ) {

	global $wpmem;
	
	// Handle backward compatibility for customizations that may call the email function directly.
	$wpmem_fields = wpmem_fields( 'admin_notify' );

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
	$arr['act_link']      = esc_url( add_query_arg( 'user_id', $user_id, get_admin_url( '', 'user-edit.php' ) ) );
	$arr['exp_type']      = ( defined( 'WPMEM_EXP_MODULE' ) && $wpmem->use_exp == 1 ) ? get_user_meta( $user_id, 'exp_type', true ) : '';
	$arr['exp_date']      = ( defined( 'WPMEM_EXP_MODULE' ) && $wpmem->use_exp == 1 ) ? get_user_meta( $user_id, 'expires',  true ) : '';
	$arr['do_shortcodes'] = true;
	$arr['add_footer']    = true;
	$arr['footer']        = get_option( 'wpmembers_email_footer' );
	$arr['disable']       = false;

	// Builds an array of the user data fields.
	$field_arr = array();
	foreach ( $wpmem_fields as $meta_key => $field ) {
		if ( $field['register'] ) {
			if ( ! in_array( $meta_key, wpmem_get_excluded_meta( 'email' ) ) ) {
				if ( ( 'user_email' != $meta_key ) && ( 'password' != $meta_key ) ) {
					if ( 'user_url' == $meta_key ) {
						$val = esc_url( $user->user_url );
					} elseif ( in_array( $meta_key, $wp_user_fields ) ) {
						$val = esc_html( $user->{$meta_key} );
					} elseif ( 'file' == $field['type'] || 'image' == $field['type'] ) {
						$val = wp_get_attachment_url( get_user_meta( $user_id, $meta_key, true ) );
					} else {
						$val = ( is_array( $field_data ) ) ? esc_html( $field_data[ $meta_key ] ) : esc_html( get_user_meta( $user_id, $meta_key, true ) );
					}
					// $field_arr[ $field['label'] ] = $val; // @todo Consider (1) if this should be implemented, and (2) if it should be done here or location "B".
					$field_arr[ __( $field['label'], 'wp-members' ) ] = $val;
				}
			}
		}
	}
	$arr['field_arr'] = $field_arr;

	// Apply filters (if set) for the sending email address.
	$default_header = ( $wpmem->email['from'] && $wpmem->email['from_name'] ) ? 'From: "' . $wpmem->email['from_name'] . '" <' . $wpmem->email['from'] . '>' : '';

	/** This filter is documented in email.php */
	$arr['headers'] = apply_filters( 'wpmem_email_headers', $default_header, 'admin' );

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
			// @todo Location "B" to to label translation. Could be as follows:
			// $field_str.= __( $key, 'wp-members' ) . ": " . $val . "\r\n";
		}

		// Get the email footer if needed.
		$foot = ( $arr['add_footer'] ) ? $arr['footer'] : '';

		// If doing shortcode replacements.
		if ( $arr['do_shortcodes'] ) {
			
			$shortcodes = array(
				'blogname'      => $arr['blogname'],
				'username'      => $arr['user_login'],
				'email'         => $arr['user_email'],
				'reglink'       => $arr['reg_link'],
				'exp-type'      => $arr['exp_type'],
				'exp-data'      => $arr['exp_date'],
				'exp-date'      => $arr['exp_date'],
				'user-ip'       => $arr['user_ip'],
				'activate-user' => $arr['act_link'],
				'fields'        => $field_str,
			);			
			
			// Add custom field shortcodes.
			foreach ( $wpmem_fields as $meta_key => $field ) {
				$val = ( is_array( $field_data ) && $field['register'] ) ? $field_data[ $meta_key ] : get_user_meta( $user_id, $meta_key, true );
				$shortcodes[ $meta_key ] = $val;
			}
			
			/**
			 * Filter available email shortcodes.
			 *
			 * @since 3.1.0
			 *
			 * @param array  $shortcodes
			 * @param string $toggle
			 */
			$shortcodes = apply_filters( 'wpmem_email_shortcodes', $shortcodes, 'notify' );
			
			$shortcd = array();
			$replace = array();
			foreach ( $shortcodes as $key => $val ) {
				// Shortcodes.
				$shortcd[] = '[' . $key . ']';
				// Replacement values.
				$replace[] = $val;
			}

			// Create the custom field shortcodes.
			foreach ( $wpmem_fields as $meta_key => $field ) {
				$shortcd[] = '[' . $meta_key . ']';
				$replace[] = ( is_array( $field_data ) && $field['register'] ) ? $field_data[ $meta_key ] : get_user_meta( $user_id, $meta_key, true );
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
		 * This is the last chance to filter the message body. At this point
		 * it is just the text that will be in the message.
		 * @todo Consider deprecating this filter as it could be accomplished
		 *       by the wp_mail filter, or a universal filter could be added
		 *       to the new email send function.
		 *
		 * @since 2.8.2
		 *
		 * @param string $arr['body'] The admin notification email body.
		 */
		$arr['body'] = apply_filters( 'wpmem_email_notify', $arr['body'] );
		
		// @todo The remainder is slated to be moved to an "email send" function.
		// Apply from and from name email filters.
		add_filter( 'wp_mail_from',      'wpmem_mail_from' );
		add_filter( 'wp_mail_from_name', 'wpmem_mail_from_name' );

		// Send the message.
		wp_mail( $arr['admin_email'], stripslashes( $arr['subj'] ), stripslashes( $arr['body'] ), $arr['headers'] );
		// @todo End of slated to be moved.
	}
}
endif;


/**
 * Filters the wp_mail from address (if set).
 *
 * @since 2.7
 * @since 3.1 Converted to use email var in object.
 *
 * @global object $wpmem
 * @param  string $email
 * @return string $wpmem_mail_from|$email
 */
function wpmem_mail_from( $email ) {
	global $wpmem;
	return ( $wpmem->email['from'] ) ? $wpmem->email['from'] : $email;
}


/**
 * Filters the wp_mail from name (if set).
 *
 * @since 2.7
 * @since 3.1 Converted to use email var in object.
 *
 * @global object $wpmem
 * @param  string $name
 * @return string $wpmem_mail_from_name|$name
 */
function wpmem_mail_from_name( $name ) {
	global $wpmem;
	return ( $wpmem->email['from_name'] ) ? stripslashes( $wpmem->email['from_name'] ) : stripslashes( $name );
}

// End of file.