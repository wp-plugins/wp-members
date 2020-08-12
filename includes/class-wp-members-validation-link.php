<?php

class WP_Members_Validation_Link {
	
	/**
	 * Meta containers
	 *
	 * @since 3.3.5
	 */
	public $validation_key_meta  = '_wpmem_validation_key';
	public $validation_key_exp   = '_wpmem_validation_exp';
	public $validation_confirm   = '_wpmem_user_confirmed';
	
	/**
	 * Options.
	 *
	 * @since 3.3.5
	 */
	public $send_welcome = true;
	public $show_success = true;
	public $send_notify  = true;
	public $auto_delete  = true;
	
	/**
	 * Initialize validation link feature.
	 *
	 * @since 3.3.5
	 */
	public function __construct() {
		
		$this->email_text      = __( 'Click to validate your account: ',       'wp-members' );
		$this->success_message = __( 'Thank you for validating your account.', 'wp-members' );
		$this->expired_message = __( 'Validation key was expired or invalid',  'wp-members' );
		
		//add_action( 'wpmem_after_init',   array( $this, 'default_to_mod'   ) );
		add_action( 'user_register',      array( $this, 'generate_key'       ) );
		add_action( 'template_redirect',  array( $this, 'validate_key'       ) );
		add_filter( 'authenticate',       array( $this, 'check_validated'    ), 99, 3 );
		add_filter( 'wpmem_email_filter', array( $this, 'add_key_to_email'   ), 10, 3 );
		add_filter( 'the_content',        array( $this, 'validation_success' ), 100 );
		
		add_action( 'wpmem_account_validation_success', array( $this, 'send_welcome' ) );
		add_action( 'wpmem_account_validation_success', array( $this, 'notify_admin' ) );
	}
	
	/**
	 * Default the site to moderated registration.
	 *
	 * @since 3.3.5
	 *
	 * @todo This may be temporary. Re-evaluate and see if we can/need to make something specific to this feature.
	 */
	public function default_to_mod() {
		global $wpmem;
		$wpmem->mod_reg = 1;
	}
	
	/**
	 * Create a validation key for the user at registration.
	 *
	 * @since 3.3.5
	 *
	 * @param int $user_id
	 */
	public function generate_key( $user_id ) {

		// Generate a random key.
		$key = md5( wp_generate_password() );

		/**
		 * Filter the key expiration.
		 *
		 * @since 3.3.5
		 *
		 * @param string $key_expires
		 */
		$key_expires = apply_filters( 'wpmem_validation_key_exp', ( time() + 21600 ) );
		
		// Save this for the new user account.
		add_user_meta( $user_id, $this->validation_key_meta, $key );
		add_user_meta( $user_id, $this->validation_key_exp, $key_expires );
	}
	
	/**
	 * Check if key is expired.
	 *
	 * @since 3.3.5
	 *
	 * @param  string  $key
	 * @param  int     $user_id
	 * @return boolean
	 */
	private function key_is_valid( $key, $user_id ) {
		$expires = get_user_meta( $user_id, $this->validation_key_exp, true );	
		return ( time() < $expires ) ? true : false;
	}
	
	/**
	 * Include the validation key in the new user registration email as a validation link.
	 *
	 * @since 3.3.5
	 *
	 * @global stdClass $wpmem
	 * @param  array    $arr
	 * @param  array    $wpmem_fields
	 * @param  array    $field_data
	 * @return array
	 */
	public function add_key_to_email( $arr, $wpmem_fields, $field_data ) {

		global $wpmem;
		
		/**
		 * Filter the return url
		 *
		 * @since 3.3.5
		 */
		$url = apply_filters( 'wpmem_validation_link_return_url', trailingslashit( wpmem_profile_url() ) );

		$key  = get_user_meta( $arr['user_id'], $this->validation_key_meta, true );
		$exp  = get_user_meta( $arr['user_id'], $this->validation_key_exp,  true );
		$link = add_query_arg( array( 'a'=>'activate', 'key'=>$key ), $url );
		
		// Only do this for new registrations.
		$email_type = ( 1 == $wpmem->mod_reg ) ? 'newmod' : 'newreg';
		if ( $arr['toggle'] == $email_type ) {
			// Does email body have the [act_link] shortcode?
			if ( strpos( $arr['body'], '[confirm_link]' ) ) {
				$arr['body'] = str_replace( '[confirm_link]', $link, $arr['body'] );
			} else {
			// Add text and link to the email body.
			$arr['body'] = $arr['body'] . "\r\n"
				. $this->email_text
				. $link;
			}
		}

		return $arr;
	}

	/**
	 * Check for a validation key and if one exists, validate and log in user.
	 *
	 * @since 3.3.5
	 */
	public function validate_key() {
		
		// Check for validation key.
		$key = ( 'activate' == wpmem_get( 'a', false, 'get' ) ) ? wpmem_get( 'key', false, 'get' ) : false;
		if ( false !== $key ) {

			// Get the user account the key is for.
			$users = get_users( array(
				'meta_key'    => $this->validation_key_meta,
				'meta_value'  => $key,
				'number'      => 1,
				'count_total' => false
			) );

			if ( $users ) {
				foreach( $users as $user ) {

					if ( true === $this->key_is_valid( $key, $user->ID ) ) {

						$this->validated = true;

						// The provided validation key was valid, log in.
						wp_set_auth_cookie( $user->ID, true );
						wp_set_current_user( $user->ID );

						// Delete validation_key meta and set active.
						$this->set_as_confirmed( $user->ID );
						
						/**
						 * Fires when a user has successfully validated their account.
						 *
						 * @since 3.3.5
						 *
						 * @param int $user_id
						 */
						do_action( 'wpmem_account_validation_success', $user->ID );

						break;
						
					} else {
						$this->validated = false;
						break;
					}
				}

			} else {
				$this->validated = false;
			}
		}
	}

	/**
	 * Display messaging.
	 *
	 * Shows success if key validates, expired if it does not.
	 *
	 * @since 3.3.5
	 *
	 * @param  string  $content
	 * @return string  $content
	 */
	public function validation_success( $content ) {

		if ( $this->show_success && 'activate' == wpmem_get( 'a', false, 'get' ) && isset( $this->validated ) ) {

			if ( true === $this->validated ) {
				$content = wpmem_inc_regmessage( '', $this->success_message ) . $content;
			} elseif ( false === $this->validated ) {
				$content = wpmem_inc_regmessage( '', $this->expired_message ) . $content;
			}
		}

		return $content;
	}

	/**
	 * Checks if a user is activated during user authentication.
	 *
	 * @since 3.3.5 Moved from core to user object.
	 *
	 * @param  object $user     The WordPress User object.
	 * @param  string $username The user's username (user_login).
	 * @param  string $password The user's password.
	 * @return object $user     The WordPress User object.
	 */ 
	function check_validated( $user, $username, $password ) {
		// Password must be validated.
		$pass = ( ( ! is_wp_error( $user ) ) && $password ) ? wp_check_password( $password, $user->user_pass, $user->ID ) : false;

		if ( ! $pass ) { 
			return $user;
		}

		// Validation flag must be confirmed.
		$validated = get_user_meta( $user->ID, $this->validation_confirm, true );
		if ( false == $validated ) {
			return new WP_Error( 'authentication_failed', __( '<strong>ERROR</strong>: User has not confirmed their account.', 'wp-members' ) );
		}

		// If the user is validated, return the $user object.
		return $user;
	}
	
	public function send_welcome( $user_id ) {
		if ( $this->send_welcome ) {
			wpmem_email_to_user( $user_id, '', 2 );
		}
	}
	
	public function notify_admin( $user_id ) {
		if ( $this->send_notify ) {
			// global $wpmem;
			wpmem_notify_admin( $user_id ); //, $wpmem->fields );
		}	
	}
	
	public function set_as_confirmed( $user_id ) {
		delete_user_meta( $user_id, $this->validation_key_meta );
		delete_user_meta( $user_id, $this->validation_key_exp );
		update_user_meta( $user_id, $this->validation_confirm, time() );
	}
}