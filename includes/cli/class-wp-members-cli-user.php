<?php

class WP_Members_CLI_User {
	
	/**
	 * CLI function to activate users.
	 *
	 * @since 3.3.5
	 */
	public function activate( $args, $assoc_args ) {
		
		$validation = $this->activate_validation( $assoc_args );

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
	 * CLI function to deactivate users.
	 *
	 * @since 3.3.5
	 */
	public function deactivate( $args, $assoc_args ) {
		$validation = $this->activate_validation( $assoc_args );
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
	private function activate_validation( $assoc_args ) {
		global $wpmem;
		if ( 1 == $wpmem->mod_reg ) {
			$user_id = ( isset( $assoc_args['id'] ) ) ? $assoc_args['id'] : false;
			
			if ( $user_id ) {
				// Is the user ID and actual user?
				if ( wpmem_is_user( $user_id ) ) {
					return true;
				} else {
					WP_CLI::error( __( 'Invalid user ID. Please specify a valid user.', 'wp-members' ) );
				}
			} else {
				WP_CLI::error( __( 'No user id specified. Must specify user id as --id=123', 'wp-members' ) );
			}
		} else {
			WP_CLI::error( __( 'Moderated registration is not enabled in WP-Members options.', 'wp-members' ) );
		}
	}
	
	/**
	 * Lists users by activation state.
	 *
	 * @since 3.3.5
	 */
	public function list( $args, $assoc_args ) {
		
		switch ( $assoc_args['status'] ) {
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
	}
	
	public function get_pending() {
		$this->list( array(), array( 'status'=>'pending' ) );
	}
	
	public function get_activated() {
		$this->list( array(), array( 'status'=>'activated' ) );
	}
	
	public function get_deactivated() {
		$this->list( array(), array( 'status'=>'deactivated' ) );
	}
}

WP_CLI::add_command( 'mem user', 'WP_Members_CLI_User' );