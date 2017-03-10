<?php
/**
 * WP-Members Sidebar Functions
 *
 * Handles functions for the sidebar.
 * 
 * This file is part of the WP-Members plugin by Chad Butler
 * You can find out more about this plugin at http://rocketgeek.com
 * Copyright (c) 2006-2017 Chad Butler
 * WP-Members(tm) is a trademark of butlerblog.com
 *
 * @package WP-Members
 * @author Chad Butler
 * @copyright 2006-2017
 *
 * Functions Included:
 * - wpmem_inc_status
 * - wpmem_do_sidebar
 * - widget_wpmemwidget
 */


if ( ! function_exists( 'wpmem_inc_status' ) ):
/**
 * Generate users login status if logged in and gives logout link.
 *
 * @since 1.8
 *
 * @global        $user_login
 * @global object $wpmem
 * @return string $status
 */
function wpmem_inc_status() {

	global $user_login, $wpmem;
	
	/** This filter is documented in wp-members/inc/dialogs.php */
	$logout = apply_filters( 'wpmem_logout_link', $url . '/?a=logout' );

	$status = '<p>' . sprintf( $wpmem->get_text( 'sb_login_status' ), $user_login )
		. ' | <a href="' . $logout . '">' . $wpmem->get_text( 'sb_logout_link' ) . '</a></p>';

	return $status;
}
endif;


if ( ! function_exists( 'wpmem_do_sidebar' ) ):
/**
 * Creates the sidebar login form and status.
 *
 * This function determines if the user is logged in and displays either
 * a login form, or the user's login status. Typically used for a sidebar.		
 * You can call this directly, or with the widget.
 *
 * @since 2.4.0
 * @since 3.0.0 Added $post_to argument.
 * @since 3.1.0 Changed $post_to to $redirect_to. 
 *
 * @param  string $redirect_to  A URL to redirect to upon login, default null.
 * @global string $wpmem_regchk
 * @global string $user_login
 */
function wpmem_do_sidebar( $redirect_to = null ) {

	global $wpmem, $wpmem_regchk;

	 // Used here and in the logout.
	$url = get_bloginfo('url');

	if ( isset( $_REQUEST['redirect_to'] ) ) {
		$post_to = $_REQUEST['redirect_to'];
	} elseif ( is_home() || is_front_page() ) {
		$post_to = $_SERVER['REQUEST_URI'];
	} elseif ( is_single() || is_page() ) {
		$post_to = get_permalink();
	} elseif ( is_category() ) {
		global $wp_query;
		$cat_id  = get_query_var( 'cat' );
		$post_to = get_category_link( $cat_id );
	} elseif ( is_search() ) {
		$post_to = add_query_arg( 's', get_search_query(), $url );
	} else {
		$post_to = $_SERVER['REQUEST_URI'];
	}

	// Clean whatever the url is.
	$post_to = esc_url( $post_to );

	if ( ! is_user_logged_in() ){

		// If the user is not logged in, we need the form.

		// Defaults.
		$defaults = array(
			// Wrappers.
			'error_before'    => '<p class="err">',
			'error_after'     => '</p>',
			'fieldset_before' => '<fieldset>',
			'fieldset_after'  => '</fieldset>',
			'inputs_before'   => '<div class="div_texbox">',
			'inputs_after'    => '</div>',
			'buttons_before'  => '<div class="button_div">',
			'buttons_after'   => '</div>',

			// Messages.
			'error_msg'  => $wpmem->get_text( 'sb_login_failed' ),
			'status_msg' => $wpmem->get_text( 'sb_not_logged_in' ) . '<br />',
			
			// Other.
			'strip_breaks'    => true,
			'wrap_inputs'     => true,
			'n'               => "\n",
			't'               => "\t",
			'login_form_action' => true,
		);

		/**
		 * Filter arguments for the sidebar defaults.
		 *
		 * @since 2.9.0
		 *
		 * @param array An array of the defaults to be changed.
		 */
		$args = apply_filters( 'wpmem_sb_login_args', '' );

		// Merge $args with defaults.
		$args = wp_parse_args( $args, $defaults );

		$form = '';

		$label = '<label for="username">' . $wpmem->get_text( 'sb_login_username' ) . '</label>';
		$input = '<input type="text" name="log" class="username" id="username" />';

		$input = ( $args['wrap_inputs'] ) ? $args['inputs_before'] . $input . $args['inputs_after'] : $input;
		$row1  = $label . $args['n'] . $input . $args['n'];

		$label = '<label for="password">' . $wpmem->get_text( 'sb_login_password' ) . '</label>';
		$input = '<input type="password" name="pwd" class="password" id="password" />';

		$input = ( $args['wrap_inputs'] ) ? $args['inputs_before'] . $input . $args['inputs_after'] : $input;
		$row2  = $label . $args['n'] . $input . $args['n'];

		$form = $row1 . $row2;
		
		// Handle outside elements added to the login form (currently ONLY for login).
		if ( $args['login_form_action'] ) {
			ob_start();
			do_action( 'login_form' );
			$add_to_form = ob_get_contents();
			ob_end_clean();
			$form.= $add_to_form;
		}

		$hidden = '<input type="hidden" name="rememberme" value="forever" />' . $args['n'] .
				'<input type="hidden" name="redirect_to" value="' . ( ( $redirect_to ) ? $redirect_to : $post_to ) . '" />' . $args['n'] .
				'<input type="hidden" name="a" value="login" />' . $args['n'] .
				'<input type="hidden" name="slog" value="true" />';
		/**
		 * Filter sidebar login form hidden fields.
		 *
		 * @since 2.9.0
		 *
		 * @param string $hidden The HTML for the hidden fields.
		 */
		$form = $form . apply_filters( 'wpmem_sb_hidden_fields', $hidden );

		$buttons = '<input type="submit" name="Submit" class="buttons" value="' . $wpmem->get_text( 'sb_login_button' ) . '" />';

		if ( $wpmem->user_pages['profile'] != null ) { 
			/** This filter is documented in wp-members/inc/forms.php */
			$link = apply_filters( 'wpmem_forgot_link', add_query_arg( 'a', 'pwdreset', $wpmem->user_pages['profile'] ) );
			$link_html = ' <a href="' . $link . '">' . $wpmem->get_text( 'sb_login_forgot' ) . '</a>&nbsp;';
			/**
			 * Filter the sidebar forgot password.
			 *
			 * @since 3.0.9
			 *
			 * @param string $link_html
			 * @param string $link
			 */
			$link_html = apply_filters( 'wpmem_sb_forgot_link_str', $link_html, $link );
			$buttons.= $link_html;
		} 			

		if ( $wpmem->user_pages['register'] != null ) {
			/** This filter is documented in wp-members/inc/forms.php */
			$link = apply_filters( 'wpmem_reg_link', $wpmem->user_pages['register'] );
			$link_html = ' <a href="' . $link . '">' . $wpmem->get_text( 'sb_login_register' ) . '</a>';
			/**
			 * Filter the sidebar register link.
			 *
			 * @since 3.0.9
			 *
			 * @param string $link_html
			 * @param string $link
			 */
			$link_html = apply_filters( 'wpmem_sb_reg_link_str', $link_html, $link );
			$buttons.= $link_html;
		}

		$form = $form . $args['n'] . $args['buttons_before'] . $buttons . $args['n'] . $args['buttons_after'];

		$form = $args['fieldset_before'] . $args['n'] . $form . $args['n'] . $args['fieldset_after'];

		$form = '<form name="form" method="post" action="' . $post_to . '">' . $args['n'] . $form . $args['n'] . '</form>';

		// Add status message.
		$form = $args['status_msg'] . $args['n'] . $form;

		// Strip breaks.
		$form = ( $args['strip_breaks'] ) ? str_replace( array( "\n", "\r", "\t" ), array( '','','' ), $form ) : $form;

		/**
		 * Filter the sidebar form.
		 *
		 * @since unknown
		 *
		 * @param string $form The HTML for the sidebar login form.
		 */
		$form = apply_filters( 'wpmem_sidebar_form', $form );

		$do_error_msg = '';
		if ( isset( $_POST['slog'] ) && $wpmem_regchk == 'loginfailed' ) {
			$do_error_msg = true;
			$error_msg = $args['error_before'] . $args['error_msg'] . $args['error_after'];
			/**
			 * Filter the sidebar login failed message.
			 *
			 * @since unknown
			 *
			 * @param string $error_msg The error message.
			 */
			$error_msg = apply_filters( 'wpmem_login_failed_sb', $error_msg );
		}
		$form = ( $do_error_msg ) ? $error_msg . $form : $form;

		echo $form;

	} else {

		global $user_login; 

		/** This filter is documented in wp-members/inc/dialogs.php */
		$logout = apply_filters( 'wpmem_logout_link', add_query_arg( 'a', 'logout', $url ) );
		
		// Defaults.
		$defaults = array(
			'user_login'     => $user_login,
			'wrapper_before' => '<p>',
			'status_text'    => sprintf( $wpmem->get_text( 'sb_status' ), $user_login ) . '<br />',
			'link_text'      => $wpmem->get_text( 'sb_logout' ),
			'wrapper_after'  => '</p>',
		);
	
		/**
		 * Filter sidebar login status arguments.
		 *
		 * @since 3.1.0
		 * @since 3.1.2 Pass default args.
		 *
		 * @param  array $defaults
		 * @return array
		 */
		$args = apply_filters( 'wpmem_sidebar_status_args', $defaults );
		
		// Merge $args with $defaults.
		$args = wp_parse_args( $args, $defaults );
		
		// Generate the message string.
		$str = $args['wrapper_before'] . $args['status_text'] . "<a href=\"$logout\">" . $args['link_text'] . '</a>' . $args['wrapper_after'];

		/**
		 * Filter the sidebar user login status.
		 *
		 * @since unknown
		 *
		 * @param string $str The login status for the user.
		 */
		$str = apply_filters( 'wpmem_sidebar_status', $str );

		echo $str;
	}
}
endif;

// End of file.