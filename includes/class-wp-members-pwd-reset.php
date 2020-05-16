<?php

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
	public $reset_key_meta  = '_wpmem_password_reset_key';
	public $reset_key_exp   = '_wpmem_password_reset_exp';
	public $reset_key_nonce = "_wpmem_pwd_reset";
	public $form_action     = 'set_password_from_key';
	
	/**
	 * Initialize the class.
	 *
	 * @since 3.3.5
	 */
	function __construct() {
		
		$this->form_submitted_key_not_found = __( "Sorry, no password reset key was found. Please check your email and try again.", 'wp-members' );
		$this->form_load_key_not_found      = __( "Sorry, no password reset key was found. Please check your email and try again.", 'wp-members' );
		$this->key_is_expired               = __( "Sorry, the password reset key is expired.", 'wp-members' );
		
		add_filter( 'wpmem_email_filter',                array( $this, 'add_reset_key_to_email' ), 10, 3 );
		add_filter( 'the_content',                       array( $this, 'display_content'        ), 100 );
		add_filter( 'wpmem_login_hidden_fields',         array( $this, 'add_hidden_form_field'  ), 10, 2 );
		add_action( 'wpmem_get_action',                  array( $this, 'get_wpmem_action'       ) );
		add_filter( 'wpmem_regchk',                      array( $this, 'change_regchk'          ), 10, 2 );
		add_filter( 'wpmem_resetpassword_form_defaults', array( $this, 'reset_password_form'    ) );
	}

	/**
	 * Create a password reset key for the user.
	 *
	 * @since 3.3.5
	 *
	 * @param   int     $user_id
	 * @return  string  $key
	 */
	function generate_reset_key( $user_id ) {
		$key = md5( wp_generate_password() );
		update_user_meta( $user_id, $this->reset_key_meta, $key );
		update_user_meta( $user_id, $this->reset_key_exp, time() + 21600 );
		return $key;
	}

	// utility for getting the user ID by the password_reset_key
	function get_user_by_pwd_key( $key ) {
		// Get the user account the key is for.
		$users = get_users( array(
			'meta_key'    => $this->reset_key_meta,
			'meta_value'  => $key,
			'number'      => 1,
			'count_total' => false
		) );
		if ( $users ) {
			foreach( $users as $user ) {
				return $user->ID;
			}
		}
		return false;
	}
	
	// Check if key is expired.
	function key_is_valid( $key, $user_id ) {
		$expires = get_user_meta( $user_id, $this->reset_key_exp, true );	
		return ( time() < $expires ) ? true : false;
	}

	// Include the key in the email to user.
	function add_reset_key_to_email( $arr, $wpmem_fields, $field_data ) {
		// Only do this for new registrations.
		if ( $arr['toggle'] == 'repass' ) {
			// Get the stored key.
			$key = $this->generate_reset_key( $arr['user_id'] );
			$query_args = array(
				'a'   => $this->form_action,
				'key' => $key,
			);
			// Add text and link to the email body.
			$arr['body'] = $arr['body'] . "\r\n"
				. add_query_arg( $query_args, trailingslashit( wpmem_profile_url() ) );
		}
		return $arr;
	}

	function display_content( $content ) {
		if ( ! is_user_logged_in() && in_the_loop() && $this->form_action == wpmem_get( 'a', false, 'request' ) ) {
			// Define variables
			$result = false; $user_id = false;
			
			// Check for key
			$key = sanitize_text_field( wpmem_get( 'key', false, 'request' ) );
			
			// Validate
			if ( 1 == wpmem_get( 'formsubmit' ) && false !== wpmem_get( 'a', false, $this->form_action ) ) {
				// form was submitted, validate fields
				$user_id = $this->get_user_by_pwd_key( $key );
				if ( $user_id ) {
					// Key was found, is it expired?
					if ( true === $this->key_is_valid( $key, $user_id ) ) {
						$result = $this->change_password( $user_id );
					} else {
						return $this->key_is_expired;
					}
				} else {
					$result = 'submittedkeynotfound';
				}
			}
			if ( $result != 'pwdchangesuccess' ) {

				if ( 'submittedkeynotfound' == $result ) {
					// If somehow the form was submitted but the key not found.
					return $this->form_submitted_key_not_found;
				}

				// If no key found on initial form load, or if no key was passed
				if ( $key ) {
					$user_id = $this->get_user_by_pwd_key( $key );
					if ( ! $user_id ) {
						return $this->form_load_key_not_found;
					} else {
						if ( false === $this->key_is_valid( $key, $user_id ) ) {
							return $this->key_is_expired;
						}
					}
				} else {
					return $this->form_load_key_not_found;
				}

				$content = wpmem_change_password_form();
			} else {
				$content = wpmem_inc_regmessage( 'pwdchangesuccess' );
				if ( $user_id ) {
					delete_user_meta( $user_id, $this->reset_key_meta );
					delete_user_meta( $user_id, $this->reset_key_exp  );
				}
			}
		}
		return $content;
	}

	function add_hidden_form_field( $hidden_fields, $action ) {
		if ( $this->form_action == wpmem_get( 'a', false, 'request' ) ) {
			$hidden_fields = str_replace( 'pwdchange', $this->form_action, $hidden_fields );
			$hidden_fields.= wpmem_create_formfield( $this->reset_key_meta, 'hidden', wpmem_get( 'key', null, 'request' ) );
		}
		return $hidden_fields;
	}
	
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
	 * Change a user's password()
	 * (A custom version of $wpmem->user->password_change().)
	 */
	function change_password( $user_id ) {
		if ( isset( $_POST['formsubmit'] ) ) {
			$args = array(
				'pass1' => wpmem_get( 'pass1', false ),
				'pass2' => wpmem_get( 'pass2', false ),
			);
		}

		$is_error = false;
		// Check for both fields being empty.
		$is_error = ( ! $args['pass1'] && ! $args['pass2'] ) ? "pwdchangempty" : $is_error;
		// Make sure the fields match.
		$is_error = ( $args['pass1'] != $args['pass2'] ) ? "pwdchangerr" : $is_error;
		/**
		 * Filters the password change error.
		 *
		 * @since 3.1.5
		 * @since 3.1.7 Moved to user object.
		 *
		 * @param string $is_error
		 * @param int    $user_id  The user's numeric ID.
		 * @param string $args['pass1']    The user's new plain text password.
		 */
		$is_error = apply_filters( 'wpmem_pwd_change_error', $is_error, $user_id, $args['pass1'] );

		// Verify nonce.
		$is_error = ( ! wp_verify_nonce( $_REQUEST['_wpmem_pwdchange_nonce'], 'wpmem_shortform_nonce' ) ) ? "reg_generic" : $is_error;
		if ( $is_error ) {
			return $is_error;
		}
		wp_set_password( $args['pass1'] , $user_id );
		return "pwdchangesuccess";
	}

	function reset_password_form( $args ) {
		global $wpmem;
		$args['inputs'][0]['name'] = $wpmem->get_text( 'login_username' );
		unset( $args['inputs'][1] );
		return $args;
	}
}