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
	 * Container for user access information.
	 *
	 * @since  3.2.0
	 * @access public
	 * @var    array
	 */
	public $access = array();
	
	/**
	 * Initilize the User object.
	 *
	 * @since 3.1.7
	 *
	 * @param object $settings The WP_Members Object
	 */
	function __construct( $settings ) {
		//add_action( 'user_register', array( $this, 'register' ), 9 ); // @todo This need rigorous testing, especially front end processing such as WC.
		add_action( 'wpmem_register_redirect', array( $this, 'register_redirect' ) );
	
		// Load anything the user as access to.
		if ( 1 == $settings->enable_products ) {
			$this->access = $this->get_user_products();
		}
	}
	
	/**
	 * Handle user login.
	 *
	 * Built from, but replaces, the original wpmem_login() function
	 * from core.php. wpmem_login() is currently maintained as a 
	 * wrapper and is the direct function called for login.
	 *
	 * @since 3.1.7
	 * @since 3.2.3 Removed wpmem_login_fields filter.
	 * @since 3.2.3 Replaced form collection with WP script to facilitate login with username OR email.
	 * @since 3.2.3 Changed to wp_safe_redirect().
	 *
	 * @return string Returns "loginfailed" if failed login.
	 */
	function login() {
		
		global $wpmem;
		
		if ( ! empty( $_POST['log'] ) && ! force_ssl_admin() ) {
			$user_name = sanitize_user( $_POST['log'] );
			$user = get_user_by( 'login', $user_name );

			if ( ! $user && strpos( $user_name, '@' ) ) {
				$user = get_user_by( 'email', $user_name );
			}
		}

		$user = wp_signon( array(), is_ssl() );

		if ( is_wp_error( $user ) ) {
			$wpmem->error = $user->get_error_message();
			return "loginfailed";
		} else {
			$redirect_to = wpmem_get( 'redirect_to', false );
			$redirect_to = ( $redirect_to ) ? esc_url_raw( trim( $redirect_to ) ) : esc_url_raw( wpmem_current_url() );
			/** This filter defined in wp-login.php */
			$redirect_to = apply_filters( 'login_redirect', $redirect_to, '', $user );
			/**
			 * Filter the redirect url.
			 *
			 * This is the plugin's original redirect filter. In 3.1.7, 
			 * WP's login_redirect filter hook was added to provide better
			 * integration support for other plugins and also for users
			 * who may already be using WP's filter(s). login_redirect
			 * comes first, then wpmem_login_redirect. So wpmem_login_redirect
			 * can be used to override a default in login_redirect.
			 *
			 * @since 2.7.7
			 *
			 * @param string $redirect_to The url to direct to.
			 * @param int    $user->ID    The user's primary key ID.
			 */
			$redirect_to = apply_filters( 'wpmem_login_redirect', $redirect_to, $user->ID );
			wp_safe_redirect( $redirect_to );
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
	 * @since 3.2.0 Added logout_redirect filter
	 *
	 * @param string $redirect_to URL to redirect the user to (default: false).
	 */
	function logout( $redirect_to = false ) {
		// Default redirect URL.
		$redirect_to = ( $redirect_to ) ? $redirect_to : home_url();

		/** This filter is documented in /wp-login.php */
		$redirect_to = apply_filters( 'logout_redirect', $redirect_to, $redirect_to, wp_get_current_user() );
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
		$wpmem->email->to_user( $user_id, $this->post_data['password'], $wpmem->mod_reg, $wpmem->fields, $this->post_data );

		// Notify admin of new reg, if needed.
		if ( $wpmem->notify == 1 ) { 
			$wpmem->email->notify_admin( $user_id, $wpmem->fields, $this->post_data );
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
					$wpmem->email->to_user( $user->ID, $new_pass, 3 );
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
	 * @global object $wpmem
	 * @return string $regchk The regchk value.
	 */
	function retrieve_username() {
		global $wpmem;
		if ( isset( $_POST['formsubmit'] ) ) {
			$email = sanitize_email( $_POST['user_email'] );
			$user  = ( isset( $_POST['user_email'] ) ) ? get_user_by( 'email', $email ) : false;
			if ( $user ) {
				// Send it in an email.
				$wpmem->email->to_user( $user->ID, '', 4 );
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
			if ( ( 'file' == $field['type'] || 'image' == $field['type'] ) && isset( $_FILES[ $meta_key ] ) && is_array( $_FILES[ $meta_key ] ) ) {
				if ( ! empty( $_FILES[ $meta_key ]['name'] ) ) {
					// Upload the file and save it as an attachment.
					$file_post_id = $wpmem->forms->do_file_upload( $_FILES[ $meta_key ], $user_id );
					// Save the attachment ID as user meta.
					update_user_meta( $user_id, $meta_key, $file_post_id );
				}
			}
		}
	}
	
	/**
	 * Get user data for all fields in WP-Members.
	 *
	 * Retrieves user data for all WP-Members fields (and WP default fiels)
	 * in an array keyed by WP-Members field meta keys.
	 *
	 * @since 3.2.0
	 *
	 * @param  mixed $user_id
	 * @return array $user_fields 
	 */
	function user_data( $user_id = false ) {
		$fields = wpmem_fields();
		$user_id = ( $user_id ) ? $user_id : get_current_user_id();
		$user_data = get_userdata( $user_id );
		$excludes = array( 'first_name', 'last_name', 'description', 'nickname' );
		foreach ( $fields as $meta => $field ) {
			if ( $field['native'] == 1 && ! in_array( $meta, $excludes ) ) {
				$user_fields[ $meta ] = $user_data->data->$meta;
			} else {
				$user_fields[ $meta ] = get_user_meta( $user_id, $meta, true );
			}
		}
		return $user_fields;
	}
	
	/**
	 * Sets the role for the specified user.
	 *
	 * @since 3.2.0
	 *
	 * @param integer $user_id
	 * @param string  $role
	 * @param string  $action (set|add|remove)
	 */
	public function update_user_role( $user_id, $role, $action = 'set' ) {
		$user = new WP_User( $user_id );
		switch ( $action ) {
			case 'add':
				$user->add_role( $role );
				break;
			case 'remove':
				$user->remove_role( $role );
				break;
			default:
				$user->set_role( $role );
				break;
		}
	}
	
	/**
	 * Sets a user's password.
	 *
	 * @since 3.2.3
	 *
	 * @param	int		$user_id
	 * @param	string	$password
	 */
	function set_password( $user_id, $password ) {
		wp_set_password( $password, $user_id );
	}
	
	/**
	 * Sets user as logged on password change.
	 *
	 * (Hooked to wpmem_pwd_change)
	 *
	 * @since 3.2.0
	 *
	 * @param	int		$user_id
	 * @param	string	$password
	 */
	function set_as_logged_in( $user_id ) {
		$user = get_user_by( 'id', $user_id );
		wp_set_current_user( $user_id, $user->user_login );
		wp_set_auth_cookie( $user_id );
	}
	
	/**
	 * Validates user access to content.
	 *
	 * @since 3.2.0
	 *
	 * @global object $wpmem
	 * @param  mixed  $product
	 * @param  int    $user_id (optional)
	 * @return bool   $access
	 */
	function has_access( $product, $user_id = false ) {
		global $wpmem;
		if ( ! is_user_logged_in() ) {
			return false;
		}
		$user_id = ( ! $user_id ) ? get_current_user_id() : $user_id; //echo '<pre>'; global $wpmem; print_r( $wpmem ); 
		$access  = false;
		foreach ( $product as $prod ) {
			if ( isset( $this->access[ $prod ] ) ) {
				// Is this an expiration product?
				if ( isset( $wpmem->membership->products[ $prod ]['expires'][0] ) && ! is_bool( $this->access[ $prod ] ) ) {
					if ( $this->is_current( $this->access[ $prod ] ) ) {
						$access = true;
						break;
					}
				} elseif ( '' != $wpmem->membership->products[ $prod ]['role'] ) {
					if ( $this->access[ $prod ] && wpmem_user_has_role( $wpmem->membership->products[ $prod ]['role'] ) ) {
						$access = true;
						break;
					}
				} else {
					if ( $this->access[ $prod ] ) {
						$access = true;
						break;
					}
				}
			}
		}
		
		/**
		 * Filter the access result.
		 *
		 * @since 3.2.0
		 * @since 3.2.3 Added $product argument.
		 *
		 * @param  boolean $access
		 * @param  mixed   $product
		 * @param  integer $user_id
		 * @param  array   $args
		 */
		return apply_filters( 'wpmem_user_has_access', $access, $product, $user_id );

	}
	
	/**
	 * Loads anything the user has access to.
	 *
	 * @since 3.2.0
	 *
	 * @param int $user_id
	 */
	function get_user_products( $user_id = false ) {
		$user_id = ( ! $user_id ) ? get_current_user_id() : $user_id;
		return get_user_meta( $user_id, '_wpmem_products', true );
	}
	
	/**
	 * Sets a product as active for a user.
	 *
	 * If the product expires, it sets an expiration date
	 * based on the time period. Otherwise the value is
	 * set to "true" (which does not expire).
	 *
	 * @since 3.2.0
	 *
	 * @param string $product
	 * @param int    $user_id
	 */
	function set_user_product( $product, $user_id = false ) {

		global $wpmem;
		
		$user_id = ( ! $user_id ) ? get_current_user_id() : $user_id;
		$user_products = $this->get_user_products( $user_id );
		
		if ( ! $user_products ) {
			$user_products = array();
		}

		// Convert date to add.
		$expires = ( isset( $wpmem->membership->products[ $product ]['expires'] ) ) ? $wpmem->membership->products[ $product ]['expires'] : false;
		
		if ( is_array( $expires ) ) {
			$add_date = explode( "|", $wpmem->membership->products[ $product ]['expires'][0] );
			$add = ( 1 < $add_date[0] ) ? $add_date[0] . " " . $add_date[1] . "s" : $add_date[0] . " " . $add_date[1];
			$user_products[ $product ] = ( isset( $user_products[ $product ] ) ) ? date( 'Y-m-d H:i:s', strtotime( $add, strtotime( $user_products[ $product ] ) ) ) : date( 'Y-m-d H:i:s', strtotime( $add ) );
		} else {
			$user_products[ $product ] = true;
		}
		//echo '<pre>'; print_r( $user_products ); echo "</pre>";
		
		// Update product setting.
		return update_user_meta( $user_id, '_wpmem_products', $user_products );
	}
	
	/**
	 * Removes a product from a user.
	 *
	 * @since 3.2.0
	 *
	 * @param string $product
	 * @param int    $user_id
	 */
	function remove_user_product( $product, $user_id = false ) {
		global $wpmem;
		$user_id = ( ! $user_id ) ? get_current_user_id() : $user_id;
		$user_products = $this->get_user_products( $user_id );
		if ( $user_products ) {
			unset( $user_products[ $product ] );
			update_user_meta( $user_id, '_wpmem_products', $user_products );
		}
		return;
	}
	
	/**
	 * Utility for expiration validation.
	 *
	 * @since 3.2.0
	 *
	 * @param date $date
	 */
	function is_current( $date ) {
		return ( time() < strtotime( $date ) ) ? true : false;
	}
	
	/**
	 * Check if a user is activated.
	 *
	 * @since 3.2.2
	 *
	 * @param  int   $user_id
	 * @return bool  $active
	 */
	function is_user_activated( $user_id = false ) {
		$user_id = ( ! $user_id ) ? get_current_user_id() : $user_id;
		$active  = get_user_meta( $user_id, 'active', true );
		return ( $active != 1 ) ? false : true;
	}

	/**
	 * Checks if a user is activated during user authentication.
	 *
	 * @since 2.7.1
	 * @since 3.2.0 Moved from core to user object.
	 *
	 * @param  object $user     The WordPress User object.
	 * @param  string $username The user's username (user_login).
	 * @param  string $password The user's password.
	 * @return object $user     The WordPress User object.
	 */ 
	function check_activated( $user, $username, $password ) {
		// Password must be validated.
		$pass = ( ( ! is_wp_error( $user ) ) && $password ) ? wp_check_password( $password, $user->user_pass, $user->ID ) : false;

		if ( ! $pass ) { 
			return $user;
		}

		// Activation flag must be validated.
		if ( ! $this->is_user_activated( $user->ID ) ) {
			return new WP_Error( 'authentication_failed', __( '<strong>ERROR</strong>: User has not been activated.', 'wp-members' ) );
		}

		// If the user is validated, return the $user object.
		return $user;
	}
	
	/**
	 * Prevents users not activated from resetting their password.
	 *
	 * @since 2.5.1
	 * @since 3.2.0 Moved to user object, renamed no_reset().
	 *
	 * @return bool Returns false if the user is not activated, otherwise true.
	 */
	function no_reset() {
		global $wpmem;
		$raw_val = wpmem_get( 'user_login', false );
		if ( $raw_val ) {
			if ( strpos( $raw_val, '@' ) ) {
				$user = get_user_by( 'email', sanitize_email( $raw_val ) );
			} else {
				$username = sanitize_user( $raw_val );
				$user     = get_user_by( 'login', $username );
			}
			if ( $wpmem->mod_reg == 1 ) { 
				if ( get_user_meta( $user->ID, 'active', true ) != 1 ) {
					return false;
				}
			}
		}

		return true;
	}

}