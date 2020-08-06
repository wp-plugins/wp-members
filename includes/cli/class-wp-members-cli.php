<?php

if ( defined( 'WP_CLI' ) && WP_CLI ) {

	class WP_Members_CLI {

		public function __construct() {}
		
		/**
		 * Gets status of a post.
		 *
		 * ## OPTIONS
		 *
		 * <post_ID>
		 * : The post ID to check.
		 *
		 * @since 3.3.5
		 */
		public function post_status( $args, $assoc_args ) {
			if ( false === get_post_status ( $args[0] ) ) {
				WP_CLI::error( 'No post id ' . $args[0] . ' exists. Try wp post list' );
			}
			if ( true === wpmem_is_hidden( $args[0] ) ) {
				$line = 'post ' . $args[0] . ' is hidden';
			} else {
				$line = ( wpmem_is_blocked( $args[0] ) ) ? 'post ' . $args[0] . ' is blocked' : 'post ' . $args[0] . ' is not blocked';
			}
			WP_CLI::line( $line );
		}
		
		/**
		 * Gets a post block status.
		 *
		 * ## OPTIONS
		 *
		 * <post_ID>
		 * : The ID of the post to check.
		 *
		 * @since 3.3.5
		 */
		public function get_block_value( $args ) {
			WP_CLI::line( 'post block setting: ' . wpmem_get_block_setting( $args[0] ) );
		}
		
		/**
		 * Refreshes the hidden post array.
		 *
		 * @since 3.3.5
		 */
		public function refresh_hidden_posts() {
			wpmem_update_hidden_posts();
			WP_CLI::success( 'hidden posts refreshed' );
		}
		
		/**
		 * Gets a list of hidden posts.
		 *
		 * @since 3.3.5
		 */
		public function get_hidden_posts() {
			
			$hidden_posts = wpmem_get_hidden_posts();
			
			if ( empty( $hidden_posts ) ) {
				WP_CLI::line( 'There are no hidden posts' );
			} else {
				foreach ( $hidden_posts as $post_id ) {
					 $list[] = array(
						 'id' => $post_id,
						 'title' => get_the_title( $post_id ),
						 'url' => get_permalink( $post_id ),
					 );
				}

				WP_CLI::line( 'WP-Members hidden posts:' );
				$formatter = new \WP_CLI\Formatter( $assoc_args, array( 'id', 'title', 'url' ) );
				$formatter->display_items( $list );
			}
		}
		
		/**
		 * Sets the block status of a post.
		 *
		 * ## OPTIONS
		 *
		 * <post_ID>
		 * : The post ID to set.
		 *
		 * <unblock|unrestrict|hide|block|restrict>
		 * : The status to set.
		 *
		 * @since 3.3.5
		 */
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

	}

    WP_CLI::add_command( 'mem', 'WP_Members_CLI' );
}