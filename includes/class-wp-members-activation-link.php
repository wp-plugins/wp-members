<?php

class WP_Members_Activation_Link {
	
	/**
	 * Meta containers
	 *
	 * @since 3.3.5
	 */
	public $activation_key_meta  = '_wpmem_activation_key';
	public $activation_key_exp   = '_wpmem_activation_exp';
	public $activation_confirm   = '_wpmem_activation_confirm';
	
	function __construct() {
		
		$this->send_welcome    = true;
		$this->show_success    = true;
		$this->send_notify     = true;
		$this->auto_delete     = true;
		
		$this->email_text      = __( 'Click to activate your account: ', 'wp-members' );
		$this->success_message = __( 'Thank you for activating your account.', 'wp-members' );
		$this->expired_message = __( 'Activation key was expired or invalid', 'wp-members' );
		
		add_action( 'wpmem_after_init', array( $this, 'default_to_mod' ) );
		add_action( 'user_register', array( $this, 'generate_key' ) );
		add_filter( 'wpmem_email_filter', array( $this, 'add_key_to_email' ), 10, 3 );
		add_action( 'template_redirect', array( $this, 'validate_key' ) );
		add_filter( 'the_content', array( $this, 'activation_success' ), 100 );
	}
	
	function default_to_mod() {
		global $wpmem;
		$wpmem->mod_reg = 1;
	}
	
	/**
	 * Create an activation key for the
	 * user at registration.
	 */
	function generate_key( $user_id ) {

		// Generate a random key.
		$key = md5( wp_generate_password() );

		// Save this for the new user account.
		add_user_meta( $user_id, $this->activation_key_meta, $key );
		add_user_meta( $user_id, $this->activation_key_exp, time() + 21600 );
	}
	
	// Check if key is expired.
	function key_is_valid( $key, $user_id ) {
		$expires = get_user_meta( $user_id, $this->activation_key_exp, true );	
		return ( time() < $expires ) ? true : false;
	}
	
	/**
	 * Include the activation key in the new user
	 * registration email as an activation link.
	 */
	function add_key_to_email( $arr, $wpmem_fields, $field_data ) {

		/**
		 * Filter the return url
		 *
		 * @since 3.3.5
		 */
		$url = apply_filters( 'wpmem_activation_link_return_url', trailingslashit( wpmem_profile_url() ) );

		// Only do this for new registrations.
		if ( $arr['toggle'] == 'newmod' ) {
			// Get the stored key.
			$key = get_user_meta( $arr['user_id'], $this->activation_key_meta, true );
			$exp = get_user_meta( $arr['user_id'], $this->activation_key_exp, true );
			// Add text and link to the email body.
			$arr['body'] = $arr['body'] . "\r\n"
				. $this->email_text
				. add_query_arg( array( 'a'=>'activate', 'key'=>$key ), $url );
		}

		return $arr;
	}

	/**
	 * Check for an activation key and if one exists,
	 * validate and log in user.
	 */
	function validate_key() {
		
		// Check for activation key.
		$key = ( 'activate' == wpmem_get( 'a', false, 'get' ) ) ? wpmem_get( 'key', false, 'get' ) : false;
		if ( false !== $key ) {

			// Get the user account the key is for.
			$users = get_users( array(
				'meta_key'    => $this->activation_key_meta,
				'meta_value'  => $key,
				'number'      => 1,
				'count_total' => false
			) );

			if ( $users ) {
				foreach( $users as $user ) {

					if ( true === $this->key_is_valid( $key, $user->ID ) ) {

						$this->validated = true;

						// The provided activation key was valid, log in.
						wp_set_auth_cookie( $user->ID, true );
						wp_set_current_user( $user->ID );

						// Delete activation_key meta and set active.
						delete_user_meta( $user->ID, $this->activation_key_meta );
						delete_user_meta( $user->ID, $this->activation_key_exp );
						update_user_meta( $user->ID, $this->activation_confirm, time() );
						update_user_meta( $user->ID, 'active', '1' );

						if ( $this->send_welcome ) {
							// Send a welcome email
							wpmem_email_to_user( $user->ID, '', 2 );
						}

						if ( $this->send_notify ) {
							// Send a welcome email
							global $wpmem;
							wpmem_notify_admin( $user->ID, $wpmem->fields );
						}
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

	function activation_success( $content ) {

		if ( $this->show_success && 'activate' == wpmem_get( 'a', false, 'get' ) && isset( $this->validated ) ) {

			if ( true === $this->validated ) {
				$content = wpmem_inc_regmessage( '', $this->success_message ) . $content;
			} elseif ( false === $this->validated ) {
				$content = wpmem_inc_regmessage( '', $this->expired_message ) . $content;
			}
		}

		return $content;
	}
}