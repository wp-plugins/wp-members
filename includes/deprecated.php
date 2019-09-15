<?php
/**
 * WP-Members Deprecated Functions
 *
 * These functions have been deprecated and are now obsolete.
 * Use alternative functions as these will be removed in a 
 * future release.
 * 
 * This file is part of the WP-Members plugin by Chad Butler
 * You can find out more about this plugin at https://rocketgeek.com
 * Copyright (c) 2006-2019  Chad Butler
 * WP-Members(tm) is a trademark of butlerblog.com
 *
 * @package   WP-Members
 * @author    Chad Butler 
 * @copyright 2006-2019
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

if ( ! function_exists( 'wpmem_selected' ) ):
/**
 * Determines if a form field is selected (i.e. lists & checkboxes).
 *
 * @since 0.1.0
 * @deprecated 3.1.0 Use selected() or checked() instead.
 *
 * @param  string $value 
 * @param  string $valtochk
 * @param  string $type
 * @return string $issame
 */
function wpmem_selected( $value, $valtochk, $type = null ) {
	wpmem_write_log( "wpmem_selected() is deprecated as of WP-Members 3.1.0. Use selected() or checked() instead" );
	$issame = ( $type == 'select' ) ? ' selected' : ' checked';
	return ( $value == $valtochk ) ? $issame : '';
}
endif;

if ( ! function_exists( 'wpmem_inc_status' ) ):
/**
 * Generate users login status if logged in and gives logout link.
 *
 * @since 1.8
 * @deprecated 3.2.0
 *
 * @global        $user_login
 * @global object $wpmem
 * @return string $status
 */
function wpmem_inc_status() {
	
	wpmem_write_log( "wpmem_inc_status() is deprecated in WP-Members 3.2.0. Use wpmem_login_status() instead." );
	
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
 * @deprecated 3.2.0 Use widget_wpmemwidget::do_sidebar() instead.
 *
 * @param  string $redirect_to  A URL to redirect to upon login, default null.
 * @global string $wpmem_regchk
 * @global string $user_login
 */
function wpmem_do_sidebar( $redirect_to = null ) {
	wpmem_write_log( "wpmem_do_sidebar() is deprecated in WP-Members 3.2.0. Use wpmem_login_status() instead." );
	widget_wpmemwidget::do_sidebar( $redirect_to );
}
endif;

if ( ! function_exists( 'wpmem_create_formfield' ) ):
/**
 * Creates form fields
 *
 * Creates various form fields and returns them as a string.
 *
 * @since 1.8.0
 * @since 3.1.0 Converted to wrapper for create_form_field() in utlities object.
 * @deprecated 3.2.0 Use wpmem_form_field() instead.
 *
 * @global object $wpmem    The WP_Members object class.
 * @param  string $name     The name of the field.
 * @param  string $type     The field type.
 * @param  string $value    The default value for the field.
 * @param  string $valtochk Optional for comparing the default value of the field.
 * @param  string $class    Optional for setting a specific CSS class for the field.
 * @return string $str      The field returned as a string.
 */
function wpmem_create_formfield( $name, $type, $value, $valtochk=null, $class='textbox' ) {
	global $wpmem;
	$args = array(
		'name'     => $name,
		'type'     => $type,
		'value'    => $value,
		'compare'  => $valtochk,
		'class'    => $class,
	);
	return $wpmem->forms->create_form_field( $args );
}
endif;

/**
 * Adds the successful registration message on the login page if reg_nonce validates.
 *
 * @since 3.1.7
 * @deprecated 3.2.0 Use $wpmem->reg_securify() instead.
 *
 * @param  string $content
 * @return string $content
 */
function wpmem_reg_securify( $content ) {
	global $wpmem, $wpmem_themsg;
	$nonce = wpmem_get( 'reg_nonce', false, 'get' );
	if ( $nonce && wp_verify_nonce( $nonce, 'register_redirect' ) ) {
		$content = wpmem_inc_regmessage( 'success', $wpmem_themsg );
		$content = $content . wpmem_inc_login();
	}
	return $content;
}

if ( ! function_exists( 'wpmem_inc_regemail' ) ):
/**
 * Builds emails for the user.
 *
 * @since 1.8.0
 * @since 2.7.4 Added wpmem_email_headers and individual body/subject filters.
 * @since 2.9.7 Major overhaul, added wpmem_email_filter filter.
 * @since 3.1.0 Can filter in custom shortcodes with wpmem_email_shortcodes.
 * @since 3.1.1 Added $custom argument for custom emails.
 * @deprecated 3.2.0 Use wpmem_email_to_user() instead.
 *
 * @global object $wpmem                The WP_Members object.
 * @global string $wpmem_mail_from      The email from address.
 * @global string $wpmem_mail_from_name The email from name.
 * @param  int    $user_ID              The User's ID.
 * @param  string $password             Password from the registration process.
 * @param  string $toggle               Toggle indicating the email being sent (newreg|newmod|appmod|repass|getuser).
 * @param  array  $wpmem_fields         Array of the WP-Members fields (defaults to null).
 * @param  array  $fields               Array of the registration data (defaults to null).
 * @param  array  $custom               Array of custom email information (defaults to null).
 */
function wpmem_inc_regemail( $user_id, $password, $toggle, $wpmem_fields = null, $field_data = null, $custom = null ) {
	global $wpmem;
	wpmem_write_log( "wpmem_inc_regemail() is deprecated since WP-Members 3.2.0. Use $ wpmem->email->to_user() instead" );
	$wpmem->email->to_user( $user_id, $password, $toggle, $wpmem_fields, $field_data, $custom );
	return;
}
endif;

if ( ! function_exists( 'wpmem_check_activated' ) ):
/**
 * Checks if a user is activated.
 *
 * @since 2.7.1
 * @deprecated 3.2.2 Use wpmem_is_user_activated() instead.
 *
 * @param  object $user     The WordPress User object.
 * @param  string $username The user's username (user_login).
 * @param  string $password The user's password.
 * @return object $user     The WordPress User object.
 */ 
function wpmem_check_activated( $user, $username, $password ) {
	wpmem_write_log( "wpmem_check_activated() is deprecated since WP-Members 3.2.2. Use wpmem_is_user_activated() instead" );
	global $wpmem;
	$user = $wpmem->user->check_activated( $user, $username, $password );
	return $user;
}
endif;

/**
 * Activates a user.
 *
 * If registration is moderated, sets the activated flag 
 * in the usermeta. Flag prevents login when $wpmem->mod_reg
 * is true (1). Function is fired from bulk user edit or
 * user profile update.
 *
 * @since 2.4
 * @since 3.1.6 Dependencies now loaded by object.
 * @deprecated 3.2.4 Use wpmem_activate_user().
 *
 * @param int   $user_id
 * @param bool  $chk_pass
 * @uses  $wpdb WordPress Database object.
 */
function wpmem_a_activate_user( $user_id, $chk_pass = false ) {
	wpmem_write_log( "wpmem_a_activate_user() is deprecated as of WP-Members 3.2.4. Use wpmem_activate_user instead" );
	wpmem_activate_user( $user_id, $chk_pass );
}

/**
 * Deactivates a user.
 *
 * Reverses the active flag from the activation process
 * preventing login when registration is moderated.
 *
 * @since 2.7.1
 * @depreacted 3.2.4 Use wpmem_deactivate_user().
 *
 * @param int $user_id
 */
function wpmem_a_deactivate_user( $user_id ) {
	wpmem_write_log( "wpmem_a_deactivate_user() is deprecated as of WP-Members 3.2.4. Use wpmem_deactivate_user instead" );
	wpmem_deactivate_user( $user_id );
}

if ( ! function_exists( 'wpmem_inc_changepassword' ) ):
/**
 * Change Password Dialog.
 *
 * Loads the form for changing password.
 *
 * @since 2.0.0
 * @since 3.2.0 Now a wrapper for $wpmem->forms->do_changepassword_form()
 * @deprecated 3.3.0 Use wpmem_change_password_form() instead.
 *
 * @global object $wpmem The WP_Members object.
 * @return string $str   The generated html for the change password form.
 */
function wpmem_inc_changepassword() {
	wpmem_write_log( "wpmem_inc_changepassword() is deprecated as of WP-Members 3.3.0. Use wpmem_inc_changepassword() instead" );
	return wpmem_changepassword_form();
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
 * @deprecated 3.3.0 Use wpmem_reset_password_form() instead.
 *
 * @global object $wpmem The WP_Members object.
 * @return string $str   The generated html fo the reset password form.
 */
function wpmem_inc_resetpassword() { 
	wpmem_write_log( "wpmem_inc_resetpassword() is deprecated as of WP-Members 3.3.0. Use wpmem_inc_resetpassword() instead" );
	return wpmem_reset_password_form();
}
endif;

/**
 * Forgot Username Form.
 *
 * Loads the form for retrieving a username.
 *
 * @since 3.0.8
 * @since 3.2.0 Moved to forms.php.
 * @deprecated 3.3.0 Use wpmem_forgot_username_form() instead.
 *
 * @global object $wpmem The WP_Members object class.
 * @return string $str   The generated html for the forgot username form.
 */
function wpmem_inc_forgotusername() {
	wpmem_write_log( "wpmem_inc_forgotusername() is deprecated as of WP-Members 3.3.0. Use wpmem_forgot_username_form() instead" );
	return wpmem_forgot_username_form();
}

if ( ! function_exists( 'wpmem_inc_recaptcha' ) ):
/**
 * Create reCAPTCHA form.
 *
 * @since  2.6.0
 * @deprecated 3.3.0 
 *
 * @param  array  $arr
 * @return string $str HTML for reCAPTCHA display.
 */
function wpmem_inc_recaptcha( $arr ) {
	wpmem_write_log( "wpmem_inc_recaptcha() is deprecated as of WP-Members 3.3.0." );
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
 * Load constants deprecated in 3.3.0
 *
 * @since 3.3.0
 */
function wpmem_load_deprecated_constants() {
	global $wpmem;
	( ! defined( 'WPMEM_BLOCK_POSTS'  ) ) ? define( 'WPMEM_BLOCK_POSTS',  $wpmem->block['post']  ) : '';             // @todo Can deprecate? Probably 3.3
	( ! defined( 'WPMEM_BLOCK_PAGES'  ) ) ? define( 'WPMEM_BLOCK_PAGES',  $wpmem->block['page']  ) : '';             // @todo Can deprecate? Probably 3.3
	( ! defined( 'WPMEM_SHOW_EXCERPT' ) ) ? define( 'WPMEM_SHOW_EXCERPT', $wpmem->show_excerpt['post'] ) : '';       // @todo Can deprecate? Probably 3.3
	( ! defined( 'WPMEM_NOTIFY_ADMIN' ) ) ? define( 'WPMEM_NOTIFY_ADMIN', $wpmem->notify    ) : '';                  // @todo Can deprecate? Probably 3.3
	( ! defined( 'WPMEM_MOD_REG'      ) ) ? define( 'WPMEM_MOD_REG',      $wpmem->mod_reg   ) : '';                  // @todo Can deprecate? Probably 3.3
	( ! defined( 'WPMEM_CAPTCHA'      ) ) ? define( 'WPMEM_CAPTCHA',      $wpmem->captcha   ) : '';                  // @todo Can deprecate? Probably 3.3
	( ! defined( 'WPMEM_NO_REG'       ) ) ? define( 'WPMEM_NO_REG',       ( -1 * $wpmem->show_reg['post'] ) ) : '';  // @todo Can deprecate? Probably 3.3
	( ! defined( 'WPMEM_IGNORE_WARN'  ) ) ? define( 'WPMEM_IGNORE_WARN',  $wpmem->warnings  ) : '';                  // @todo Can deprecate? Probably 3.3
	( ! defined( 'WPMEM_MSURL'  ) ) ? define( 'WPMEM_MSURL',  $wpmem->user_pages['profile']  ) : '';                 // @todo Can deprecate? Probably 3.3
	( ! defined( 'WPMEM_REGURL' ) ) ? define( 'WPMEM_REGURL', $wpmem->user_pages['register'] ) : '';                 // @todo Can deprecate? Probably 3.3
	( ! defined( 'WPMEM_LOGURL' ) ) ? define( 'WPMEM_LOGURL', $wpmem->user_pages['login']    ) : '';                 // @todo Can deprecate? Probably 3.3
	( ! defined( 'WPMEM_DROPIN_DIR' ) ) ? define( 'WPMEM_DROPIN_DIR', WP_PLUGIN_DIR . '/wp-members-dropins/' ) : '';
	define( 'WPMEM_CSSURL', $wpmem->cssurl );
}

if ( ! function_exists( 'wpmem_registration' ) ):
/**
 * Register function.
 *
 * Handles registering new users and updating existing users.
 *
 * @since 2.2.1
 * @since 2.7.2 Added pre/post process actions.
 * @since 2.8.2 Added validation and data filters.
 * @since 2.9.3 Added validation for multisite.
 * @since 3.0.0 Moved from wp-members-register.php to /inc/register.php.
 * @deprecated 3.3.0 Use wpmem_user_register() instead.
 *
 * @global int    $user_ID
 * @global object $wpmem
 * @global string $wpmem_themsg
 * @global array  $userdata
 *
 * @param  string $tag           Identifies 'register' or 'update'.
 * @return string $wpmem_themsg|success|editsuccess
 */
function wpmem_registration( $tag ) {
	return wpmem_user_register( $tag );
} // End registration function.
endif;

if ( ! function_exists( 'wpmem_get_captcha_err' ) ):
/**
 * Generate reCAPTCHA error messages.
 *
 * @since 2.4
 * @deprecated 3.3.0 No replacement exists.
 *
 * @param  string $wpmem_captcha_err The response from the reCAPTCHA API.
 * @return string $wpmem_captcha_err The appropriate error message.
 */
function wpmem_get_captcha_err( $wpmem_captcha_err ) {

	switch ( $wpmem_captcha_err ) {

	case "invalid-site-public-key":
		$wpmem_captcha_err = __( 'We were unable to validate the public key.', 'wp-members' );
		break;

	case "invalid-site-public-key":
		$wpmem_captcha_err = __( 'We were unable to validate the private key.', 'wp-members' );
		break;

	case "invalid-request-cookie":
		$wpmem_captcha_err = __( 'The challenge parameter of the verify script was incorrect.', 'wp-members' );
		break;

	case "incorrect-captcha-sol":
		$wpmem_captcha_err = __( 'The CAPTCHA solution was incorrect.', 'wp-members' );
		break;

	case "verify-params-incorrect":
		$wpmem_captcha_err = __( 'The parameters to verify were incorrect', 'wp-members' );
		break;

	case "invalid-referrer":
		$wpmem_captcha_err = __( 'reCAPTCHA API keys are tied to a specific domain name for security reasons.', 'wp-members' );
		break;

	case "recaptcha-not-reachable":
		$wpmem_captcha_err = __( 'The reCAPTCHA server was not reached.  Please try to resubmit.', 'wp-members' );
		break;

	case 'really-simple':
		$wpmem_captcha_err = __( 'You have entered an incorrect code value. Please try again.', 'wp-members' );
		break;
	}

	return $wpmem_captcha_err;
}
endif;

if ( ! function_exists( 'wpmem_inc_login' ) ):
/**
 * Login Dialog.
 *
 * Loads the login form for user login.
 *
 * @since 1.8
 * @since 3.1.4 Global $wpmem_regchk no longer needed.
 * @since 3.2.0 Now a wrapper for $wpmem->forms->do_login_form().
 * @deprecated 3.3.0 Use wpmem_login_form() instead.
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

if ( ! function_exists( 'wpmem_inc_registration' ) ):
/**
 * Registration Form Dialog.
 *
 * Outputs the form for new user registration and existing user edits.
 *
 * @since 2.5.1
 * @since 3.1.7 Now a wrapper for $wpmem->forms->register_form()
 * @since 3.2.0 Preparing for deprecation, use wpmem_register_form() instead.
 * @deprecated 3.3.0 Use wpmem_register_form() instead.
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