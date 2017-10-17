<?php
/**
 * WP-Members Admin Functions
 *
 * Functions to manage the post/page editor screens.
 * 
 * This file is part of the WP-Members plugin by Chad Butler
 * You can find out more about this plugin at http://rocketgeek.com
 * Copyright (c) 2006-2017 Chad Butler
 * WP-Members(tm) is a trademark of butlerblog.com
 *
 * @package WP-Members
 * @author Chad Butler
 * @copyright 2006-2017
 *
 * Functions included:
 * - wpmem_bulk_posts_action
 * - wpmem_posts_page_load
 * - wpmem_posts_admin_notices
 * - wpmem_block_meta_add
 * - wpmem_block_meta
 * - wpmem_block_meta_save
 * - wpmem_post_columns
 * - wpmem_post_columns_content
 * - wpmem_load_tinymce
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

/**
 * Function to add block/unblock to the bulk dropdown list.
 *
 * @since 2.9.2
 *
 * @global object $wpmem The WP_Members object.
 */
function wpmem_bulk_posts_action() {  
	global $wpmem;
	if ( ( isset( $_GET['post_type'] ) && ( 'page' == $_GET['post_type'] || 'post' == $_GET['post_type'] || array_key_exists( $_GET['post_type'], $wpmem->post_types ) ) ) || ! isset( $_GET['post_type'] ) ) { ?>
	<script type="text/javascript">
		jQuery(document).ready(function() {
		jQuery('<option>').val('block').text('<?php   _e( 'Block',   'wp-members' ) ?>').appendTo("select[name='action']");
		jQuery('<option>').val('unblock').text('<?php _e( 'Unblock', 'wp-members' ) ?>').appendTo("select[name='action']");
		jQuery('<option>').val('block').text('<?php   _e( 'Block',   'wp-members' ) ?>').appendTo("select[name='action2']");
		jQuery('<option>').val('unblock').text('<?php _e( 'Unblock', 'wp-members' ) ?>').appendTo("select[name='action2']");
		});
	</script><?php
	}
}


/**
 * Function to handle bulk actions at page load.
 *
 * @since 2.9.2
 *
 * @global object $wpmem The WP_Members object.
 */
function wpmem_posts_page_load() {

	global $wpmem;

	$wp_list_table = _get_list_table( 'WP_Posts_List_Table' );
	$action = $wp_list_table->current_action();
	$sendback = '';

	switch ( $action ) {

		case ( 'block' ):
		case ( 'unblock' ):
			// Validate nonce.
			check_admin_referer( 'bulk-posts' );
			// Get the posts.
			$posts = ( isset( $_REQUEST['post'] ) ) ? $_REQUEST['post'] : '';
			// Update posts.
			$x = '';
			if ( $posts ) {
				foreach ( $posts as $post_id ) {
					$x++;
					$post = get_post( $post_id );
					$type = $post->post_type;
					// Update accordingly.
					if ( $wpmem->block[ $type ] == 0 ) {
						if ( $action == 'block' ) {
							update_post_meta( $post_id, '_wpmem_block', 1 );
						} else {
							delete_post_meta( $post_id, '_wpmem_block' );
						}
					}

					if ( $wpmem->block[ $type ] == 1 ) {
						if ( $action == 'unblock' ) {
							update_post_meta( $post_id, '_wpmem_block', 0 );
						} else {
							delete_post_meta( $post_id, '_wpmem_block' );
						}
					}
				}
				// Set the return message.
				$arr = array( 
					'a' => $action,
					'n' => $x,
					'post_type' => $type,
				);
				if ( isset( $_GET['post_status'] ) && 'all' != $_GET['post_status'] ) {
					$arr['post_status'] = sanitize_text_field( $_GET['post_status'] );
				}
	
				$sendback = add_query_arg( array( $arr ), '', $sendback );

			} else {
				// Set the return message.
				$sendback = add_query_arg( array( 'a' => 'none' ), '', $sendback );
			}
			break;

		default:
			return;

	}

	// If we did not return already, we need to wp_redirect.
	wp_redirect( $sendback );
	exit();
}


/**
 * Function to echo admin update message.
 *
 * @since 2.8.2
 *
 * @global $pagenow
 * @global $post_type
 */
function wpmem_posts_admin_notices() {

	global $pagenow, $post_type;
	if ( $pagenow == 'edit.php' && isset( $_REQUEST['a'] ) ) {
		$msg = ( $_REQUEST['a'] == 'block' ) ? sprintf( __( '%s blocked', 'wp-members' ), $post_type ) : sprintf( __( '%s unblocked', 'wp-members' ), $post_type );
		echo '<div class="updated"><p>' . esc_html( $_REQUEST['n'] ) . ' ' . esc_html( $msg ) . '</p></div>';
	}
}


/**
 * Adds the blocking meta boxes for post and page editor screens.
 *
 * @since 2.8
 *
 * @global object $wp_post_types The Post Type object.
 * @global object $wpmem         The WP-Members object.
 */
function wpmem_block_meta_add() {
	
	global $wp_post_types, $wpmem;

	// Build an array of post types
	$post_arr = array(
		'post' => 'Posts',
		'page' => 'Pages',
	);
	if ( isset( $wpmem->post_types ) ) {
		foreach ( $wpmem->post_types as $key => $val ) {
			$post_arr[ $key ] = $val;
		}
	}

	foreach ( $post_arr as $key => $val ) {
		if ( isset( $wp_post_types[ $key ] ) ) {
			$post_type = $wp_post_types[ $key ];
			/**
			 * Filter the post meta box title.
			 *
			 * @since 2.9.0
			 *
			 * @param Post restriction title.
			 */
			$post_title = apply_filters( 'wpmem_admin_' . $key . '_meta_title', sprintf( __( '%s Restriction', 'wp-members' ), $post_type->labels->singular_name ) );
	
			add_meta_box( 'wpmem-block-meta-id', $post_title, 'wpmem_block_meta', $key, 'side', 'high' );
		}
	}
}


/**
 * Builds the meta boxes for post and page editor screens.
 *
 * @since 2.8
 *
 * @global object $post          The WordPress post object.
 * @global object $wp_post_types The Post Type object.
 * @global object $wpmem         The WP-Members object.
 */
function wpmem_block_meta() {

	global $post, $wp_post_types, $wpmem;

	wp_nonce_field( 'wpmem_block_meta_nonce', 'wpmem_block_meta_nonce' );

	$post_type = $wp_post_types[ $post->post_type ];

	if ( isset( $wpmem->block[ $post->post_type ] ) && $wpmem->block[ $post->post_type ] == 1 ) {
		$block = 0;
		$notice_text = sprintf( __( '%s are blocked by default.', 'wp-members' ), $post_type->labels->name );
		$text = sprintf( __( 'Unblock this %s', 'wp-members' ), strtolower( $post_type->labels->singular_name ) );
	} else {
		$block = 1;
		$notice_text = sprintf( __( '%s are not blocked by default.', 'wp-members' ), $post_type->labels->name );
		$text = sprintf( __( 'Block this %s', 'wp-members' ), strtolower( $post_type->labels->singular_name ) );
	}
	$meta = '_wpmem_block';
	$admin_url = get_admin_url(); ?>
	
	<p>
		<?php echo $notice_text . '&nbsp;&nbsp;<a href="' . add_query_arg( 'page', 'wpmem-settings', get_admin_url() . 'options-general.php' ) . '">' . __( 'Edit', 'wp-members' ) . '</a>'; ?>
	</p>
	<p>
		<input type="checkbox" id="wpmem_block" name="wpmem_block" value="<?php echo $block; ?>" <?php checked( get_post_meta( $post->ID, $meta, true ), $block ); ?> />
		<label for="wpmem_block"><?php echo $text; ?></label>
	</p>
	<?php
	/**
	 * Fires after the post block meta box.
	 *
	 * Allows actions at the end of the block meta box on pages and posts.
	 *
	 * @since 2.8.8
	 *
	 * @param $post  object  The WP Post Object.
	 * @param $block boolean The WP-Members block value.
	 */
	do_action( 'wpmem_admin_after_block_meta', $post, $block );
}


/**
 * Saves the meta boxes data for post and page editor screens.
 *
 * @since 2.8
 *
 * @global object $post
 * @param  int $post_id The post ID
 */
function wpmem_block_meta_save( $post_id ) {

	// Quit if we are doing autosave.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	// Quit if the nonce isn't there, or is wrong.
	if ( ! isset( $_POST['wpmem_block_meta_nonce'] ) || ! wp_verify_nonce( $_POST['wpmem_block_meta_nonce'], 'wpmem_block_meta_nonce' ) ) {
		return;
	}

	// Quit if the current user cannot edit posts.
	if ( ! current_user_can( 'edit_posts' ) ) {
		return;
	}

	// Get value.
	$block = ( isset( $_POST['wpmem_block'] ) ) ? sanitize_text_field( $_POST['wpmem_block'] ) : null;

	// Need the post object.
	global $post; 

	// Update accordingly.
	if ( $block != null ) {
		update_post_meta( $post_id, '_wpmem_block', $block );
	} else {
		delete_post_meta( $post_id, '_wpmem_block' );
	}

	/**
	 * Fires after the post block meta box is saved.
	 *
	 * Allows actions to be hooked to the meta save process.
	 *
	 * @since 2.8.8
	 *
	 * @param $post  object  The WP Post Object.
	 * @param $block boolean The WP-Members block value.
	 */
	do_action( 'wpmem_admin_block_meta_save', $post, $block, '' );
}


/**
 * Adds WP-Members blocking status to Posts Table columns.
 *
 * @since 2.8.3
 *
 * @global object $wpmem   The WP-Members Object
 * @param  array  $columns The array of table columns.
 * @return array  $columns
 */
function wpmem_post_columns( $columns ) {
	global $wpmem;
	$post_type = ( isset( $_REQUEST['post_type'] ) ) ? sanitize_text_field( $_REQUEST['post_type'] ) : 'post';
	
	if ( $post_type == 'page' || $post_type == 'post' || array_key_exists( $post_type, $wpmem->post_types ) ) {
		$columns['wpmem_block'] = ( $wpmem->block[ $post_type ] == 1 ) ? __( 'Unblocked?', 'wp-members' ) : __( 'Blocked?', 'wp-members' );
	}
	return $columns;
}


/**
 * Adds blocking status to the Post Table column.
 *
 * @since 2.8.3
 *
 * @global object $wpmem       The WP_Members Object.
 * @param  string $column_name
 * @param  int    $post_ID
 */
function wpmem_post_columns_content( $column_name, $post_ID ) {

	global $wpmem;
	$post_type = ( isset( $_REQUEST['post_type'] ) ) ? sanitize_text_field( $_REQUEST['post_type'] ) : 'post';

	if ( $column_name == 'wpmem_block' ) { 

		$block_meta = get_post_meta( $post_ID, '_wpmem_block', true );

		// Backward compatibility for old block/unblock meta.
		if ( ! $block_meta ) {
			// Check for old meta.
			$old_block   = get_post_meta( $post_ID, 'block',   true );
			$old_unblock = get_post_meta( $post_ID, 'unblock', true );
			$block_meta = ( $old_block ) ? 1 : ( ( $old_unblock ) ? 0 : $block_meta );
		}

		echo ( $wpmem->block[ $post_type ] == 1 && $block_meta == '0' ) ? __( 'Yes' ) : '';
		echo ( $wpmem->block[ $post_type ] == 0 && $block_meta == '1' ) ? __( 'Yes' ) : '';
	}
}


/**
 * Adds shortcode dropdown to post editor tinymce.
 *
 * @since 3.0
 *
 * @global object $wpmem_shortcode The WP_Members_TinyMCE_Buttons object.
 */
function wpmem_load_tinymce() {
	// @todo For now, only load if WP version is high enough.
	if ( version_compare( get_bloginfo( 'version' ), '3.9', '>=' ) ) {
		global $wpmem_shortcode;
		include( WPMEM_PATH . 'admin/includes/class-wp-members-tinymce-buttons.php' );
		$wpmem_shortcode = new WP_Members_TinyMCE_Buttons;
	}
}

// End of File.