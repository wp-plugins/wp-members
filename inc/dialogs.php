<?php
/**
 * WP-Members Dialog Functions
 *
 * Handles functions that output front-end dialogs to end users.
 * 
 * This file is part of the WP-Members plugin by Chad Butler
 * You can find out more about this plugin at http://rocketgeek.com
 * Copyright (c) 2006-2017  Chad Butler
 * WP-Members(tm) is a trademark of butlerblog.com
 *
 * @package WP-Members
 * @author Chad Butler
 * @copyright 2006-2017
 *
 * Functions Included:
 * - wpmem_inc_loginfailed
 * - wpmem_inc_regmessage
 * - wpmem_inc_memberlinks
 * - wpmem_page_pwd_reset
 * - wpmem_page_user_edit
 * - wpmem_page_forgot_username
 * - wpmem_inc_forgotusername
 */


// Include the form building functions.
include_once( WPMEM_PATH . 'inc/forms.php' );


if ( ! function_exists( 'wpmem_inc_loginfailed' ) ):
/**
 * Login Failed Dialog.
 *
 * Returns the login failed error message.
 *
 * @since 1.8
 *
 * @global object $wpmem The WP_Members object.
 * @return string $str   The generated html for the login failed message.
 */
function wpmem_inc_loginfailed() {
	
	global $wpmem;

	// Defaults.
	$defaults = array(
		'div_before'     => '<div align="center" id="wpmem_msg">',
		'div_after'      => '</div>', 
		'heading_before' => '<h2>',
		'heading'        => $wpmem->get_text( 'login_failed_heading' ),
		'heading_after'  => '</h2>',
		'p_before'       => '<p>',
		'message'        => $wpmem->get_text( 'login_failed' ),
		'p_after'        => '</p>',
		'link'           => '<a href="' . esc_url( $_SERVER['REQUEST_URI'] ) . '">' . $wpmem->get_text( 'login_failed_link' ) . '</a>',
	);
	
	/**
	 * Filter the login failed dialog arguments.
	 *
	 * @since 2.9.0
	 *
	 * @param array An array of arguments to merge with defaults.
	 */
	$args = apply_filters( 'wpmem_login_failed_args', '' );
	
	// Merge $args with defaults.
	$args = wp_parse_args( $args, $defaults );
	
	$str = $args['div_before']
		. $args['heading_before'] . $args['heading'] . $args['heading_after']
		. $args['p_before'] . $args['message'] . $args['p_after']
		. $args['p_before'] . $args['link'] . $args['p_after']
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
endif;


if ( ! function_exists( 'wpmem_inc_regmessage' ) ):
/**
 * Message Dialog.
 *
 * Returns various dialogs and error messages.
 *
 * @since 1.8
 *
 * @global object $wpmem
 * @param  string $toggle Error message toggle to look for specific error messages.
 * @param  string $msg    A message that has no toggle that is passed directly to the function.
 * @return string $str    The final HTML for the message.
 */
function wpmem_inc_regmessage( $toggle, $msg = '' ) {
	
	global $wpmem;

	// defaults
	$defaults = array(
		'div_before' => '<div class="wpmem_msg" align="center">',
		'div_after'  => '</div>', 
		'p_before'   => '<p>',
		'p_after'    => '</p>',
		'toggles'    => array(
			'user',
			'email',
			'success',
			'editsuccess',
			'pwdchangerr',
			'pwdchangesuccess',
			'pwdreseterr',
			'pwdresetsuccess',
		),
	);
	
	/**
	 * Filter the message arguments.
	 *
	 * @since 2.9.0
	 *
	 * @param array An array of arguments to merge with defaults.
	 */
	$args = apply_filters( 'wpmem_msg_args', '' );

	// Get dialogs set in the db.
	$dialogs = get_option( 'wpmembers_dialogs' );

	if ( array_key_exists( $toggle, $dialogs ) ) {
		$msg = $wpmem->get_text( $toggle );
		$msg = ( $dialogs[ $toggle ] == $msg ) ? $msg : __( stripslashes( $dialogs[ $toggle ] ), 'wp-members' );
	}
	$defaults['msg'] = $msg;
	
	/**
	 * Filter the message array
	 *
	 * @since 2.9.2
	 * @since 3.1.1 added $dialogs parameter.
	 *
	 * @param array  $defaults An array of the defaults.
	 * @param string $toggle   The toggle that we are on, if any.
	 */
	$defaults = apply_filters( 'wpmem_msg_dialog_arr', $defaults, $toggle, $dialogs );
	
	// Merge $args with defaults.
	$args = wp_parse_args( $args, $defaults );
	
	$str = $args['div_before'] . $args['p_before'] . stripslashes( $args['msg'] ) . $args['p_after'] . $args['div_after'];

	/**
	 * Filter the message.
	 *
	 * @since 2.7.4
	 * @since 3.1.0 Added $toggle.
	 *
	 * @param string $str    The message.
	 * @param string $toggle The toggle of the message being displayed.
	 */
	return apply_filters( 'wpmem_msg_dialog', $str, $toggle );

}
endif;


if ( ! function_exists( 'wpmem_inc_memberlinks' ) ):
/**
 * Member Links Dialog.
 *
 * Outputs the links used on the members area.
 *
 * @since 2.0
 *
 * @gloabl        $user_login
 * @global object $wpmem
 * @param  string $page
 * @return string $str
 */
function wpmem_inc_memberlinks( $page = 'member' ) {

	global $user_login, $wpmem;

	/**
	 * Filter the log out link.
	 *
	 * @since 2.8.3
	 *
	 * @param string The default logout link.
	 */
	$logout = apply_filters( 'wpmem_logout_link', add_query_arg( 'a', 'logout' ) );

	switch ( $page ) {

	case 'member':
		
		$arr = array(
			'before_wrapper' => '',
			'wrapper_before' => '<ul>',
			'wrapper_after'  => '</ul>',
			'rows'           => array(
				'<li><a href="' . add_query_arg( 'a', 'edit' )      . '">' . $wpmem->get_text( 'profile_edit' )     . '</a></li>',
				'<li><a href="' . add_query_arg( 'a', 'pwdchange' ) . '">' . $wpmem->get_text( 'profile_password' ) . '</a></li>',
			),
			'after_wrapper'  => '',
		);

		if ( defined( 'WPMEM_EXP_MODULE' ) && $wpmem->use_exp == 1 && function_exists( 'wpmem_user_page_detail' ) ) {
			$arr['rows'][] = wpmem_user_page_detail();
		}
		
		/**
		 * Filter the member links array.
		 *
		 * @since 3.0.9
		 * @since 3.1.0 Added after_wrapper
		 *
		 * @param array $arr {
		 *      The components of the links.
		 *
		 *      @type string $before_wrapper Anything that comes before the wrapper.
		 *      @type string $wrapper_before The wrapper opening tag (default: <ul>).
		 *      @type string $wrapper_after  The wrapper closing tag (default: </ul>).
		 *      @type array  $rows           Row items HTML.
		 *      @type string $after_wrapper  Anything that comes after the wrapper.
		 * }
		 */
		$arr = apply_filters( "wpmem_{$page}_links_args", $arr );
		
		$str = $arr['before_wrapper'];
		$str.= $arr['wrapper_before'];
		foreach ( $arr['rows'] as $row ) {
			$str.= $row;
		}
		$str.= $arr['wrapper_after'];
		$str.= $arr['after_wrapper'];
	
		/**
		 * Filter the links displayed on the User Profile page (logged in state).
		 *
		 * @since 2.8.3
		 *
		 * @param string $str The default links.
		 */
		$str = apply_filters( "wpmem_{$page}_links", $str );
		break;

	case 'register':
		
		$arr = array(
			'before_wrapper' => '<p>' . sprintf( $wpmem->get_text( 'register_status' ), $user_login ) . '</p>',
			'wrapper_before' => '<ul>',
			'wrapper_after'  => '</ul>',
			'rows'           => array(
				'<li><a href="' . $logout . '">' . $wpmem->get_text( 'register_logout' ) . '</a></li>',
				'<li><a href="' . get_option('home') . '">' . $wpmem->get_text( 'register_continue' ) . '</a></li>',
			),
			'after_wrapper'  => '',
		);
		
		/**
		 * Filter the register links array.
		 *
		 * @since 3.0.9
		 * @since 3.1.0 Added after_wrapper
		 *
		 * @param array $arr {
		 *      The components of the links.
		 *
		 *      @type string $before_wrapper HTML before the wrapper (default: login status).
		 *      @type string $wrapper_before The wrapper opening tag (default: <ul>).
		 *      @type string $wrapper_after  The wrapper closing tag (default: </ul>).
		 *      @type array  $rows           Row items HTML.
		 *      @type string $after_wrapper  Anything that comes after the wrapper.
		 * }
		 */
		$arr = apply_filters( "wpmem_{$page}_links_args", $arr );
		
		$str = $arr['before_wrapper'];
		$str.= $arr['wrapper_before'];
		foreach ( $arr['rows'] as $row ) {
			$str.= $row;
		}
		$str.= $arr['wrapper_after'];
		$str.= $arr['after_wrapper'];
		
		/**
		 * Filter the links displayed on the Register page (logged in state).
		 *
		 * @since 2.8.3
		 *
		 * @param string $str The default links.
		 */
		$str = apply_filters( "wpmem_{$page}_links", $str );
		break;

	case 'login':

		$args = array(
			'wrapper_before' => '<p>',
			'wrapper_after'  => '</p>',
			'user_login'     => $user_login,
			'welcome'        => $wpmem->get_text( 'login_welcome' ),
			'logout_text'    => $wpmem->get_text( 'login_logout' ),
			'logout_link'    => '<a href="' . $logout . '">%s</a>',
			'separator'      => '<br />',
		);
		/**
		 * Filter the status message parts.
		 *
		 * @since 2.9.9
		 *
		 * @param array $args {
		 *      The components of the links.
		 *
		 *      @type string $wrapper_before The wrapper opening tag (default: <p>).
		 *      @type string $wrapper_after  The wrapper closing tag (default: </p>).
		 *      @type string $user_login
		 *      @type string $welcome
		 *      @type string $logout_text
		 *      @type string $logout_link
		 *      @type string $separator
		 * }
		 */
		$args = apply_filters( "wpmem_{$page}_links_args", $args );

		// Assemble the message string.
		$str = $args['wrapper_before']
			. sprintf( $args['welcome'], $args['user_login'] )
			. $args['separator']
			. sprintf( $args['logout_link'], $args['logout_text'] )
			. $args['wrapper_after'];

		/**
		 * Filter the links displayed on the Log In page (logged in state).
		 *
		 * @since 2.8.3
		 *
		 * @param string $str The default links.
		 */
		$str = apply_filters( "wpmem_{$page}_links", $str );
		break;

	case 'status':
		$args = array(
			'wrapper_before' => '<p>',
			'wrapper_after'  => '</p>',
			'user_login'     => $user_login,
			'welcome'        => $wpmem->get_text( 'status_welcome' ),
			'logout_text'    => $wpmem->get_text( 'status_logout' ),
			'logout_link'    => '<a href="' . $logout . '">%s</a>',
			'separator'      => ' | ',
		);
		/**
		 * Filter the status message parts.
		 *
		 * @since 2.9.9
		 *
		 * @param array $args {
		 *      The components of the links.
		 *
		 *      @type string $wrapper_before The wrapper opening tag (default: <p>).
		 *      @type string $wrapper_after  The wrapper closing tag (default: </p>).
		 *      @type string $user_login
		 *      @type string $welcome
		 *      @type string $logout_text
		 *      @type string $logout_link
		 *      @type string $separator
		 * }
		 */
		$args = apply_filters( 'wpmem_status_msg_args', $args );

		// Assemble the message string.
		$str = $args['wrapper_before']
			. sprintf( $args['welcome'], $args['user_login'] )
			. $args['separator']
			. sprintf( $args['logout_link'], $args['logout_text'] )
			. $args['wrapper_after'];
		break;

	}

	return $str;
}
endif;


if ( ! function_exists( 'wpmem_page_pwd_reset' ) ):
/**
 * Password reset forms.
 *
 * This function creates both password reset and forgotten
 * password forms for page=password shortcode.
 *
 * @since 2.7.6
 *
 * @global object $wpmem
 * @param  string $wpmem_regchk
 * @param  string $content
 * @return string $content
 */
function wpmem_page_pwd_reset( $wpmem_regchk, $content ) {
	
	global $wpmem;

	if ( is_user_logged_in() ) {
	
		switch ( $wpmem_regchk ) {

		case "pwdchangempty":
			$content = wpmem_inc_regmessage( $wpmem_regchk, $wpmem->get_text( 'pwdchangempty' ) );
			$content = $content . wpmem_inc_changepassword();
			break;

		case "pwdchangerr":
			$content = wpmem_inc_regmessage( $wpmem_regchk );
			$content = $content . wpmem_inc_changepassword();
			break;

		case "pwdchangesuccess":
			$content = $content . wpmem_inc_regmessage( $wpmem_regchk );
			break;

		default:
			$content = $content . wpmem_inc_changepassword();
			break;
		}

	} else {
		
		// If the password shortcode page is set as User Profile page.
		if ( 'getusername' == $wpmem->action ) {
			
			return wpmem_page_forgot_username( $wpmem_regchk, $content );
		
		} else {

			switch( $wpmem_regchk ) {
	
			case "pwdreseterr":
				$content = $content 
					. wpmem_inc_regmessage( $wpmem_regchk )
					. wpmem_inc_resetpassword();
				$wpmem_regchk = ''; // Clear regchk.
				break;
	
			case "pwdresetsuccess":
				$content = $content . wpmem_inc_regmessage( $wpmem_regchk );
				$wpmem_regchk = ''; // Clear regchk.
				break;
	
			default:
				$content = $content . wpmem_inc_resetpassword();
				break;
			}
		
		}
		
	}

	return $content;

}
endif;


if ( ! function_exists( 'wpmem_page_user_edit' ) ):
/**
 * Creates a user edit page.
 *
 * @since 2.7.6
 *
 * @global object $wpmem
 * @global string $wpmem_a
 * @global string $wpmem_themsg
 * @param  string $wpmem_regchk
 * @param  string $content
 * @return string $content
 */
function wpmem_page_user_edit( $wpmem_regchk, $content ) {

	global $wpmem, $wpmem_a, $wpmem_themsg;
	/**
	 * Filter the default User Edit heading for shortcode.
	 *
	 * @since 2.7.6
	 *
	 * @param string The default edit mode heading.
	 */	
	$heading = apply_filters( 'wpmem_user_edit_heading', $wpmem->get_text( 'profile_heading' ) );
	
	if ( $wpmem_a == "update") { $content.= wpmem_inc_regmessage( $wpmem_regchk, $wpmem_themsg ); }
	$content = $content . wpmem_inc_registration( 'edit', $heading );
	
	return $content;
}
endif;


/**
 * Forgot username form.
 *
 * This function creates a form for retrieving a forgotten username.
 *
 * @since 3.0.8
 *
 * @param  string $wpmem_regchk
 * @param  string $content
 * @return string $content
 */
function wpmem_page_forgot_username( $wpmem_regchk, $content ) {
	
	if ( ! is_user_logged_in() ) {

		global $wpmem;
		switch( $wpmem->regchk ) {

		case "usernamefailed":
			$msg = $wpmem->get_text( 'usernamefailed' );
			$content = $content
				. wpmem_inc_regmessage( 'usernamefailed', $msg ) 
				. wpmem_inc_forgotusername();
			$wpmem->regchk = ''; // Clear regchk.
			break;

		case "usernamesuccess":
			$email = ( isset( $_POST['user_email'] ) ) ? $_POST['user_email'] : '';
			$msg = sprintf( $wpmem->get_text( 'usernamesuccess' ), $email );
			$content = $content . wpmem_inc_regmessage( 'usernamesuccess', $msg );
			$wpmem->regchk = ''; // Clear regchk.
			break;

		default:
			$content = $content . wpmem_inc_forgotusername();
			break;
		}
		
	}

	return $content;

}


/**
 * Forgot Username Form.
 *
 * Loads the form for retrieving a username.
 *
 * @since 3.0.8
 *
 * @global object $wpmem The WP_Members object class.
 * @return string $str   The generated html for the forgot username form.
 */
function wpmem_inc_forgotusername() {
	
	global $wpmem;

	// create the default inputs
	$default_inputs = array(
		array(
			'name'   => $wpmem->get_text( 'username_email' ), 
			'type'   => 'text',
			'tag'    => 'user_email',
			'class'  => 'username',
			'div'    => 'div_text',
		),
	);

	/**
	 * Filter the array of forgot username form fields.
	 *
	 * @since 2.9.0
	 *
	 * @param array $default_inputs An array matching the elements used by default.
 	 */	
	$default_inputs = apply_filters( 'wpmem_inc_forgotusername_inputs', $default_inputs );
	
	$defaults = array(
		'heading'      => $wpmem->get_text( 'username_heading' ), 
		'action'       => 'getusername', 
		'button_text'  => $wpmem->get_text( 'username_button' ),
		'inputs'       => $default_inputs,
	);

	/**
	 * Filter the arguments to override change password form defaults.
	 *
	 * @since 
	 *
	 * @param array $args An array of arguments to use. Default null.
 	 */
	$args = apply_filters( 'wpmem_inc_forgotusername_args', '' );

	$arr  = wp_parse_args( $args, $defaults );

    $str  = wpmem_login_form( 'page', $arr );
	
	return $str;
}

// End of file.