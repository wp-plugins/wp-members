<?php
/**
 * WP-Members Form Building Functions
 *
 * Handles functions that build the various forms.
 * 
 * This file is part of the WP-Members plugin by Chad Butler
 * You can find out more about this plugin at http://rocketgeek.com
 * Copyright (c) 2006-2016 Chad Butler
 * WP-Members(tm) is a trademark of butlerblog.com
 *
 * @package WP-Members
 * @subpackage WP-Members Form Building Functions
 * @author Chad Butler
 * @copyright 2006-2016
 *
 * Functions Included:
 * - wpmem_inc_login
 * - wpmem_inc_changepassword
 * - wpmem_inc_resetpassword
 * - wpmem_login_form
 * - wpmem_inc_registration
 * - wpmem_inc_recaptcha
 * - wpmem_inc_attribution
 * - wpmem_build_rs_captcha
 */


if ( ! function_exists( 'wpmem_inc_login' ) ):
/**
 * Login Dialog.
 *
 * Loads the login form for user login.
 *
 * @since 1.8
 * @since 3.1.4 Global $wpmem_regchk no longer needed.
 *
 * @global object $post         The WordPress Post object.
 * @global object $wpmem        The WP_Members object.
 * @param  string $page         If the form is being displayed in place of blocked content. Default: page.
 * @param  string $redirect_to  Redirect URL. Default: null.
 * @param  string $show         If the form is being displayed in place of blocked content. Default: show.
 * @return string $str          The generated html for the login form.
 */
function wpmem_inc_login( $page = "page", $redirect_to = null, $show = 'show' ) {
 	
	global $post, $wpmem;

	$str = '';

	if ( $page == "page" ) {

	     if ( $wpmem->regchk != "success" ) {

			$dialogs = get_option( 'wpmembers_dialogs' );
			
			// This shown above blocked content.
			$msg = $wpmem->get_text( 'restricted_msg' );
			$msg = ( $dialogs['restricted_msg'] == $msg ) ? $msg : __( stripslashes( $dialogs['restricted_msg'] ), 'wp-members' );
			$str = "<p>$msg</p>";
			
			/**
			 * Filter the post restricted message.
			 *
			 * @since 2.7.3
			 *
			 * @param string $str The post restricted message.
		 	 */
			$str = apply_filters( 'wpmem_restricted_msg', $str );

		} 	
	} 
	
	// Create the default inputs.
	$default_inputs = array(
		array(
			'name'   => $wpmem->get_text( 'login_username' ), 
			'type'   => 'text', 
			'tag'    => 'log',
			'class'  => 'username',
			'div'    => 'div_text',
		),
		array( 
			'name'   => $wpmem->get_text( 'login_password' ), 
			'type'   => 'password', 
			'tag'    => 'pwd', 
			'class'  => 'password',
			'div'    => 'div_text',
		),
	);
	
	/**
	 * Filter the array of login form fields.
	 *
	 * @since 2.9.0
	 *
	 * @param array $default_inputs An array matching the elements used by default.
 	 */
	$default_inputs = apply_filters( 'wpmem_inc_login_inputs', $default_inputs );
	
	$defaults = array( 
		'heading'      => $wpmem->get_text( 'login_heading' ), 
		'action'       => 'login', 
		'button_text'  => $wpmem->get_text( 'login_button' ),
		'inputs'       => $default_inputs,
		'redirect_to'  => $redirect_to,
	);	

	/**
	 * Filter the arguments to override login form defaults.
	 *
	 * @since 2.9.0
	 *
	 * @param array $args An array of arguments to use. Default null.
 	 */
	$args = apply_filters( 'wpmem_inc_login_args', '' );

	$arr  = wp_parse_args( $args, $defaults );
	
	$str  = ( $show == 'show' ) ? $str . wpmem_login_form( $page, $arr ) : $str;
	
	return $str;
}
endif;


if ( ! function_exists( 'wpmem_inc_changepassword' ) ):
/**
 * Change Password Dialog.
 *
 * Loads the form for changing password.
 *
 * @since 2.0.0
 *
 * @global object $wpmem The WP_Members object.
 * @return string $str   The generated html for the change password form.
 */
function wpmem_inc_changepassword() {
	
	global $wpmem;

	// create the default inputs
	$default_inputs = array(
		array(
			'name'   => $wpmem->get_text( 'pwdchg_password1' ), 
			'type'   => 'password',
			'tag'    => 'pass1',
			'class'  => 'password',
			'div'    => 'div_text',
		),
		array( 
			'name'   => $wpmem->get_text( 'pwdchg_password2' ), 
			'type'   => 'password', 
			'tag'    => 'pass2',
			'class'  => 'password',
			'div'    => 'div_text',
		),
	);

	/**
	 * Filter the array of change password form fields.
	 *
	 * @since 2.9.0
	 *
	 * @param array $default_inputs An array matching the elements used by default.
 	 */	
	$default_inputs = apply_filters( 'wpmem_inc_changepassword_inputs', $default_inputs );
	
	$defaults = array(
		'heading'      => $wpmem->get_text( 'pwdchg_heading' ), 
		'action'       => 'pwdchange', 
		'button_text'  => $wpmem->get_text( 'pwdchg_button' ), 
		'inputs'       => $default_inputs,
	);

	/**
	 * Filter the arguments to override change password form defaults.
	 *
	 * @since 2.9.0
	 *
	 * @param array $args An array of arguments to use. Default null.
 	 */
	$args = apply_filters( 'wpmem_inc_changepassword_args', '' );

	$arr  = wp_parse_args( $args, $defaults );

    $str  = wpmem_login_form( 'page', $arr );
	
	return $str;
}
endif;


if ( ! function_exists( 'wpmem_inc_resetpassword' ) ):
/**
 * Reset Password Dialog.
 *
 * Loads the form for resetting password.
 *
 * @since 2.1.0
 *
 * @global object $wpmem The WP_Members object.
 * @return string $str   The generated html fo the reset password form.
 */
function wpmem_inc_resetpassword() { 

	global $wpmem;

	// Create the default inputs.
	$default_inputs = array(
		array(
			'name'   => $wpmem->get_text( 'pwdreset_username' ), 
			'type'   => 'text',
			'tag'    => 'user', 
			'class'  => 'username',
			'div'    => 'div_text',
		),
		array( 
			'name'   => $wpmem->get_text( 'pwdreset_email' ), 
			'type'   => 'text', 
			'tag'    => 'email', 
			'class'  => 'password',
			'div'    => 'div_text',
		),
	);

	/**
	 * Filter the array of reset password form fields.
	 *
	 * @since 2.9.0
	 *
	 * @param array $default_inputs An array matching the elements used by default.
 	 */	
	$default_inputs = apply_filters( 'wpmem_inc_resetpassword_inputs', $default_inputs );
	
	$defaults = array(
		'heading'      => $wpmem->get_text( 'pwdreset_heading' ),
		'action'       => 'pwdreset', 
		'button_text'  => $wpmem->get_text( 'pwdreset_button' ), 
		'inputs'       => $default_inputs,
	);

	/**
	 * Filter the arguments to override reset password form defaults.
	 *
	 * @since 2.9.0
	 *
	 * @param array $args An array of arguments to use. Default null.
 	 */
	$args = apply_filters( 'wpmem_inc_resetpassword_args', '' );

	$arr  = wp_parse_args( $args, $defaults );

    $str  = wpmem_login_form( 'page', $arr );
	
	return $str;
}
endif;


if ( ! function_exists( 'wpmem_login_form' ) ):
/**
 * Login Form Dialog.
 *
 * Builds the form used for login, change password, and reset password.
 *
 * @since 2.5.1
 *
 * @param  string $page 
 * @param  array  $arr {
 *     The elements needed to generate the form (login|reset password|forgotten password).
 *
 *     @type string $heading     Form heading text.
 *     @type string $action      The form action (login|pwdchange|pwdreset).
 *     @type string $button_text Form submit button text.
 *     @type array  $inputs {
 *         The form input values.
 *
 *         @type array {
 *
 *             @type string $name  The field label.
 *             @type string $type  Input type.
 *             @type string $tag   Input tag name.
 *             @type string $class Input tag class.
 *             @type string $div   Div wrapper class.
 *         }
 *     }
 *     @type string $redirect_to Optional. URL to redirect to.
 * }
 * @return string $form  The HTML for the form as a string.
 */
function wpmem_login_form( $page, $arr ) {

	global $wpmem;

	// set up default wrappers
	$defaults = array(
		
		// wrappers
		'heading_before'  => '<legend>',
		'heading_after'   => '</legend>',
		'fieldset_before' => '<fieldset>',
		'fieldset_after'  => '</fieldset>',
		'main_div_before' => '<div id="wpmem_login">',
		'main_div_after'  => '</div>',
		'txt_before'      => '[wpmem_txt]',
		'txt_after'       => '[/wpmem_txt]',
		'row_before'      => '',
		'row_after'       => '',
		'buttons_before'  => '<div class="button_div">',
		'buttons_after'   => '</div>',
		'link_before'     => '<div align="right" class="link-text">',
		'link_after'      => '</div>',
		
		// classes & ids
		'form_id'         => '',
		'form_class'      => 'form',
		'button_id'       => '',
		'button_class'    => 'buttons',
		
		// other
		'strip_breaks'    => true,
		'wrap_inputs'     => true,
		'remember_check'  => true,
		'n'               => "\n",
		't'               => "\t",
		'redirect_to'     => ( isset( $_REQUEST['redirect_to'] ) ) ? esc_url( $_REQUEST['redirect_to'] ) : ( ( isset( $arr['redirect_to'] ) ) ? $arr['redirect_to'] : get_permalink() ),
		
	);
	
	/**
	 * Filter the default form arguments.
	 *
	 * This filter accepts an array of various elements to replace the form defaults. This
	 * includes default tags, labels, text, and small items including various booleans.
	 *
	 * @since 2.9.0
	 *
	 * @param array                 An array of arguments to merge with defaults. Default null.
	 * @param string $arr['action'] The action being performed by the form. login|pwdreset|pwdchange|getusername.
	 */
	$args = apply_filters( 'wpmem_login_form_args', '', $arr['action'] );
	
	// Merge $args with defaults.
	$args = wp_parse_args( $args, $defaults );
	
	// Build the input rows.
	foreach ( $arr['inputs'] as $input ) {
		$label = '<label for="' . $input['tag'] . '">' . $input['name'] . '</label>';
		$field = wpmem_create_formfield( $input['tag'], $input['type'], '', '', $input['class'] );
		$field_before = ( $args['wrap_inputs'] ) ? '<div class="' . $input['div'] . '">' : '';
		$field_after  = ( $args['wrap_inputs'] ) ? '</div>' : '';
		$rows[] = array( 
			'row_before'   => $args['row_before'],
			'label'        => $label,
			'field_before' => $field_before,
			'field'        => $field,
			'field_after'  => $field_after,
			'row_after'    => $args['row_after'],
		);
	}
	
	/**
	 * Filter the array of form rows.
	 *
	 * This filter receives an array of the main rows in the form, each array element being
	 * an array of that particular row's pieces. This allows making changes to individual 
	 * parts of a row without needing to parse through a string of HTML.
	 *
	 * @since 2.9.0
	 *
	 * @param array  $rows          An array containing the form rows.
	 * @param string $arr['action'] The action being performed by the form. login|pwdreset|pwdchange|getusername.
	 */
	$rows = apply_filters( 'wpmem_login_form_rows', $rows, $arr['action'] );
	
	// Put the rows from the array into $form.
	$form = '';
	foreach ( $rows as $row_item ) {
		$row  = ( $row_item['row_before']   != '' ) ? $row_item['row_before'] . $args['n'] . $row_item['label'] . $args['n'] : $row_item['label'] . $args['n'];
		$row .= ( $row_item['field_before'] != '' ) ? $row_item['field_before'] . $args['n'] . $args['t'] . $row_item['field'] . $args['n'] . $row_item['field_after'] . $args['n'] : $row_item['field'] . $args['n'];
		$row .= ( $row_item['row_after']    != '' ) ? $row_item['row_after'] . $args['n'] : '';
		$form.= $row;
	}

	// Build hidden fields, filter, and add to the form.
	$hidden = wpmem_create_formfield( 'redirect_to', 'hidden', $args['redirect_to'] ) . $args['n'];
	$hidden = $hidden . wpmem_create_formfield( 'a', 'hidden', $arr['action'] ) . $args['n'];
	$hidden = ( $arr['action'] != 'login' ) ? $hidden . wpmem_create_formfield( 'formsubmit', 'hidden', '1' ) : $hidden;

	/**
	 * Filter the hidden field HTML.
	 *
	 * @since 2.9.0
	 *
	 * @param string $hidden        The generated HTML of hidden fields.
	 * @param string $arr['action'] The action being performed by the form. login|pwdreset|pwdchange|getusername.
	 */
	$form = $form . apply_filters( 'wpmem_login_hidden_fields', $hidden, $arr['action'] );

	// Build the buttons, filter, and add to the form.
	if ( $arr['action'] == 'login' ) {
		$args['remember_check'] = ( $args['remember_check'] ) ? $args['t'] . wpmem_create_formfield( 'rememberme', 'checkbox', 'forever' ) . '&nbsp;' . '<label for="rememberme">' . $wpmem->get_text( 'remember_me' ) . '</label>&nbsp;&nbsp;' . $args['n'] : '';
		$buttons =  $args['remember_check'] . $args['t'] . '<input type="submit" name="Submit" value="' . $arr['button_text'] . '" class="' . $args['button_class'] . '" />' . $args['n'];
	} else {
		$buttons = '<input type="submit" name="Submit" value="' . $arr['button_text'] . '" class="' . $args['button_class'] . '" />' . $args['n'];
	}
	
	/**
	 * Filter the HTML for form buttons.
	 *
	 * The string includes the buttons, as well as the before/after wrapper elements.
	 *
	 * @since 2.9.0
	 *
	 * @param string $buttons        The generated HTML of the form buttons.
	 * @param string $arr['action']  The action being performed by the form. login|pwdreset|pwdchange|getusername.
	 */
	$form = $form . apply_filters( 'wpmem_login_form_buttons', $args['buttons_before'] . $args['n'] . $buttons . $args['buttons_after'] . $args['n'], $arr['action'] );

	if ( ( $wpmem->user_pages['profile'] != null || $page == 'members' ) && $arr['action'] == 'login' ) { 
		
		/**
		 * Filters the forgot password link.
		 *
		 * @since 2.8.0
		 *
		 * @param string The forgot password link.
		 */
		$link = apply_filters( 'wpmem_forgot_link', add_query_arg( 'a', 'pwdreset', $wpmem->user_pages['profile'] ) );
		$str  = $wpmem->get_text( 'forgot_link_before' ) . '<a href="' . $link . '">' . $wpmem->get_text( 'forgot_link' ) . '</a>';
		/**
		 * Filters the forgot password HTML.
		 *
		 * @since 2.9.0
		 * @since 3.0.9 Added $link parameter.
		 *
		 * @param string $str  The forgot password link HTML.
		 * @param string $link The forgot password link.
		 */
		$form = $form . $args['link_before'] . apply_filters( 'wpmem_forgot_link_str', $str, $link ) . $args['link_after'] . $args['n'];
		
	}
	
	if ( ( $wpmem->user_pages['register'] != null ) && $arr['action'] == 'login' ) { 

		/**
		 * Filters the link to the registration page.
		 *
		 * @since 2.8.0
		 *
		 * @param string The registration page link.
		 */
		$link = apply_filters( 'wpmem_reg_link', $wpmem->user_pages['register'] );
		$str  = $wpmem->get_text( 'register_link_before' ) . '<a href="' . $link . '">' . $wpmem->get_text( 'register_link' ) . '</a>';
		/**
		 * Filters the register link HTML.
		 *
		 * @since 2.9.0
		 * @since 3.0.9 Added $link parameter.
		 *
		 * @param string $str  The register link HTML.
		 * @param string $link The register link.
		 */
		$form = $form . $args['link_before'] . apply_filters( 'wpmem_reg_link_str', $str, $link ) . $args['link_after'] . $args['n'];
		
	}
	
	if ( ( $wpmem->user_pages['profile'] != null || $page == 'members' ) && $arr['action'] == 'pwdreset' ) {
		
		/**
		 * Filters the forgot username link.
		 *
		 * @since 3.0.9
		 *
		 * @param string The forgot username link.
		 */
		$link = apply_filters( 'wpmem_username_link',  add_query_arg( 'a', 'getusername', $wpmem->user_pages['profile'] ) );
		$str  = $wpmem->get_text( 'username_link_before' ) . '<a href="' . $link . '">' . $wpmem->get_text( 'username_link' ) . '</a>';
		/**
		 * Filters the forgot username link HTML.
		 *
		 * @since 3.0.9
		 *
		 * @param string $str  The forgot username link HTML.
		 * @param string $link The forgot username link.
		 */
		$form = $form . $args['link_before'] . apply_filters( 'wpmem_username_link_str', $str, $link ) . $args['link_after'] . $args['n'];
		
	}
	
	// Apply the heading.
	$form = $args['heading_before'] . $arr['heading'] . $args['heading_after'] . $args['n'] . $form;
	
	// Apply fieldset wrapper.
	$form = $args['fieldset_before'] . $args['n'] . $form . $args['fieldset_after'] . $args['n'];
	
	// Apply form wrapper.
	$form = '<form action="' . get_permalink() . '" method="POST" id="' . $args['form_id'] . '" class="' . $args['form_class'] . '">' . $args['n'] . $form . '</form>';
	
	// Apply anchor.
	$form = '<a name="' . $arr['action'] . '"></a>' . $args['n'] . $form;
	
	// Apply main wrapper.
	$form = $args['main_div_before'] . $args['n'] . $form . $args['n'] . $args['main_div_after'];
	
	// Apply wpmem_txt wrapper.
	$form = $args['txt_before'] . $form . $args['txt_after'];
	
	// Remove line breaks.
	$form = ( $args['strip_breaks'] ) ? str_replace( array( "\n", "\r", "\t" ), array( '','','' ), $form ) : $form;
	
	/**
	 * Filter the generated HTML of the entire form.
	 *
	 * @since 2.7.4
	 *
	 * @param string $form          The HTML of the final generated form.
	 * @param string $arr['action'] The action being performed by the form. login|pwdreset|pwdchange|getusername.
	 */
	$form = apply_filters( 'wpmem_login_form', $form, $arr['action'] );
	
	/**
	 * Filter before the form.
	 *
	 * This rarely used filter allows you to stick any string onto the front of
	 * the generated form.
	 *
	 * @since 2.7.4
	 *
	 * @param string $str           The HTML to add before the form. Default null.
	 * @param string $arr['action'] The action being performed by the form. login|pwdreset|pwdchange|getusername.
	 */
	$form = apply_filters( 'wpmem_login_form_before', '', $arr['action'] ) . $form;
	
	return $form;
} // End wpmem_login_form.
endif;


if ( ! function_exists( 'wpmem_inc_registration' ) ):
/**
 * Registration Form Dialog.
 *
 * Outputs the form for new user registration and existing user edits.
 *
 * @since 2.5.1
 *
 * @global object $wpmem        The WP_Members object.
 * @global string $wpmem_regchk Used to determine if the form is in an error state.
 * @global array  $userdata     Used to get the user's registration data if they are logged in (user profile edit).
 * @param  string $tag          (optional) Toggles between new registration ('new') and user profile edit ('edit').
 * @param  string $heading      (optional) The heading text for the form, null (default) for new registration.
 * @return string $form         The HTML for the entire form as a string.
 */
function wpmem_inc_registration( $tag = 'new', $heading = '', $redirect_to = null ) {

	global $wpmem, $wpmem_regchk, $userdata; 
	
	// Set up default wrappers.
	$defaults = array(
		
		// Wrappers.
		'heading_before'   => '<legend>',
		'heading_after'    => '</legend>',
		'fieldset_before'  => '<fieldset>',
		'fieldset_after'   => '</fieldset>',
		'main_div_before'  => '<div id="wpmem_reg">',
		'main_div_after'   => '</div>',
		'txt_before'       => '[wpmem_txt]',
		'txt_after'        => '[/wpmem_txt]',
		'row_before'       => '',
		'row_after'        => '',
		'buttons_before'   => '<div class="button_div">',
		'buttons_after'    => '</div>',
		
		// Classes & ids.
		'form_id'          => '',
		'form_class'       => 'form',
		'button_id'        => '',
		'button_class'     => 'buttons',
		
		// Required field tags and text.
		'req_mark'         => $wpmem->get_text( 'register_req_mark' ),
		'req_label'        => $wpmem->get_text( 'register_required' ),
		'req_label_before' => '<div class="req-text">',
		'req_label_after'  => '</div>',
		
		// Buttons.
		'show_clear_form'  => false,
		'clear_form'       => $wpmem->get_text( 'register_clear' ),
		'submit_register'  => $wpmem->get_text( 'register_submit' ),
		'submit_update'    => $wpmem->get_text( 'profile_submit' ),
		
		// Other.
		'strip_breaks'     => true,
		'use_nonce'        => false,
		'wrap_inputs'      => true,
		'n'                => "\n",
		't'                => "\t",
		
	);
	
	/**
	 * Filter the default form arguments.
	 *
	 * This filter accepts an array of various elements to replace the form defaults. This
	 * includes default tags, labels, text, and small items including various booleans.
	 *
	 * @since 2.9.0
	 *
	 * @param array        An array of arguments to merge with defaults. Default null.
	 * @param string $tag  Toggle new registration or profile update. new|edit.
	 */
	$args = apply_filters( 'wpmem_register_form_args', '', $tag );
	
	// Merge $args with defaults.
	$args = wp_parse_args( $args, $defaults );
	
	// Username is editable if new reg, otherwise user profile is not.
	if ( $tag == 'edit' ) {
		// This is the User Profile edit - username is not editable.
		$val   = $userdata->user_login;
		$label = '<label for="user_login" class="text">' . $wpmem->get_text( 'profile_username' ) . '</label>';
		$input = '<p class="noinput">' . $val . '</p>';
		$field_before = ( $args['wrap_inputs'] ) ? '<div class="div_text">' : '';
		$field_after  = ( $args['wrap_inputs'] ) ? '</div>' : '';
	} else { 
		// This is a new registration.
		$val   = ( isset( $_POST['user_login'] ) ) ? stripslashes( $_POST['user_login'] ) : '';
		$label = '<label for="user_login" class="text">' . $wpmem->get_text( 'register_username' ) . $args['req_mark'] . '</label>';
		$input = wpmem_form_field( array( 
			'name'     => 'user_login',
			'type'     => 'text',
			'value'    => $val,
			'compare'  => '',
			'required' => true,
		) );

	}
	$field_before = ( $args['wrap_inputs'] ) ? '<div class="div_text">' : '';
	$field_after  = ( $args['wrap_inputs'] ) ? '</div>': '';
	
	// Add the username row to the array.
	$rows['username'] = array( 
		'meta'         => 'username',
		'type'         => 'text',
		'value'        => $val,
		'label_text'   => $wpmem->get_text( 'register_username' ),
		'row_before'   => $args['row_before'],
		'label'        => $label,
		'field_before' => $field_before,
		'field'        => $input,
		'field_after'  => $field_after,
		'row_after'    => $args['row_after'],
	);

	/**
	 * Filter the array of form fields.
	 *
	 * The form fields are stored in the WP options table as wpmembers_fields. This
	 * filter can filter that array after the option is retreived before the fields
	 * are parsed. This allows you to change the fields that may be used in the form
	 * on the fly.
	 *
	 * @since 2.9.0
	 *
	 * @param array        The array of form fields.
	 * @param string $tag  Toggle new registration or profile update. new|edit.
	 */
	$wpmem_fields = apply_filters( 'wpmem_register_fields_arr', wpmem_fields(), $tag );
	//$wpmem_fields = wpmem_verify_fields( $wpmem_fields );
	
	$hidden = '';
	
	// Loop through the remaining fields.
	foreach ( $wpmem_fields as $meta_key => $field ) {

		// Start with a clean row.
		$val = ''; $label = ''; $input = ''; $field_before = ''; $field_after = '';
		
		// Skips user selected passwords for profile update.
		$pass_arr = array( 'password', 'confirm_password', 'password_confirm' );
		$do_row = ( $tag == 'edit' && in_array( $meta_key, $pass_arr ) ) ? false : true;
		
		// Skips tos, makes tos field hidden on user edit page, unless they haven't got a value for tos.
		if ( $meta_key == 'tos' && $tag == 'edit' && ( get_user_meta( $userdata->ID, 'tos', true ) ) ) { 
			$do_row = false; 
			$hidden_tos = wpmem_form_field( array(
				'name'  => $meta_key, 
				'type'  => 'hidden', 
				'value' => get_user_meta( $userdata->ID, 'tos', true )
			) );
		}
		
		// Handle hidden fields
		if ( 'hidden' == $field['type'] ) {
			$do_row = false;
			$hidden.= wpmem_form_field( array( 
				'name'     => $meta_key,
				'type'     => $field['type'],
				'value'    => $field['value'],
				'compare'  => $valtochk,
				//'class'    => ( $class ) ? $class : 'textbox',
				'required' => $field['required'],
			) );
		}
		
		// If the field is set to display and we aren't skipping, construct the row.
		if ( $do_row && $field['register'] ) {

			// Label for all but TOS.
			if ( $meta_key != 'tos' ) {

				$class = ( $field['type'] == 'password' || $field['type'] == 'email' || $field['type'] == 'url' ) ? 'text' : $field['type'];
				
				$label = '<label for="' . $meta_key . '" class="' . $class . '">' . __( $field['label'], 'wp-members' );
				$label = ( $field['required'] ) ? $label . $args['req_mark'] : $label;
				$label = $label . '</label>';

			} 

			// Gets the field value for both edit profile and submitted reg w/ error.
			if ( ( $tag == 'edit' ) && ( $wpmem_regchk != 'updaterr' ) ) { 

				switch ( $meta_key ) {
					case( 'description' ):
						$val = htmlspecialchars( get_user_meta( $userdata->ID, 'description', 'true' ) );
						break;

					case 'user_email':
					case 'confirm_email':
						$val = sanitize_email( $userdata->user_email );
						break;

					case 'user_url':
						$val = esc_url( $userdata->user_url );
						break;
						
					case 'display_name':
						$val = sanitize_text_field( $userdata->display_name );
						break; 

					default:
						$val = sanitize_text_field( get_user_meta( $userdata->ID, $meta_key, 'true' ) );
						break;
				}

			} else {
				if ( 'file' == $field['type'] ) {
					$val = ( isset( $_FILES[ $meta_key ]['name'] ) ) ? $_FILES[ $meta_key ]['name'] : '' ;
				} else {
					$val = ( isset( $_POST[ $meta_key ] ) ) ? $_POST[ $meta_key ] : '';
				}
			}
			
			// Does the tos field.
			if ( $meta_key == 'tos' ) {

				$val = ( isset( $_POST[ $meta_key ] ) ) ? $_POST[ $meta_key ] : ''; 

				// Should be checked by default? and only if form hasn't been submitted.
				$val   = ( ! $_POST && $field['checked_default'] ) ? $field['checked_value'] : $val;
				$input = wpmem_form_field( array(
					'name'     => $meta_key, 
					'type'     => $field['type'], 
					'value'    => $field['checked_value'], 
					'compare'  => $val 
				) );
				$input = ( $field['required'] ) ? $input . $args['req_mark'] : $input;

				// Determine if TOS is a WP page or not.
				$tos_content = stripslashes( get_option( 'wpmembers_tos' ) );
				if ( has_shortcode( $tos_content, 'wp-members' ) || has_shortcode( $tos_content, 'wpmem_tos' ) ) {	
					$link = do_shortcode( $tos_content );
					$tos_pop = '<a href="' . $link . '" target="_blank">';
				} else { 
					$tos_pop = "<a href=\"#\" onClick=\"window.open('" . WPMEM_DIR . "/wp-members-tos.php','mywindow');\">";
				}
				
				/**
				 * Filter the TOS link text.
				 *
				 * @since 2.7.5
				 *
				 * @param string       The link text.
				 * @param string $tag  Toggle new registration or profile update. new|edit.
				 */
				$input.= apply_filters( 'wpmem_tos_link_txt', sprintf( $wpmem->get_text( 'register_tos' ), $tos_pop, '</a>' ), $tag );
				
				// In previous versions, the div class would end up being the same as the row before.
				$field_before = ( $args['wrap_inputs'] ) ? '<div class="div_text">' : '';
				$field_after  = ( $args['wrap_inputs'] ) ? '</div>' : '';

			} else {

				// For checkboxes.
				if ( $field['type'] == 'checkbox' ) { 
					$valtochk = $val;
					$val = $field['checked_value']; 
					// if it should it be checked by default (& only if form not submitted), then override above...
					if ( $field['checked_default'] && ( ! $_POST && $tag != 'edit' ) ) { $val = $valtochk = $field['checked_value']; }
				}

				// For dropdown select.
				if ( $field['type'] == 'select' || $field['type'] == 'radio' || $field['type'] == 'multiselect' || $field['type'] == 'multicheckbox' ) {
					$valtochk = $val;
					$val = $field['values'];
				}

				if ( ! isset( $valtochk ) ) { $valtochk = ''; }
				
				if ( 'edit' == $tag && ( 'file' == $field['type'] || 'image' == $field['type'] ) ) {
					
					$attachment_url = wp_get_attachment_url( $val );
					$empty_file = '<span class="description">' . __( 'None' ) . '</span>';
					if ( 'file' == $field['type'] ) {
						$input = ( $attachment_url ) ? '<a href="' . $attachment_url . '">' . get_the_title( $val ) . '</a>' : $empty_file;
					} else {
						$input = ( $attachment_url ) ? '<img src="' . $attachment_url . '">' : $empty_file;
					}
					// @todo - come up with a way to handle file updates - user profile form does not support multitype
					$input.= '<br />' . $wpmem->get_text( 'profile_upload' ) . '<br />';
					$input.= wpmem_form_field( array(
						'name'    => $meta_key, 
						'type'    => $field['type'], 
						'value'   => $val, 
						'compare' => $valtochk,
					) );
					
				} else {
				
					// For all other input types.
					//$input = wpmem_create_formfield( $meta_key, $field['type'], $val, $valtochk );
					$formfield_args = array( 
						'name'     => $meta_key,
						'type'     => $field['type'],
						'value'    => $val,
						'compare'  => $valtochk,
						//'class'    => ( $class ) ? $class : 'textbox',
						'required' => $field['required'],
						'placeholder' => ( isset( $field['placeholder'] ) ) ? $field['placeholder'] : '',
					);
					if ( 'multicheckbox' == $field['type'] || 'multiselect' == $field['type'] ) {
						$formfield_args['delimiter'] = $field['delimiter'];
					}
					$input = wpmem_form_field( $formfield_args );
				
				}
				
				// Determine input wrappers.
				$field_before = ( $args['wrap_inputs'] ) ? '<div class="div_' . $class . '">' : '';
				$field_after  = ( $args['wrap_inputs'] ) ? '</div>' : '';
			}

		}

		// If the row is set to display, add the row to the form array.
		if ( $field['register'] ) {
			
			$values = '';
			if ( 'multicheckbox' == $field['type'] || 'select' == $field['type'] || 'multiselect' == $field['type'] || 'radio' == $field['type'] ) {
				$values = $val;
				$val = $valtochk;
			}
			
			$rows[ $meta_key ] = array(
				'meta'         => $meta_key,
				'type'         => $field['type'],
				'value'        => $val,
				'values'       => $values,
				'label_text'   => __( $field['label'], 'wp-members' ),
				'row_before'   => $args['row_before'],
				'label'        => $label,
				'field_before' => $field_before,
				'field'        => $input,
				'field_after'  => $field_after,
				'row_after'    => $args['row_after'],
			);
		}
	}
	
	// If captcha is Really Simple CAPTCHA.
	if ( $wpmem->captcha == 2 && $tag != 'edit' ) {
		$row = wpmem_build_rs_captcha();
		$rows['captcha'] = array(
			'meta'         => '', 
			'type'         => 'text', 
			'value'        => '',
			'values'       => '',
			'label_text'   => $row['label_text'],
			'row_before'   => $args['row_before'],
			'label'        => $row['label'],
			'field_before' => $field_before,
			'field'        => $row['field'],
			'field_after'  => $field_after,
			'row_after'    => $args['row_after'],
		);
	}
	
	/**
	 * Filter the array of form rows.
	 *
	 * This filter receives an array of the main rows in the form, each array element being
	 * an array of that particular row's pieces. This allows making changes to individual 
	 * parts of a row without needing to parse through a string of HTML.
	 *
	 * @since 2.9.0
	 * @since 3.0.9 Added $rows['label_text'].
	 * @since 3.1.0 Added $rows['key'].
	 * @since 3.1.6 Deprecated $rows['order'].
	 *
	 * @param array  $rows    {
	 *     An array containing the form rows. 
	 *
	 *     @type string order        Field display order. (deprecated as of 3.1.6)
	 *     @type string meta         Field meta tag (not used for display).
	 *     @type string type         Input field type (not used for display).
	 *     @type string value        Input field value (not used for display).
	 *     @type string values       Possible field values (dropdown, multiple select/check, radio).
	 *     @type string label_text   Raw text for the label (not used for display).
	 *     @type string row_before   Opening wrapper tag around the row.
	 *     @type string label        Label tag.
	 *     @type string field_before Opening wrapper tag before the input tag.
	 *     @type string field        The field input tag.
	 *     @type string field_after  Closing wrapper tag around the input tag.
	 *     @type string row_after    Closing wrapper tag around the row.
	 * }
	 * @param string $tag  Toggle new registration or profile update. new|edit.
	 */
	$rows = apply_filters( 'wpmem_register_form_rows', $rows, $tag );
	
	// Put the rows from the array into $form.
	$form = ''; $enctype = '';
	foreach ( $rows as $row_item ) {
		$enctype = ( $row_item['type'] == 'file' ||  $row_item['type'] == 'image' ) ? "multipart/form-data" : $enctype;
		$row  = ( $row_item['row_before']   != '' ) ? $row_item['row_before'] . $args['n'] . $row_item['label'] . $args['n'] : $row_item['label'] . $args['n'];
		$row .= ( $row_item['field_before'] != '' ) ? $row_item['field_before'] . $args['n'] . $args['t'] . $row_item['field'] . $args['n'] . $row_item['field_after'] . $args['n'] : $row_item['field'] . $args['n'];
		$row .= ( $row_item['row_after']    != '' ) ? $row_item['row_after'] . $args['n'] : '';
		$form.= $row;
	}
	
	// Do recaptcha if enabled.
	if ( ( $wpmem->captcha == 1 || $wpmem->captcha == 3 ) && $tag != 'edit' ) { // don't show on edit page!
		
		// Get the captcha options.
		$wpmem_captcha = get_option( 'wpmembers_captcha' ); 
		
		// Start with a clean row.
		$row = '';
		$row = '<div class="clear"></div>';
		$row.= '<div align="right" class="captcha">' . wpmem_inc_recaptcha( $wpmem_captcha['recaptcha'] ) . '</div>';
		
		// Add the captcha row to the form.
		/**
		 * Filter the HTML for the CAPTCHA row.
		 *
		 * @since 2.9.0
		 *
		 * @param string       The HTML for the entire row (includes HTML tags plus reCAPTCHA).
		 * @param string $tag  Toggle new registration or profile update. new|edit.
		 */
		$form.= apply_filters( 'wpmem_register_captcha_row', $args['row_before'] . $row . $args['row_after'], $tag );
	}

	// Create hidden fields.
	$var         = ( $tag == 'edit' ) ? 'update' : 'register';
	$redirect_to = ( isset( $_REQUEST['redirect_to'] ) ) ? esc_url( $_REQUEST['redirect_to'] ) : ( ( $redirect_to ) ? $redirect_to : get_permalink() );
	$hidden     .= '<input name="a" type="hidden" value="' . $var . '" />' . $args['n'];
	$hidden     .= '<input name="wpmem_reg_page" type="hidden" value="' . get_permalink() . '" />' . $args['n'];
	if ( $redirect_to != get_permalink() ) {
		$hidden     .= '<input name="redirect_to" type="hidden" value="' . $redirect_to . '" />' . $args['n'];
	}
	$hidden      = ( isset( $hidden_tos ) ) ? $hidden . $hidden_tos . $args['n'] : $hidden;
	
	/**
	 * Filter the hidden field HTML.
	 *
	 * @since 2.9.0
	 *
	 * @param string $hidden The generated HTML of hidden fields.
	 * @param string $tag    Toggle new registration or profile update. new|edit.
	 */
	$hidden = apply_filters( 'wpmem_register_hidden_fields', $hidden, $tag );
	
	// Add the hidden fields to the form.
	$form.= $hidden;
	
	// Create buttons and wrapper.
	$button_text = ( $tag == 'edit' ) ? $args['submit_update'] : $args['submit_register'];
	$buttons = ( $args['show_clear_form'] ) ? '<input name="reset" type="reset" value="' . $args['clear_form'] . '" class="' . $args['button_class'] . '" /> ' . $args['n'] : '';
	$buttons.= '<input name="submit" type="submit" value="' . $button_text . '" class="' . $args['button_class'] . '" />' . $args['n'];
	
	/**
	 * Filter the HTML for form buttons.
	 *
	 * The string passed through the filter includes the buttons, as well as the HTML wrapper elements.
	 *
	 * @since 2.9.0
	 *
	 * @param string $buttons The generated HTML of the form buttons.
	 * @param string $tag     Toggle new registration or profile update. new|edit.
	 */
	$buttons = apply_filters( 'wpmem_register_form_buttons', $buttons, $tag );
	
	// Add the buttons to the form.
	$form.= $args['buttons_before'] . $args['n'] . $buttons . $args['buttons_after'] . $args['n'];

	// Add the required field notation to the bottom of the form.
	$form.= $args['req_label_before'] . $args['req_label'] . $args['req_label_after'];
	
	// Apply the heading.
	/**
	 * Filter the registration form heading.
	 *
	 * @since 2.8.2
	 *
	 * @param string $str
	 * @param string $tag Toggle new registration or profile update. new|edit.
	 */
	$heading = ( !$heading ) ? apply_filters( 'wpmem_register_heading', $wpmem->get_text( 'register_heading' ), $tag ) : $heading;
	$form = $args['heading_before'] . $heading . $args['heading_after'] . $args['n'] . $form;
	
	// Apply fieldset wrapper.
	$form = $args['fieldset_before'] . $args['n'] . $form . $args['n'] . $args['fieldset_after'];
	
	// Apply attribution if enabled.
	$form = $form . wpmem_inc_attribution();
	
	// Apply nonce.
	$form = ( defined( 'WPMEM_USE_NONCE' ) || $args['use_nonce'] ) ? wp_nonce_field( 'wpmem-validate-submit', 'wpmem-form-submit' ) . $args['n'] . $form : $form;
	
	// Apply form wrapper.
	$enctype = ( $enctype == 'multipart/form-data' ) ? ' enctype="multipart/form-data"' : '';
	$post_to = get_permalink();
	$form = '<form name="form" method="post"' . $enctype . ' action="' . $post_to . '" id="' . $args['form_id'] . '" class="' . $args['form_class'] . '">' . $args['n'] . $form . $args['n'] . '</form>';
	
	// Apply anchor.
	$form = '<a name="register"></a>' . $args['n'] . $form;
	
	// Apply main div wrapper.
	$form = $args['main_div_before'] . $args['n'] . $form . $args['n'] . $args['main_div_after'] . $args['n'];
	
	// Apply wpmem_txt wrapper.
	$form = $args['txt_before'] . $form . $args['txt_after'];
	
	// Remove line breaks if enabled for easier filtering later.
	$form = ( $args['strip_breaks'] ) ? str_replace( array( "\n", "\r", "\t" ), array( '','','' ), $form ) : $form;
	
	/**
	 * Filter the generated HTML of the entire form.
	 *
	 * @since 2.7.4
	 *
	 * @param string $form The HTML of the final generated form.
	 * @param string $tag  Toggle new registration or profile update. new|edit.
	 * @param array  $rows   {
	 *     An array containing the form rows. 
	 *
	 *     @type string order        Field display order.
	 *     @type string meta         Field meta tag (not used for display).
	 *     @type string type         Input field type (not used for display).
	 *     @type string value        Input field value (not used for display).
	 *     @type string values       The possible values for the field (dropdown, multiple select/checkbox, radio group).
	 *     @type string label_text   Raw text for the label (not used for display).
	 *     @type string row_before   Opening wrapper tag around the row.
	 *     @type string label        Label tag.
	 *     @type string field_before Opening wrapper tag before the input tag.
	 *     @type string field        The field input tag.
	 *     @type string field_after  Closing wrapper tag around the input tag.
	 *     @type string row_after    Closing wrapper tag around the row.
	 * }
	 * @param string $hidden The HTML string of hidden fields
	 */
	$form = apply_filters( 'wpmem_register_form', $form, $tag, $rows, $hidden );
	
	/**
	 * Filter before the form.
	 *
	 * This rarely used filter allows you to stick any string onto the front of
	 * the generated form.
	 *
	 * @since 2.7.4
	 *
	 * @param string $str The HTML to add before the form. Default null.
	 * @param string $tag Toggle new registration or profile update. new|edit.
	 */
	$form = apply_filters( 'wpmem_register_form_before', '', $tag ) . $form;

	// Return the generated form.
	return $form;
} // End wpmem_inc_registration.
endif;


if ( ! function_exists( 'wpmem_inc_recaptcha' ) ):
/**
 * Create reCAPTCHA form.
 *
 * @since  2.6.0
 *
 * @param  array  $arr
 * @return string $str HTML for reCAPTCHA display.
 */
function wpmem_inc_recaptcha( $arr ) {

	// Determine if reCAPTCHA should be another language.
	$allowed_langs = array( 'nl', 'fr', 'de', 'pt', 'ru', 'es', 'tr' );
	/** This filter is documented in wp-includes/l10n.php */
	$locale = apply_filters( 'plugin_locale', get_locale(), 'wp-members' );
	$compare_lang  = strtolower( substr( $locale, -2 ) );
	$use_the_lang  = ( in_array( $compare_lang, $allowed_langs ) ) ? $compare_lang : false;
	$lang = ( $use_the_lang  ) ? ' lang : \'' . $use_the_lang  . '\'' : '';	

	// Determine if we need ssl.
	$http = wpmem_use_ssl();

	global $wpmem;
	if ( $wpmem->captcha == 1 ) {

		$str  = '<script type="text/javascript">
			var RecaptchaOptions = { theme : \''. $arr['theme'] . '\'' . $lang . ' };
		</script>
		<script type="text/javascript" src="' . $http . 'www.google.com/recaptcha/api/challenge?k=' . $arr['public'] . '"></script>
		<noscript>
			<iframe src="' . $http . 'www.google.com/recaptcha/api/noscript?k=' . $arr['public'] . '" height="300" width="500" frameborder="0"></iframe><br/>
			<textarea name="recaptcha_challenge_field" rows="3" cols="40"></textarea>
			<input type="hidden" name="recaptcha_response_field" value="manual_challenge"/>
		</noscript>';
	
	} elseif ( $wpmem->captcha == 3 ) {
		
		$str = '<script src="https://www.google.com/recaptcha/api.js" async defer></script>
		<div class="g-recaptcha" data-sitekey="' . $arr['public'] . '"></div>';
	}

	/**
	 * Filter the reCAPTCHA HTML.
	 *
	 * @since 2.7.4
	 *
	 * @param string $str A string of HTML for the reCAPTCHA.
 	 */
	$str = apply_filters( 'wpmem_recaptcha', $str );
	
	return $str;
}
endif;


/**
 * Create an attribution link in the form.
 *
 * @since 2.6.0
 * @since 3.1.1 Updated to use new object setting.
 *
 * @global object $wpmem
 * @return string $str
 */
function wpmem_inc_attribution() {

	global $wpmem;
	$http = ( is_ssl() ) ? 'https://' : 'http://';
	$str = '
	<div align="center">
		<small>Powered by <a href="' . $http . 'rocketgeek.com" target="_blank">WP-Members</a></small>
	</div>';
		
	return ( 1 == $wpmem->attrib ) ? $str : '';
}


/**
 * Create Really Simple CAPTCHA.
 *
 * @since 2.9.5
 *
 * @global object $wpmem The WP_Members object.
 * @return array {
 *     HTML Form elements for Really Simple CAPTCHA.
 *
 *     @type string label_text The raw text used for the label.
 *     @type string label      The HTML for the label.
 *     @type string field      The input tag and the CAPTCHA image.
 * }
 */
function wpmem_build_rs_captcha() {
	
	global $wpmem;

	if ( defined( 'REALLYSIMPLECAPTCHA_VERSION' ) ) {
		// setup defaults								
		$defaults = array( 
			'characters'   => 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789',
			'num_char'     => '4',
			'dim_w'        => '72',
			'dim_h'        => '30',
			'font_color'   => '0,0,0',
			'bg_color'     => '255,255,255',
			'font_size'    => '12',
			'kerning'      => '14',
			'img_type'     => 'png',
		);
		$wpmem_captcha = get_option( 'wpmembers_captcha' );
		
		$args = ( isset( $wpmem_captcha['really_simple'] ) && is_array( $wpmem_captcha['really_simple'] ) ) ? $wpmem_captcha['really_simple'] : array();
		$args = wp_parse_args( $args, $defaults );
		
		$img_size = array( $args['dim_w'], $args['dim_h'] );
		$fg       = explode( ",", $args['font_color'] );
		$bg       = explode( ",", $args['bg_color'] );
		
		$wpmem_captcha = new ReallySimpleCaptcha();
		$wpmem_captcha->chars = $args['characters'];
		$wpmem_captcha->char_length = $args['num_char'];
		$wpmem_captcha->img_size = $img_size;
		$wpmem_captcha->fg = $fg;
		$wpmem_captcha->bg = $bg;
		$wpmem_captcha->font_size = $args['font_size'];
		$wpmem_captcha->font_char_width = $args['kerning'];
		$wpmem_captcha->img_type = $args['img_type'];

		$wpmem_captcha_word   = $wpmem_captcha->generate_random_word();
		$wpmem_captcha_prefix = mt_rand();
		$wpmem_captcha_image_name = $wpmem_captcha->generate_image( $wpmem_captcha_prefix, $wpmem_captcha_word );
		
		/**
		 * Filters the default Really Simple Captcha folder location.
		 *
		 * @since 3.0
		 *
		 * @param string The default location of RS Captcha.
		 */
		$wpmem_captcha_image_url = apply_filters( 'wpmem_rs_captcha_folder', get_bloginfo('wpurl') . '/wp-content/plugins/really-simple-captcha/tmp/' );

		$img_w = $wpmem_captcha->img_size[0];
		$img_h = $wpmem_captcha->img_size[1];
		$src   = $wpmem_captcha_image_url . $wpmem_captcha_image_name;
		$size  = $wpmem_captcha->char_length;
		$pre   = $wpmem_captcha_prefix;

		return array( 
			'label_text' => $wpmem->get_text( 'register_rscaptcha' ),
			'label'      => '<label class="text" for="captcha">' . $wpmem->get_text( 'register_rscaptcha' ) . '</label>',
			'field'      => '<input id="captcha_code" name="captcha_code" size="'.$size.'" type="text" />
					<input id="captcha_prefix" name="captcha_prefix" type="hidden" value="' . $pre . '" />
					<img src="'.$src.'" alt="captcha" width="'.$img_w.'" height="'.$img_h.'" />'
		);
	} else {
		return;
	}
}

// End of file.