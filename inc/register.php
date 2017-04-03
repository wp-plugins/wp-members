<?php
/**
 * WP-Members Registration Functions
 *
 * Handles new user registration and existing user updates.
 * 
 * This file is part of the WP-Members plugin by Chad Butler
 * You can find out more about this plugin at http://rocketgeek.com
 * Copyright (c) 2006-2017 Chad Butler
 * WP-Members(tm) is a trademark of butlerblog.com
 *
 * @package WP-Members
 * @subpackage WP-Members Registration Functions
 * @author Chad Butler
 * @copyright 2006-2017
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
 * @since 2.7.2 Added pre/post process actions.
 * @since 2.8.2 Added validation and data filters.
 * @since 2.9.3 Added validation for multisite.
 * @since 3.0.0 Moved from wp-members-register.php to /inc/register.php.
 *
 * @param  string $tag           Identifies 'register' or 'update'.
 * @global int    $user_ID
 * @global string $wpmem_themsg
 * @global array  $userdata
 * @return string $wpmem_themsg|success|editsuccess
 */
function wpmem_registration( $tag ) {

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
	if ( $tag == 'register' ) { 
		$wpmem->user->post_data['username'] = sanitize_user( wpmem_get( 'user_login' ) );
	}
	
	// Add the user email to the $wpmem->user->post_data array for _data hooks.
	$wpmem->user->post_data['user_email'] = sanitize_email( wpmem_get( 'user_email' ) );

	/** This filter defined in inc/class-wp-members-forms.php */
	/** @deprecated 3.1.7 Use wpmem_fields instead. */
	$wpmem->fields = apply_filters( 'wpmem_register_fields_arr', wpmem_fields( $tag ), $tag );
	
	// Build the $wpmem->user->post_data array from $_POST data.
	foreach ( $wpmem->fields as $meta_key => $field ) {
		if ( $field['register'] ) {
			if ( 'password' != $meta_key || 'confirm_password' != $meta_key ) {
				if ( isset( $_POST[ $meta_key ] ) ) {
					switch ( $field['type'] ) {
					case 'checkbox':
						$wpmem->user->post_data[ $meta_key ] = sanitize_text_field( $_POST[ $meta_key ] );
						break;
					case 'multiselect':
					case 'multicheckbox':
						$delimiter = ( isset( $field['delimiter'] ) ) ? $field['delimiter'] : '|';
						$wpmem->user->post_data[ $meta_key ] = ( isset( $_POST[ $meta_key ] ) ) ? implode( $delimiter, $_POST[ $meta_key ] ) : '';
						break;
					case 'textarea':
						$wpmem->user->post_data[ $meta_key ] = $_POST[ $meta_key ];
						break;
					default:
						$wpmem->user->post_data[ $meta_key ] = sanitize_text_field( $_POST[ $meta_key ] );
						break;
					}
				} else {
					$wpmem->user->post_data[ $meta_key ] = '';
				}
			} else {
				// We do have password as part of the registration form.
				if ( isset( $_POST['password'] ) ) {
					$wpmem->user->post_data['password'] = $_POST['password'];
				}
				if ( isset( $_POST['confirm_password'] ) ) {
					$wpmem->user->post_data['confirm_password'] = $_POST['confirm_password'];
				}
			}
		}
	}
	
	/**
	 * Filter the submitted form fields prior to validation.
	 *
	 * @since 2.8.2
	 * @since 3.1.7 Added $tag
	 *
	 * @param array  $wpmem->user->post_data An array of the posted form field data.
	 * @param string $tag
	 */
	$wpmem->user->post_data = apply_filters( 'wpmem_pre_validate_form', $wpmem->user->post_data, $tag );

	// Check for required fields, reverse the array for logical error message order.
	$wpmem_fields_rev = array_reverse( $wpmem->fields );

	foreach ( $wpmem_fields_rev as $meta_key => $field ) {
		$pass_arr = array( 'password', 'confirm_password', 'password_confirm' );
		$pass_chk = ( $tag == 'update' && in_array( $meta_key, $pass_arr ) ) ? true : false;
		// Validation if the field is required.
		if ( $field['required'] && $pass_chk == false ) { // @todo - verify $field['required']
			if ( 'file' == $field['type'] || 'image' == $field['type'] ) {
				// If this is a new registration.
				if ( 'register' == $tag ) {
					// If the required field is a file type.
					if ( empty( $_FILES[ $meta_key ]['name'] ) ) {
						$wpmem_themsg = sprintf( $wpmem->get_text( 'reg_empty_field' ), __( $field['label'], 'wp-members' ) );
					}
				}
			} else {
				// If the required field is any other field type.
				if ( ! $wpmem->user->post_data[ $meta_key ] ) { 
					$wpmem_themsg = sprintf( $wpmem->get_text( 'reg_empty_field' ), __( $field['label'], 'wp-members' ) );
				}
			}
		}
	}

	switch ( $tag ) {

	case "register":
		
		if ( is_multisite() ) {
			// Multisite has different requirements.
			$result = wpmu_validate_user_signup( $wpmem->user->post_data['username'], $wpmem->user->post_data['user_email'] ); 
			$errors = $result['errors'];
			if ( $errors->errors ) {
				$wpmem_themsg = $errors->get_error_message(); return $wpmem_themsg; exit;
			}

		} else {
			// Validate username and email fields.
			$wpmem_themsg = ( email_exists( $wpmem->user->post_data['user_email'] ) ) ? "email" : $wpmem_themsg;
			$wpmem_themsg = ( username_exists( $wpmem->user->post_data['username'] ) ) ? "user" : $wpmem_themsg;
			$wpmem_themsg = ( ! is_email( $wpmem->user->post_data['user_email']) ) ? $wpmem->get_text( 'reg_valid_email' ) : $wpmem_themsg;
			$wpmem_themsg = ( ! validate_username( $wpmem->user->post_data['username'] ) ) ? $wpmem->get_text( 'reg_non_alphanumeric' ) : $wpmem_themsg;
			$wpmem_themsg = ( ! $wpmem->user->post_data['username'] ) ? $wpmem->get_text( 'reg_empty_username' ) : $wpmem_themsg;
			
			// If there is an error from username, email, or required field validation, stop registration and return the error.
			if ( $wpmem_themsg ) {
				return $wpmem_themsg;
				exit();
			}
		}

		// If form contains password and email confirmation, validate that they match.
		if ( array_key_exists( 'confirm_password', $wpmem->user->post_data ) && $wpmem->user->post_data['confirm_password'] != $wpmem->user->post_data ['password'] ) { 
			$wpmem_themsg = $wpmem->get_text( 'reg_password_match' );
		}
		if ( array_key_exists( 'confirm_email', $wpmem->user->post_data ) && $wpmem->user->post_data['confirm_email'] != $wpmem->user->post_data ['user_email'] ) { 
			$wpmem_themsg = $wpmem->get_text( 'reg_email_match' ); 
		}
		
		// Process CAPTCHA.
		if ( 0 != $wpmem->captcha ) {
			$check_captcha = wpmem_register_handle_captcha();
			if ( 'passed_captcha' != $check_captcha ) {
				return $check_captcha;
			}
		}

		// Check for user defined password.
		$wpmem->user->post_data['password'] = wpmem_get( 'password', wp_generate_password() );

		// Add for _data hooks
		$wpmem->user->post_data['user_registered'] = current_time( 'mysql', 1 );
		$wpmem->user->post_data['user_role']       = get_option( 'default_role' );
		$wpmem->user->post_data['wpmem_reg_ip']    = $_SERVER['REMOTE_ADDR'];
		$wpmem->user->post_data['wpmem_reg_url']   = wpmem_get( 'wpmem_reg_page', wpmem_get( 'redirect_to', false, 'request' ), 'request' );

		/*
		 * These native fields are not installed by default, but if they
		 * are added, use the $_POST value - otherwise, default to username.
		 * Value can be filtered with wpmem_register_data.
	 	 */
		$wpmem->user->post_data['user_nicename']   = wpmem_get( 'user_nicename', $wpmem->user->post_data['username'] );
		$wpmem->user->post_data['display_name']    = wpmem_get( 'display_name',  $wpmem->user->post_data['username'] );
		$wpmem->user->post_data['nickname']        = wpmem_get( 'nickname',      $wpmem->user->post_data['username'] );
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
			
		// @todo Temporarily run custom field handling until testing on new user_register hook is complete.
		$wpmem->user->register( $user_id );

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
		global $current_user; wp_get_current_user();
		if ( $wpmem->user->post_data['user_email'] !=  $current_user->user_email ) {
			if ( email_exists( $wpmem->user->post_data['user_email'] ) ) { 
				return "email";
				exit();
			} 
			if ( !is_email( $wpmem->user->post_data['user_email']) ) { 
				$wpmem_themsg = $wpmem->get_text( 'reg_valid_email' );
				return "updaterr";
				exit();
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
		$native_update = array( 'ID' => $user_ID );

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
							update_user_meta( $user_ID, $meta_key, $wpmem->user->post_data[ $meta_key ] );
						}
						break;
					}
				}
			}
		}
		
		// Handle file uploads, if any.
		if ( ! empty( $_FILES ) ) {
	
			foreach ( $wpmem->fields as $meta_key => $field ) {
	
				if ( ( 'file' == $field['type'] || 'image' == $field['type'] ) && is_array( $_FILES[ $meta_key ] ) ) {
					if ( ! empty( $_FILES[ $meta_key ]['name'] ) ) {
						// Upload the file and save it as an attachment.
						$file_post_id = $wpmem->forms->do_file_upload( $_FILES[ $meta_key ], $wpmem->user->post_data['ID'] );
	
						// Save the attachment ID as user meta.
						update_user_meta( $wpmem->user->post_data['ID'], $meta_key, $file_post_id );
					}
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
	
	// If captcha is on, check the captcha.
	if ( $wpmem->captcha == 1 && $wpmem_captcha['recaptcha'] ) { 
		
		// If there is no api key, the captcha never displayed to the end user.
		if ( $wpmem_captcha['recaptcha']['public'] && $wpmem_captcha['recaptcha']['private'] ) {   
			if ( ! $_POST["recaptcha_response_field"] ) { // validate for empty captcha field
				$wpmem_themsg = $wpmem->get_text( 'reg_empty_captcha' );
				return "empty";
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