<?php
/**
 * WP-Members Registration Functions
 *
 * Handles new user registration and existing user updates.
 * 
 * This file is part of the WP-Members plugin by Chad Butler
 * You can find out more about this plugin at https://rocketgeek.com
 * Copyright (c) 2006-2019 Chad Butler
 * WP-Members(tm) is a trademark of butlerblog.com
 *
 * @package WP-Members
 * @subpackage WP-Members Registration Functions
 * @author Chad Butler
 * @copyright 2006-2019
 *
 * Functions Included:
 * - wpmem_registration
 * - wpmem_get_captcha_err
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

if ( ! function_exists( 'wpmem_registration' ) ):
/**
 * Register function.
 *
 * Handles registering new users and updating existing users.
 *
 * @since 2.2.1
 * @since 2.7.2 Added pre/post process actions.
 * @since 2.8.2 Added validation and data filters.
 * @since 2.9.3 Added validation for multisite.
 * @since 3.0.0 Moved from wp-members-register.php to /inc/register.php.
 *
 * @global int    $user_ID
 * @global object $wpmem
 * @global string $wpmem_themsg
 * @global array  $userdata
 *
 * @param  string $tag           Identifies 'register' or 'update'.
 * @return string $wpmem_themsg|success|editsuccess
 */
function wpmem_registration( $tag ) {

	// Get the globals.
	global $user_ID, $wpmem, $wpmem_themsg, $userdata; 
	
	$wpmem->user->register_validate( $tag );

	switch ( $tag ) {

	case "register":
		
		/**
		 * Filter registration data after validation before data insertion.
		 *
		 * @since 2.8.2
		 *
		 * @param array  $wpmem->user->post_data An array of the registration field data.
		 * @param string $tag    A switch to indicate the action (new|edit).
		 */
		$wpmem->user->post_data = apply_filters( 'wpmem_register_data', $wpmem->user->post_data, 'new' ); 

		/**
		 * Fires before any insertion/emails.
		 *
		 * This action is the final step in pre registering a user. This
		 * can be used for attaching custom validation to the registration
		 * process. It cannot be used for changing any user registration
		 * data. Use the wpmem_register_data filter for that.
		 *
		 * @since 2.7.2
		 *
		 * @param array $wpmem->user->post_data The user's submitted registration data.
		 */
		do_action( 'wpmem_pre_register_data', $wpmem->user->post_data );

		// If the _pre_register_data hook sends back an error message.
		if ( $wpmem_themsg ) { 
			return $wpmem_themsg;
		}

		// Main new user fields are ready.
		$new_user_fields = array (
			'user_pass'       => $wpmem->user->post_data['password'], 
			'user_login'      => $wpmem->user->post_data['username'],
			'user_nicename'   => $wpmem->user->post_data['user_nicename'],
			'user_email'      => $wpmem->user->post_data['user_email'],
			'display_name'    => $wpmem->user->post_data['display_name'],
			'nickname'        => $wpmem->user->post_data['nickname'],
			'user_registered' => $wpmem->user->post_data['user_registered'],
			'role'            => $wpmem->user->post_data['user_role']
		);

		// Get any excluded meta fields.
		$wpmem->excluded_meta = wpmem_get_excluded_meta( 'register' );

		// Fields for wp_insert_user: user_url, first_name, last_name, description, jabber, aim, yim.
		$new_user_fields_meta = array( 'user_url', 'first_name', 'last_name', 'description', 'jabber', 'aim', 'yim' );
		foreach ( $wpmem->fields as $meta_key => $field ) {
			if ( in_array( $meta_key, $new_user_fields_meta ) ) {
				if ( $field['register'] && ! in_array( $meta_key, $wpmem->excluded_meta ) ) {
					$new_user_fields[ $meta_key ] = $wpmem->user->post_data[ $meta_key ];
				}
			}
		}

		// Inserts to wp_users table.
		$user_id = wp_insert_user( $new_user_fields );

		/**
		 * Fires after user insertion but before email.
		 *
		 * @since 2.7.2
		 * @since 3.3.0 Moved action after email.
		 *
		 * @param array $wpmem->user->post_data The user's submitted registration data.
		 */
		do_action( 'wpmem_post_register_data', $wpmem->user->post_data );

		/**
		 * Fires after registration is complete.
		 *
		 * @since 2.7.1
		 * @since 3.1.0 Added $fields
		 * @since 3.1.7 Changed $fields to $this->post_data
		 * @since 3.3.0 Moved to registration function.
		 *
		 * @param array $wpmem->user->post_data The user's submitted registration data.
		 */
		do_action( 'wpmem_register_redirect', $wpmem->user->post_data );

		// successful registration message
		return "success";
		break;

	case "update":

		if ( $wpmem_themsg ) { 
			return "updaterr";
			exit();
		}

		/*
		 * Doing a check for existing email is not the same as a new reg. check first to 
		 * see if it's different, then check if it is a valid address and it exists.
		 */
		global $current_user; wp_get_current_user();
		if ( isset( $wpmem->user->post_data['user_email'] ) ) {
			if ( $wpmem->user->post_data['user_email'] != $current_user->user_email ) {
				if ( email_exists( $wpmem->user->post_data['user_email'] ) ) { 
					return "email";
					exit();
				} 
				if ( in_array( 'user_email', $wpmem->fields ) && ! is_email( $wpmem->user->post_data['user_email']) ) { 
					$wpmem_themsg = $wpmem->get_text( 'reg_valid_email' );
					return "updaterr";
					exit();
				}
			}
		}

		// If form includes email confirmation, validate that they match.
		if ( array_key_exists( 'confirm_email', $wpmem->user->post_data ) && $wpmem->user->post_data['confirm_email'] != $wpmem->user->post_data ['user_email'] ) { 
			$wpmem_themsg = $wpmem->get_text( 'reg_email_match' );
			return "updaterr";
			exit();
		}
		
		// Add the user_ID to the fields array.
		$wpmem->user->post_data['ID'] = $user_ID;
		
		/** This filter is documented in register.php */
		$wpmem->user->post_data = apply_filters( 'wpmem_register_data', $wpmem->user->post_data, 'edit' ); 
		
		/**
		 * Fires before data insertion.
		 *
		 * This action is the final step in pre updating a user. This
		 * can be used for attaching custom validation to the update
		 * process. It cannot be used for changing any user update
		 * data. Use the wpmem_register_data filter for that.
		 *
		 * @since 2.7.2
		 *
		 * @param array $wpmem->user->post_data The user's submitted update data.
		 */
		do_action( 'wpmem_pre_update_data', $wpmem->user->post_data );
		
		// If the _pre_update_data hook sends back an error message.
		if ( $wpmem_themsg ){ 
			return "updaterr";
		}

		// A list of fields that can be updated by wp_update_user.
		$native_fields = array( 
			'user_nicename',
			'user_url',
			'user_email',
			'display_name',
			'nickname',
			'first_name',
			'last_name',
			'description',
			'role',
			'jabber',
			'aim',
			'yim' 
		);
		$native_update = array( 'ID' => $wpmem->user->post_data['ID'] );

		foreach ( $wpmem->fields as $meta_key => $field ) {
			// If the field is not excluded, update accordingly.
			if ( ! in_array( $meta_key, wpmem_get_excluded_meta( 'update' ) ) ) {
				if ( 'file' != $field['type'] && 'image' != $field['type'] ) {
					switch ( $meta_key ) {
	
					// If the field can be updated by wp_update_user.
					case( in_array( $meta_key, $native_fields ) ):
						$wpmem->user->post_data[ $meta_key ] = ( isset( $wpmem->user->post_data[ $meta_key ] ) ) ? $wpmem->user->post_data[ $meta_key ] : '';
						$native_update[ $meta_key ] = $wpmem->user->post_data[ $meta_key ];
						break;
	
					// If the field is password.
					case( 'password' ):
						// Do nothing.
						break;
	
					// Everything else goes into wp_usermeta.
					default:
						if ( $field['register'] ) {
							update_user_meta( $wpmem->user->post_data['ID'], $meta_key, $wpmem->user->post_data[ $meta_key ] );
						}
						break;
					}
				}
			}
		}
		
		// Handle file uploads, if any.
		if ( ! empty( $_FILES ) ) {
			$wpmem->user->upload_user_files( $wpmem->user->post_data['ID'], $wpmem->fields );
		}

		// Update wp_update_user fields.
		wp_update_user( $native_update );

		/**
		 * Fires at the end of user update data insertion.
		 *
		 * @since 2.7.2
		 *
		 * @param array $wpmem->user->post_data The user's submitted registration data.
		 */
		do_action( 'wpmem_post_update_data', $wpmem->user->post_data );

		return "editsuccess"; exit();
		break;
	}
} // End registration function.
endif;


if ( ! function_exists( 'wpmem_get_captcha_err' ) ):
/**
 * Generate reCAPTCHA error messages.
 *
 * @since 2.4
 *
 * @param  string $wpmem_captcha_err The response from the reCAPTCHA API.
 * @return string $wpmem_captcha_err The appropriate error message.
 */
function wpmem_get_captcha_err( $wpmem_captcha_err ) {

	switch ( $wpmem_captcha_err ) {

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

	case 'really-simple':
		$wpmem_captcha_err = __( 'You have entered an incorrect code value. Please try again.', 'wp-members' );
		break;
	}

	return $wpmem_captcha_err;
}
endif;

/**
 * Process registration captcha.
 *
 * @since 3.1.6
 *
 * @global $wpmem
 * @global $wpmem_themsg
 * @return $string
 */
function wpmem_register_handle_captcha() {
	
	global $wpmem, $wpmem_themsg;
	
	// Get the captcha settings (api keys).
	$wpmem_captcha = get_option( 'wpmembers_captcha' );

	/*
	 * @todo reCAPTCHA v1 is deprecated by Google. It is also no longer allowed
	 * to be set for new installs of WP-Members.  It is NOT compatible with
	 * PHP 7.1 and is therefore fully obsolete.
	 */
	// If captcha is on, check the captcha.
	if ( $wpmem->captcha == 1 && $wpmem_captcha['recaptcha'] ) { 
		$wpmem->captcha = 3;
	} 
	
	if ( $wpmem->captcha == 2 ) {
		if ( defined( 'REALLYSIMPLECAPTCHA_VERSION' ) ) {
			// Validate Really Simple Captcha.
			$wpmem_captcha = new ReallySimpleCaptcha();
			// This variable holds the CAPTCHA image prefix, which corresponds to the correct answer.
			$wpmem_captcha_prefix = ( isset( $_POST['captcha_prefix'] ) ) ? $_POST['captcha_prefix'] : '';
			// This variable holds the CAPTCHA response, entered by the user.
			$wpmem_captcha_code = ( isset( $_POST['captcha_code'] ) ) ? $_POST['captcha_code'] : '';
			// Check CAPTCHA validity.
			$wpmem_captcha_correct = ( $wpmem_captcha->check( $wpmem_captcha_prefix, $wpmem_captcha_code ) ) ? true : false;
			// Clean up the tmp directory.
			$wpmem_captcha->remove( $wpmem_captcha_prefix );
			$wpmem_captcha->cleanup();
			// If CAPTCHA validation fails (incorrect value entered in CAPTCHA field), return an error.
			if ( ! $wpmem_captcha_correct ) {
				$wpmem_themsg = wpmem_get_captcha_err( 'really-simple' );
				return "empty";
			}
		}
	} elseif ( $wpmem->captcha == 3 && $wpmem_captcha['recaptcha'] ) {
		// Get the captcha response.
		if ( isset( $_POST['g-recaptcha-response'] ) ) {
			$captcha = $_POST['g-recaptcha-response'];
		}
		
		// If there is no captcha value, return error.
		if ( ! $captcha ) {
			$wpmem_themsg = $wpmem->get_text( 'reg_empty_captcha' );
			return "empty";
		}
		
		// We need the private key for validation.
		$privatekey = $wpmem_captcha['recaptcha']['private'];
		
		// Validate the captcha.
		$response = wp_remote_fopen( "https://www.google.com/recaptcha/api/siteverify?secret=" . $privatekey . "&response=" . $captcha . "&remoteip=" . $_SERVER['REMOTE_ADDR'] );
		
		// Decode the json response.
		$response = json_decode( $response, true );
		
		// If captcha validation was unsuccessful.
		if ( $response['success'] == false ) {
			$wpmem_themsg = $wpmem->get_text( 'reg_invalid_captcha' );
			if ( WP_DEBUG && isset( $response['error-codes'] ) ) {
				$wpmem_themsg.= '<br /><br />';
				foreach( $response['error-codes'] as $code ) {
					$wpmem_themsg.= "Error code: " . $code . "<br />";
				}
			}
			return "empty";
		}
	}	
	
	return "passed_captcha";
}

// End of file.