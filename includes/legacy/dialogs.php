<?php
/**
 * WP-Members Dialog Functions
 *
 * Handles functions that output front-end dialogs to end users.
 * 
 * This file is part of the WP-Members plugin by Chad Butler
 * You can find out more about this plugin at https://rocketgeek.com
 * Copyright (c) 2006-2021  Chad Butler
 * WP-Members(tm) is a trademark of butlerblog.com
 *
 * @package WP-Members
 * @author Chad Butler
 * @copyright 2006-2021
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

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

if ( ! function_exists( 'wpmem_inc_memberlinks' ) ):
/**
 * Member Links Dialog.
 *
 * Outputs the links used on the members area.
 *
 * @since 2.0
 * @since 3.4.0 "status" is technically deprecated. Use wpmem_login_status() instead.
 *
 * @gloabl        $user_login
 * @global object $wpmem
 * @param  string $page
 * @return string $str
 */
function wpmem_inc_memberlinks( $page = 'member' ) {

	global $user_login, $wpmem;

	$logout = wpmem_logout_link();

	switch ( $page ) {

	case 'member':
		
		$arr = array(
			'before_wrapper' => '',
			'wrapper_before' => '<ul>',
			'wrapper_after'  => '</ul>',
			'rows'           => array(
				'<li><a href="' . esc_url( add_query_arg( 'a', 'edit',      remove_query_arg( 'key' ) ) ) . '">' . $wpmem->get_text( 'profile_edit'     ) . '</a></li>',
				'<li><a href="' . esc_url( add_query_arg( 'a', 'pwdchange', remove_query_arg( 'key' ) ) ) . '">' . $wpmem->get_text( 'profile_password' ) . '</a></li>',
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
			
		$url = ( isset( $wpmem->user_pages['profile'] ) && '' != $wpmem->user_pages['profile'] ) ? $wpmem->user_pages['profile'] : get_option( 'home' );
		
		$arr = array(
			'before_wrapper' => '<p>' . sprintf( $wpmem->get_text( 'register_status' ), $user_login ) . '</p>',
			'wrapper_before' => '<ul>',
			'wrapper_after'  => '</ul>',
			'rows'           => array(
				'<li><a href="' . esc_url( $logout ) . '">' . $wpmem->get_text( 'register_logout' ) . '</a></li>',
				'<li><a href="' . esc_url( $url ) . '">' . $wpmem->get_text( 'register_continue' ) . '</a></li>',
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
		
		$logout = urldecode( $logout ); // @todo Resolves sprintf issue if url is encoded.
		$args = array(
			'wrapper_before' => '<p>',
			'wrapper_after'  => '</p>',
			'user_login'     => $user_login,
			'welcome'        => $wpmem->get_text( 'login_welcome' ),
			'logout_text'    => $wpmem->get_text( 'login_logout' ),
			'logout_link'    => '<a href="' . esc_url( $logout ) . '">%s</a>',
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
		$str = wpmem_login_status();
		break;

	}

	return $str;
}
endif;


/**
 * Displays the post restricted message.
 *
 * @since 3.3.5
 *
 * @return string
 */
function wpmem_restricted_dialog() {
	global $wpmem;
	return $wpmem->forms->add_restricted_msg();
}

// End of file.