<?php
/**
 * WP-Members Registration Functions
 *
 * Handles new user registration and existing user updates.
 * 
 * This file is part of the WP-Members plugin by Chad Butler
 * You can find out more about this plugin at http://rocketgeek.com
 * Copyright (c) 2006-2016 Chad Butler
 * WP-Members(tm) is a trademark of butlerblog.com
 *
 * @package WP-Members
 * @subpackage WP-Members Registration Functions
 * @author Chad Butler
 * @copyright 2006-2016
 *
 * Functions Included:
 * - wpmem_registration
 * - wpmem_get_captcha_err
 */


if ( ! function_exists( 'wpmem_registration' ) ):
/**
 * Register function.
 *
 * Handles registering new users and updating existing users.
 *
 * @since 2.2.1
 *
 * @param  string $toggle toggles the function between 'register' and 'update'.
 * @global int    $user_ID
 * @global string $wpmem_themsg
 * @global array  $userdata
 * @return string $wpmem_themsg|success|editsuccess
 */
function wpmem_registration( $toggle ) {

	// Get the globals.
	global $user_ID, $wpmem, $wpmem_themsg, $userdata; 
	
	// Check the nonce.
	if ( defined( 'WPMEM_USE_NONCE' ) ) {
		if ( empty( $_POST ) || ! wp_verify_nonce( $_POST['wpmem-form-submit'], 'wpmem-validate-submit' ) ) {
			$wpmem_themsg = __( 'There was an error processing the form.', 'wp-members' );
			return;
		}
	}

	// Is this a registration or a user profile update?
	if ( $toggle == 'register' ) { 
		$fields['username'] = ( isset( $_POST['log'] ) ) ? sanitize_user( $_POST['log'] ) : '';
	}
	
	// Add the user email to the $fields array for _data hooks.
	$fields['user_email'] = ( isset( $_POST['user_email'] ) ) ? $_POST['user_email'] : '';

	// Build the $fields array from $_POST data.
	$wpmem_fields = $wpmem->fields; // get_option( 'wpmembers_fields' );
	foreach ( $wpmem_fields as $meta ) {
		if ( $meta[4] == 'y' ) {
			if ( $meta[2] != 'password' ) {
				$fields[ $meta[2] ] = ( isset( $_POST[ $meta[2] ] ) ) ? sanitize_text_field( $_POST[ $meta[2] ] ) : '';
			} else {
				// We do have password as part of the registration form.
				$fields['password'] = ( isset( $_POST['password'] ) ) ? $_POST['password'] : '';
			}
		}
	}
	
	/**
	 * Filter the submitted form field date prior to validation.
	 *
	 * @since 2.8.2
	 *
	 * @param array $fields An array of the posted form field data.
	 */
	$fields = apply_filters( 'wpmem_pre_validate_form', $fields );

	// Check for required fields, reverse the array for logical error message order.
	$wpmem_fields_rev = array_reverse( $wpmem_fields );

	foreach ( $wpmem_fields_rev as $meta ) {
		$pass_arr = array( 'password', 'confirm_password', 'password_confirm' );
		$pass_chk = ( $toggle == 'update' && in_array( $meta[2], $pass_arr ) ) ? true : false;
		if ( $meta[5] == 'y' && $pass_chk == false ) {
			if ( ! $fields[ $meta[2] ] ) { 
				$wpmem_themsg = sprintf( __( 'Sorry, %s is a required field.', 'wp-members' ), $meta[1] ); 
			}
		}
	}

	switch ( $toggle ) {

	case "register":
		
		if ( is_multisite() ) {
			// Multisite has different requirements.
			$result = wpmu_validate_user_signup( $fields['username'], $fields['user_email'] ); 
			$errors = $result['errors'];
			if ( $errors->errors ) {
				$wpmem_themsg = $errors->get_error_message(); return $wpmem_themsg; exit;
			}

		} else {
			// Validate username and email fields.
			$wpmem_themsg = ( email_exists( $fields['user_email'] ) ) ? "email" : $wpmem_themsg;
			$wpmem_themsg = ( username_exists( $fields['username'] ) ) ? "user" : $wpmem_themsg;
			$wpmem_themsg = ( ! is_email( $fields['user_email']) ) ? __( 'You must enter a valid email address.', 'wp-members' ) : $wpmem_themsg;
			$wpmem_themsg = ( ! validate_username( $fields['username'] ) ) ? __( 'The username cannot include non-alphanumeric characters.', 'wp-members' ) : $wpmem_themsg;
			$wpmem_themsg = ( ! $fields['username'] ) ? __( 'Sorry, username is a required field', 'wp-members' ) : $wpmem_themsg;
			
			// If there is an error from username, email, or required field validation, stop registration and return the error.
			if ( $wpmem_themsg ) {
				return $wpmem_themsg;
				exit();
			}
		}

		// If form contains password and email confirmation, validate that they match.
		if ( array_key_exists( 'confirm_password', $fields ) && $fields['confirm_password'] != $fields ['password'] ) { 
			$wpmem_themsg = __( 'Passwords did not match.', 'wp-members' );
		}
		if ( array_key_exists( 'confirm_email', $fields ) && $fields['confirm_email'] != $fields ['user_email'] ) { 
			$wpmem_themsg = __( 'Emails did not match.', 'wp-members' ); 
		}
		
		// Get the captcha settings (api keys).
		$wpmem_captcha = get_option( 'wpmembers_captcha' );
		
		// If captcha is on, check the captcha.
		if ( $wpmem->captcha == 1 && $wpmem_captcha['recaptcha'] ) { 
			
			// If there is no api key, the captcha never displayed to the end user.
			if ( $wpmem_captcha['recaptcha']['public'] && $wpmem_captcha['recaptcha']['private'] ) {   
				if ( ! $_POST["recaptcha_response_field"] ) { // validate for empty captcha field
					$wpmem_themsg = __( 'You must complete the CAPTCHA form.', 'wp-members' );
					return "empty"; exit();
				}
			}

			// Check to see if the recaptcha library has already been loaded by another plugin.
			if ( ! function_exists( '_recaptcha_qsencode' ) ) { 
				require_once( WPMEM_PATH . 'lib/recaptchalib.php' ); 
			}

			$publickey  = $wpmem_captcha['recaptcha']['public'];
			$privatekey = $wpmem_captcha['recaptcha']['private'];

			// The response from reCAPTCHA.
			$resp = null;
			// The error code from reCAPTCHA, if any.
			$error = null;

			if ( $_POST["recaptcha_response_field"] ) {

				$resp = recaptcha_check_answer (
					$privatekey,
					$_SERVER["REMOTE_ADDR"],
					$_POST["recaptcha_challenge_field"],
					$_POST["recaptcha_response_field"]
				);

				if ( ! $resp->is_valid ) {

					// Set the error code so that we can display it.
					global $wpmem_captcha_err;
					$wpmem_captcha_err = $resp->error;
					$wpmem_captcha_err = wpmem_get_captcha_err( $wpmem_captcha_err );

					return "captcha";
					exit();

				}
			} // End check recaptcha.
		} elseif ( $wpmem->captcha == 2 ) {
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
					return "empty"; exit();
				}
			}
		} elseif ( $wpmem->captcha == 3 && $wpmem_captcha['recaptcha'] ) {
			// Get the captcha response.
			if ( isset( $_POST['g-recaptcha-response'] ) ) {
				$captcha = $_POST['g-recaptcha-response'];
			}
			
			// If there is no captcha value, return error.
			if ( ! $captcha ) {
				$wpmem_themsg = __( 'You must complete the CAPTCHA form.', 'wp-members' );
				return "empty"; exit();
			}
			
			// We need the private key for validation.
			$privatekey = $wpmem_captcha['recaptcha']['private'];
			
			// Validate the captcha.
			$response = file_get_contents( "https://www.google.com/recaptcha/api/siteverify?secret=" . $privatekey . "&response=" . $captcha . "&remoteip=" . $_SERVER['REMOTE_ADDR'] );
			
			// Decode the json response.
			$response = json_decode( $response, true );
			
			// If captcha validation was unsuccessful.
			if ( $response['success'] == false ) {
				$wpmem_themsg = __( 'CAPTCHA was not valid.', 'wp-members' );
				return "empty"; exit();
			}
		}

		// Check for user defined password.
		$fields['password'] = ( ! isset( $_POST['password'] ) ) ? wp_generate_password() : $_POST['password'];

		// Add for _data hooks
		$fields['user_registered'] = gmdate( 'Y-m-d H:i:s' );
		$fields['user_role']       = get_option( 'default_role' );
		$fields['wpmem_reg_ip']    = $_SERVER['REMOTE_ADDR'];
		$fields['wpmem_reg_url']   = ( isset( $_REQUEST['wpmem_reg_page'] ) ) ? $_REQUEST['wpmem_reg_page'] : $_REQUEST['redirect_to'];

		/*
		 * These native fields are not installed by default, but if they
		 * are added, use the $_POST value - otherwise, default to username.
		 * Value can be filtered with wpmem_register_data.
	 	 */
		$fields['user_nicename']   = ( isset( $_POST['user_nicename'] ) ) ? sanitize_title( $_POST['user_nicename'] ) : $fields['username'];
		$fields['display_name']    = ( isset( $_POST['display_name'] ) )  ? sanitize_user ( $_POST['display_name']  ) : $fields['username'];
		$fields['nickname']        = ( isset( $_POST['nickname'] ) )      ? sanitize_user ( $_POST['nickname']      ) : $fields['username'];

		/**
		 * Filter registration data after validation before data insertion.
		 *
		 * @since 2.8.2
		 *
		 * @param array  $fields An array of the registration field data.
		 * @param string $toggle A switch to indicate the action (new|edit).
		 */
		$fields = apply_filters( 'wpmem_register_data', $fields, 'new' ); 

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
		 * @param array $fields The user's submitted registration data.
		 */
		do_action( 'wpmem_pre_register_data', $fields );

		// If the _pre_register_data hook sends back an error message.
		if ( $wpmem_themsg ) { 
			return $wpmem_themsg;
		}

		// Main new user fields are ready.
		$new_user_fields = array (
			'user_pass'       => $fields['password'], 
			'user_login'      => $fields['username'],
			'user_nicename'   => $fields['user_nicename'],
			'user_email'      => $fields['user_email'],
			'display_name'    => $fields['display_name'],
			'nickname'        => $fields['nickname'],
			'user_registered' => $fields['user_registered'],
			'role'            => $fields['user_role']
		);

		// Get any excluded meta fields.
		$excluded_meta = wpmem_get_excluded_meta( 'register' );

		// Fields for wp_insert_user: user_url, first_name, last_name, description, jabber, aim, yim.
		$new_user_fields_meta = array( 'user_url', 'first_name', 'last_name', 'description', 'jabber', 'aim', 'yim' );
		foreach ( $wpmem_fields as $meta ) {
			if ( in_array( $meta[2], $new_user_fields_meta ) ) {
				if ( $meta[4] == 'y' && ! in_array( $meta[2], $excluded_meta ) ) {
					$new_user_fields[ $meta[2] ] = $fields[ $meta[2] ];
				}
			}
		}

		// Inserts to wp_users table.
		$fields['ID'] = wp_insert_user( $new_user_fields );

		// Set remaining fields to wp_usermeta table.
		foreach ( $wpmem_fields as $meta ) {
			// If the field is not excluded, update accordingly.
			if ( ! in_array( $meta[2], $excluded_meta ) && ! in_array( $meta[2], $new_user_fields_meta ) ) {
				if ( $meta[4] == 'y' && $meta[2] != 'user_email' ) {
					update_user_meta( $fields['ID'], $meta[2], $fields[ $meta[2] ] );
				}
			}
		}

		// Capture IP address of user at registration.
		update_user_meta( $fields['ID'], 'wpmem_reg_ip', $fields['wpmem_reg_ip'] );

		// Store the registration url.
		update_user_meta( $fields['ID'], 'wpmem_reg_url', $fields['wpmem_reg_url'] );

		// Set user expiration, if used.
		if ( $wpmem->use_exp == 1 && $wpmem->mod_reg != 1 ) { wpmem_set_exp( $fields['ID'] ); }

		/**
		 * Fires after user insertion but before email.
		 *
		 * @since 2.7.2
		 *
		 * @param array $fields The user's submitted registration data.
		 */
		do_action( 'wpmem_post_register_data', $fields );

		require_once( WPMEM_PATH . 'inc/email.php' );

		/*
		 * If this was successful, and you have email properly
		 * configured, send a notification email to the user.
		 */
		wpmem_inc_regemail( $fields['ID'], $fields['password'], $wpmem->mod_reg, $wpmem_fields, $fields );

		// Notify admin of new reg, if needed.
		if ( $wpmem->notify == 1 ) { 
			wpmem_notify_admin( $fields['ID'], $wpmem_fields, $fields );
		}

		/**
		 * Fires after registration is complete.
		 *
		 * @since 2.7.1
		 */
		do_action( 'wpmem_register_redirect' );

		// successful registration message
		return "success"; exit();
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
		global $current_user; get_currentuserinfo();
		if ( $fields['user_email'] !=  $current_user->user_email ) {
			if ( email_exists( $fields['user_email'] ) ) { 
				return "email";
				exit();
			} 
			if ( !is_email( $fields['user_email']) ) { 
				$wpmem_themsg = __( 'You must enter a valid email address.', 'wp-members' );
				return "updaterr";
				exit();
			}
		}

		// If form includes email confirmation, validate that they match.
		if ( array_key_exists( 'confirm_email', $fields ) && $fields['confirm_email'] != $fields ['user_email'] ) { 
			$wpmem_themsg = __( 'Emails did not match.', 'wp-members' );
		}
		
		// Add the user_ID to the fields array.
		$fields['ID'] = $user_ID;
		
		/** This filter is documented in register.php */
		$fields = apply_filters( 'wpmem_register_data', $fields, 'edit' ); 
		
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
		 * @param array $fields The user's submitted update data.
		 */
		do_action( 'wpmem_pre_update_data', $fields );
		
		/*
		 * If the _pre_update_data hook sends back an error message.
		 * @todo - double check this. it should probably return "updaterr" and the hook should globalize wpmem_themsg
		 */
		if ( $wpmem_themsg ){ return $wpmem_themsg; }

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
		$native_update = array( 'ID' => $user_ID );

		foreach ( $wpmem_fields as $meta ) {
			// If the field is not excluded, update accordingly.
			if ( ! in_array( $meta[2], wpmem_get_excluded_meta( 'update' ) ) ) {
				switch ( $meta[2] ) {

				// If the field can be updated by wp_update_user.
				case( in_array( $meta[2], $native_fields ) ):
					$fields[ $meta[2] ] = ( isset( $fields[ $meta[2] ] ) ) ? $fields[ $meta[2] ] : '';
					$native_update[ $meta[2] ] = $fields[ $meta[2] ];
					break;

				// If the field is password.
				case( 'password' ):
					// Do nothing.
					break;

				// Everything else goes into wp_usermeta.
				default:
					if ( $meta[4] == 'y' ) {
						update_user_meta( $user_ID, $meta[2], $fields[ $meta[2] ] );
					}
					break;
				}
			}
		}

		// Update wp_update_user fields.
		wp_update_user( $native_update );

		/**
		 * Fires at the end of user update data insertion.
		 *
		 * @since 2.7.2
		 *
		 * @param array $fields The user's submitted registration data.
		 */
		do_action( 'wpmem_post_update_data', $fields );

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

// End of file.