<?php
/**
 * WP-Members Dialog Functions
 *
 * Handles functions that output front-end dialogs to end users.
 * 
 * This file is part of the WP-Members plugin by Chad Butler
 * You can find out more about this plugin at http://rocketgeek.com
 * Copyright (c) 2006-2016  Chad Butler
 * WP-Members(tm) is a trademark of butlerblog.com
 *
 * @package WP-Members
 * @author Chad Butler
 * @copyright 2006-2016
 *
 * Functions Included:
 * - wpmem_inc_loginfailed
 * - wpmem_inc_regmessage
 * - wpmem_inc_memberlinks
 * - wpmem_page_pwd_reset
 * - wpmem_page_user_edit
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
 * @return string $str the generated html for the login failed message.
 */
function wpmem_inc_loginfailed() {

	// Defaults.
	$defaults = array(
		'div_before'     => '<div align="center" id="wpmem_msg">',
		'div_after'      => '</div>', 
		'heading_before' => '<h2>',
		'heading'        => __( 'Login Failed!', 'wp-members' ),
		'heading_after'  => '</h2>',
		'p_before'       => '<p>',
		'message'        => __( 'You entered an invalid username or password.', 'wp-members' ),
		'p_after'        => '</p>',
		'link'           => '<a href="' . $_SERVER['REQUEST_URI'] . '">' . __( 'Click here to continue.', 'wp-members' ) . '</a>',
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
	 * @since ?.?
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
 * @param  string $toggle Error message toggle to look for specific error messages.
 * @param  string $msg    A message that has no toggle that is passed directly to the function.
 * @return string $str    The final HTML for the message.
 */
function wpmem_inc_regmessage( $toggle, $msg = '' ) {

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

	for ( $r = 0; $r < count( $defaults['toggles'] ); $r++ ) {
		if ( $toggle == $defaults['toggles'][$r] ) {
			$msg = __( stripslashes( $dialogs[$r+1] ), 'wp-members' );
			break;
		}
	}
	$defaults['msg'] = $msg;
	
	/**
	 * Filter the message array
	 *
	 * @since 2.9.2
	 *
	 * @param array  $defaults An array of the defaults.
	 * @param string $toggle   The toggle that we are on, if any.
	 */
	$defaults = apply_filters( 'wpmem_msg_dialog_arr', $defaults, $toggle );
	
	// Merge $args with defaults.
	$args = wp_parse_args( $args, $defaults );
	
	$str = $args['div_before'] . $args['p_before'] . stripslashes( $args['msg'] ) . $args['p_after'] . $args['div_after'];

	/**
	 * Filter the message.
	 *
	 * @since ?.?
	 *
	 * @param string $str The message.
	 */
	return apply_filters( 'wpmem_msg_dialog', $str );

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
 * @param  string $page
 * @return string $str
 */
function wpmem_inc_memberlinks( $page = 'members' ) {

	global $user_login, $wpmem;

	$link = wpmem_chk_qstr();

	/**
	 * Filter the log out link.
	 *
	 * @since 2.8.3
	 *
	 * @param string $link The default logout link.
	 */
	$logout = apply_filters( 'wpmem_logout_link', $link . 'a=logout' );

	switch ( $page ) {

	case 'members':
		
		$arr = array(
			'before_wrapper' => '',
			'wrapper_before' => '<ul>',
			'wrapper_after'  => '</ul>',
			'rows'           => array(
				'<li><a href="' . $link . 'a=edit">'      . __( 'Edit My Information', 'wp-members' ) . '</a></li>',
				'<li><a href="' . $link . 'a=pwdchange">' . __( 'Change Password', 'wp-members' )     . '</a></li>',
			),
		);

		if ( defined( 'WPMEM_EXP_MODULE' ) && $wpmem->use_exp == 1 && function_exists( 'wpmem_user_page_detail' ) ) {
			$arr['rows'][] = wpmem_user_page_detail();
		}
		
		/**
		 * Filter the member links array.
		 *
		 * @since 3.0.9
		 *
		 * @param array $arr {
		 *      The components of the links.
		 *
		 *      @type string $before_wrapper Anything that comes before the wrapper.
		 *      @type string $wrapper_before The wrapper opening tag (default: <ul>).
		 *      @type string $wrapper_after  The wrapper closing tag (default: </ul>).
		 *      @type array  $rows           Row items HTML.
		 * }
		 */
		$arr = apply_filters( 'wpmem_member_links_args', $arr );
		
		$str = $arr['before_wrapper'];
		$str.= $arr['wrapper_before'];
		foreach ( $arr['rows'] as $row ) {
			$str.= $row;
		}
		$str.= $arr['wrapper_after'];
	
		/**
		 * Filter the links displayed on the User Profile page (logged in state).
		 *
		 * @since 2.8.3
		 *
		 * @param string $str The default links.
		 */
		$str = apply_filters( 'wpmem_member_links', $str );
		break;

	case 'register':
		
		$arr = array(
			'before_wrapper' => '<p>' . sprintf( __( 'You are logged in as %s', 'wp-members' ), $user_login ) . '</p>',
			'wrapper_before' => '<ul>',
			'wrapper_after'  => '</ul>',
			'rows'           => array(
				'<li><a href="' . $logout . '">' . __( 'Click to log out.', 'wp-members' ) . '</a></li>',
				'<li><a href="' . get_option('home') . '">' . __( 'Begin using the site.', 'wp-members' ) . '</a></li>',
			),
		);
		
		/**
		 * Filter the register links array.
		 *
		 * @since 3.0.9
		 *
		 * @param array $arr {
		 *      The components of the links.
		 *
		 *      @type string $before_wrapper HTML before the wrapper (default: login status).
		 *      @type string $wrapper_before The wrapper opening tag (default: <ul>).
		 *      @type string $wrapper_after  The wrapper closing tag (default: </ul>).
		 *      @type array  $rows           Row items HTML.
		 * }
		 */
		$arr = apply_filters( 'wpmem_register_links_args', $arr );
		
		$str = $arr['before_wrapper'];
		$str.= $arr['wrapper_before'];
		foreach ( $arr['rows'] as $row ) {
			$str.= $row;
		}
		$str.= $arr['wrapper_after'];
		
		/**
		 * Filter the links displayed on the Register page (logged in state).
		 *
		 * @since 2.8.3
		 *
		 * @param string $str The default links.
		 */
		$str = apply_filters( 'wpmem_register_links', $str );
		break;

	case 'login':

		$args = array(
			'wrapper_before' => '<p>',
			'wrapper_after'  => '</p>',
			'user_login'     => $user_login,
			'welcome'        => __( 'You are logged in as %s', 'wp-members' ),
			'logout_text'    => __( 'Click to log out', 'wp-members' ),
			'logout_link'    => '<a href="' . $logout . '">%s</a>',
			'separator'      => '<br />',
		);
		/**
		 * Filter the status message parts.
		 *
		 * @since 2.9.9
		 *
		 * @param array $args.
		 */
		$args = apply_filters( 'wpmem_login_links_args', $args );

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
		$str = apply_filters( 'wpmem_login_links', $str );
		break;

	case 'status':
		$args = array(
			'wrapper_before' => '<p>',
			'wrapper_after'  => '</p>',
			'user_login'     => $user_login,
			'welcome'        => __( 'You are logged in as %s', 'wp-members' ),
			'logout_text'    => __( 'click to log out', 'wp-members' ),
			'logout_link'    => '<a href="' . $logout . '">%s</a>',
			'separator'      => ' | ',
		);
		/**
		 * Filter the status message parts.
		 *
		 * @since 2.9.9
		 *
		 * @param array $args.
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
 * @param  string $wpmem_regchk
 * @param  string $content
 * @return string $content
 */
function wpmem_page_pwd_reset( $wpmem_regchk, $content ) {

	if ( is_user_logged_in() ) {
	
		switch ( $wpmem_regchk ) {

		case "pwdchangempty":
			$content = wpmem_inc_regmessage( $wpmem_regchk, __( 'Password fields cannot be empty', 'wp-members' ) );
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

	return $content;

}
endif;


if ( ! function_exists( 'wpmem_page_user_edit' ) ):
/**
 * Creates a user edit page.
 *
 * @since 2.7.6
 *
 * @param  string $wpmem_regchk
 * @param  string $content
 * @return string $content
 */
function wpmem_page_user_edit( $wpmem_regchk, $content ) {

	global $wpmem_a, $wpmem_themsg;
	/**
	 * Filter the default User Edit heading for shortcode.
	 *
	 * @since 2.7.6
	 *
	 * @param string The default edit mode heading.
	 */	
	$heading = apply_filters( 'wpmem_user_edit_heading', __( 'Edit Your Information', 'wp-members' ) );
	
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
			$msg = __( 'Sorry, that email address was not found.', 'wp-members' );
			$content = $content
				. wpmem_inc_regmessage( 'usernamefailed', $msg ) 
				. wpmem_inc_forgotusername();
			$wpmem->regchk = ''; // Clear regchk.
			break;

		case "usernamesuccess":
			$email = ( isset( $_POST['user_email'] ) ) ? $_POST['user_email'] : '';
			$msg = sprintf( __( 'An email was sent to %s with your username.', 'wp-members' ), $email );
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
 * @return string $str The generated html for the forgot username form.
 */
function wpmem_inc_forgotusername() {

	// create the default inputs
	$default_inputs = array(
		array(
			'name'   => __( 'Email Address', 'wp-members' ), 
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
		'heading'      => __( 'Retrieve username', 'wp-members' ), 
		'action'       => 'getusername', 
		'button_text'  => __( 'Retrieve username', 'wp-members' ), 
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