<?php
if ( defined( 'WP_CLI' ) && WP_CLI ) {

	class WP_Members_CLI_User {

		/**
		 * CLI command to activate users.
		 *
		 * ## OPTIONS
		 *
		 * --id=<user_id>
		 * : The WP ID of the user to activate.
		 *
		 * [--notify=<boolean>]
		 * : Whether to send notifcation to user (true if omitted).
		 *
		 * @since 3.3.5
		 */
		public function activate( $args, $assoc_args ) {
			global $wpmem;
			if ( 1 == $wpmem->mod_reg ) {
				$validation = $this->validate_user_id( $assoc_args['id'] );
			} else {
				WP_CLI::error( __( 'Moderated registration is not enabled in WP-Members options.', 'wp-members' ) );
			}

			if ( true === $validation ) {

				$notify  = ( isset( $assoc_args['notify'] ) && 'false' == $assoc_args['notify'] ) ? false : true;

				// Is the user already activated?
				if ( false === wpmem_is_user_activated( $assoc_args['id'] ) ) {

					wpmem_activate_user( $assoc_args['id'], $notify );
					WP_CLI::success( __( 'User activated.', 'wp-members' ) );
					if ( $notify ) {
						WP_CLI::success( __( 'Email notification sent to user.', 'wp-members' ) );
					}
				} else {
					WP_CLI::error( __( 'User is already activated.', 'wp-members' ) );
				}
			} else {
				WP_CLI::error( $validation );
			}
		}

		/**
		 * CLI command to deactivate users.
		 *
		 * ## OPTIONS
		 *
		 * --id=<user_id>
		 * : The WP ID of the user to deactivate.
		 *
		 * @since 3.3.5
		 */
		public function deactivate( $args, $assoc_args ) {
			global $wpmem;
			if ( 1 == $wpmem->mod_reg ) {
				$validation = $this->validate_user_id( $assoc_args['id'] );
			} else {
				WP_CLI::error( __( 'Moderated registration is not enabled in WP-Members options.', 'wp-members' ) );
			}
			
			if ( true === $validation ) {
				wpmem_deactivate_user( $assoc_args['id'] );
				WP_CLI::success( __( 'User deactivated.', 'wp-members' ) );
			} else {
				WP_CLI::error( $validation );
			}		
		}

		/**
		 * Validates user info for activation.
		 *
		 * @since 3.3.5
		 */
		private function validate_user_id( $user_id ) {
			global $wpmem;
			
			$user_id = ( isset( $user_id ) ) ? $user_id : false;

			if ( $user_id ) {
				// Is the user ID and actual user?
				if ( wpmem_is_user( $user_id ) ) {
					return true;
				} else {
					WP_CLI::error( __( 'Invalid user ID. Please specify a valid user. Try `wp user list`.', 'wp-members' ) );
				}
			} else {
				WP_CLI::error( __( 'No user id specified. Must specify user id as --id=123', 'wp-members' ) );
			}
		}

		/**
		 * Lists users by activation state.
		 *
		 * ## OPTIONS
		 *
		 * <pending|activated|deactivated>
		 * : status of the user
		 *
		 * @subcommand list
		 *
		 * @since 3.3.5
		 */
		public function list_users( $args, $assoc_args ) {

			// Accepted list args.
			$accepted = array( 'pending', 'activated', 'deactivated' );

			if ( ! in_array( $args[0], $accepted ) ) {
				WP_CLI::error( 'Must include a user status from the following: pending|activated|deactivated' );
			}

			switch ( $args[0] ) {
				case 'pending':
					$users = wpmem_get_pending_users();
					$status = 'pending';
					break;
				case 'activated':
					$users = wpmem_get_activated_users();
					$status = 'activated';
					break;
				case 'deactivated':
					$users = wpmem_get_deactivated_users();
					$status = 'deactivated';
					break;
			}

			if ( ! empty( $users ) ) {
				foreach ( $users as $user_id ) {
					$user = get_userdata( $user_id );
					$list[] = array(
						'ID'       => $user->ID,
						'username' => $user->user_login,
						'email'    => $user->user_email,
						'status'   => $status,
					);
				}

				$formatter = new \WP_CLI\Formatter( $assoc_args, array( 'ID', 'username', 'email', 'status' ) );
				$formatter->display_items( $list );
			} else {
				WP_CLI::line( 'Currently there are no ' . $status . ' users.' );
			}
		}

		/**
		 * Gets a list of pending users.
		 *
		 * @since 3.3.5
		 */
		public function get_pending() {
			$this->list_users( array( 'pending' ), array() );
		}

		/**
		 * Gets a list of activated users.
		 *
		 * @since 3.3.5
		 */
		public function get_activated() {
			$this->list_users( array( 'activated' ), array() );
		}

		/**
		 * Gets a list of deactivated users.
		 *
		 * @since 3.3.5
		 */
		public function get_deactivated() {
			$this->list_users( array( 'deactivated' ), array() );
		}

		/**
		 * Gets detail of requested user.
		 *
		 * ## OPTIONS
		 *
		 * <username>
		 * : Get user by username.
		 *
		 * [--all]
		 * : Gets all user meta.
		 *
		 * @since 3.3.5
		 */
		public function detail( $args, $assoc_args ) {
			// is user by id, email, or login
			$user = get_user_by( 'login', $args[0] );
			if ( empty( $user ) || ! $user ) {
				WP_CLI::error( 'User does not exist. Try wp user list' );
			}
			$all  = ( $assoc_args['all'] ) ? true : false;
			$this->display_user_detail( $user->ID, $all );
		}
		
		/**
		 * Handles user detail display.
		 *
		 * @since 3.3.5
		 */
		private function display_user_detail( $user_id, $all ) {
			WP_CLI::line( __( 'User: %s', 'wp-members' ) );

			$values = wpmem_user_data( $user_id, $all );
			foreach ( $values as $key => $meta ) {
				 $list[] = array(
					 'meta' => $key,
					 'value' => $meta,
				 );
			}

			$formatter = new \WP_CLI\Formatter( $assoc_args, array( 'meta', 'value' ) );

			$formatter->display_items( $list );
		}
		
		/**
		 * Manually set a user as confirmed.
		 *
		 * ## OPTIONS
		 *
		 * --id=<user_id>
		 * : The WP ID of the user to activate.
		 *
		 * @since 3.3.5
		 */
		public function confirm( $args, $assoc_args ) {
			global $wpmem;
			$validation = $this->validate_user_id( $assoc_args['id'] );
			if ( true === $validation ) {
				wpmem_set_user_as_confirmed( $assoc_args['id'] );
				WP_CLI::success( 'User confirmed' );
			}
		}
	}
}
WP_CLI::add_command( 'mem user', 'WP_Members_CLI_User' );