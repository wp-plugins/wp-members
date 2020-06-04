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
		
		public function post_status( $args, $assoc_args ) {
			if ( true === wpmem_is_hidden( $args[0] ) ) {
				$line = 'post ' . $args[0] . ' is hidden';
			} else {
				$line = ( wpmem_is_blocked( $args[0] ) ) ? 'post ' . $args[0] . ' is blocked' : 'post ' . $args[0] . ' is not blocked';
			}
			WP_CLI::line( $line );
		}
		
		public function get_block_value( $args ) {
			WP_CLI::line( 'post block setting: ' . wpmem_get_block_setting( $args[0] ) );
		}
		
		public function refresh_hidden_posts() {
			wpmem_update_hidden_posts();
			WP_CLI::line( 'hidden posts refreshed' );
		}
		
		public function get_hidden_posts() {
			$posts = implode( ", ", wpmem_get_hidden_posts() );
			WP_CLI::line( 'hidden post IDs: ' . $posts );
		}
		
		public function set_post_status( $args, $assoc_args ) {
			switch( $args[1] ) {
				case 'unblock':
				case 'unrestrict':
					$val = 0; $line = 'unrestricted';
					break;
				case 'hide':
					$val = 2; $line = 'hidden';
					break;
				case 'block':
				case 'restrict':
				default;
					$val = 1; $line = 'restricted';
					break;
			}
			update_post_meta( $args[0], '_wpmem_block', $val );
			WP_CLI::line( 'Set post id ' . $args[0] . ' as ' . $line );
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