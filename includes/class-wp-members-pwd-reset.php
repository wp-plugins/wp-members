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
	public  $form_submitted_key_not_found;
	public  $form_load_key_not_found;
	public  $key_is_expired;
	private $reset_key;
	
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
			'request_new_key'              => __( "Request a new reset key.", 'wp-members' ),
		);
		
		/**
		 * Filter default dialogs.
		 *
		 * @since 3.3.8
		 *
		 * @param array $defaults {
		 *  
		 * }
		 */
		$defaults = apply_filters( 'wpmem_pwd_reset_default_dialogs', $defaults );

		foreach ( $defaults as $key => $value ) {
			$this->{$key} = $value;
		}
		
		add_filter( 'wpmem_email_filter', array( $this, 'add_reset_key_to_email' ), 10, 3 );
		add_filter( 'the_content',        array( $this, 'display_content'        ), 100 );
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
			$key = get_password_reset_key( $user );
			$query_args = array(
				'a'     => $this->form_action,
				'key'   => $key,
				'login' => $user->user_login,
			);
			
			// urlencode, primarily for user_login with a space.
			$query_args = array_map( 'rawurlencode', $query_args );
			
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
		
		if ( ! is_user_logged_in() && $this->form_action == wpmem_get( 'a', false, 'request' ) && ! is_admin() ) {
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
			
			if ( $user->has_errors() ) {
				$errors->add( 'user_not_found', $this->form_load_key_not_found );
			}
		
			// Validate
			if ( 1 == wpmem_get( 'formsubmit' ) && false !== wpmem_get( 'a', false, $this->form_action ) ) {
				
				// Verify nonce.
				if ( ! wp_verify_nonce( $_REQUEST['_wpmem_pwdchange_nonce'], 'wpmem_shortform_nonce' ) ) {
					$errors->add( 'reg_generic', wpmem_get_text( 'reg_generic' ) );
				}
				
				// Make sure submitted passwords match.
				if ( $pass1 !== $pass2 ) {
					// Legacy WP-Members error.
					$result = 'pwdchangerr';
					$msg = wpmem_get_display_message( 'pwdchangerr' );
					// WP Error.
					$errors->add( 'password_reset_mismatch', __( 'The passwords do not match.' ) );
				}
				
				/** This action is documented in wp-login.php */
				// do_action( 'validate_password_reset', $errors, $user );

				if ( ( ! $errors->has_errors() ) && isset( $pass1 ) && ! empty( $pass1 ) ) {			
					reset_password( $user, $pass1 );
					$msg = wpmem_get_display_message( 'pwdchangesuccess' ) . wpmem_login_form( 'pwdreset' );
					$result = 'pwdchangesuccess';
				}
			}
			
			if ( $result != 'pwdchangesuccess' ) {

				if ( 'invalid_key' == $user->get_error_code() ) {
					// If somehow the form was submitted but the key not found.
					$pwd_reset_link = wpmem_profile_url( 'pwdreset' );
					$msg = wpmem_get_display_message( 'invalid_key', $this->form_submitted_key_not_found . '<br /><a href="' . $pwd_reset_link . '">' . $this->request_new_key . '</a>' );
					$form = '';
				} else {
					$form = wpmem_change_password_form();
				}
				
			}
			
			$content = $msg . $form;
		}
		
		return $content;
	}
}