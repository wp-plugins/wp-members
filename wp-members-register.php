<?php
/**
 * WP-Members Registration Functions
 *
 * Handles new user registration and existing user updates.
 * 
 * This file is part of the WP-Members plugin by Chad Butler
 * You can find out more about this plugin at http://rocketgeek.com
 * Copyright (c) 2006-2013 Chad Butler (email : plugins@butlerblog.com)
 * WP-Members(tm) is a trademark of butlerblog.com
 *
 * @package WordPress
 * @subpackage WP-Members
 * @author Chad Butler
 * @copyright 2006-2013
 */


if( ! function_exists( 'wpmem_registration' ) ):
/**
 * Register function
 *
 * Handles registering new users and updating existing users.
 *
 * @since 2.2.1
 *
 * @uses apply_filters Calls 'wpmem_register_data' filter
 * @uses do_action Calls 'wpmem_pre_register_data' action
 * @uses do_action Calls 'wpmem_post_register_data' action
 * @uses do_action Calls 'wpmem_register_redirect' action
 * @uses do_action Calls 'wpmem_pre_update_data' action
 * @uses do_action Calls 'wpmem_post_update_data' action
 *
 * @param  string $toggle toggles the function between 'register' and 'update'.
 * @global int    $user_ID
 * @global string $wpmem_themsg
 * @global array  $userdata
 * @return string $wpmem_themsg|success|editsuccess
 */
function wpmem_registration( $toggle )
{
	// get the globals
	global $user_ID, $wpmem_themsg, $userdata;
	
	// check the nonce
	if( defined( 'WPMEM_USE_NONCE' ) ) {
		if( empty( $_POST ) || !wp_verify_nonce( $_POST['wpmem-form-submit'], 'wpmem-validate-submit' ) ) {
			$wpmem_themsg = __( 'There was an error processing the form.', 'wp-members' );
			return;
		}
	}

	// is this a registration or a user profile update?
	if( $toggle == 'register' ) { 
		$fields['username'] = ( isset( $_POST['log'] ) ) ? $_POST['log'] : '';
	}
	
	// add the user email to the $fields array for _data hooks
	$fields['user_email'] = ( isset( $_POST['user_email'] ) ) ? $_POST['user_email'] : '';

	// build the $fields array from $_POST data
	$wpmem_fields = get_option( 'wpmembers_fields' );
	for( $row = 0; $row < count( $wpmem_fields ); $row++ ) {
		if( $wpmem_fields[$row][4] == 'y' ) {
			if( $wpmem_fields[$row][2] != 'password' ) {
				$fields[$wpmem_fields[$row][2]] = ( isset( $_POST[$wpmem_fields[$row][2]] ) ) ? sanitize_text_field( $_POST[$wpmem_fields[$row][2]] ) : '';
			} else {
				// we do have password as part of the registration form
				$fields['password'] = $_POST['password'];
			}
		}
	}
	
	// filters fields prior to default field validation
	$fields = apply_filters( 'wpmem_pre_validate_form', $fields ); 

	// check for required fields	
	$wpmem_fields_rev = array_reverse( $wpmem_fields );

	for( $row = 0; $row < count($wpmem_fields); $row++ ) {
		$pass_chk = ( $toggle == 'update' && $wpmem_fields_rev[$row][2] == 'password' ) ? true : false;
		if( $wpmem_fields_rev[$row][5] == 'y' && $pass_chk == false ) {
			if( ! $fields[$wpmem_fields_rev[$row][2]] ) { $wpmem_themsg = sprintf( __('Sorry, %s is a required field.', 'wp-members'), $wpmem_fields_rev[$row][1] ); }
		}
	}

	switch( $toggle ) {

	case "register":

		if( !$fields['username'] ) { $wpmem_themsg = __( 'Sorry, username is a required field', 'wp-members' ); return $wpmem_themsg; exit(); } 
		if( !validate_username( $fields['username'] ) ) { $wpmem_themsg = __( 'The username cannot include non-alphanumeric characters.', 'wp-members' ); return $wpmem_themsg; exit(); }
		if( !is_email( $fields['user_email']) ) { $wpmem_themsg = __( 'You must enter a valid email address.', 'wp-members' ); return $wpmem_themsg; exit(); }
		if( $wpmem_themsg ) { return "empty"; exit(); }
		if( username_exists( $fields['username'] ) ) { return "user"; exit(); } 
		if( email_exists( $fields['user_email'] ) ) { return "email"; exit(); }
		
		$wpmem_captcha = get_option( 'wpmembers_captcha' ); // get the captcha settings (api keys) 
		if( WPMEM_CAPTCHA == 1 && $wpmem_captcha[0] && $wpmem_captcha[1] ) { // if captcha is on, check the captcha
			
			if( $wpmem_captcha[0] && $wpmem_captcha[1] ) {   // if there is no api key, the captcha never displayed to the end user
				if( !$_POST["recaptcha_response_field"] ) { // validate for empty captcha field
					$wpmem_themsg = __( 'You must complete the CAPTCHA form.', 'wp-members' );
					return "empty"; exit();
				}
			}

			// check to see if the recaptcha library has already been loaded by another plugin
			if( ! function_exists( '_recaptcha_qsencode' ) ) { require_once('lib/recaptchalib.php'); }

			$publickey  = $wpmem_captcha[0];
			$privatekey = $wpmem_captcha[1];

			// the response from reCAPTCHA
			$resp = null;
			// the error code from reCAPTCHA, if any
			$error = null;
			
			if( $_POST["recaptcha_response_field"] ) {
				
				$resp = recaptcha_check_answer (
					$privatekey,
					$_SERVER["REMOTE_ADDR"],
					$_POST["recaptcha_challenge_field"],
					$_POST["recaptcha_response_field"]
				);
				
				if( ! $resp->is_valid ) {

					// set the error code so that we can display it
					global $wpmem_captcha_err;
					$wpmem_captcha_err = $resp->error;
					$wpmem_captcha_err = wpmem_get_captcha_err( $wpmem_captcha_err );
					
					return "captcha";
					exit();

				} 
			} // end check recaptcha
		}		
		
		// check for user defined password
		$fields['password'] = ( ! isset( $_POST['password'] ) ) ? wp_generate_password() : $_POST['password'];
		
		// add for _data hooks	
		$fields['user_registered'] = gmdate( 'Y-m-d H:i:s' );
		$fields['user_role']       = get_option( 'default_role' );
		$fields['wpmem_reg_ip']    = $_SERVER['REMOTE_ADDR'];
		$fields['wpmem_reg_url']   = $_REQUEST['redirect_to'];

		/**
		 * these native fields are not installed by default, but if they
		 * are added, use the $_POST value - otherwise, default to username. 
		 * value can be filtered with wpmem_register_data
	 	 */
		$fields['user_nicename']   = ( isset( $_POST['user_nicename'] ) ) ? $_POST['user_nicename'] : $fields['username'];
		$fields['display_name']    = ( isset( $_POST['display_name'] ) )  ? $_POST['display_name']  : $fields['username'];
		$fields['nickname']        = ( isset( $_POST['nickname'] ) )      ? $_POST['nickname']      : $fields['username'];

		// allows all $field values to be filtered
		$fields = apply_filters( 'wpmem_register_data', $fields ); 
		
		// _data hook is before any insertion/emails
		do_action( 'wpmem_pre_register_data', $fields );
		
		// if the _pre_register_data hook sends back an error message
		if( $wpmem_themsg ){ return $wpmem_themsg; }
	
		// inserts to wp_users table
		$fields['ID'] = wp_insert_user( array (
			'user_pass'       => $fields['password'], 
			'user_login'      => $fields['username'],
			'user_nicename'   => $fields['user_nicename'],
			'user_email'      => $fields['user_email'],
			'display_name'    => $fields['display_name'],
			'nickname'        => $fields['nickname'],
			'user_registered' => $fields['user_registered'],
			'role'            => $fields['user_role']
		) );
		
		// set remaining fields to wp_usermeta table
		for( $row = 0; $row < count( $wpmem_fields ); $row++ ) {
			if( $wpmem_fields[$row][2] != 'password' ) {
				if( $wpmem_fields[$row][2] == 'user_url' ) { // if the field is user_url, it goes in the wp_users table
					$fields['user_url'] = ( isset( $fields['user_url'] ) ) ? $fields['user_url'] : '';
					wp_update_user( array ( 'ID' => $fields['ID'], 'user_url' => $fields['user_url'] ) );
				} else {
					if( $wpmem_fields[$row][2] != 'user_email' ) { // email is already done above, so if it's not email...
						if( $wpmem_fields[$row][4] == 'y' ) { // are we using this field?
							update_user_meta( $fields['ID'], $wpmem_fields[$row][2], $fields[$wpmem_fields[$row][2]] );
						}
					}
				}
			}
		} 
		
		// capture IP address of user at registration
		update_user_meta( $fields['ID'], 'wpmem_reg_ip', $fields['wpmem_reg_ip'] );
		
		// store the registration url
		update_user_meta( $fields['ID'], 'wpmem_reg_url', $fields['wpmem_reg_url'] );

		// set user expiration, if used
		if( WPMEM_USE_EXP == 1 && WPMEM_MOD_REG != 1 ) { wpmem_set_exp( $fields['ID'] ); }
		
		// _data hook after insertion but before email
		do_action( 'wpmem_post_register_data', $fields );
		
		require_once( 'wp-members-email.php' );

		// if this was successful, and you have email properly
		// configured, send a notification email to the user
		wpmem_inc_regemail( $fields['ID'], $fields['password'], WPMEM_MOD_REG );
		
		// notify admin of new reg, if needed;
		if( WPMEM_NOTIFY_ADMIN == 1 ) { wpmem_notify_admin( $fields['ID'], $wpmem_fields ); }
		
		// add action for redirection
		do_action( 'wpmem_register_redirect' );

		// successful registration message
		return "success"; exit();
		break;

	case "update":

		if( $wpmem_themsg ) { return "updaterr"; exit(); }
		
		// doing a check for existing email is not the same as a new reg. 
		// check first to see if it's different, then check if it exists.
		global $current_user; get_currentuserinfo();
		if( $fields['user_email'] !=  $current_user->user_email ) {
			if( email_exists( $fields['user_email'] ) ) { return "email"; exit; } 
		}

		// add the user_ID to the fields array
		$fields['ID'] = $user_ID;
		// allow all $field values to be filtered
		$fields = apply_filters( 'wpmem_register_data', $fields ); 
		// _pre_update_data hook is before data insertion
		do_action( 'wpmem_pre_update_data', $fields );
		
		// if the _pre_update_data hook sends back an error message
		if( $wpmem_themsg ){ return $wpmem_themsg; }

		for( $row = 0; $row < count( $wpmem_fields ); $row++ ) {
		
			switch( $wpmem_fields[$row][2] ) {

			case( 'user_url' ):
			case( 'user_email'  ):
			case( 'user_nicename' ):
			case( 'display_name' ):
			case( 'nickname' ):
				wp_update_user( array( 'ID' => $user_ID, $wpmem_fields[$row][2] => $fields[$wpmem_fields[$row][2]] ) );
				break;
		
			case( 'password' ):
				// do nothing...
				break;

			default: // everything else goes into wp_usermeta
				if( $wpmem_fields[$row][4] == 'y' ) {
					update_user_meta( $user_ID, $wpmem_fields[$row][2], $fields[$wpmem_fields[$row][2]] );
				}
				break;
			}
		}
		
		// _post_update_data hook is after insertion
		do_action( 'wpmem_post_update_data', $fields );

		return "editsuccess"; exit();
		break;
	}
} // end registration function
endif;


if( ! function_exists( 'wpmem_get_captcha_err' ) ):
/**
 * Generate reCAPTCHA error messages
 *
 * @since 2.4
 *
 * @param string $wpmem_captcha_err the response from the reCAPTCHA API
 * @return string $wpmem_captcha_err the appropriate error message
 */
function wpmem_get_captcha_err( $wpmem_captcha_err )
{
	switch( $wpmem_captcha_err ) {
	
	case "invalid-site-public-key":
		$wpmem_captcha_err = __( 'We were unable to validate the public key.', 'wp-members' );
		break;
		
	case "invalid-site-public-key":
		$wpmem_captcha_err = __( 'We were unable to validate the private key.', 'wp-members' );
		break;
	
	case "invalid-request-cookie":
		$wpmem_captcha_err = __( 'The challenge parameter of the verify script was incorrect.', 'wp-members' );
		break;
		
	case "incorrect-captcha-sol":
		$wpmem_captcha_err = __( 'The CAPTCHA solution was incorrect.', 'wp-members' );
		break;
	
	case "verify-params-incorrect":
		$wpmem_captcha_err = __( 'The parameters to verify were incorrect', 'wp-members' );
		break;
		
	case "invalid-referrer":
		$wpmem_captcha_err = __( 'reCAPTCHA API keys are tied to a specific domain name for security reasons.', 'wp-members' );
		break;
		
	case "recaptcha-not-reachable":
		$wpmem_captcha_err = __( 'The reCAPTCHA server was not reached.  Please try to resubmit.', 'wp-members' );
		break;
	}
	
	return $wpmem_captcha_err;
}
endif;

/** End of File **/