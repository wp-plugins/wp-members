<?php
/**
 * The WP_Members_User Class.
 *
 * This is the main WP_Members object class. This class contains functions
 * for loading settings, shortcodes, hooks to WP, plugin dropins, constants,
 * and registration fields. It also manages whether content should be blocked.
 *
 * @package WP-Members
 * @subpackage WP_Members Object Class
 * @since 3.0.0
 */

class WP_Members_User {
	
	public $post_data = array();
	
	/**
	 * Initilize the User object.
	 *
	 * @since 3.1.7
	 */
	function __construct() {
		add_action( 'user_register', array( $this, 'register' ), 9 );
		add_action( 'wpmem_register_redirect', array( $this, 'register_redirect' ) );
	}
	
	/**
	 * Handle user login.
	 *
	 * @since 3.1.7
	 *
	 * @return string Returns "loginfailed" if failed login.
	 */
	function login() {
		
		$creds = array( 'user_login' => 'log', 'user_password' => 'pwd', 'remember' => 'rememberme', 'redirect_to' => 'redirect_to' );
		/**
		 * Filter the $fields the function handles.
		 *
		 * @since 3.1.7
		 *
		 * @param array $creds
		 */
		$creds = apply_filters( 'wpmem_login_fields', $creds );
		foreach ( $creds as $key => $val ) {
			$creds[ $key ] = ( 'user_login' == $key ) ? sanitize_user( wpmem_get( $val ) ) : wpmem_get( $val );
		}

		$user = wp_signon( $creds, is_ssl() );

		if ( is_wp_error( $user ) ) {
			return "loginfailed";
		} else {
			$redirect_to = wpmem_get( 'redirect_to', false );
			$redirect_to = ( $redirect_to ) ? esc_url_raw( trim( $redirect_to ) ) : esc_url_raw( wpmem_current_url() );
			/**
			 * Filter the redirect url.
			 *
			 * @since 2.7.7
			 *
			 * @param string $redirect_to The url to direct to.
			 * @param int    $user->ID    The user's primary key ID.
			 */
			$redirect_to = apply_filters( 'wpmem_login_redirect', $redirect_to, $user->ID );
			wp_redirect( $redirect_to );
			exit();
		}
	}
	
	/**
	 * Handle user logout.
	 *
	 * @since 3.1.7
	 *
	 * @param string $redirect_to URL to redirect the user to (default: false).
	 */
	function logout( $redirect_to = false ) {
		// Default redirect URL.
		$redirect_to = ( $redirect_to ) ? $redirect_to : home_url();

		/**
		 * Filter where the user goes when logged out.
		 *
		 * @since 2.7.1
		 * @since 3.1.7 Moved to WP_Members_Users Class.
		 *
		 * @param string The blog home page.
		 */
		$redirect_to = apply_filters( 'wpmem_logout_redirect', $redirect_to );

		wp_destroy_current_session();
		wp_clear_auth_cookie();

		/** This action is defined in /wp-includes/pluggable.php. */
		do_action( 'wp_logout' );

		wp_redirect( $redirect_to );
		exit();
	}
	
	/**
	 * User registration functions.
	 *
	 * @since 3.1.7
	 *
	 * @global object $wpmem
	 * @param  int    $user_id
	 */
	function register( $user_id ) {
		
		global $wpmem;
		
		// Put user ID into post_data array.
		$wpmem->user->post_data['ID'] = $user_id;
		
		// Set remaining fields to wp_usermeta table.
		$new_user_fields_meta = array( 'user_url', 'first_name', 'last_name', 'description', 'jabber', 'aim', 'yim' );
		foreach ( $wpmem->fields as $meta_key => $field ) {
			// If the field is not excluded, update accordingly.
			if ( ! in_array( $meta_key, $wpmem->excluded_meta ) && ! in_array( $meta_key, $new_user_fields_meta ) ) {
				if ( $field['register'] && 'user_email' != $meta_key ) {
					update_user_meta( $user_id, $meta_key, $this->post_data[ $meta_key ] );
				}
			}
		}

		// Capture IP address of user at registration.
		update_user_meta( $user_id, 'wpmem_reg_ip', $this->post_data['wpmem_reg_ip'] );

		// Store the registration url.
		update_user_meta( $user_id, 'wpmem_reg_url', $this->post_data['wpmem_reg_url'] );

		// Set user expiration, if used.
		if ( $wpmem->use_exp == 1 && $wpmem->mod_reg != 1 ) {
			if ( function_exists( 'wpmem_set_exp' ) ) {
				wpmem_set_exp( $user_id );
			}
		}

		// Handle file uploads, if any.
		if ( ! empty( $_FILES ) ) {

			foreach ( $wpmem->fields as $meta_key => $field ) {

				if ( ( 'file' == $field['type'] || 'image' == $field['type'] ) && is_array( $_FILES[ $meta_key ] ) ) {

					// Upload the file and save it as an attachment.
					$file_post_id = $wpmem->forms->do_file_upload( $_FILES[ $meta_key ], $user_id );

					// Save the attachment ID as user meta.
					update_user_meta( $user_id, $meta_key, $file_post_id );
				}
			}
		}

		/**
		 * Fires after user insertion but before email.
		 *
		 * @since 2.7.2
		 *
		 * @param array $this->post_data The user's submitted registration data.
		 */
		do_action( 'wpmem_post_register_data', $this->post_data );

		// Send a notification email to the user.
		wpmem_inc_regemail( $user_id, $this->post_data['password'], $wpmem->mod_reg, $wpmem->fields, $this->post_data );

		// Notify admin of new reg, if needed.
		if ( $wpmem->notify == 1 ) { 
			wpmem_notify_admin( $user_id, $wpmem->fields, $this->post_data );
		}
		
		/**
		 * Fires after registration is complete.
		 *
		 * @since 2.7.1
		 * @since 3.1.0 Added $wpmem->user->post_data
		 * @since 3.1.7 Changed $wpmem->user->post_data to $wpmem->user->post_data
		 */
		do_action( 'wpmem_register_redirect', $this->post_data );

	}
	
	/**
	 * Redirects user on registration.
	 *
	 * @since 3.1.7
	 */
	function register_redirect() {
		$redirect_to = wpmem_get( 'redirect_to', false );
		if ( $redirect_to ) {
			$nonce_url = wp_nonce_url( $redirect_to, 'register_redirect', 'reg_nonce' );
			wp_redirect( $nonce_url );
			exit();
		}
	}
	
}