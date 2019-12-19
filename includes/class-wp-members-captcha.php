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

class WP_Members_Captcha {

	/**
	 * Create reCAPTCHA form.
	 *
	 * @since  3.3.0  Replaces wpmem_inc_recaptcha().
	 *
	 * @param  array  $arr
	 * @return string $str HTML for reCAPTCHA display.
	 */
	static function recaptcha( $arr ) {

		// Determine if reCAPTCHA should be another language.
		$allowed_langs = array( 'nl', 'fr', 'de', 'pt', 'ru', 'es', 'tr' );
		/** This filter is documented in wp-includes/l10n.php */
		$locale = apply_filters( 'plugin_locale', get_locale(), 'wp-members' );
		$compare_lang  = strtolower( substr( $locale, -2 ) );
		$use_the_lang  = ( in_array( $compare_lang, $allowed_langs ) ) ? $compare_lang : false;
		$lang = ( $use_the_lang  ) ? ' lang : \'' . $use_the_lang  . '\'' : '';	

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

	/**
	 * Create Really Simple CAPTCHA.
	 *
	 * @since 3.3.0 Replaces wpmem_build_rs_captcha().
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
	static function rs_captcha() {

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
	 * Process registration captcha.
	 *
	 * @since 3.1.6
	 * @since 3.3.0 Ported from wpmem_register_handle_captcha() in register.php.
	 *
	 * @global $wpmem
	 * @global $wpmem_themsg
	 * @return $string
	 */
	static function validate() {

		global $wpmem, $wpmem_themsg;

		// Get the captcha settings (api keys).
		$wpmem_captcha = get_option( 'wpmembers_captcha' );

		/*
		 * @todo reCAPTCHA v1 is deprecated by Google. It is also no longer allowed
		 * to be set for new installs of WP-Members.  It is NOT compatible with
		 * PHP 7.1 and is therefore fully obsolete.
		 */
		// If captcha is on, check the captcha.
		if ( $wpmem->captcha == 1 && $wpmem_captcha['recaptcha'] ) { 
			$wpmem->captcha = 3;
		} 

		if ( 2 == $wpmem->captcha ) {
			if ( defined( 'REALLYSIMPLECAPTCHA_VERSION' ) ) {
				// Validate Really Simple Captcha.
				$wpmem_captcha = new ReallySimpleCaptcha();
				// This variable holds the CAPTCHA image prefix, which corresponds to the correct answer.
				$wpmem_captcha_prefix = ( isset( $_POST['captcha_prefix'] ) ) ? $_POST['captcha_prefix'] : '';
				// This variable holds the CAPTCHA response, entered by the user.
				$wpmem_captcha_code = ( isset( $_POST['captcha_code'] ) ) ? $_POST['captcha_code'] : '';
				// Check CAPTCHA validity.
				$wpmem_captcha_correct = ( $wpmem_captcha->check( $wpmem_captcha_prefix, $wpmem_captcha_code ) ) ? true : false;
				// Clean up the tmp directory.
				$wpmem_captcha->remove( $wpmem_captcha_prefix );
				$wpmem_captcha->cleanup();
				// If CAPTCHA validation fails (incorrect value entered in CAPTCHA field), return an error.
				if ( ! $wpmem_captcha_correct ) {
					$wpmem_themsg = __( 'You have entered an incorrect code value. Please try again.', 'wp-members' );
					return "empty";
				}
			}
		} elseif ( 3 == $wpmem->captcha && $wpmem_captcha['recaptcha'] ) {
			// Get the captcha response.
			if ( isset( $_POST['g-recaptcha-response'] ) ) {
				$captcha = $_POST['g-recaptcha-response'];
			}

			// If there is no captcha value, return error.
			if ( ! $captcha ) {
				$wpmem_themsg = $wpmem->get_text( 'reg_empty_captcha' );
				return "empty";
			}

			// We need the private key for validation.
			$privatekey = $wpmem_captcha['recaptcha']['private'];

			// Validate the captcha.
			$response = wp_remote_fopen( "https://www.google.com/recaptcha/api/siteverify?secret=" . $privatekey . "&response=" . $captcha . "&remoteip=" . wpmem_get_user_ip() );

			// Decode the json response.
			$response = json_decode( $response, true );

			// If captcha validation was unsuccessful.
			if ( false == $response['success'] ) {
				$wpmem_themsg = $wpmem->get_text( 'reg_invalid_captcha' );
				if ( WP_DEBUG && isset( $response['error-codes'] ) ) {
					$wpmem_themsg.= '<br /><br />';
					foreach( $response['error-codes'] as $code ) {
						$wpmem_themsg.= "Error code: " . $code . "<br />";
					}
				}
				return "empty";
			}
		} elseif ( 4 == $wpmem->captcha && $wpmem_captcha['recaptcha'] ) {
			if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['recaptcha_response'] ) ) {

				// Make and decode POST request:
				$recaptcha = file_get_contents( 'https://www.google.com/recaptcha/api/siteverify?secret=' . $wpmem_captcha['recaptcha']['private'] . '&response=' . $_POST['recaptcha_response'] );
				$recaptcha = json_decode( $recaptcha );

				// Take action based on the score returned:
				if ( $recaptcha->score >= 0.5 ) {
					// Verified - send email
				} else {
					$wpmem_themsg = $wpmem->get_text( 'reg_invalid_captcha' );
					return "empty";
				}
			}		
		}	

		return "passed_captcha";
	}
}