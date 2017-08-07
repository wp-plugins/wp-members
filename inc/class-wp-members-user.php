<?php
/**
 * The WP_Members_User Class.
 *
 * This is the WP_Members User object class. This class contains functions
 * for login, logout, registration and other user related methods.
 *
 * @package WP-Members
 * @subpackage WP_Members_User Object Class
 * @since 3.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

class WP_Members_User {
	
	/**
	 * Container for reg form data.
	 *
	 * @since  3.1.7
	 * @access public
	 * @var    array
	 */
	public $post_data = array();
	
	/**
	 * Initilize the User object.
	 *
	 * @since 3.1.7
	 */
	function __construct() {
		//add_action( 'user_register', array( $this, 'register' ), 9 ); // @todo This need rigorous testing, especially front end processing such as WC.
		add_action( 'wpmem_register_redirect', array( $this, 'register_redirect' ) );
	}
	
	/**
	 * Handle user login.
	 *
	 * Built from, but replaces, the original wpmem_login() function
	 * from core.php. wpmem_login() is currently maintained as a 
	 * wrapper and is the direct function called for login.
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
			/** This filter defined in wp-login.php */
			$redirect_to = apply_filters( 'login_redirect', $redirect_to, '', $user );
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
	 * Built from, but replaces, the original wpmem_logout() function
	 * from core.php. wpmem_logout() is currently maintained as a 
	 * wrapper and is the direct function called for logout.
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
			$this->upload_user_files( $user_id, $wpmem->fields );
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
		 * @since 3.1.0 Added $fields
		 * @since 3.1.7 Changed $fields to $wpmem->user->post_data
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
	
	/**
	 * Password change or reset.
	 *
	 * @since 3.1.7
	 *
	 * @param  string $action
	 * @return string $result
	 */
	function password_update( $action ) {
		if ( isset( $_POST['formsubmit'] ) ) {
			$params = ( 'reset' == $action ) ? array( 'user', 'email' ) : array( 'pass1', 'pass2' );
			$args = array( 
				$params[0] => wpmem_get( $params[0], false ), 
				$params[1] => wpmem_get( $params[1], false ),
			);
			return ( 'reset' == $action ) ? $this->password_reset( $args ) : $this->password_change( $args );
		}
		return;
	}
	
	/**
	 * Change a user's password()
	 *
	 * @since 3.1.7
	 *
	 * @return
	 */
	function password_change( $args ) {
		global $user_ID;
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
		 * @param int    $user_ID  The user's numeric ID.
		 * @param string $args['pass1']    The user's new plain text password.
		 */
		$is_error = apply_filters( 'wpmem_pwd_change_error', $is_error, $user_ID, $args['pass1'] );
		if ( $is_error ) {
			return $is_error;
		}
		// Update user password.
		wp_set_password( $args['pass1'], $user_ID );
		// Maintain login state.
		$user = get_user_by( 'id', $user_ID );
		wp_set_current_user( $user_ID, $user->user_login );
		wp_set_auth_cookie( $user_ID );
		/**
		 * Fires after password change.
		 *
		 * @since 2.9.0
		 * @since 3.0.5 Added $args['pass1'] to arguments passed.
		 * @since 3.1.7 Moved to user object.
		 *
		 * @param int    $user_ID The user's numeric ID.
		 * @param string $args['pass1']   The user's new plain text password.
		 */
		do_action( 'wpmem_pwd_change', $user_ID, $args['pass1'] );
		return "pwdchangesuccess";
	}
	
	/**
	 * Reset a user's password.
	 *
	 * @since 3.1.7
	 *
	 */
	function password_reset( $args ) {
		global $wpmem;
		/**
		 * Filter the password reset arguments.
		 *
		 * @since 2.7.1
		 * @since 3.1.7 Moved to user object.
		 *
		 * @param array The username and email.
		 */
		$arr = apply_filters( 'wpmem_pwdreset_args', $args );
		if ( ! $arr['user'] || ! $arr['email'] ) { 
			// There was an empty field.
			return "pwdreseterr";

		} else {

			if ( username_exists( $arr['user'] ) ) {
				$user = get_user_by( 'login', $arr['user'] );
				if ( strtolower( $user->user_email ) !== strtolower( $arr['email'] ) || ( ( $wpmem->mod_reg == 1 ) && ( get_user_meta( $user->ID, 'active', true ) != 1 ) ) ) {
					// The username was there, but the email did not match OR the user hasn't been activated.
					return "pwdreseterr";
				} else {
					// Generate a new password.
					$new_pass = wp_generate_password();
					// Update the users password.
					wp_set_password( $new_pass, $user->ID );
					// Send it in an email.
					wpmem_inc_regemail( $user->ID, $new_pass, 3 );
					/**
					 * Fires after password reset.
					 *
					 * @since 2.9.0
					 * @since 3.0.5 Added $new_pass to arguments passed.
					 * @since 3.1.7 Moved to user object.
					 *
					 * @param int    $user_ID  The user's numeric ID.
					 * @param string $new_pass The new plain text password.
					 */
					do_action( 'wpmem_pwd_reset', $user->ID, $new_pass );
					return "pwdresetsuccess";
				}
			} else {
				// Username did not exist.
				return "pwdreseterr";
			}
		}
		return;
	}
	
	/**
	 * Handles retrieving a forgotten username.
	 *
	 * @since 3.0.8
	 * @since 3.1.6 Dependencies now loaded by object.
	 * @since 3.1.8 Moved to user object.
	 *
	 * @return string $regchk The regchk value.
	 */
	function retrieve_username() {
		if ( isset( $_POST['formsubmit'] ) ) {
			$email = sanitize_email( $_POST['user_email'] );
			$user  = ( isset( $_POST['user_email'] ) ) ? get_user_by( 'email', $email ) : false;
			if ( $user ) {
				// Send it in an email.
				wpmem_inc_regemail( $user->ID, '', 4 );
				/**
				 * Fires after retrieving username.
				 *
				 * @since 3.0.8
				 *
				 * @param int $user_ID The user's numeric ID.
				 */
				do_action( 'wpmem_get_username', $user->ID );
				return 'usernamesuccess';
			} else {
				return 'usernamefailed';
			}
		}
		return;
	}
	
	/**
	 * Handle user file uploads for registration and profile update.
	 *
	 * @since 3.1.8
	 *
	 * @param string $user_id
	 * @param array  $fields
	 */
	function upload_user_files( $user_id, $fields ) {
		global $wpmem;
		foreach ( $fields as $meta_key => $field ) {
			if ( ( 'file' == $field['type'] || 'image' == $field['type'] ) && is_array( $_FILES[ $meta_key ] ) ) {
				if ( ! empty( $_FILES[ $meta_key ]['name'] ) ) {
					// Upload the file and save it as an attachment.
					$file_post_id = $wpmem->forms->do_file_upload( $_FILES[ $meta_key ], $user_id );
					// Save the attachment ID as user meta.
					update_user_meta( $user_id, $meta_key, $file_post_id );
				}
			}
		}
	}
	
}