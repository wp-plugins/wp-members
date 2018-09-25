<?php
/**
 * WP-Members Admin Functions
 *
 * Functions to manage the post/page editor screens.
 * 
 * This file is part of the WP-Members plugin by Chad Butler
 * You can find out more about this plugin at https://rocketgeek.com
 * Copyright (c) 2006-2018 Chad Butler
 * WP-Members(tm) is a trademark of butlerblog.com
 *
 * @package WP-Members
 * @author Chad Butler
 * @copyright 2006-2018
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
		jQuery('<option>').val('unblock').text('<?php _e( 'Unblock', 'wp-members' ) ?>').appendTo("select[name='action']");
		jQuery('<option>').val('block').text('<?php   _e( 'Block',   'wp-members' ) ?>').appendTo("select[name='action']");
		jQuery('<option>').val('hide').text('<?php    _e( 'Hide',    'wp-members' ) ?>').appendTo("select[name='action']");
		jQuery('<option>').val('unblock').text('<?php _e( 'Unblock', 'wp-members' ) ?>').appendTo("select[name='action2']");
		jQuery('<option>').val('block').text('<?php   _e( 'Block',   'wp-members' ) ?>').appendTo("select[name='action2']");
		jQuery('<option>').val('hide').text('<?php    _e( 'Hide',    'wp-members' ) ?>').appendTo("select[name='action2']");
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

		case ( 'unblock' ):
		case ( 'block'   ):
		case ( 'hide'    ):
			// Validate nonce.
			check_admin_referer( 'bulk-posts' );
			// Get the posts.
			$posts = wpmem_get( 'post', '', 'request' );
			// Convert action.
			$status = ( 'hide' == $action ) ? 2 : ( ( 'block' == $action ) ? 1 : 0 );
			// Update posts.
			$x = '';
			if ( $posts ) {
				foreach ( $posts as $post_id ) {
					// Keep a count of posts updated.
					$x++;
					// Make sure $post_id is just an integer.
					$post_id = (int)$post_id;
					// Get the post type.
					$post = get_post( $post_id );
					$type = $post->post_type;
					// Update accordingly.
					wpmem_set_block_status( $status, $post_id, $post->post_type );
				}
				// Set the return message.
				$arr = array( 
					'a' => 'updated',
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
	
			add_meta_box( 'wpmem-block-meta-id', $post_title, 'wpmem_block_meta', $key, 'side', 'high'
				// ,array( '__back_compat_meta_box' => true, ) // @todo Convert to Block and declare this for backwards compat ONLY!
			);
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

	$post_type       = $wp_post_types[ $post->post_type ];
	$post_meta_value = get_post_meta( $post->ID, '_wpmem_block', true );
	$post_meta_value = ( null == $post_meta_value ) ? $wpmem->block[ $post->post_type ] : $post_meta_value;
	$post_meta_settings = array(
		'0' => array( 'text' => __( 'Unblocked', 'wp-members' ), 'icon' => '<span id="wpmem_post_icon_0" class="dashicons dashicons-unlock" ' . ( ( 0 != $post_meta_value ) ? 'style="display:none;"' : '' ) . '></span>' ),
		'1' => array( 'text' => __( 'Blocked',   'wp-members' ), 'icon' => '<span id="wpmem_post_icon_1" class="dashicons dashicons-lock" '   . ( ( 1 != $post_meta_value ) ? 'style="display:none;"' : '' ) . '></span>' ),
		'2' => array( 'text' => __( 'Hidden',    'wp-members' ), 'icon' => '<span id="wpmem_post_icon_2" class="dashicons dashicons-hidden" ' . ( ( 2 != $post_meta_value ) ? 'style="display:none;"' : '' ) . '></span>' ),
	); ?>
	<p><?php
		foreach ( $post_meta_settings as $key => $value ) {
			echo $value['icon'];
		} ?> <?php
		_e( 'Status:', 'wp-members' ); ?> <span id="wpmem_post_block_status"><?php echo $post_meta_settings[ $post_meta_value ]['text']; ?></span> <a href="#" class="hide-if-no-js" id="wpmem_edit_block_status"><?php _e( 'Edit' ); ?></a>
	</p>
	<p>
		<div id="wpmem_block">
		<?php
		$original_value = ''; $original_label = '';
		foreach ( $post_meta_settings as $key => $value ) {
			$original_value = ( $post_meta_value == $key ) ? $key                  : $original_value;
			$original_label = ( $post_meta_value == $key ) ? $value['text'] : $original_label;
			echo '<input type="radio" id="wpmem_block_status_' . $key . '" name="wpmem_block" value="' . $key . '" ' . checked( $post_meta_value, $key, false ) . ' /><label>' . $value['text'] . '</label><br />';
		}
		echo '<input type="hidden" id="wpmem_block_original_value" name="wpmem_block_original_value" value="' . $original_value . '" />';
		echo '<input type="hidden" id="wpmem_block_original_label" name="wpmem_block_original_label" value="' . $original_label . '" />';
		?>
		<p><a href="#" class="hide-if-no-js button" id="wpmem_ok_block_status"><?php echo _e( 'OK' ); ?></a> <!--<a href="#" class="hide-if-no-js" id="wpmem_cancel_block_status"><?php _e( 'Cancel' ); ?></a>--></p>
		</div>
	</p>
	<?php
	/**
	 * Fires after the post block meta box.
	 *
	 * Allows actions at the end of the block meta box on pages and posts.
	 *
	 * @since 2.8.8
	 * @since 3.2.0 Changed to $post_meta_value (same as $block).
	 *
	 * @param $post           object  The WP Post Object.
	 * @param post_meta_value string  The WP-Members block value: 0|1|2 for unblock|block|hide.
	 */
	do_action( 'wpmem_admin_after_block_meta', $post, $post_meta_value );
}


/**
 * Saves the meta boxes data for post and page editor screens.
 *
 * @since 2.8
 *
 * @global object $post
 * @global object $wpmem
 * @param  int    $post_id The post ID
 */
function wpmem_block_meta_save( $post_id ) {
	
	global $post, $wpmem;

	// Quit if we are doing autosave.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	// Quit if the nonce isn't there, or is wrong.
	if ( ! isset( $_POST['wpmem_block_meta_nonce'] ) || ! wp_verify_nonce( $_POST['wpmem_block_meta_nonce'], 'wpmem_block_meta_nonce' ) ) {
		return;
	}
	// Quit if it's a post revision
	if ( false !== wp_is_post_revision( $post_id ) ) {
		return;
	}
	// Quit if the current user cannot edit posts.
	if ( ! current_user_can( 'edit_posts' ) ) {
		return;
	}
	
	// Get value.
	$block = ( isset( $_POST['wpmem_block'] ) ) ? sanitize_text_field( $_POST['wpmem_block'] ) : null;
	
	// Set the value.
	wpmem_set_block_status( $block, $post_id, $post->post_type );

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
		$columns['wpmem_block'] = __( 'Status', 'wp-members' );
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
	$post_type = sanitize_text_field( wpmem_get( 'post_type', 'post', 'request' ) );

	if ( $column_name == 'wpmem_block' ) { 

		$block_meta = get_post_meta( $post_ID, '_wpmem_block', true );

		// Backward compatibility for old block/unblock meta.
		if ( ! $block_meta ) {
			// Check for old meta.
			$old_block   = get_post_meta( $post_ID, 'block',   true );
			$old_unblock = get_post_meta( $post_ID, 'unblock', true );
			$block_meta = ( $old_block ) ? 1 : ( ( $old_unblock ) ? 0 : $block_meta );
		}

		if ( $wpmem->block[ $post_type ] == 1 ) {
			$block_span = array( 'lock', 'green', 'Blocked' );
		}
		if ( $wpmem->block[ $post_type ] == 0 ) {
			$block_span =  array( 'unlock', 'red', 'Unblocked' );
		}
		if ( $wpmem->block[ $post_type ] == 1 && $block_meta == '0' ) {
			$block_span = array( 'unlock', 'red', 'Unblocked' );
		} elseif ( $wpmem->block[ $post_type ] == 0 && $block_meta == '1' ) {
			$block_span = array( 'lock', 'green', 'Blocked' );
		} elseif ( 2 == $block_meta ) {
			$block_span = array( 'hidden', '', 'Hidden' );
		}
		echo '<span class="dashicons dashicons-' . $block_span[0] . '" style="color:' . $block_span[1] . '" title="' . $block_span[2] . '"></span>';
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

/**
 * Sets custom block status for a post.
 *
 * @since 3.2.0
 *
 * @global object $wpmem     The WP_Members object class.
 * @param  int    $status    0|1|2 for unblock|block|hide
 * @param  int    $post_id   The post ID to set a meta value.
 * @param  string $post_type The post type.
 */
function wpmem_set_block_status( $status, $post_id, $post_type ) {
	global $wpmem;
	
	// Previous value.
	$prev_value = get_post_meta( $post_id, '_wpmem_block', true );

	// Update accordingly.
	if ( false !== $prev_value && $status != $prev_value ) {
		if ( $status == $wpmem->block[ $post_type ] ) {
			delete_post_meta( $post_id, '_wpmem_block' );
		} else {
			update_post_meta( $post_id, '_wpmem_block', $status );
		}
	} elseif ( ! $prev_value && $status != $wpmem->block[ $post_type ] ) {
		update_post_meta( $post_id, '_wpmem_block', $status );
	} elseif ( $status != $prev_value ) {
		delete_post_meta( $post_id, '_wpmem_block' );
	}
	
	// If the value is to hide, delete the transient so that it updates.
	if ( 2 == $status || ( 2 == $prev_value && $status != $prev_value ) ) {
		$wpmem->update_hidden_posts();
	}
}

// End of File.