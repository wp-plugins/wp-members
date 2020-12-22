<?php
/**
 * An object class for WP-Members Password Reset.
 *
 * @since 3.3.5
 * @since 3.3.8 Rebuild processing to utilize WP native functions and user_activation_key.
 */
class WP_Members_Pwd_Reset {
	
	/**
	 * Message containers.
	 *
	 * @since 3.3.5
	 */
	public $form_submitted_key_not_found;
	public $form_load_key_not_found;
	public $key_is_expired;
	
	/**
	 * Meta containers
	 *
	 * @since 3.3.5
	 */
	public $reset_key_nonce = "_wpmem_pwd_reset";
	public $form_action     = 'set_password_from_key';
	
	/**
	 * Initialize the class.
	 *
	 * @since 3.3.5
	 */
	function __construct() {
		
		$defaults = array(
			'form_submitted_key_not_found' => __( "Sorry, no password reset key was found. Please check your email and try again.", 'wp-members' ),
			'form_load_key_not_found'      => __( "Sorry, no password reset key was found. Please check your email and try again.", 'wp-members' ),
			'key_is_expired'               => __( "Sorry, the password reset key is expired.", 'wp-members' ),
		);
		
		/**
		 * Filter default dialogs.
		 *
		 * @since 3.3.8
		 *
		 * @param array $defaults
		 */
		$defaults = apply_filters( 'wpmem_pwd_reset_default_dialogs', $defaults );

		foreach ( $defaults as $key => $value ) {
			$this->{$key} = $value;
		}
		
		add_filter( 'wpmem_email_filter',                array( $this, 'add_reset_key_to_email' ), 10, 3 );
		add_filter( 'the_content',                       array( $this, 'display_content'        ), 100 );
		add_filter( 'wpmem_login_hidden_fields',         array( $this, 'add_hidden_form_field'  ), 10, 2 );
		add_action( 'wpmem_get_action',                  array( $this, 'get_wpmem_action'       ) );
		add_filter( 'wpmem_regchk',                      array( $this, 'change_regchk'          ), 10, 2 );
		add_filter( 'wpmem_resetpassword_form_defaults', array( $this, 'reset_password_form'    ) );
	}

	/**
	 * Add reset key to the email.
	 *
	 * @since 3.3.5
	 *
	 * @param  array  $arr
	 * @param  array  $wpmem_fields
	 * @param  array  $field_data
	 * @return array  $arr
	 */
	function add_reset_key_to_email( $arr, $wpmem_fields, $field_data ) {

		if ( $arr['toggle'] == 'repass' ) {
			
			$user = get_user_by( 'ID', $arr['user_id'] );
			
			// Get the stored key.
			$key = $this->get_password_reset_key( $user );
			$query_args = array(
				'a'     => $this->form_action,
				'key'   => $key,
				'login' => $user->user_login,
			);
			
			// Generate reset link.
			$link = add_query_arg( $query_args, trailingslashit( wpmem_profile_url() ) );
			
			// Does email body have the [reset_link] shortcode?
			if ( strpos( $arr['body'], '[reset_link]' ) ) {
				$arr['body'] = str_replace( '[reset_link]', $link, $arr['body'] );
			} else {
				// Add text and link to the email body.
				$arr['body'] = $arr['body'] . "\r\n"
					. $link;
			}
		}
		return $arr;
	}

	/**
	 * Display page content to user.
	 *
	 * @since 3.3.5
	 *
	 * @param  string  $content
	 * @return string  $content
	 */
	function display_content( $content ) {
		
		global $wpmem;
		
		if ( ! is_user_logged_in() && in_the_loop() && $this->form_action == wpmem_get( 'a', false, 'request' ) ) {
			// Define variables
			$result  = '';
			$user_id = false;
			$msg     = '';
			$form    = '';
			
			// Check for key.
			$key        = sanitize_text_field( wpmem_get( 'key',   false, 'request' ) );
			$user_login = sanitize_text_field( wpmem_get( 'login', false, 'request' ) );
			$pass1      = wpmem_get( 'pass1', false );
			$pass2      = wpmem_get( 'pass2', false );
			
			// Set an error container.
			$errors = new WP_Error();
			
			/**
			 * Validate the key.
			 *
			 * WP_Error will be invalid_key or expired_key. Process triggers password_reset_expiration filter
			 * filtering DAY_IN_SECONDS default. Filter password_reset_key_expired is also triggered filtering
			 * the return value (which can be used to override the expired/invalid check based on user_id).
			 *
			 * WP filter/actions triggered:
			 * - password_reset_expiration
			 * - password_reset_key_expired
			 *
			 * @see https://developer.wordpress.org/reference/functions/check_password_reset_key/
			 * @param string Hash to validate sending user's password.
			 * @param string The user login.
			 * @return WP_User|WP_Error WP_User object on success, WP_Error object for invalid or expired keys (invalid_key|expired_key).
			 */
			$user = check_password_reset_key( $key, $user_login );
		
			// Validate
			if ( 1 == wpmem_get( 'formsubmit' ) && false !== wpmem_get( 'a', false, $this->form_action ) ) {
				
				// Verify nonce.
				if ( ! wp_verify_nonce( $_REQUEST['_wpmem_pwdchange_nonce'], 'wpmem_shortform_nonce' ) ) {
					$errors->add( 'reg_generic', $wpmem->get_text( 'reg_generic' ) );
				}
				
				// Make sure submitted passwords match.
				if ( $pass1 !== $pass2 ) {
					// Legacy WP-Members error.
					$result = 'pwdchangerr';
					$msg = wpmem_inc_regmessage( 'pwdchangerr' );
					// WP Error.
					$errors->add( 'password_reset_mismatch', __( 'The passwords do not match.' ) );
				}
				
				/** This action is documented in wp-login.php */
				// do_action( 'validate_password_reset', $errors, $user );

				if ( ( ! $errors->has_errors() ) && isset( $pass1 ) && ! empty( $pass1 ) ) {			
					reset_password( $user, $pass1 );
					$msg = wpmem_inc_regmessage( 'pwdchangesuccess' ) . $wpmem->forms->do_login_form( 'pwdreset' );
					$result = 'pwdchangesuccess';
				}
			}
			
			if ( $result != 'pwdchangesuccess' ) {

				if ( 'invalid_key' == $user->get_error_code() ) {
					// If somehow the form was submitted but the key not found.
					$msg = wpmem_inc_regmessage( 'invalid_key', $this->form_submitted_key_not_found );
				}
				
				$form = wpmem_change_password_form();
				
			}
			
			$content = $msg . $form;
		}
		
		return $content;
	}

	/**
	 * Add hidden form field for form action.
	 *
	 * @since 3.3.5
	 *
	 * @param  string  $hidden_fields
	 * @return string  $hidden_fields
	 */
	function add_hidden_form_field( $hidden_fields, $action ) {
		if ( $this->form_action == wpmem_get( 'a', false, 'request' ) ) {
			$hidden_fields = str_replace( 'pwdchange', $this->form_action, $hidden_fields );
			$hidden_fields.= wpmem_create_formfield( 'key',   'hidden', wpmem_get( 'key',   null, 'request' ) );
			$hidden_fields.= wpmem_create_formfield( 'login', 'hidden', wpmem_get( 'login', null, 'request' ) );
		}
		return $hidden_fields;
	}
	
	/**
	 * Get the wpmem action variable.
	 *
	 * @since 3.3.5
	 */
	function get_wpmem_action() {
		global $wpmem; 
		if ( 'pwdreset' == $wpmem->action && isset( $_POST['formsubmit'] ) ) {

			$user_to_check = wpmem_get( 'user', false );
			$user_to_check = ( strpos( $user_to_check, '@' ) ) ? sanitize_email( $user_to_check ) : sanitize_user( $user_to_check );
		
			if ( username_exists( $user_to_check ) ) {
				$user = get_user_by( 'login', $user_to_check );
				if ( ( 1 == $wpmem->mod_reg ) && ( 1 != get_user_meta( $user->ID, 'active', true ) ) ) {
					$user = false;
				}
			} elseif ( email_exists( $user_to_check ) ) {
				$user = get_user_by( 'email', $user_to_check );
			} else {
				$user = false;
			}
			
			if ( false === $user ) {
				return "pwdreseterr";
			}

			$new_pass = '';
			wpmem_email_to_user( $user->ID, $new_pass, 3 );
			do_action( 'wpmem_pwd_reset', $user->ID, $new_pass );
			$wpmem->action = 'pwdreset_link';
			global $wpmem_regchk;
			$wpmem->regchk = 'pwdresetsuccess';
			return "pwdresetsuccess";
		}
		return;
	}

	/**
	 * Changes the wpmem_regchk value.
	 *
	 * @since 3.3.5
	 *
	 * @param  string  $regchk
	 */
	function change_regchk( $regchk, $action ) {
		global $wpmem;
		if ( 'pwdreset_link' == $action && 'pwdresetsuccess' == $wpmem->regchk ) {
			global $wpmem;
			$wpmem->action = 'pwdreset';
			return 'pwdresetsuccess';
		}
		return $regchk;
	}

	/**
	 * Filter the reset password form.
	 *
	 * @since 3.3.5
	 *
	 * @param  array  $args
	 */
	function reset_password_form( $args ) {
		global $wpmem;
		$args['inputs'][0]['name'] = $wpmem->get_text( 'login_username' );
		unset( $args['inputs'][1] );
		return $args;
	}
	
	/**
	 * Sets and gets the password reset key.
	 *
	 * This function is a wrapper for the WP function get_password_reset_key().
	 *
	 * @since 3.3.8
	 *
	 * @param  object  $user
	 * @return string  The reset key.
	 */
	private function get_password_reset_key( $user ) {
		return get_password_reset_key( $user );
	}
}