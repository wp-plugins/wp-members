<?php
/**
 * WP-Members Admin Functions
 *
 * Functions to manage the captcha tab.
 * 
 * This file is part of the WP-Members plugin by Chad Butler
 * You can find out more about this plugin at http://rocketgeek.com
 * Copyright (c) 2006-2013  Chad Butler (email : plugins@butlerblog.com)
 * WP-Members(tm) is a trademark of butlerblog.com
 *
 * @package WordPress
 * @subpackage WP-Members
 * @author Chad Butler
 * @copyright 2006-2013
 */


/**
 * builds the captcha options
 *
 * @since 2.4.0
 */
function wpmem_a_build_captcha_options()
{ 
	$wpmem_captcha = get_option( 'wpmembers_captcha' );
	$url           = home_url();
	?>
	<div class="metabox-holder has-right-sidebar">
	
		<div class="inner-sidebar">
			<?php wpmem_a_meta_box(); ?>
			<div class="postbox">
				<h3><span><?php _e( 'Need help?', 'wp-members' ); ?></span></h3>
				<div class="inside">
					<strong><i>See the <a href="http://rocketgeek.com/plugins/wp-members/users-guide/registration/using-captcha/" target="_blank">Users Guide on reCAPTCHA</a>.</i></strong>
				</div>
			</div>			
		</div> <!-- .inner-sidebar -->	

		<div id="post-body">
			<div id="post-body-content">
				<div class="postbox">
				
					<h3><?php _e( 'Manage reCAPTCHA Options', 'wp-members' ); ?></h3>
					<div class="inside">
						<form name="updatecaptchaform" id="updatecaptchaform" method="post" action="<?php echo $_SERVER['REQUEST_URI']?>"> 
						<?php wp_nonce_field( 'wpmem-update-captcha' ); ?>
							<table class="form-table">
								<tr>
									<td colspan="2">
										<p><?php _e( 'reCAPTCHA is a free, accessible CAPTCHA service that helps to digitize books while blocking spam on your blog.', 'wp-members' ); ?></p>
										<p><?php printf( __( 'reCAPTCHA asks commenters to retype two words scanned from a book to prove that they are a human. This verifies that they are not a spambot while also correcting the automatic scans of old books. So you get less spam, and the world gets accurately digitized books. Everybody wins! For details, visit the %s reCAPTCHA website%s', 'wp-members' ), '<a href="http://recaptcha.net/" target="_blank">', '</a>' ); ?>.</p>
										<p>
									</td>
								</tr>        
								<tr valign="top"> 
									<th scope="row"><?php _e( 'reCAPTCHA Keys', 'wp-members' ); ?></th> 
									<td>
										<?php printf( __( 'reCAPTCHA requires an API key, consisting of a "public" and a "private" key. You can sign up for a %s free reCAPTCHA key%s', 'wp-members' ), "<a href=\"http://recaptcha.net/api/getkey?domain=$url&amp;app=wordpress\" target=\"_blank\">", '</a>' ); ?>.<br />
										<?php _e( 'Public Key', 'wp-members' ); ?>:&nbsp;&nbsp;<input type="text" name="wpmem_captcha_publickey" size="50" value="<?php echo $wpmem_captcha[0]; ?>" /><br />
										<?php _e( 'Private Key', 'wp-members' ); ?>:&nbsp;<input type="text" name="wpmem_captcha_privatekey" size="50" value="<?php echo $wpmem_captcha[1]; ?>" />
									 </td> 
								</tr>
								<tr valign="top">
									<th scope="row"><?php _e( 'Choose Theme', 'wp-members' ); ?></th>
									<td>
										<select name="wpmem_captcha_theme">
											<!--<?php echo wpmem_create_formfield( __( 'WP-Members', 'wp-members' ), 'option', 'custom', $wpmem_captcha[2] ); ?>--><?php
											echo wpmem_create_formfield( __( 'Red', 'wp-members' ), 'option', 'red', $wpmem_captcha[2] ); 
											echo wpmem_create_formfield( __( 'White', 'wp-members' ), 'option', 'white', $wpmem_captcha[2] );
											echo wpmem_create_formfield( __( 'Black Glass', 'wp-members' ), 'option', 'blackglass', $wpmem_captcha[2] ); 
											echo wpmem_create_formfield( __( 'Clean', 'wp-members' ), 'option', 'clean', $wpmem_captcha[2] ); ?>
											<!--<?php echo wpmem_create_formfield( __( 'Custom', 'wp-members' ), 'option', 'custom', $wpmem_captcha[2] ); ?>-->
										</select>
									</td>
								</tr><!--
								<tr valign="top"> 
									<th scope="row">Custom reCAPTCHA theme</th> 
									<td><input type="text" name="wpmem_settings_regurl" value="<?php echo $wpmem_regurl; ?>" size="50" />&nbsp;<span class="description"><?php _e( 'Optional', 'wp-members' ); ?></span></td> 
								</tr>-->
								<tr valign="top"> 
									<th scope="row">&nbsp;</th> 
									<td>
										<input type="hidden" name="wpmem_admin_a" value="update_captcha" />
										<input type="submit" name="save"  class="button-primary" value="<?php _e( 'Update reCAPTCHA Settings', 'wp-members' ); ?> &raquo;" />
									</td> 
								</tr> 
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
 * Updates the captcha options
 *
 * @since 2.8
 *
 * @return string The captcha option update message
 */
function wpmem_update_captcha()
{
	//check nonce
	check_admin_referer( 'wpmem-update-captcha' );

	$wpmem_captcha = array(
		$_POST['wpmem_captcha_publickey'],
		$_POST['wpmem_captcha_privatekey'],
		$_POST['wpmem_captcha_theme']
		);
	
	update_option( 'wpmembers_captcha', $wpmem_captcha );
	return __( 'reCAPTCHA was updated for WP-Members', 'wp-members' );
}

/** End of File **/