<?php
/**
 * WP-Members Form Building Functions
 *
 * Handles functions that build the various forms.
 * 
 * This file is part of the WP-Members plugin by Chad Butler
 * You can find out more about this plugin at https://rocketgeek.com
 * Copyright (c) 2006-2019 Chad Butler
 * WP-Members(tm) is a trademark of butlerblog.com
 *
 * @package WP-Members
 * @subpackage WP-Members Form Building Functions
 * @author Chad Butler
 * @copyright 2006-2019
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

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

if ( ! function_exists( 'wpmem_inc_login' ) ):
/**
 * Login Dialog.
 *
 * Loads the login form for user login.
 *
 * @since 1.8
 * @since 3.1.4 Global $wpmem_regchk no longer needed.
 * @since 3.2.0 Now a wrapper for $wpmem->forms->do_login_form()
 *
 * @global object $post         The WordPress Post object.
 * @global object $wpmem        The WP_Members object.
 * @param  string $page         If the form is being displayed in place of blocked content. Default: page.
 * @param  string $redirect_to  Redirect URL. Default: null.
 * @param  string $show         If the form is being displayed in place of blocked content. Default: show.
 * @return string $str          The generated html for the login form.
 */
function wpmem_inc_login( $page = "page", $redirect_to = null, $show = 'show' ) {
	global $wpmem;
	return $wpmem->forms->do_login_form( $page, $redirect_to, $show );
}
endif;


if ( ! function_exists( 'wpmem_inc_changepassword' ) ):
/**
 * Change Password Dialog.
 *
 * Loads the form for changing password.
 *
 * @since 2.0.0
 * @since 3.2.0 Now a wrapper for $wpmem->forms->do_changepassword_form()
 *
 * @global object $wpmem The WP_Members object.
 * @return string $str   The generated html for the change password form.
 */
function wpmem_inc_changepassword() {
	global $wpmem;
	return $wpmem->forms->do_changepassword_form();
}
endif;


if ( ! function_exists( 'wpmem_inc_resetpassword' ) ):
/**
 * Reset Password Dialog.
 *
 * Loads the form for resetting password.
 *
 * @since 2.1.0
 * @since 3.2.0 Now a wrapper for $wpmem->forms->do_resetpassword_form()
 *
 * @global object $wpmem The WP_Members object.
 * @return string $str   The generated html fo the reset password form.
 */
function wpmem_inc_resetpassword() { 
	global $wpmem;
	return $wpmem->forms->do_resetpassword_form();
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
	$args = $arr;
	$args['page'] = $page;
	return $wpmem->forms->login_form( $args );
}
endif;

/**
 * Forgot Username Form.
 *
 * Loads the form for retrieving a username.
 *
 * @since 3.0.8
 * @since 3.2.0 Moved to forms.php.
 *
 * @global object $wpmem The WP_Members object class.
 * @return string $str   The generated html for the forgot username form.
 */
function wpmem_inc_forgotusername() {
	global $wpmem;
	return $wpmem->forms->do_forgotusername_form();
}


if ( ! function_exists( 'wpmem_inc_registration' ) ):
/**
 * Registration Form Dialog.
 *
 * Outputs the form for new user registration and existing user edits.
 *
 * @since 2.5.1
 * @since 3.1.7 Now a wrapper for $wpmem->forms->register_form()
 * @since 3.2.0 Preparing for deprecation, use wpmem_register_form() instead.
 *
 * @global object $wpmem        The WP_Members object.
 * @param  string $tag          (optional) Toggles between new registration ('new') and user profile edit ('edit').
 * @param  string $heading      (optional) The heading text for the form, null (default) for new registration.
 * @return string $form         The HTML for the entire form as a string.
 */
function wpmem_inc_registration( $tag = 'new', $heading = '', $redirect_to = null ) {
	global $wpmem;
	$args = array( 'tag' => $tag, 'heading' => $heading, 'redirect_to' => $redirect_to );
	return $wpmem->forms->register_form( $args );
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
	if ( $wpmem->captcha == 3 ) {
		
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
	$str = '
	<div align="center">
		<small>Powered by <a href="https://rocketgeek.com" target="_blank">WP-Members</a></small>
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
			'field'      => '<input id="captcha_code" name="captcha_code" size="' . esc_attr( $size ) . '" type="text" />
					<input id="captcha_prefix" name="captcha_prefix" type="hidden" value="' . esc_attr( $pre ) . '" />
					<img src="' . esc_url( $src ) . '" alt="captcha" width="' . esc_attr( $img_w ) . '" height="' . esc_attr( $img_h ) . '" />'
		);
	} else {
		return;
	}
}

/**
 * Add registration fields to WooCommerce registration.
 *
 * As of WooCommerce 3.0, the WC registration process no longer includes the
 * WP register_form action hook.  It only includes woocommerce_register_form.
 * In previous versions, WP-Members hooked to register_form for both WP and
 * WC registration. To provide backward compatibility with users who may
 * continue to use updated WP-Members with pre-3.0 WooCommerce, this function
 * checks for WC version and if it is older than 3.0 it will ignore adding
 * the WP-Members form fields as they would have already been added when the
 * register_form action hook fired.
 *
 * @since 3.1.8
 */
function wpmem_woo_register_form() {
	if ( class_exists( 'WooCommerce' ) ) {
		global $woocommerce;
		if ( version_compare( $woocommerce->version, '3.0', ">=" ) ) {
			wpmem_wp_register_form( 'woo' );
		}
	}
}

// End of file.