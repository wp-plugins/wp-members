<?php
/**
 * WP-Members Email Functions
 *
 * Generates emails sent by the plugin.
 * 
 * This file is part of the WP-Members plugin by Chad Butler
 * You can find out more about this plugin at http://rocketgeek.com
 * Copyright (c) 2006-2015 Chad Butler
 * WP-Members(tm) is a trademark of butlerblog.com
 *
 * @package WordPress
 * @subpackage WP-Members
 * @author Chad Butler
 * @copyright 2006-2015
 *
 * Functions Included:
 * * wpmem_inc_regemail
 * * wpmem_notify_admin
 * * wpmem_mail_from
 * * wpmem_mail_from_name
 */


if ( ! function_exists( 'wpmem_inc_regemail' ) ):
/**
 * Builds emails for the user
 *
 * @since 1.8
 *
 * @uses wp_mail
 *
 * @param int    $user_ID      The User's ID.
 * @param string $password     Password from the registration process.
 * @param string $toggle       Toggle indicating the email being sent (newreg|newmod|appmod|repass).
 * @param array  $wpmem_fields Array of the WP-Members fields (defaults to null).
 * @param array  $fields       Array of the registration data (defaults to null).
 */
function wpmem_inc_regemail( $user_id, $password, $toggle, $wpmem_fields = null, $field_data = null ) {

	/**
	 * Determine which email is being sent.
	 *
	 * Stored option is an array with keys 'body' and 'subj'.
	 */
	switch ( $toggle ) {

	case 0: 
		//this is a new registration
		$arr = get_option( 'wpmembers_email_newreg' );
		$arr['toggle'] = 'newreg';
		break;
		
	case 1:
		//registration is moderated
		$arr = get_option( 'wpmembers_email_newmod' );
		$arr['toggle'] = 'newmod';
		break;

	case 2:
		//registration is moderated, user is approved
		$arr = get_option( 'wpmembers_email_appmod' );
		$arr['toggle'] = 'appmod';
		break;

	case 3:
		//this is a password reset
		$arr = get_option( 'wpmembers_email_repass' );
		$arr['toggle'] = 'repass';
		break;

	}

	/** get the user ID */
	$user = new WP_User( $user_id );

	/** userdata for default shortcodes */
	$arr['user_id']       = $user_id;
	$arr['user_login']    = stripslashes( $user->user_login );
	$arr['user_email']    = stripslashes( $user->user_email );
	$arr['blogname']      = wp_specialchars_decode( get_option ( 'blogname' ), ENT_QUOTES );
	$arr['exp_type']      = ( WPMEM_USE_EXP == 1 ) ? get_user_meta( $user_id, 'exp_type', true ) : '';
	$arr['exp_date']      = ( WPMEM_USE_EXP == 1 ) ? get_user_meta( $user_id, 'expires',  true ) : '';
	$arr['wpmem_msurl']   = get_option( 'wpmembers_msurl', null );
	$arr['reg_link']      = esc_url( get_user_meta( $user_id, 'wpmem_reg_url', true ) );
	$arr['do_shortcodes'] = true;
	$arr['add_footer']    = true;
	$arr['disable']       = false;

	/* Apply filters (if set) for the sending email address */
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
	 * @param string $toggle         Toggle to determine what email is being generated (newreg|newmod|appmod|repass|admin).
	 */
	$arr['headers'] = apply_filters( 'wpmem_email_headers', $default_header, $toggle );

	/** handle backward compatibility for customizations that may call the email function directly */
	if ( ! $wpmem_fields ) {
		$wpmem_fields = get_option( 'wpmembers_fields' );
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

	/** extract the array **/
	extract( $arr );
	
	/**
	 * If emails are not disabled, continue the email process
	 */
	if ( ! $disable ) {

		/**
		 * Legacy email filters applied
		 */
		switch ( $toggle ) {
		
		case 'newreg': 
			//this is a new registration
			/**
			 * Filters the new registration email.
			 *
			 * @since 2.7.4
			 *
			 * @param string $arr['body'] The body content of the new registration email.
			 */
			$body = apply_filters( 'wpmem_email_newreg', $body );
			break;
			
		case 'newmod':
			//registration is moderated
			/**
			 * Filters the new moderated registration email.
			 *
			 * @since 2.7.4
			 *
			 * @param string $body The body content of the moderated registration email.
			 */
			$body = apply_filters( 'wpmem_email_newmod', $body );
			break;

		case 'appmod':
			//registration is moderated, user is approved
			/**
			 * Filters the reset password email.
			 *
			 * @since 2.7.4
			 *
			 * @param string $body The body content of the reset password email.
			 */
			$body = apply_filters( 'wpmem_email_appmod', $body );
			break;

		case 'repass':
			//this is a password reset
			/**
			 * Filters the approved registration email.
			 *
			 * @since 2.7.4
			 *
			 * @param string $body The body content of the approved registration email.
			 */
			$body = apply_filters( 'wpmem_email_repass', $body );
			break;
			
		}

		/** Get the email footer if needed */
		$foot = ( $add_footer ) ? get_option ( 'wpmembers_email_footer' ) : '';

		/** if doing shortcode replacements **/
		if ( $do_shortcodes ) {
			/** Setup default shortcodes */
			$shortcd = array( '[blogname]', '[username]', '[password]', '[reglink]', '[members-area]', '[exp-type]', '[exp-data]' );
			$replace = array( $blogname, $user_login, $password, $reg_link, $wpmem_msurl, $exp_type, $exp_date );

			/** Setup custom field shortcodes */
			foreach ( $wpmem_fields as $field ) {
				$shortcd[] = '[' . $field[2] . ']'; 
				$replace[] = get_user_meta( $user_id, $field[2], true );
			}

			/* Get the subject, body, and footer shortcodes */
			$subj = str_replace( $shortcd, $replace, $subj );
			$body = str_replace( $shortcd, $replace, $body );
			$foot = ( $add_footer ) ? str_replace( $shortcd, $replace, $foot ) : '';
		}

		/** Append footer if needed **/
		$body = ( $add_footer ) ? $body . "\r\n" . $foot : $body;

		/* Send the message */
		wp_mail( $user_email, stripslashes( $subj ), stripslashes( $body ), $headers );

	}

	return;

}
endif;


if ( ! function_exists( 'wpmem_notify_admin' ) ):
/**
 * Builds the email for admin notification of new user registration
 *
 * @since 2.3
 *
 * @uses wp_mail
 *
 * @param int   $user_id
 * @param array $wpmem_fields
 * @param array $field_data
 */
function wpmem_notify_admin( $user_id, $wpmem_fields, $field_data = null ) {

	$wp_user_fields = array( 'user_login', 'user_nicename', 'user_url', 'user_registered', 'display_name', 'first_name', 'last_name', 'nickname', 'description' );

	/** get the user ID */
	$user = get_userdata( $user_id );
	
	/** get the email stored values */
	$arr  = get_option( 'wpmembers_email_notify' );

	/** userdata for default shortcodes */ 
	$arr['user_id']       = $user_id;
	$arr['user_login']    = stripslashes( $user->user_login );
	$arr['user_email']    = stripslashes( $user->user_email );
	$arr['blogname']      = wp_specialchars_decode( get_option ( 'blogname' ), ENT_QUOTES );
	$arr['user_ip']       = get_user_meta( $user_id, 'wpmem_reg_ip', true );
	$arr['reg_link']      = esc_url( get_user_meta( $user_id, 'wpmem_reg_url', true ) );
	$arr['act_link']      = get_bloginfo ( 'wpurl' ) . "/wp-admin/user-edit.php?user_id=".$user_id;
	$arr['exp_type']      = ( WPMEM_USE_EXP == 1 ) ? get_user_meta( $user_id, 'exp_type', true ) : '';
	$arr['exp_date']      = ( WPMEM_USE_EXP == 1 ) ? get_user_meta( $user_id, 'expires',  true ) : '';
	$arr['do_shortcodes'] = true;
	$arr['add_footer']    = true;
	$arr['disable']       = false;

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
						$val = esc_html( get_user_meta( $user_id, $meta[2], true ) );
					}
					$field_arr[ $name ] = $val;
				}
			}
		}
	}
	$arr['field_arr'] = $field_arr;

	/* Apply filters (if set) for the sending email address */
	global $wpmem_mail_from, $wpmem_mail_from_name;
	add_filter( 'wp_mail_from',      'wpmem_mail_from'      );
	add_filter( 'wp_mail_from_name', 'wpmem_mail_from_name' );
	$default_header = ( $wpmem_mail_from && $wpmem_mail_from_name ) ? 'From: ' . $wpmem_mail_from_name . ' <' . $wpmem_mail_from . '>' : '';

	/**
	 * Filters the email headers.
	 *
	 * @since 2.7.4
	 *
	 * @param mixed  $default_header The email headers (default = null).
	 * @param string $toggle         Toggle to determine what email is being generated (newreg|newmod|appmod|repass|admin).
	 */
	$arr['headers'] = apply_filters( 'wpmem_email_headers', $default_header, 'admin' );

	/** handle backward compatibility for customizations that may call the email function directly */
	if ( ! $wpmem_fields ) {
		$wpmem_fields = get_option( 'wpmembers_fields' );
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
	 * @param array $arr          An array containing email body, subject, user id, and additional settings.
	 * @param array $wpmem_fields An array of the WP-Members fields.
	 * @param array $field_arr    An array of the posted registration data.
	 */
	$arr = apply_filters( 'wpmem_notify_filter', $arr, $wpmem_fields, $field_data );

	/** extract the array **/
	extract( $arr );

	/**
	 * If emails are not disabled, continue the email process
	 */
	if ( ! $disable ) {

		/** split field_arr into field_str */
		$field_str = '';
		foreach ( $field_arr as $key => $val ) {
			$field_str.= $key . ': ' . $val . "\r\n";
		}

		/** Get the email footer if needed */
		$foot = ( $add_footer ) ? get_option ( 'wpmembers_email_footer' ) : '';

		/** if doing shortcode replacements **/
		if ( $do_shortcodes ) {
			/** Setup default shortcodes */
			$shortcd = array( '[blogname]', '[username]', '[email]', '[reglink]', '[exp-type]', '[exp-data]', '[user-ip]', '[activate-user]', '[fields]' );
			$replace = array( $blogname, $user->user_login, $user->user_email, $reg_link, $exp_type, $exp_date, $user_ip, $act_link, $field_str );

			/** create the custom field shortcodes */
			foreach ( $wpmem_fields as $field ) {
				$shortcd[] = '[' . $field[2] . ']';
				$replace[] = get_user_meta( $user_id, $field[2], true );
			}

			/** Get the subject, body, and footer shortcodes */
			$subj = str_replace( $shortcd, $replace, $subj );
			$body = str_replace( $shortcd, $replace, $body );
			$foot = ( $add_footer ) ? str_replace( $shortcd, $replace, $foot ) : '';
		}

		/** Append footer if needed **/
		$body = ( $add_footer ) ? $body . "\r\n" . $foot : $body;

		/**
		 * Filters the admin notification email.
		 *
		 * @since 2.8.2
		 *
		 * @param string $body The admin notification email body.
		 */
		$body = apply_filters( 'wpmem_email_notify', $body );

		/* Send the message */
		wp_mail( $admin_email, stripslashes( $subj ), stripslashes( $body ), $headers );
	}
}
endif;


/**
 * Filters the wp_mail from address (if set)
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
 * Filters the wp_mail from name (if set)
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

/** End of File **/
