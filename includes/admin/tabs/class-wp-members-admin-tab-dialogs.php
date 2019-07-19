<?php
/**
 * WP-Members Admin functions
 *
 * Static functions to manage the dialogs tab.
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

class WP_Members_Admin_Tab_Dialogs {

	/**
	 * Creates the tab.
	 *
	 * @since 3.2.0
	 * @since 3.3.0 Ported from wpmem_a_dialogs_tab().
	 *
	 * @param  string      $tab The admin tab being displayed.
	 * @return string|bool      The tab html, otherwise false.
	 */
	static function do_tab( $tab ) {
		if ( $tab == 'dialogs' || ! $tab ) {
			// Render the tab.
			return self::build_settings();
		} else {
			return false;
		}
	}

	/**
	 * Builds the dialogs panel.
	 *
	 * @since 2.2.2
	 * @since 3.3.0 Ported from wpmem_a_build_dialogs().
	 *
	 * @global object $wpmem
	 */
	static function build_settings() { 
		global $wpmem; ?>
		<div class="metabox-holder has-right-sidebar">

			<div class="inner-sidebar">
				<?php wpmem_a_meta_box(); ?>
				<div class="postbox">
					<h3><span><?php _e( 'Need help?', 'wp-members' ); ?></span></h3>
					<div class="inside">
						<strong><i>See the <a href="https://rocketgeek.com/plugins/wp-members/users-guide/plugin-settings/dialogs/" target="_blank">Users Guide on dialogs</a>.</i></strong>
					</div>
				</div>
			</div> <!-- .inner-sidebar -->

			<div id="post-body">
				<div id="post-body-content">
					<div class="postbox">
						<h3><span>WP-Members <?php _e( 'Dialogs and Error Messages', 'wp-members' ); ?></span></h3>
						<div class="inside">
							<p><?php printf( __( 'You can customize the text for dialogs and error messages. Simple HTML is allowed %s etc.', 'wp-members' ), '- &lt;p&gt;, &lt;b&gt;, &lt;i&gt;,' ); ?></p>
							<form name="updatedialogform" id="updatedialogform" method="post" action="<?php echo esc_url( wpmem_admin_form_post_url() ); ?>"> 
							<?php wp_nonce_field( 'wpmem-update-dialogs' ); ?>
								<table class="form-table">
								<?php if ( ! empty ( $wpmem->admin->dialogs ) ) {	
									foreach( $wpmem->admin->dialogs as $dialog ) {
										$wpmem->admin->do_dialog_input( $dialog );
									}
								} ?>
								<?php $wpmem_tos = stripslashes( get_option( 'wpmembers_tos' ) ); ?>
									<tr valign="top"> 
										<th scope="row"><?php _e( 'Terms of Service (TOS)', 'wp-members' ); ?></th> 
										<td><textarea name="dialogs_tos" rows="3" cols="50" id="" class="large-text code"><?php echo esc_textarea( $wpmem_tos ); ?></textarea></td> 
									</tr>
									<tr valign="top">
										<th scope="row">&nbsp;</th>
										<td>
											<input type="hidden" name="wpmem_admin_a" value="update_dialogs" />
											<?php submit_button( __( 'Update Dialogs', 'wp-members' ) ); ?>
										</td> 
									</tr>
								</table>
							</form>
						</div><!-- .inside -->
					</div><!-- #post-box -->
				</div><!-- #post-body-content -->
			</div><!-- #post-body -->
		</div> <!-- .metabox-holder -->
		<?php
	}


	/**
	 * Updates the dialog settings.
	 *
	 * @since 2.8.0
	 * @since 3.3.0 Ported from wpmem_update_dialogs().
	 *
	 * @global object $wpmem
	 * @return string The dialogs updated message.
	 */
	static function update() {

		global $wpmem;

		// Check nonce.
		check_admin_referer( 'wpmem-update-dialogs' );

		if ( ! empty ( $wpmem->admin->dialogs ) ) {
			$wpmem->admin->dialog_update();
		}

		// Terms of Service.
		update_option( 'wpmembers_tos', wp_kses( $_POST['dialogs_tos'], 'post' ) );

		return __( 'WP-Members dialogs were updated', 'wp-members' );
	}

} // End of file.