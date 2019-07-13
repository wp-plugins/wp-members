<?php
/**
 * The WP_Members Admin API Class.
 *
 * @package WP-Members
 * @subpackage WP_Members Admin API Object Class
 * @since 3.1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

class WP_Members_Admin_API {
	
	function load_hooks() {

		// Actions and Filters. 
		add_filter( 'wpmem_admin_tabs',    array( $this, 'email_test_tab'          ) );
		add_action( 'wpmem_admin_do_tab',  array( $this, 'do_email_test_tab', 1, 1 ) );
		
	}

	/**
	 * Adds Email Test tab to the WP-Members options.
	 *
	 * @since 1.0
	 *
	 * @param  array $tabs An array of the admin tabs.
	 * @return array $tabs The array with the Email Test tab added.
	 */
	function email_test_tab( $tabs ) {
		return array_merge( $tabs, array( 'emailtest' => 'Email Test' ) );
	}

	/**
	 * Builds the Email Test tab.
	 *
	 * @since 1.0
	 *
	 * @global object $current_user The current user object for test data.
	 * @param  string $tab          The admin tab being displayed.
	 */
	function do_email_test_tab( $tab ) {

		if ( $tab == 'emailtest' ) {

			global $current_user, $wpmem;

			// Use admin data for test user and send-to address.
			$current_user = wp_get_current_user();

			// Puts together consistent layout with the rest of the plugin. ?>
			<div class="metabox-holder has-right-sidebar">
				<div id="post-body">
					<div id="post-body-content">
						<div class="postbox">
							<h3>Send test emails</h3>
							<div class="inside"><?php

			// If the form is submitted, send emails accordingly.
			if ( isset( $_POST['submit'] ) ) {

				// Tee up builtin emails.
				$builtins = array(
					'wpmembers_email_newreg'  => 0,
					'wpmembers_email_newmod'  => 1,
					'wpmembers_email_appmod'  => 2,
					'wpmembers_email_repass'  => 3,
					'wpmembers_email_getuser' => 4,
				);

				// Send emails.
				$success = '<p>';
				foreach ( $wpmem->admin->emails as $key => $val ) {
					if ( isset( $_POST[ $key ] ) ) {
						if ( array_key_exists( $key, $builtins ) ) {
							// Builtin user emails
							wpmem_email_to_user( $current_user->ID, 'fakepassword', $builtins[ $key ] );
						} elseif ( 'wpmembers_email_notify' == $key ) {
							wpmem_notify_admin( $current_user->ID, $wpmem->fields );
						} else {
							// This is a custom email.
							$arr['subj']   = $val['subject_value'];
							$arr['body']   = $val['body_value'];
							$arr['toggle'] = $key;
							wpmem_email_to_user( $current_user->ID, '', 5, $wpmem->fields, '', $arr );
						}
						$success.= '<span class="dashicons dashicons-yes"></span> <i>' . $val['heading'] . '</i> test message sent to ' . $current_user->user_email . '<br />';
					}
				}
				echo ( '<p>' === $success ) ? 'No messages were sent</p>' : $success . '</p>';			
			}

			// This builds the form for the test email selections.
			$p2 = $_SERVER['REQUEST_URI']; ?>

			<p>This process will send a test version of each email
			   the plugin sends to the following email address:<br />
			   <strong><?php echo $current_user->user_email; ?></strong>
			</p>
			<p>Check below which emails you would like to send: </p>
			<form method="post" name="testemails" url="<?php echo $p2;?>">
			<p><?php
			// User emails, including any added via api.
			foreach ( $wpmem->admin->emails as $key => $val ) {
				echo '<input type="checkbox" name="' . $key . '" value="' . $key . '" /> ';
				echo '<label> ' . $val['heading'] . '</label><br />';
			} ?>
			</p>
			<p><?php submit_button( 'Send Test Emails' ); ?></p>
			</form>
			<?php

			// Close all the admin div wrappers for layout. ?>
			</div></div></div></div></div><?php
		}
	}
	
}

// End of file.