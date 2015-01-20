<?php
/**
 * WP-Members Admin Functions
 *
 * Functions to manage the post/page editor screens.
 * 
 * This file is part of the WP-Members plugin by Chad Butler
 * You can find out more about this plugin at http://rocketgeek.com
 * Copyright (c) 2006-2015  Chad Butler
 * WP-Members(tm) is a trademark of butlerblog.com
 *
 * @package WordPress
 * @subpackage WP-Members
 * @author Chad Butler
 * @copyright 2006-2015
 */


/**
 * Actions
 */
add_action( 'admin_footer-edit.php', 'wpmem_bulk_posts_action' );
add_action( 'load-edit.php', 'wpmem_posts_page_load' );
add_action( 'admin_notices', 'wpmem_posts_admin_notices');


/**
 * Function to add block/unblock to the bulk dropdown list
 *
 * @since 2.9.2
 */
function wpmem_bulk_posts_action()
{ ?>
    <script type="text/javascript">
      jQuery(document).ready(function() {
        jQuery('<option>').val('block').text('<?php _e( 'Block', 'wp-members' )?>').appendTo("select[name='action']");
		jQuery('<option>').val('unblock').text('<?php _e( 'Unblock', 'wp-members' )?>').appendTo("select[name='action']");
        jQuery('<option>').val('block').text('<?php _e( 'Block', 'wp-members' )?>').appendTo("select[name='action2']");
		jQuery('<option>').val('unblock').text('<?php _e( 'Unblock', 'wp-members' )?>').appendTo("select[name='action2']");
      });
    </script>
    <?php
}


/**
 * Function to handle bulk actions at page load
 *
 * @since 2.9.2
 *
 * @uses WP_Users_List_Table
 */
function wpmem_posts_page_load()
{
	$wp_list_table = _get_list_table( 'WP_Posts_List_Table' );
	$action = $wp_list_table->current_action();
	$sendback = '';

	switch( $action ) { 
	
		case ( 'block' ):
		case ( 'unblock' ):
			/** validate nonce **/
			check_admin_referer( 'bulk-posts' );
			/** get the posts **/
			$posts = ( isset( $_REQUEST['post'] ) ) ? $_REQUEST['post'] : '';
			/** update posts **/
			$x = '';
			if( $posts ) {	
				foreach( $posts as $post_id ) {
					$x++;
					$post = get_post( $post_id );
					$type = $post->post_type;
					// update accordingly
					if( ( $type == 'post' && WPMEM_BLOCK_POSTS == 0 ) || 
						( $type == 'page' && WPMEM_BLOCK_PAGES == 0 ) ) {
						if( $action == 'block' ) {
							update_post_meta( $post_id, '_wpmem_block', 1 );
						} else {
							delete_post_meta( $post_id, '_wpmem_block' );
						}
					}
					
					if( ( $type == 'post' && WPMEM_BLOCK_POSTS == 1 ) || 
						( $type == 'page' && WPMEM_BLOCK_PAGES == 1 ) ) {
						if( $action == 'unblock' ) {
							update_post_meta( $post_id, '_wpmem_block', 0 );	
						} else {
							delete_post_meta( $post_id, '_wpmem_block' );
						}
					}
				}
				/** set the return message */
				$arr = array( 
					'a' => $action, 
					'n' => $x
				);
				if( $type == 'page' ) {
					$arr['post_type'] = 'page';
				}
				$sendback = add_query_arg( array( $arr ), '', $sendback );
			} else {
				/** set the return message */
				$sendback = add_query_arg( array( 'a' => 'none' ), '', $sendback );
			}
			break;
		
		default:
			return;

	}

	/** if we did not return already, we need to wp_redirect */
	wp_redirect( $sendback );
	exit();
}


/**
 * Function to echo admin update message
 *
 * @since 2.8.2
 */
function wpmem_posts_admin_notices()
{    
	global $pagenow, $post_type;
	if( $pagenow == 'edit.php' && isset( $_REQUEST['a'] ) ) {
		$action = ( $_REQUEST['a'] == 'block' ) ? 'blocked' : 'unblocked';
		echo '<div class="updated"><p>' . $_REQUEST['n'] . ' ' . $post_type . ' ' . $action . '</p></div>';
	}
}


/**
 * Adds the blocking meta boxes for post and page editor screens.
 *
 * @since 2.8
 */
function wpmem_block_meta_add() 
{
	/**
	 * Filter the post meta box title
	 *
	 * @since 2.9.0
	 */
	$post_title = apply_filters( 'wpmem_admin_post_meta_title', __( 'Post Restriction', 'wp-members' ) );
	
	/**
	 * Filter the page meta box title
	 *
	 * @since 2.9.0
	 */
	$page_title = apply_filters( 'wpmem_admin_page_meta_title', __( 'Page Restriction', 'wp-members' ) );

	add_meta_box( 'wpmem-block-meta-id', $post_title, 'wpmem_block_meta', 'post', 'side', 'high' );
	add_meta_box( 'wpmem-block-meta-id', $page_title, 'wpmem_block_meta', 'page', 'side', 'high' );	
}


/**
 * Builds the meta boxes for post and page editor screens.
 *
 * @since 2.8
 *
 * @uses do_action Calls 'wpmem_admin_after_block_meta' Allows actions at the end of the block meta box on pages and posts
 *
 * @global $post The WordPress post object
 */
function wpmem_block_meta()  
{  
    global $post;

    wp_nonce_field( 'wpmem_block_meta_nonce', 'wpmem_block_meta_nonce' );

	$post_type = get_post_type_object( $post->post_type );

	if( ( $post->post_type == 'post' && WPMEM_BLOCK_POSTS == 1 ) || ( $post->post_type == 'page' && WPMEM_BLOCK_PAGES == 1 ) ) {
		$block = 0;
		$notice_text = 'blocked';
		$text = 'Unblock';
	} elseif( ( $post->post_type == 'post' && WPMEM_BLOCK_POSTS == 0 ) || ( $post->post_type == 'page' && WPMEM_BLOCK_PAGES == 0 ) ) {
		$block = 1;
		$notice_text = 'not blocked';
		$text = 'Block';	
	}
	$meta = '_wpmem_block';
	$admin_url = get_admin_url(); ?>
	
	<p>
		<?php
		printf( '%s are %s by default.', $post_type->labels->name, $notice_text );
		echo '&nbsp;&nbsp;';
		printf( '<a href="%s/options-general.php?page=wpmem-settings">Edit</a>', $admin_url );
		?>
	</p>
    <p>
	<?php if( $block == 1 ) { ?>
		<input type="checkbox" id="wpmem_block" name="wpmem_block" value="1" <?php checked( get_post_meta( $post->ID, $meta, true ), '1' ); ?> />
	<?php } else { ?>
		<input type="checkbox" id="wpmem_block" name="wpmem_block" value="0" <?php checked( get_post_meta( $post->ID, $meta, true ), '0' ); ?> />
	<?php } ?>
		<label for="wpmem_block"><?php printf( '%s this %s', $text, strtolower( $post_type->labels->singular_name ) ); ?></label>
    </p>
    <?php
	/**
	 * Fires after the post block meta box.
	 *
	 * @since 2.8.8
	 *
	 * @param $post
	 * @param $block
	 */
	do_action( 'wpmem_admin_after_block_meta', $post, $block );
}


/**
 * Saves the meta boxes data for post and page editor screens.
 *
 * @since 2.8
 *
 * @uses do_action Calls 'wpmem_admin_block_meta_save' allows actions to be hooked to the meta save process
 *
 * @param int $post_id The post ID
 */
function wpmem_block_meta_save( $post_id )  
{  
    // quit if we are doing autosave
    if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return; 
	
    // quit if the nonce isn't there, or is wrong
    if( ! isset( $_POST['wpmem_block_meta_nonce'] ) || ! wp_verify_nonce( $_POST['wpmem_block_meta_nonce'], 'wpmem_block_meta_nonce' ) ) return; 
    
	// quit if the current user cannot edit posts
    if( ! current_user_can( 'edit_posts' ) ) return;  
    
	// get value
    $block = isset( $_POST['wpmem_block'] ) ? $_POST['wpmem_block'] : null; 	
	
	// need the post object
	global $post; 
	
	// update accordingly
	if( $block != null ) {
		update_post_meta( $post_id, '_wpmem_block', $block );
	} else {
		delete_post_meta( $post_id, '_wpmem_block' );
	}
	
	do_action( 'wpmem_admin_block_meta_save', $post, $block, '' );
}


/**
 * Adds WP-Members blocking status to Posts Table columns
 *
 * @since 2.8.3
 *
 * @uses wp_enqueue_style Loads the WP-Members admin stylesheet
 *
 * @param arr $columns The array of table columns
 */
function wpmem_post_columns( $columns ) {
	wp_enqueue_style ( 'wpmem-admin-css', WPMEM_DIR . '/css/admin.css', '', WPMEM_VERSION );
	$columns['wpmem_block'] = ( WPMEM_BLOCK_POSTS == 1 ) ? __( 'Unblocked?', 'wp-members' ) : __( 'Blocked?', 'wp-members' );
    return $columns;
}


/**
 * Adds blocking status to the Post Table column
 *
 * @since 2.8.3
 *
 * @param $column_name
 * @param $post_ID
 */
function wpmem_post_columns_content( $column_name, $post_ID ) {
	if( $column_name == 'wpmem_block' ) { 
	
		$block_meta = get_post_meta( $post_ID, '_wpmem_block', true );
		
		/**
		 * Backward compatibility for old block/unblock meta
		 */
		if( ! $block_meta ) {
			// check for old meta
			$old_block   = get_post_meta( $post_ID, 'block',   true );
			$old_unblock = get_post_meta( $post_ID, 'unblock', true );
			$block_meta = ( $old_block ) ? 1 : ( ( $old_unblock ) ? 0 : $block_meta );
		}	
		
		echo ( WPMEM_BLOCK_POSTS == 1 && $block_meta == '0' ) ? __( 'Yes' ) : ''; 
		echo ( WPMEM_BLOCK_POSTS == 0 && $block_meta == '1' ) ? __( 'Yes' ) : ''; 
    } 
}


/**
 * Adds WP-Members blocking status to Page Table columns
 *
 * @since 2.8.3
 *
 * @uses wp_enqueue_style Loads the WP-Members admin stylesheet
 *
 * @param arr $columns The array of table columns
 */
function wpmem_page_columns( $columns ) {
	wp_enqueue_style ( 'wpmem-admin-css', WPMEM_DIR . '/css/admin.css', '', WPMEM_VERSION );
	$columns['wpmem_block'] = ( WPMEM_BLOCK_PAGES == 1 ) ? __( 'Unblocked?', 'wp-members' ) : __( 'Blocked?', 'wp-members' );  
    return $columns;
}


/**

 * Adds blocking status to the Page Table column
 *
 * @since 2.8.3
 *
 * @param $column_name
 * @param $post_ID
 */
function wpmem_page_columns_content( $column_name, $post_ID ) {
	if( $column_name == 'wpmem_block' ) {
	
		$block_meta = get_post_meta( $post_ID, '_wpmem_block', true );
	
		/**
		 * Backward compatibility for old block/unblock meta
		 */
		if( ! $block_meta ) {
			// check for old meta
			$old_block   = get_post_meta( $post_ID, 'block',   true );
			$old_unblock = get_post_meta( $post_ID, 'unblock', true );
			$block_meta = ( $old_block ) ? 1 : ( ( $old_unblock ) ? 0 : $block_meta );
		}	
		
		echo ( WPMEM_BLOCK_PAGES == 1 && $block_meta == '0' ) ? __( 'Yes' ) : ''; 
		echo ( WPMEM_BLOCK_PAGES == 0 && $block_meta == '1' ) ? __( 'Yes' ) : ''; 
    } 
}

/** End of File **/