<?php
/**
 * The WP_Members_Dialogs Class.
 *
 * This class contains  functions for handling dialongs and messaging..
 *
 * @package WP-Members
 * @subpackage WP_Members_Dialogs Object Class
 * @since 3.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

class WP_Members_Dialogs {
	
	function __construct() {
		
	}
	
	/**
	 * Returns a requested text string.
	 *
	 * This function manages all of the front-end facing text.
	 * All defaults can be filtered using wpmem_default_text_strings.
	 *
	 * @since 3.1.0
	 *
	 * @global object $wpmem
	 *
	 * @param  string $str
	 * @return string $text
	 */	
	function get_text( $str ) {
		global $wpmem;
		
		// Default Form Fields.
		$default_form_fields = array(
			'first_name'       => __( 'First Name', 'wp-members' ),
			'last_name'        => __( 'Last Name', 'wp-members' ),
			'addr1'            => __( 'Address 1', 'wp-members' ),
			'addr2'            => __( 'Address 2', 'wp-members' ),
			'city'             => __( 'City', 'wp-members' ),
			'thestate'         => __( 'State', 'wp-members' ),
			'zip'              => __( 'Zip', 'wp-members' ),
			'country'          => __( 'Country', 'wp-members' ),
			'phone1'           => __( 'Day Phone', 'wp-members' ),
			'user_email'       => __( 'Email', 'wp-members' ),
			'confirm_email'    => __( 'Confirm Email', 'wp-members' ),
			'user_url'         => __( 'Website', 'wp-members' ),
			'description'      => __( 'Biographical Info', 'wp-members' ),
			'password'         => __( 'Password', 'wp-members' ),
			'confirm_password' => __( 'Confirm Password', 'wp-members' ),
			'tos'              => __( 'TOS', 'wp-members' ),
		);
		
		/*
		 * Strings to be added or removed in future versions, included so they will
		 * be in the translation template.
		 * @todo Check whether any of these should be removed.
		 */
		$benign_strings = array(
			__( 'No fields selected for deletion', 'wp-members' ),
			__( 'You are not logged in.', 'wp-members' ), // Technically removed 3.5
		);
	
		$defaults = array(
			
			// Login form.
			'login_heading'        => __( 'Existing Users Log In', 'wp-members' ),
			'login_username'       => __( 'Username or Email', 'wp-members' ),
			'login_password'       => __( 'Password', 'wp-members' ),
			'login_button'         => __( 'Log In', 'wp-members' ),
			'remember_me'          => __( 'Remember Me', 'wp-members' ),
			'forgot_link_before'   => __( 'Forgot password?', 'wp-members' ) . '&nbsp;',
			'forgot_link'          => __( 'Click here to reset', 'wp-members' ),
			'register_link_before' => __( 'New User?', 'wp-members' ) . '&nbsp;',
			'register_link'        => __( 'Click here to register', 'wp-members' ),
			
			// Password change form.
			'pwdchg_heading'       => __( 'Change Password', 'wp-members' ),
			'pwdchg_password1'     => __( 'New password', 'wp-members' ),
			'pwdchg_password2'     => __( 'Confirm new password', 'wp-members' ),
			'pwdchg_button'        => __( 'Update Password', 'wp-members' ),
			'pwdchg_update'        => __( 'Password successfully changed!', 'wp-members' ),
			
			// Password reset form.
			'pwdreset_heading'     => __( 'Reset Forgotten Password', 'wp-members' ),
			'pwdreset_username'    => __( 'Username', 'wp-members' ),
			'pwdreset_email'       => __( 'Email', 'wp-members' ),
			'pwdreset_button'      => __( 'Reset Password' ),
			'username_link_before' => __( 'Forgot username?', 'wp-members' ) . '&nbsp;',
			'username_link'        => __( 'Click here', 'wp-members' ),
			
			// Retrieve username form.
			'username_heading'     => __( 'Retrieve username', 'wp-members' ),
			'username_email'       => __( 'Email Address', 'wp-members' ),
			'username_button'      => __( 'Retrieve username', 'wp-members' ),
			
			// Register form.
			'register_heading'     => __( 'New User Registration', 'wp-members' ),
			'register_username'    => __( 'Choose a Username', 'wp-members' ),
			'register_rscaptcha'   => __( 'Input the code:', 'wp-members' ),
			'register_tos'         => __( 'Please indicate that you agree to the %s Terms of Service %s', 'wp-members' ), // @note: if default changes, default check after wpmem_tos_link_txt must change.
			'register_clear'       => __( 'Reset Form', 'wp-members' ),
			'register_submit'      => __( 'Register', 'wp-members' ),
			'register_req_mark'    => '<span class="req">*</span>',
			'register_required'    => '<span class="req">*</span>' . __( 'Required field', 'wp-members' ),
			
			// User profile update form.
			'profile_heading'      => __( 'Edit Your Information', 'wp-members' ),
			'profile_username'     => __( 'Username', 'wp-members' ),
			'profile_submit'       => __( 'Update Profile', 'wp-members' ),
			'profile_upload'       => __( 'Update this file', 'wp-members' ),
			
			// Error messages and dialogs.
			'login_failed_heading' => __( 'Login Failed!', 'wp-members' ), // @deprecated 3.4.0
			'login_failed'         => __( 'You entered an invalid username or password.', 'wp-members' ),
			'login_failed_link'    => __( 'Click here to continue.', 'wp-members' ), // @deprecated 3.4.0
			'pwdchangempty'        => __( 'Password fields cannot be empty', 'wp-members' ),
			'usernamefailed'       => __( 'Sorry, that email address was not found.', 'wp-members' ),
			'usernamesuccess'      => __( 'An email was sent to %s with your username.', 'wp-members' ),
			'reg_empty_field'      => __( 'Sorry, %s is a required field.', 'wp-members' ),
			'reg_valid_email'      => __( 'You must enter a valid email address.', 'wp-members' ),
			'reg_non_alphanumeric' => __( 'The username cannot include non-alphanumeric characters.', 'wp-members' ),
			'reg_empty_username'   => __( 'Sorry, username is a required field', 'wp-members' ),
			'reg_username_taken'   => __( 'Sorry, that username is taken, Please try another.', 'wp-members' ),
			'reg_email_taken'      => __( 'Sorry, that email address already has an account. Please try another.', 'wp-members' ),
			'reg_password_match'   => __( 'Passwords did not match.', 'wp-members' ),
			'reg_email_match'      => __( 'Emails did not match.', 'wp-members' ),
			'reg_empty_captcha'    => __( 'You must complete the CAPTCHA form.', 'wp-members' ),
			'reg_invalid_captcha'  => __( 'CAPTCHA was not valid.', 'wp-members' ),
			'reg_generic'          => __( 'There was an error processing the form.', 'wp-members' ),
			'reg_captcha_err'      => __( 'There was an error with the CAPTCHA form.', 'wp-members' ),
			'reg_file_type'        => __( 'Sorry, you can only upload the following file types for the %s field: %s.', 'wp-members' ),
			'profile_update'       => __( 'Your information was updated!', 'wp-members' ),
			
			// Links.
			'profile_edit'         => __( 'Edit My Information', 'wp-members' ),
			'profile_password'     => __( 'Change Password', 'wp-members' ),
			'register_status'      => __( 'You are logged in as %s', 'wp-members' ),
			'register_logout'      => __( 'Log out', 'wp-members' ),
			'register_continue'    => ( isset( $wpmem->user_pages['profile'] ) && '' != $wpmem->user_pages['profile'] ) ? __( 'Edit profile', 'wp-members' ) : __( 'Begin using the site.', 'wp-members' ),
			'login_welcome'        => __( 'You are logged in as %s', 'wp-members' ),
			'login_logout'         => __( 'Click to log out', 'wp-members' ),
			'status_welcome'       => __( 'You are logged in as %s', 'wp-members' ),
			'status_logout'        => __( 'click to log out', 'wp-members' ),
			'menu_logout'          => __( 'Log Out', 'wp-members' ),
			
			// Widget.
			'widget_status'         => __( 'You are logged in as %s', 'wp-members' ),
			'widget_logout'         => __( 'click here to log out', 'wp-members' ),
			'widget_login_failed'   => __( 'Login Failed!<br />You entered an invalid username or password.', 'wp-members' ),
			'widget_login_failed_new' => __( 'Invalid username or password.', 'wp-members' ), // @todo New string replacement. Replace widget_login_failed when translated.
			'widget_not_logged_in'  => '',
			'widget_login_username' => __( 'Username or Email', 'wp-members' ),
			'widget_login_password' => __( 'Password', 'wp-members' ),
			'widget_login_button'   => __( 'log in', 'wp-members' ),
			'widget_login_forgot'   => __( 'Forgot?', 'wp-members' ),
			'widget_login_register' => __( 'Register', 'wp-members' ),
			
			// Default Dialogs.
			'restricted_msg'       => __( "This content is restricted to site members.  If you are an existing user, please log in.  New users may register below.", 'wp-members' ),
			'success'              => __( "Congratulations! Your registration was successful.<br /><br />You may now log in using the password that was emailed to you.", 'wp-members' ),
			
			// @todo Under consideration for removal from the Dialogs tab.
			'user'                 => __( "Sorry, that username is taken, please try another.", 'wp-members' ),
			'email'                => __( "Sorry, that email address already has an account.<br />Please try another.", 'wp-members' ),
			'editsuccess'          => __( "Your information was updated!", 'wp-members' ),
			
			// @todo These are defaults and are under consideration for removal from the dialogs tab, possibly as we change the password reset to a link based process.
			'pwdchangerr'          => __( "Passwords did not match.", 'wp-members' ),
			'pwdchangesuccess'     => __( "Password successfully changed.", 'wp-members' ),
			'pwdreseterr'          => __( "Invalid username or email address.", 'wp-members' ),
			'pwdresetsuccess'      => __( "Password successfully reset! An email containing a new password has been sent to the email address on file for your account.", 'wp-members' ),
			'pwdresetsuccess_alt'  => __( "Reset request received. An email with instructions to complete the password reset has been sent.", 'wp-members' ),
			
			'acct_not_approved'    => __( "Your account request is still pending approval.", 'wp-members' ),
			'acct_not_validated'   => __( "You have not completed account validation. Check your inbox for the valdation email.", 'wp-members' ),
			
			'product_restricted_single'    => __( "This content requires the following membership: ", 'wp-members' ),
			'product_restricted_multiple'  => __( "This content requires one of the following memberships: ", 'wp-members' ),
		
		); // End of $defaults array.
		
		/**
		 * Filter default terms.
		 *
		 * @since 3.1.0
		 * @deprecated 3.2.7 Use wpmem_default_text instead.
		 */
		$text = apply_filters( 'wpmem_default_text_strings', '' );
		
		// Merge filtered $terms with $defaults.
		$text = wp_parse_args( $text, $defaults );
		
		/**
		 * Filter the default terms.
		 *
		 * Replaces 'wpmem_default_text_strings' so that multiple filters could
		 * be run. This allows for custom filters when also running the Text
		 * String Editor extension.
		 *
		 * @since 3.2.7
		 */
		$text = apply_filters( 'wpmem_default_text', $text );
		
		// Manage legacy keys (i.e. sb_ to widget_ ). 
		// @todo Legacy keys to be obsolete by 3.5.0
		$str = ( false !== strpos( $str, 'sb_' ) ) ? str_replace( 'sb_', 'widget_', $str ) : $str;
		foreach ( $text as $key => $value ) {
			if ( false !== strpos( $key, 'sb_' ) ) {
				$new_key = str_replace( 'sb_', 'widget_', $key );
				$text[ $new_key ] = $text[ $key ];
			}
		}
		
		// Return the requested text string.
		return $text[ $str ];
	}
	
	/**
	 * Login Failed Dialog.
	 *
	 * Returns the login failed error message.
	 *
	 * @since 1.8
	 * @since 3.4.0 Removed "continue" (return) link (login form now displays by default under the error message).
	 *
	 * @todo 3.5.0 will fold this into the main message function and this functions associated filters will be obsolete.
	 *
	 * @global object $wpmem The WP_Members object.
	 * @return string $str   The generated html for the login failed message.
	 */
	function login_failed() {

		global $wpmem;

		// Defaults.
		$defaults = array(
			'div_before'     => '',
			'div_after'      => '', 
			'heading_before' => '',
			'heading'        => '', //wpmem_get_text( 'login_failed_heading' ),
			'heading_after'  => '',
			'p_before'       => '',
			'message'        => ( $wpmem->error && 1 == $wpmem->login_error ) ? $wpmem->error : wpmem_get_text( 'login_failed' ), // @todo $this->error
			'p_after'        => '',
			//'link'           => '<a href="' . esc_url( $_SERVER['REQUEST_URI'] ) . '">' . wpmem_get_text( 'login_failed_link' ) . '</a>',
		);

		/**
		 * Filter the login failed dialog arguments.
		 *
		 * @since 2.9.0
		 * @since 3.3.3 Should pass defaults to filter.
		 * @deprecated 3.4.0
		 *
		 * @param array An array of arguments to merge with defaults.
		 */
		$args = apply_filters( 'wpmem_login_failed_args', $defaults );

		// Merge $args with defaults.
		$args = wp_parse_args( $args, $defaults );

		$str = $args['div_before']
			. $args['heading_before'] . $args['heading'] . $args['heading_after']
			. $args['p_before'] . $args['message'] . $args['p_after']
			//. $args['p_before'] . $args['link'] . $args['p_after']
			. $args['div_after'];

		/**
		 * Filter the login failed dialog.
		 *
		 * @since 2.7.3
		 *
		 * @param string $str The login failed dialog.
		 */
		$str = apply_filters( 'wpmem_login_failed', $str );

		return $str;
	}

	/**
	 * Gets the message to display.
	 * 
	 * @since 3.4.0
	 * 
	 * @todo This replaces some other functions and usage seems to be inconsistent.
	 *       Review and replace useage as needed.
	 * 
	 * @param  $tag     string
	 * @param  $custom  string
	 * @return $message string
	 */
	function get_message( $tag, $custom = false ) {

		// defaults
		$defaults = array(
			'div_before' => '<div class="wpmem_msg">',
			'div_after'  => '</div>', 
			'p_before'   => '', // @deprecated 3.4.0
			'p_after'    => '', // @deprecated 3.4.0
			'tags'       => array(
				'user',
				'email',
				'success',
				'editsuccess',
				'pwdchangerr',
				'pwdchangesuccess',
				'pwdreseterr',
				'pwdresetsuccess',
				'loginfailed',
			),
		);

		/**
		 * Filter the message arguments.
		 *
		 * @since 2.9.0
		 * @deprecated 3.3.0 Use wpmem_msg_defaults instead.
		 * @todo Obsolete in 3.5.0
		 *
		 * @param array An array of arguments to merge with defaults.
		 */
		$args = apply_filters( 'wpmem_msg_args', '' );

		/** This filter is documented in /includes/class-wp-members-admin-api.php */
		$dialogs = apply_filters( 'wpmem_dialogs', get_option( 'wpmembers_dialogs' ) );

		// @todo Temporary(?) workaround for custom dialogs as an array (WP-Members Security).
		if ( array_key_exists( $tag, $dialogs ) ) {
			if ( is_array( $dialogs[ $tag ] ) ) {
				$msg = stripslashes( $dialogs[ $tag ]['value'] );
			} else {
				$msg = wpmem_get_text( $tag );
				$msg = ( $dialogs[ $tag ] == $msg ) ? $msg : __( stripslashes( $dialogs[ $tag ] ), 'wp-members' );
			}
		} elseif ( 'loginfailed' == $tag ) {
			$msg = $this->login_failed();
		} elseif ( $custom ) {
			$msg = $custom;
		} else {
			// It must be a custom message ("custom" in that it is not included in the dialogs array).
			$msg = $tag;
		}
	
		$defaults['msg'] = $msg;

		/**
		 * Filter the message array
		 *
		 * @since 2.9.2
		 * @since 3.1.1 added $dialogs parameter.
		 * @deprecated 3.3.0 Use wpmem_msg_defaults instead.
		 * @todo Obsolete in 3.5.0
		 *
		 * @param array  $defaults An array of the defaults.
		 * @param string $tag      The tag that we are on, if any.
		 * @param array  $dialogs
		 */
		$defaults = apply_filters( 'wpmem_msg_dialog_arr', $defaults, $tag, $dialogs );

		// Merge $args with defaults.
		$args = wp_parse_args( $args, $defaults );

		// Backwards compatibility for 'toggles'.
		if ( isset( $args['toggles'] ) ) {
			$args['tags'] = $args['toggles'];
		}

		/**
		 * Filter the message settings.
		 *
		 * @since 3.3.0
		 *
		 * @param array  $defaults An array of the defaults.
		 * @param string $tag      The tag that we are on, if any.
		 * @param array  $dialogs
		 */
		$args = apply_filters( 'wpmem_msg_defaults', $defaults, $tag, $dialogs );

		// @todo Temporary(?) workaround for custom dialogs as an array (WP-Members Security).
		$display_msg = ( is_array( $args['msg'] ) ) ? $args['msg']['value'] : $args['msg'];

		$str = $args['div_before'] . stripslashes( $display_msg ) . $args['div_after'];

		/**
		 * Filter the message.
		 *
		 * @since 2.7.4
		 * @since 3.1.0 Added tag.
		 *
		 * @param string $str The message.
		 * @param string $tag The tag of the message being displayed.
		 */
		return apply_filters( 'wpmem_msg_dialog', $str, $tag );
	}
}