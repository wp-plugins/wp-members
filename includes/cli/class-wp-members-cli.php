<?php

if ( defined( 'WP_CLI' ) && WP_CLI ) {

	class WP_Members_CLI {

		public function __construct() {}

		public function user( $args, $assoc_args ) {
			// is user by id, email, or login
			$user = get_user_by( 'login', $args[0] );
			$all  = ( $assoc_args['all'] ) ? true : false;
			$this->display_user_detail( $user->ID, $all );
		}

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

	}

    WP_CLI::add_command( 'mem', 'WP_Members_CLI' );

}