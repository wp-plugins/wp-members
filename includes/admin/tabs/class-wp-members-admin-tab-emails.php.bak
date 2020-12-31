<?php
/**
 * WP-Members Admin Functions
 *
 * Functions to manage the emails tab.
 * 
 * This file is part of the WP-Members plugin by Chad Butler
 * You can find out more about this plugin at https://rocketgeek.com
 * Copyright (c) 2006-2020  Chad Butler
 * WP-Members(tm) is a trademark of butlerblog.com
 *
 * @package WP-Members
 * @author Chad Butler
 * @copyright 2006-2020
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

class WP_Members_Admin_Tab_Emails {
	/**
	 * Creates the tab.
	 *
	 * @since 3.2.0
	 * @since 3.3.0 Ported from wpmem_a_emails_tab().
	 *
	 * @param  string      $tab The admin tab being displayed.
	 * @return string|bool      The tab html, otherwise false.
	 */
	static function do_tab( $tab ) {
		if ( $tab == 'emails' || ! $tab ) {
			// Render the tab.
			return self::build_settings();
		} else {
			return false;
		}
	}

	/**
	 * Builds the emails panel.
	 *
	 * @since 2.7.0
	 * @since 3.3.0 Ported wpmem_a_build_emails().
	 *
	 * @global object $wpmem
	 */
	static function build_settings() {

		global $wpmem; ?>
		<div class="metabox-holder">

			<div id="post-body">
				<div id="post-body-content">
					<div class="postbox">
						<h3><span>WP-Members <?php _e( 'Email Messages', 'wp-members' ); ?></span></h3>
						<div class="inside">
							<p>
							<?php _e( 'You can customize the content of the emails sent by the plugin.', 'wp-members' ); ?><br />
							<a href="https://rocketgeek.com/plugins/wp-members/users-guide/customizing-emails/" target="_blank">
							<?php _e( 'A list of shortcodes is available here.', 'wp-members' ); ?></a>
							</p>
							<hr />
							<form name="updateemailform" id="updateemailform" method="post" action="<?php echo esc_url( wpmem_admin_form_post_url() ); ?>"> 
							<?php wp_nonce_field( 'wpmem-update-emails' ); ?>
								<table class="form-table"> 
									<tr valign="top"> 
										<th scope="row"><?php _e( 'Set a custom email address', 'wp-members' ); ?></th> 
										<td><input type="text" name="wp_mail_from" size="40" value="<?php echo esc_attr( $wpmem->email->from ); ?>" />&nbsp;<span class="description"><?php _e( '(optional)', 'wp-members' ); ?> email@yourdomain.com</span></td> 
									</tr>
									<tr valign="top"> 
										<th scope="row"><?php _e( 'Set a custom email name', 'wp-members' ); ?></th> 
										<td><input type="text" name="wp_mail_from_name" size="40" value="<?php echo esc_attr( stripslashes( $wpmem->email->from_name ) ); ?>" />&nbsp;<span class="description"><?php _e( '(optional)', 'wp-members' ); ?> John Smith</span></td>
									</tr>
									<tr><td colspan="2"><hr /></td></tr>
								<?php if ( ! empty ( $wpmem->admin->emails ) ) {	
										foreach( $wpmem->admin->emails as $email ) {
											$wpmem->admin->do_email_input( $email );
										}
									}
									$arr = get_option( 'wpmembers_email_footer' ); ?>
									<tr valign="top">
										<th scope="row"><strong><?php echo __( "Email Signature", 'wp-members' ); ?></strong> <span class="description"><?php _e( '(optional)', 'wp-members' ); ?></span></th>
										<td><textarea name="<?php echo 'wpmembers_email_footer_body'; ?>" rows="10" cols="50" id="" class="large-text code"><?php echo esc_textarea( stripslashes( $arr ) ); ?></textarea></td>
									</tr>
									<tr><td colspan="2"><hr /></td></tr>
									<tr valign="top">
										<th scope="row">&nbsp;</th>
										<td>
											<input type="hidden" name="wpmem_admin_a" value="update_emails" />
											<?php submit_button( __( 'Update Emails', 'wp-members' ) ); ?>
										</td>
									</tr>
								</table>
							</form>
						</div><!-- .inside -->
					</div><!-- #post-box -->
					<div class="postbox">
						<h3><span><?php _e( 'Need help?', 'wp-members' ); ?></span></h3>
						<div class="inside">
							<strong><i>See the <a href="https://rocketgeek.com/plugins/wp-members/users-guide/plugin-settings/emails/" target="_blank">Users Guide on email options</a>.</i></strong>
						</div>
					</div>
				</div> <!-- #post-body-content -->
			</div><!-- #post-body -->
		</div><!-- .metabox-holder -->
		<?php
	}


	/**
	 * Updates the email message settings.
	 *
	 * @since 2.8.0
	 * @since 3.3.0 Ported from wpmem_update_emails().
	 *
	 * @global object $wpmem The WP_Members object class.
	 * @return string        The emails updated message.
	 */
	static function update() {

		global $wpmem;

		// Check nonce.
		check_admin_referer( 'wpmem-update-emails' );

		// Update the email address (if applicable).
		if ( $wpmem->email->from    != $_POST['wp_mail_from'] || $wpmem->email->from_name != $_POST['wp_mail_from_name'] ) {
			$wpmem->email->from      = sanitize_email( $_POST['wp_mail_from'] );
			$wpmem->email->from_name = sanitize_text_field( $_POST['wp_mail_from_name'] );
			update_option( 'wpmembers_email_wpfrom', $wpmem->email->from );
			update_option( 'wpmembers_email_wpname', $wpmem->email->from_name );
		}

		// Update the various emails being used.
		( $wpmem->mod_reg == 0 ) ? $arr = array( 'wpmembers_email_newreg' ) : $arr = array( 'wpmembers_email_newmod', 'wpmembers_email_appmod' );
		array_push( $arr, 'wpmembers_email_repass' );
		array_push( $arr, 'wpmembers_email_getuser' );
		( $wpmem->notify == 1 ) ? array_push( $arr, 'wpmembers_email_notify' ) : false;
		array_push(	$arr, 'wpmembers_email_footer' );

		for ( $row = 0; $row < ( count( $arr ) - 1 ); $row++ ) {
			$arr2 = array( 
				"subj" => sanitize_text_field( $_POST[ $arr[ $row ] . '_subj' ] ),
				"body" => wp_kses( $_POST[ $arr[ $row ] . '_body' ], 'post' ),
			);
			update_option( $arr[ $row ], $arr2, false );
			$arr2 = '';
		}

		// Updated the email footer.
		update_option( $arr[ $row ], wp_kses( $_POST[ $arr[ $row ] . '_body' ], 'post' ), false );

		if ( ! empty ( $wpmem->admin->emails ) ) {
			foreach( $wpmem->admin->emails as $email ) {
				$wpmem->admin->email_update( $email );
			}
		}

		return __( 'WP-Members emails were updated', 'wp-members' );

	}

} // End of file.