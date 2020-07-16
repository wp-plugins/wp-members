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
	 * Initialize activation link feature.
	 *
	 * @since 3.3.5
	 */
	public function __construct() {
		
		$this->email_text      = __( 'Click to activate your account: ',       'wp-members' );
		$this->success_message = __( 'Thank you for activating your account.', 'wp-members' );
		$this->expired_message = __( 'Activation key was expired or invalid',  'wp-members' );
		
		add_action( 'wpmem_after_init',   array( $this, 'default_to_mod'     ) );
		add_action( 'user_register',      array( $this, 'generate_key'       ) );
		add_action( 'template_redirect',  array( $this, 'validate_key'       ) );
		add_filter( 'wpmem_email_filter', array( $this, 'add_key_to_email'   ), 10, 3 );
		add_filter( 'the_content',        array( $this, 'activation_success' ), 100 );
		
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
	 * Create an activation key for the user at registration.
	 *
	 * @since 3.3.5
	 *
	 * @param int $user_id
	 */
	public function generate_key( $user_id ) {

		// Generate a random key.
		$key = md5( wp_generate_password() );

		// Save this for the new user account.
		add_user_meta( $user_id, $this->activation_key_meta, $key );
		add_user_meta( $user_id, $this->activation_key_exp, time() + 21600 );
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
		$expires = get_user_meta( $user_id, $this->activation_key_exp, true );	
		return ( time() < $expires ) ? true : false;
	}
	
	/**
	 * Include the activation key in the new user registration email as an activation link.
	 *
	 * @since 3.3.5
	 *
	 * @param  array   $arr
	 * @param  array   $wpmem_fields
	 * @param  array   $field_data
	 * @return array
	 */
	public function add_key_to_email( $arr, $wpmem_fields, $field_data ) {

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
	 * Check for an activation key and if one exists, validate and log in user.
	 *
	 * @since 3.3.5
	 */
	public function validate_key() {
		
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
	public function activation_success( $content ) {

		if ( $this->show_success && 'activate' == wpmem_get( 'a', false, 'get' ) && isset( $this->validated ) ) {

			if ( true === $this->validated ) {
				$content = wpmem_inc_regmessage( '', $this->success_message ) . $content;
			} elseif ( false === $this->validated ) {
				$content = wpmem_inc_regmessage( '', $this->expired_message ) . $content;
			}
		}

		return $content;
	}
	
	public function send_welcome( $user_id ) {
		if ( $this->send_welcome ) {
			wpmem_email_to_user( $user->ID, '', 2 );
		}
	}
	
	public function notify_admin( $user_id ) {
		if ( $this->send_notify ) {
			// global $wpmem;
			wpmem_notify_admin( $user->ID ); //, $wpmem->fields );
		}	
	}
}