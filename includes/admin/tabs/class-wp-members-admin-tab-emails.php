<?php
/**
 * WP-Members Admin Functions
 *
 * Functions to manage the emails tab.
 * 
 * This file is part of the WP-Members plugin by Chad Butler
 * You can find out more about this plugin at https://rocketgeek.com
 * Copyright (c) 2006-2022  Chad Butler
 * WP-Members(tm) is a trademark of butlerblog.com
 *
 * @package WP-Members
 * @author Chad Butler
 * @copyright 2006-2022
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
							<a href="https://rocketgeek.com/plugins/wp-members/docs/customizing-emails/" target="_blank">
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
									<tr valign="top">
										<th scope="row"><?php _e( 'Send HTML email', 'wp-members' ); ?></th>
										<td><input type="checkbox" name="wpmem_email_html" value="1" <?php checked( $wpmem->email->html, 1, true ); ?> /></td>
									</tr>
									<tr><td colspan="2"><hr /></td></tr>
								<?php // Here is where we handle all emails, both standard and custom.
									if ( ! empty ( $wpmem->admin->emails ) ) {	
										foreach( $wpmem->admin->emails as $email ) {
											self::do_email_input( $email );
										}
									}
									$arr = get_option( 'wpmembers_email_footer' ); 
									$footer_args = array(
										'body_input' => 'wpmembers_email_footer_body',
										'body_value' => $arr,
									); ?>
									<tr valign="top">
										<th scope="row"><strong><?php echo __( "Email Signature", 'wp-members' ); ?></strong> <span class="description"><?php _e( '(optional)', 'wp-members' ); ?></span></th>
										<td><?php self::do_email_editor( $footer_args ); ?></td>
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
							<strong><i>See the <a href="https://rocketgeek.com/plugins/wp-members/docs/plugin-settings/emails/" target="_blank">Users Guide on email options</a>.</i></strong>
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
		if ( $wpmem->email->from    != $_POST['wp_mail_from'] || $wpmem->email->from_name != $_POST['wp_mail_from_name'] || $wpmem->email->html != wpmem_get( 'wpmem_email_html', 0 ) ) {
			$wpmem->email->from      = sanitize_email( $_POST['wp_mail_from'] );
			$wpmem->email->from_name = sanitize_text_field( $_POST['wp_mail_from_name'] );
			$wpmem->email->html      = intval( wpmem_get( 'wpmem_email_html', 0 ) );
			update_option( 'wpmembers_email_wpfrom', $wpmem->email->from );
			update_option( 'wpmembers_email_wpname', $wpmem->email->from_name );
			update_option( 'wpmembers_email_html',   $wpmem->email->html );
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
				self::email_update( $email );
			}
		}

		return __( 'WP-Members emails were updated', 'wp-members' );

	}

	/**
	 * Adds custom email dialog to the Emails tab.
	 *
	 * @since 3.1.0
	 * @since 3.4.0 Moved to emails tab class.
	 *
	 * @param array $args Settings array for the email.
	 */
	static private function do_email_input( $args ) { ?>
        <tr valign="top"><td colspan="2"><strong><?php echo esc_html( $args['heading'] ); ?></strong></td></tr>
        <tr valign="top">
            <th scope="row"><?php echo esc_html( $args['subject_label'] ); ?></th>
            <td><input type="text" name="<?php echo esc_attr( $args['subject_input'] ); ?>" size="80" value="<?php echo esc_attr( wp_unslash( $args['subject_value'] ) ); ?>"></td> 
        </tr>
        <tr valign="top">
            <th scope="row"><?php echo esc_html( $args['body_label'] ); ?></th>
            <td><?php self::do_email_editor( $args ); ?></td>
        </tr>
        <tr><td colspan="2"><hr /></td></tr><?php
	}

	static private function do_email_editor( $args ) {
		global $wpmem;
		if ( 1 == $wpmem->email->html ) { 
			$editor_args = array(
				'media_buttons' => false,
				'textarea_rows' => 10,
			);
			wp_editor( $args['body_value'], esc_attr( $args['body_input'] ), $editor_args );
		} else { ?>
			<textarea name="<?php echo esc_attr( $args['body_input'] ); ?>" rows="12" cols="50" id="" class="large-text code"><?php echo esc_textarea( wp_unslash( $args['body_value'] ) ); ?></textarea>
        <?php }
	}

	/**
	 * Saves custom email settings.
	 *
	 * @since 3.1.0
	 * @since 3.4.0 Moved to emails tab class.
	 *
	 * @param array $args Settings array for the email.
	 */
	static private function email_update( $args ) {
		global $wpmem;
		$settings = array(
			'subj' => sanitize_text_field( wpmem_get( $args['subject_input'] ) ),
			'body' => wp_kses( wpmem_get( $args['body_input'] ), 'post' ),
		);
		update_option( $args['name'], $settings, true );
		$wpmem->admin->emails[ $args['name'] ]['subject_value'] = $settings['subj'];
		$wpmem->admin->emails[ $args['name'] ]['body_value']    = $settings['body'];
		return;
	}
} // End of file.