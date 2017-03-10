<?php
/**
 * WP-Members Form Building Functions
 *
 * Handles functions that build the various forms.
 * 
 * This file is part of the WP-Members plugin by Chad Butler
 * You can find out more about this plugin at http://rocketgeek.com
 * Copyright (c) 2006-2017 Chad Butler
 * WP-Members(tm) is a trademark of butlerblog.com
 *
 * @package WP-Members
 * @subpackage WP-Members Form Building Functions
 * @author Chad Butler
 * @copyright 2006-2017
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
			$str = '<div id="wpmem_restricted_msg"><p>' . $msg . '</p></div>';
			
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
 * @since 3.1.7 Now a wrapper for $wpmem->forms->login_form()
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
	return $wpmem->forms->login_form( $page, $arr );
}
endif;


if ( ! function_exists( 'wpmem_inc_registration' ) ):
/**
 * Registration Form Dialog.
 *
 * Outputs the form for new user registration and existing user edits.
 *
 * @since 2.5.1
 * @since 3.1.7 Now a wrapper for $wpmem->forms->register_form()
 *
 * @global object $wpmem        The WP_Members object.
 * @param  string $tag          (optional) Toggles between new registration ('new') and user profile edit ('edit').
 * @param  string $heading      (optional) The heading text for the form, null (default) for new registration.
 * @return string $form         The HTML for the entire form as a string.
 */
function wpmem_inc_registration( $tag = 'new', $heading = '', $redirect_to = null ) {
	global $wpmem; 
	return $wpmem->forms->register_form( $tag, $heading, $redirect_to );
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