<?php
/**
 * WP-Members Admin Functions
 *
 * Functions to manage the captcha tab.
 * 
 * This file is part of the WP-Members plugin by Chad Butler
 * You can find out more about this plugin at https://rocketgeek.com
 * Copyright (c) 2006-2019  Chad Butler
 * WP-Members(tm) is a trademark of butlerblog.com
 *
 * @package WP-Members
 * @author Chad Butler
 * @copyright 2006-2019
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

class WP_Members_Admin_Tab_Captcha {

	/**
	 * Creates the captcha tab.
	 *
	 * @since 2.8.0
	 * @since 3.3.0 Ported to do_tab().
	 *
	 * @param  string      $tab The admin tab being displayed.
	 * @return string|bool      The captcha options tab, otherwise false.
	 */
	public static function do_tab( $tab ) {
		if ( $tab == 'captcha' ) {
			return self::build_settings();
		} else {
			return false;
		}
	}

	/**
	 * Adds the captcha tab.
	 *
	 * @since 2.8.0
	 * @since 3.3.0 Ported wpmem_add_captcha_tab() to add_tab().
	 *
	 * @param  array $tabs The array of tabs for the admin panel.
	 * @return array       The updated array of tabs for the admin panel.
	 */
	public static function add_tab( $tabs ) {
		return array_merge( $tabs, array( 'captcha' => 'Captcha' ) );
	}

	/**
	 * Builds the captcha options.
	 *
	 * @since 2.4.0
	 * @since 3.3.0 Ported wpmem_a_build_captcha_options() to build_settings().
	 */
	public static function build_settings() {

		// Global settings.
		global $wpmem;

		$wpmem_captcha = get_option( 'wpmembers_captcha' );
		$url           = home_url();
		$help_link     = sprintf( __( 'See the %sUsers Guide on CAPTCHA%s.', 'wp-members' ), '<a href="https://rocketgeek.com/plugins/wp-members/users-guide/registration/using-captcha/" target="_blank">', '</a>' );	
		?>
		<div class="metabox-holder has-right-sidebar">

			<div class="inner-sidebar">
				<?php wpmem_a_meta_box(); ?>
				<div class="postbox">
					<h3><span><?php _e( 'Need help?', 'wp-members' ); ?></span></h3>
					<div class="inside">
						<strong><i><?php echo $help_link; ?></i></strong>
					</div>
				</div>
			</div> <!-- .inner-sidebar -->

			<div id="post-body">
				<div id="post-body-content">
					<div class="postbox">

						<h3><?php _e( 'Manage reCAPTCHA Options', 'wp-members' ); ?></h3>
						<div class="inside">
							<form name="updatecaptchaform" id="updatecaptchaform" method="post" action="<?php echo wpmem_admin_form_post_url(); ?>">
							<?php wp_nonce_field( 'wpmem-update-captcha' ); ?>
								<table class="form-table">
								<?php // if reCAPTCHA is enabled...
								if ( $wpmem->captcha == 1 ) {
									$show_update_button = true; 
									$private_key   = ( isset( $wpmem_captcha['recaptcha'] ) ) ? $wpmem_captcha['recaptcha']['private'] : '';
									$public_key    = ( isset( $wpmem_captcha['recaptcha'] ) ) ? $wpmem_captcha['recaptcha']['public']  : '';
									$captcha_theme = ( isset( $wpmem_captcha['recaptcha'] ) ) ? $wpmem_captcha['recaptcha']['theme']   : '';
									?>
									<tr>
										<td colspan="2">
											<p><?php _e( 'reCAPTCHA is a free, accessible CAPTCHA service that helps to digitize books while blocking spam on your blog.', 'wp-members' ); ?></p>
											<p><?php printf( __( 'reCAPTCHA asks commenters to retype two words scanned from a book to prove that they are a human. This verifies that they are not a spambot while also correcting the automatic scans of old books. So you get less spam, and the world gets accurately digitized books. Everybody wins! For details, visit the %s reCAPTCHA website%s', 'wp-members' ), '<a href="http://www.google.com/recaptcha/intro/index.html" target="_blank">', '</a>' ); ?>.</p>
											<p>
										</td>
									</tr>
									<tr valign="top">
										<th scope="row"><?php _e( 'reCAPTCHA Keys', 'wp-members' ); ?></th>
										<td>
											<?php printf( __( 'reCAPTCHA requires an API key, consisting of a "public" and a "private" key. You can sign up for a %s free reCAPTCHA key%s', 'wp-members' ), "<a href=\"https://www.google.com/recaptcha/admin#whyrecaptcha\" target=\"_blank\">", '</a>' ); ?>.<br />
											<?php _e( 'Public Key', 'wp-members' ); ?>:&nbsp;&nbsp;<input type="text" name="wpmem_captcha_publickey" size="50" value="<?php echo $public_key; ?>" /><br />
											<?php _e( 'Private Key', 'wp-members' ); ?>:&nbsp;<input type="text" name="wpmem_captcha_privatekey" size="50" value="<?php echo $private_key; ?>" />
										 </td>
									</tr>
									<tr valign="top">
										<th scope="row"><?php _e( 'Choose Theme', 'wp-members' ); ?></th>
										<td>
											<select name="wpmem_captcha_theme"><?php
												echo wpmem_create_formfield( __( 'Red', 'wp-members' ), 'option', 'red', $captcha_theme ); 
												echo wpmem_create_formfield( __( 'White', 'wp-members' ), 'option', 'white', $captcha_theme );
												echo wpmem_create_formfield( __( 'Black Glass', 'wp-members' ), 'option', 'blackglass', $captcha_theme ); 
												echo wpmem_create_formfield( __( 'Clean', 'wp-members' ), 'option', 'clean', $captcha_theme ); ?>
											</select>
										</td>
									</tr>
								<?php 
								// if reCAPTCHA v2 is enabled...	
								} elseif ( 3 == $wpmem->captcha || 4 == $wpmem->captcha ) {
									$show_update_button = true; 
									$private_key = ( isset( $wpmem_captcha['recaptcha'] ) ) ? $wpmem_captcha['recaptcha']['private'] : '';
									$public_key  = ( isset( $wpmem_captcha['recaptcha'] ) ) ? $wpmem_captcha['recaptcha']['public']  : '';
									?>
									<tr valign="top">
										<th scope="row"><?php _e( 'reCAPTCHA Keys', 'wp-members' ); ?></th>
										<td>
											<?php printf( __( 'reCAPTCHA requires an API key, consisting of a "site" and a "secret" key. You can sign up for a %s free reCAPTCHA key%s', 'wp-members' ), "<a href=\"https://www.google.com/recaptcha/admin#whyrecaptcha\" target=\"_blank\">", '</a>' ); ?>.<br />
											<p><label><?php _e( 'Site Key', 'wp-members' ); ?>:</label><br /><input type="text" name="wpmem_captcha_publickey" size="60" value="<?php echo $public_key; ?>" /></p>
											<p><label><?php _e( 'Secret Key', 'wp-members' ); ?>:</label><br /><input type="text" name="wpmem_captcha_privatekey" size="60" value="<?php echo $private_key; ?>" /></p>
										 </td>
									</tr>
								<?php 
								// If Really Simple CAPTCHA is enabled.
								} elseif ( $wpmem->captcha == 2 ) {

									// Setup defaults.
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

									$args = ( isset( $wpmem_captcha['really_simple'] ) && is_array( $wpmem_captcha['really_simple'] ) ) ? $wpmem_captcha['really_simple'] : array();

									$args = wp_parse_args( $args, $defaults );

									// Explode colors.
									$font_color = explode( ',', $args['font_color'] );
									$bg_color   = explode( ',', $args['bg_color']   );

									$show_update_button = true;
									if ( is_plugin_active( 'really-simple-captcha/really-simple-captcha.php' ) ) { ?>
										<tr>
											<th scope="row"><?php _e( 'Characters for image', 'wp-members' ); ?></th>
											<td><input name="characters" type="text" size="34" value="<?php echo $args['characters']; ?>" /></td>
										</tr>
										<tr>
											<th scope="row"><?php _e( 'Number of characters', 'wp-members' ); ?></th>
											<td><input name="num_char" type="text" size="2" value="<?php echo $args['num_char']; ?>" /></td>
										</tr>
										<tr>
											<th scope="row"><?php _e( 'Image dimensions', 'wp-members' ); ?></th>
											<td><?php _e( 'Width' ); ?> <input name="dim_w" type="text" size="2" value="<?php echo $args['dim_w']; ?>" /> <?php _e( 'Height' ); ?> <input name="dim_h" type="text" size="2" value="<?php echo $args['dim_h']; ?>" /></td>
										</tr>
										<tr>
											<th scope="row"><?php _e( 'Font color of characters', 'wp-members' ); ?></th>
											<td>R:<input name="font_color_r" type="text" size="2" value="<?php echo $font_color[0]; ?>" /> G:<input name="font_color_g" type="text" size="2" value="<?php echo $font_color[1]; ?>" /> B:<input name="font_color_b" type="text" size="2" value="<?php echo $font_color[2]; ?>" /></td>
										</tr>
										<tr>
											<th scope="row"><?php _e( 'Background color of image', 'wp-members' ); ?></th>
											<td>R:<input name="bg_color_r" type="text" size="2" value="<?php echo $bg_color[0]; ?>" /> G:<input name="bg_color_g" type="text" size="2" value="<?php echo $bg_color[1]; ?>" /> B:<input name="bg_color_b" type="text" size="2" value="<?php echo $bg_color[2]; ?>" /></td>
										</tr>
										<tr>
											<th scope="row"><?php _e( 'Font size', 'wp-members' ); ?></th>
											<td><input name="font_size" type="text" value="<?php echo $args['font_size']; ?>" /></td>
										</tr>
										<tr>
											<th scope="row"><?php _e( 'Width between characters', 'wp-members' ); ?></th>
											<td><input name="kerning" type="text" value="<?php echo $args['kerning']; ?>" /></td>
										</tr>
										<tr>
											<th scope="row"><?php _e( 'Image type', 'wp-members' ); ?></th>
											<td><select name="img_type">
												<option<?php echo ( $args['img_type'] == 'png' ) ? ' selected' : ''; ?>>png</option>
												<option<?php echo ( $args['img_type'] == 'gif' ) ? ' selected' : ''; ?>>gif</option>
												<option<?php echo ( $args['img_type'] == 'jpg' ) ? ' selected' : ''; ?>>jpg</option>
												</select>
											</td>
										</tr><?php

									} else {

										$show_update_button = false; ?>
										<tr>
											<td colspan="2">
												<p><?php _e( 'To use Really Simple CAPTCHA, you must have the Really Simple CAPTCHA plugin installed and activated.', 'wp-members' ); ?></p>
												<p><?php _e( sprintf( 'You can download Really Simple CAPTCHA from the %swordpress.org plugin repository%s.', '<a href="http://wordpress.org/plugins/really-simple-captcha/">', '</a>' ), 'wp-members' ); ?></p>
											</td>
										</tr><?php
									}
								} // End if RSC is selected.
								if ( $show_update_button ) { 

									switch ( $wpmem->captcha ) {
										case 1: 
											$captcha_type = 'recaptcha';
											break;
										case 2:
											$captcha_type = 'really_simple';
											break;
										case 3:
										case 4:
											$captcha_type = 'recaptcha2';
											break;
									} ?>
									<tr valign="top">
										<th scope="row">&nbsp;</th>
										<td>
											<input type="hidden" name="wpmem_recaptcha_type" value="<?php echo $captcha_type ?>" />
											<input type="hidden" name="wpmem_admin_a" value="update_captcha" />
											<?php submit_button( __( 'Update CAPTCHA Settings', 'wp-members' ) ); ?>
										</td>
									</tr>
								<?php } ?>
								</table>
							</form>
						</div><!-- .inside -->
					</div>
				</div><!-- #post-body-content -->
			</div><!-- #post-body -->
		</div><!-- .metabox-holder -->
		<?php
	}

	/**
	 * Updates the captcha options.
	 *
	 * @since 2.8
	 * @since 3.3.0 Ported wpmem_update_captcha() to update().
	 *
	 * @return string The captcha option update message.
	 */
	public static function update() {

		// Check nonce.
		check_admin_referer( 'wpmem-update-captcha' );

		$settings     = get_option( 'wpmembers_captcha' );
		$update_type  = sanitize_text_field( $_POST['wpmem_recaptcha_type'] );
		$new_settings = array();

		// If there are no current settings.
		if ( ! $settings ) {
			$settings = array();
		}

		if ( $update_type == 'recaptcha' || $update_type == 'recaptcha2' ) {
			if ( array_key_exists( 'really_simple', $settings ) ) {
				// Updating recaptcha but need to maintain really_simple.
				$new_settings['really_simple'] = $settings['really_simple'];
			}
			$new_settings['recaptcha'] = array(
				'public'  => sanitize_text_field( $_POST['wpmem_captcha_publickey'] ),
				'private' => sanitize_text_field( $_POST['wpmem_captcha_privatekey'] ),
			);
			if ( $update_type == 'recaptcha' && isset( $_POST['wpmem_captcha_theme'] ) ) {
				$new_settings['recaptcha']['theme'] = sanitize_text_field( $_POST['wpmem_captcha_theme'] );
			}
		}

		if ( $update_type == 'really_simple' ) {
			if ( array_key_exists( 'recaptcha', $settings ) ) {
				// Updating really_simple but need to maintain recaptcha.
				$new_settings['recaptcha'] = $settings['recaptcha'];
			}
			$font_color = sanitize_text_field( $_POST['font_color_r'] ) . ',' . sanitize_text_field( $_POST['font_color_g'] ) . ',' . sanitize_text_field( $_POST['font_color_b'] );
			$bg_color   = sanitize_text_field( $_POST['bg_color_r'] )   . ',' . sanitize_text_field( $_POST['bg_color_g'] )   . ',' . sanitize_text_field( $_POST['bg_color_b']   );
			$new_settings['really_simple'] = array(
					'characters'   => sanitize_text_field( $_POST['characters'] ),
					'num_char'     => sanitize_text_field( $_POST['num_char'] ),
					'dim_w'        => sanitize_text_field( $_POST['dim_w'] ),
					'dim_h'        => sanitize_text_field( $_POST['dim_h'] ),
					'font_color'   => $font_color,
					'bg_color'     => $bg_color,
					'font_size'    => sanitize_text_field( $_POST['font_size'] ),
					'kerning'      => sanitize_text_field( $_POST['kerning'] ),
					'img_type'     => sanitize_text_field( $_POST['img_type'] ),
			);
		}

		update_option( 'wpmembers_captcha', $new_settings );
		return __( 'CAPTCHA was updated for WP-Members', 'wp-members' );
	}
}