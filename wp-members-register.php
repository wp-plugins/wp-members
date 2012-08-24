<?php
/**
 * WP-Members Registration Functions
 *
 * Handles new user registration and existing user updates.
 * 
 * This file is part of the WP-Members plugin by Chad Butler
 * You can find out more about this plugin at http://rocketgeek.com
 * Copyright (c) 2006-2012 Chad Butler (email : plugins@butlerblog.com)
 * WP-Members(tm) is a trademark of butlerblog.com
 *
 * @package WordPress
 * @subpackage WP-Members
 * @author Chad Butler
 * @copyright 2006-2012
 */


if( ! function_exists( 'wpmem_registration' ) ):
/**
 * Register function
 *
 * Handles registering new users and updating existing users.
 *
 * @since 2.2.1
 *
 * @uses do_action Calls 'wpmem_pre_register_data' action
 * @uses do_action Calls 'wpmem_post_register_data' action
 * @uses do_action Calls 'wpmem_register_redirect' action
 * @uses do_action Calls 'wpmem_pre_update_data' action
 * @uses do_action Calls 'wpmem_post_update_data' action
 *
 * @param string $toggle toggles the function between 'register' and 'update'.
 * @global int $user_ID
 * @global array $userdata
 * @global string $wpmem_themsg
 * @global string $username
 * @global string $user_mail
 * @global array $wpmem_fieldval_arr
 * @return string $wpmem_themsg|success|editsuccess
 */
function wpmem_registration( $toggle )
{
	global $user_ID,$userdata,$wpmem_themsg,$username,$user_email,$wpmem_fieldval_arr;

	if( $toggle=='register' ) { 
		$username = $_POST['log'];
		// add for _data hooks	
		$fields['username'] = $username;
	}
	$user_email = $_POST['user_email'];
	// add for _data hooks
	$fields['user_email'] = $user_email; 

	// build array of the posts
	$wpmem_fields = get_option( 'wpmembers_fields' );
	for( $row = 0; $row < count( $wpmem_fields ); $row++ ) {
		$wpmem_fieldval_arr[$row] = $_POST[$wpmem_fields[$row][2]];
		// add for _data hooks
		if( $wpmem_fields[$row][2] != 'password' && $wpmem_fields[$row][4] == 'y' ) {
			$fields[$wpmem_fields[$row][2]] = $wpmem_fieldval_arr[$row];
		}
	}
	
	// check for required fields	
	$wpmem_fields_rev = array_reverse( $wpmem_fields );
	$wpmem_fieldval_arr_rev = array_reverse( $wpmem_fieldval_arr );

	for( $row = 0; $row < count($wpmem_fields); $row++ ) {
		$pass_chk = false; if( $toggle == 'update' && $wpmem_fields_rev[$row][2] == 'password' ) { $pass_chk = true; }
		if( $wpmem_fields_rev[$row][5] == 'y' && $pass_chk == false ) {
			if( !$wpmem_fieldval_arr_rev[$row] ) { $wpmem_themsg = sprintf( __('Sorry, %s is a required field.', 'wp-members'), $wpmem_fields_rev[$row][1] ); }
		}
	}

	switch( $toggle ) {

	case "register":

		if( !$username ) { $wpmem_themsg = __( 'Sorry, username is a required field', 'wp-members' ); return $wpmem_themsg; exit(); } 
		if( !validate_username( $username ) ) { $wpmem_themsg = __( 'The username cannot include non-alphanumeric characters.', 'wp-members' ); return $wpmem_themsg; exit(); }
		if( !is_email( $user_email) ) { $wpmem_themsg = __( 'You must enter a valid email address.', 'wp-members' ); return $wpmem_themsg; exit(); }
		if( $wpmem_themsg ) { return "empty"; exit(); }
		if( username_exists( $username ) ) { return "user"; exit(); } 
		if( email_exists( $user_email ) ) { return "email"; exit(); }
		
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
		if( ! $_POST['password'] ) {
			$password = wp_generate_password();
		} else {
			$password = $_POST['password'];
		}
		
		$user_registered = gmdate( 'Y-m-d H:i:s' );
		$user_role       = get_option( 'default_role' );
		
		// add for _data hooks	
		$fields['user_registered'] = $user_registered;
		$fields['wpmem_reg_ip']    = $_SERVER['REMOTE_ADDR'];
		$fields['wpmem_reg_url']   = $_REQUEST['redirect_to'];
		
		// _data hook is before any insertion/emails
		do_action( 'wpmem_pre_register_data', $fields );
		
		// if the _pre_register_data hook sends back an error message
		if( $wpmem_themsg ){ return $wpmem_themsg; }
	
		// inserts to wp_users table
		$user_id = wp_insert_user( array (
			'user_pass'       => $password, 
			'user_login'      => $username,
			'user_nicename'   => $username,
			'user_email'      => $user_email,
			'display_name'    => $username,
			'nickname'        => $username,
			'user_registered' => $user_registered,
			'role'            => $user_role
		) );
		
		// set remaining fields to wp_usermeta table
		for( $row = 0; $row < count( $wpmem_fields ); $row++ ) {
			if( $wpmem_fields[$row][2] != 'password' ) {
				if( $wpmem_fields[$row][2] == 'user_url' ) { // if the field is user_url, it goes in the wp_users table
					wp_update_user( array ( 'ID' => $user_id, 'user_url' => $wpmem_fieldval_arr[$row] ) );
				} else {
					if( $wpmem_fields[$row][2] != 'user_email' ) { // email is already done above, so if it's not email...
						if( $wpmem_fields[$row][4] == 'y' ) { // are we using this field?
							update_user_meta( $user_id, $wpmem_fields[$row][2], $wpmem_fieldval_arr[$row] );
						}
					}
				}
			}
		} 
		
		// capture IP address of user at registration
		update_user_meta( $user_id, 'wpmem_reg_ip', $_SERVER['REMOTE_ADDR'] );
		
		// store the registration url
		update_user_meta( $user_id, 'wpmem_reg_url', $_REQUEST['redirect_to'] );

		// set user expiration, if used
		if( WPMEM_USE_EXP == 1 && WPMEM_MOD_REG != 1 ) { wpmem_set_exp( $user_id ); }
		
		// _data hook after insertion but before email
		$fields['ID'] = $user_id;
		do_action( 'wpmem_post_register_data', $fields );
		
		require_once( 'wp-members-email.php' );

		// if this was successful, and you have email properly
		// configured, send a notification email to the user
		wpmem_inc_regemail( $user_id, $password, WPMEM_MOD_REG );
		
		// notify admin of new reg, if needed;
		if( WPMEM_NOTIFY_ADMIN == 1 ) { wpmem_notify_admin( $user_id, $wpmem_fields ); }
		
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
		if( $user_email !=  $current_user->user_email ) {
	
			if( email_exists( $user_email ) ) { return "email"; exit; } 
			
		}
		
		// _data hook is before data insertion
		$fields['ID'] = $user_ID;
		do_action( 'wpmem_pre_update_data', $fields );
		
		// if the _pre_update_data hook sends back an error message
		if( $wpmem_themsg ){ return $wpmem_themsg; }

		for( $row = 0; $row < count( $wpmem_fields ); $row++ ) {
		
			switch( $wpmem_fields[$row][2] ) {

			case( 'user_url' ): // user_url goes into wp_users
				wp_update_user( array ('ID' => $user_ID, 'user_url' => $wpmem_fieldval_arr[$row] ) );
				break;

			case( 'user_email' ): // user_email goes into wp_users
				wp_update_user( array ('ID' => $user_ID, 'user_email' => $wpmem_fieldval_arr[$row] ) );
				break;
			
			case( 'password' ):
				// do nothing...
				break;

			default: // everything else goes into wp_usermeta
				if( $wpmem_fields[$row][4] == 'y' ) {
					update_user_meta( $user_ID, $wpmem_fields[$row][2], $wpmem_fieldval_arr[$row] );
				}
				break;
			}
		}
		
		// _data hook is after insertion
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
?>