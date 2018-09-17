<?php
/**
 * The WP_Members Products Admin Class.
 *
 * @package WP-Members
 * @subpackage WP_Members_Products_Admin
 * @since 3.2.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

class WP_Members_Products_Admin {

	/**
	 * Class constructor.
	 *
	 * @since 3.2.0
	 *
	 * @global object $wpmem
	 */
	function __construct() {
		global $wpmem;
		if ( 1 == $wpmem->enable_products ) {
			add_filter( 'manage_wpmem_product_posts_columns',       array( $this, 'columns_heading' ) );
			add_action( 'manage_wpmem_product_posts_custom_column', array( $this, 'columns_content' ), 10, 2 );
			add_action( 'add_meta_boxes',               array( $this, 'meta_boxes' ) );
			add_action( 'save_post',                    array( $this, 'save_details' ) );
			add_action( 'wpmem_admin_after_block_meta', array( $this, 'add_product_to_post' ), 10, 2 );
			add_action( 'wpmem_admin_block_meta_save',  array( $this, 'save_product_to_post' ), 10, 3 );
			add_action( 'admin_footer',                 array( $this, 'enqueue_select2' ) );
			add_filter( 'manage_users_columns',         array( $this, 'user_columns' ) );
			add_filter( 'manage_users_custom_column',   array( $this, 'user_columns_content' ), 10, 3 );
			add_filter( 'manage_posts_columns',         array( $this, 'post_columns' ) );
			add_action( 'manage_posts_custom_column',   array( $this, 'post_columns_content' ), 10, 2 );
			add_filter( 'manage_pages_columns',         array( $this, 'post_columns' ) );
			add_action( 'manage_pages_custom_column',   array( $this, 'post_columns_content' ), 10, 2 );
			foreach( $wpmem->post_types as $key => $val ) {
				add_filter( 'manage_' . $key . '_posts_columns',       array( $this, 'post_columns' ) );
				add_action( 'manage_' . $key . '_posts_custom_column', array( $this, 'post_columns_content' ), 10, 2 );
			}
		}
	}

	/**
	 * Column headings for list table.
	 *
	 * @since 3.2.0
	 *
	 * @param  array $columns
	 * @return array $columns
	 */
	function columns_heading( $columns ) {
		unset( $columns['date'] );
		$columns['slug']         = __( 'Slug', 'wp-members' );
		$columns['role']         = __( 'Role', 'wp-members' );
		$columns['expires']      = __( 'Expires', 'wp-members' );
		$columns['last_updated'] = __( 'Last updated', 'wp-members' );
		return $columns;
	}

	/**
	 * Column content for list table.
	 *
	 * @since 3.2.0
	 *
	 * @param  string $column_name
	 * @param  int    $post_id
	 */
	function columns_content( $column, $post_id ) {
		$post = get_post( $post_id );
		switch ( $column ) {
			case 'slug':
				echo $post->post_name;
				break;
			case 'role':
				$role = $this->get_meta( 'wpmem_product_role' );
				echo ( $role ) ? $role : __( 'No role required', 'wp-members' );
				break;
			case 'expires':
				$expires = $this->get_meta( 'wpmem_product_expires' );
				$period = ( false !== $expires ) ? explode( "|", $expires[0] ) : __( 'Does not expire', 'wp-members' );
				echo ( is_array( $period ) ) ? $period[0] . ' ' . $period[1] : $period;
				break;
			case 'last_updated':
				echo date_i18n( get_option( 'date_format' ), strtotime( $post->post_modified ) );
				break;
		}
	}

	/**
	 * Gets value of requested post meta.
	 *
	 * @since 3.2.0
	 *
	 * @param  string $value
	 * @return string
	 */
	function get_meta( $value ) {
		global $post;
		$field = get_post_meta( $post->ID, $value, true );
		if ( ! empty( $field ) ) {
			return is_array( $field ) ? stripslashes_deep( $field ) : stripslashes( wp_kses_decode_entities( $field ) );
		} else {
			return false;
		}
	}

	/**
	 * Handles meta boxes for CPT editor.
	 *
	 * @since 3.2.0
	 */
	function meta_boxes() {
		remove_meta_box( 'slugdiv', 'wpmem_product', 'normal' );
		add_meta_box(
			'membership_product',
			__( 'Membership Product Details', 'wp-members' ),
			array( $this, 'details_html' ),
			'wpmem_product',
			'normal',
			'high'
		);
	}

	/**
	 * Outputs HTML for CPT editor.
	 *
	 * @since 3.2.0
	 *
	 * @param  object $post
	 */
	function details_html( $post ) {
		
		$product_expires = $this->get_meta( 'wpmem_product_expires' );
		$product_role    = $this->get_meta( 'wpmem_product_role'    );
	
		$product_expires = ( false !== $product_expires ) ? $product_expires[0] : $product_expires;
		
		$periods = array( __( 'Period', 'wp-members' ) . '|', __( 'Day', 'wp-members' ) . '|day', __( 'Week', 'wp-members' ) . '|week', __( 'Month', 'wp-members' ) . '|month', __( 'Year', 'wp-members' ) . '|year' ); 
		$show_role_detail = ( false !== $product_role    ) ? 'show' : 'hide';
		$show_exp_detail  = ( false !== $product_expires ) ? 'show' : 'hide'; ?>

			<?php wp_nonce_field( '_wpmem_product_nonce', 'wpmem_product_nonce' ); ?>
			<p>
				<label for="wpmem_product_name"><?php _e( 'Name (slug)', 'wp-members' ); ?></label>
				<input type="text" name="wpmem_product_name" id="wpmem_product_name" value="<?php echo esc_attr( $post->post_name ); ?>">
			</p>
			<p>
				<input type="checkbox" name="wpmem_product_role_required" id="wpmem_product_role_required" value="role-required" <?php echo ( false !== $product_role ) ? 'checked' : ''; ?>>
				<label for="wpmem_product_role_required"><?php _e( 'Role Required?', 'wp-members' ); ?></label>
				<label for="wpmem_product_role"></label>
				<select name="wpmem_product_role" id="wpmem_product_role">
					<option value=""><?php _e( 'No Role', 'wp-members' ); ?></option>
					<?php wp_dropdown_roles( $this->get_meta( 'wpmem_product_role' ) ); ?>
				</select>
			</p>
			<p>
				<input type="checkbox" name="wpmem_product_expires" id="wpmem_product_expires" value="expires" <?php echo ( false !== $product_expires ) ? 'checked' : ''; ?>>
				<label for="wpmem_product_expires"><?php _e( 'Expires', 'wp-members' ); ?></label>
				<span id="wpmem_product_expires_wrap">
					<label for="wpmem_product_number_of_periods" style="display:none;"><?php _e( 'Number', 'wp-members' ); ?></label>
					<?php $period = explode( '|', $product_expires ); ?>
					<input type="text" name="wpmem_product_number_of_periods" id="wpmem_product_number_of_periods" value="<?php echo $period[0]; ?>" class="small-text" placeholder="<?php _e( 'Number', 'membership_product' ); ?>" style="width:66px;height:28px;vertical-align:middle;">
					<label for="wpmem_product_time_period" style="display:none;"><?php _e( 'Period', 'wp-members' ); ?></label>
					<?php echo wpmem_form_field( array( 'name'=>'wpmem_product_time_period', 'type'=>'select', 'value'=>$periods, 'compare'=>( ( isset( $period[1] ) ) ? $period[1] : '' ) ) ); ?>
				</span>
			</p>
		<script>
			(function($) {
				$(document).ready(function() {
					$("#wpmem_product_role").<?php echo ( $show_role_detail ); ?>();
					$("#wpmem_product_expires_wrap").<?php echo ( $show_exp_detail ); ?>();
				});
				$(document).ready(function() {
				  $('#wpmem_product_role_required').on('change', function (){
					if ($(this).is(':checked')) {
						$('#wpmem_product_role').show();
						} else {
						$('#wpmem_product_role').hide();
						}
				  });
				 $('#wpmem_product_expires').on('change', function (){
					if ($(this).is(':checked')) {
						$('#wpmem_product_expires_wrap').show();
						} else {
						$('#wpmem_product_expires_wrap').hide();
						}
				  });
				});
			})(jQuery);
		</script><?php
	}

	/**
	 * Saves meta fields for CPT
	 *
	 * @since 3.2.0
	 *
	 * @param  int $post_id
	 */
	function save_details( $post_id ) {
		
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
		if ( ! isset( $_POST['wpmem_product_nonce'] ) || ! wp_verify_nonce( $_POST['wpmem_product_nonce'], '_wpmem_product_nonce' ) ) return;
		if ( ! current_user_can( 'edit_post', $post_id ) ) return;
		
		$post = get_post( $post_id );

		$product_name = wpmem_get( 'wpmem_product_name', false );
		$product_name = ( $product_name ) ? $product_name : $post->post_name;
		update_post_meta( $post_id, 'wpmem_product_name', esc_attr( $product_name ) );
		
		$role_required = wpmem_get( 'wpmem_product_role_required', false );
		if ( ! $role_required ) {
			update_post_meta( $post_id, 'wpmem_product_role', false );
		} else {
			update_post_meta( $post_id, 'wpmem_product_role', wpmem_get( 'wpmem_product_role' ) );
		}
		
		$expires = wpmem_get( 'wpmem_product_expires', false );
		if ( ! $expires ) {
			update_post_meta( $post_id, 'wpmem_product_expires', false );
		} else {
			$expires_array = array( wpmem_get( 'wpmem_product_number_of_periods' ) . "|" . wpmem_get( 'wpmem_product_time_period' ) );
			update_post_meta( $post_id, 'wpmem_product_expires', $expires_array );
		}
	}

	/**
	 * Add dropdown to post and page meta box for marking access by product..
	 *
	 * @since 3.2.0
	 *
	 * @global object $wpmem
	 * @param  object $post
	 * @param  string $block
	 */
	function add_product_to_post( $post, $block ) {
		global $wpmem;
		$product  = $wpmem->membership->get_post_products( $post->ID ); //get_post_meta( $post->ID, $wpmem->membership->post_meta, true );
		$product  = ( $product ) ? $product : array();
		$values[] = __( 'None', 'wp-members' ) . '|';
		foreach ( $wpmem->membership->products as $key => $value ) {
			$values[] = $value['title'] . '|' . $key;
		}
		echo wpmem_form_label( array( 
			'meta_key'=>$wpmem->membership->post_meta,
			'label'=>__( 'Limit access to:', 'wp-members' ),
			'type'=> 'multiselect'
		) );
		echo "<br />";
		echo wpmem_form_field( array( 
			'name' => $wpmem->membership->post_meta, 
			'type' => 'multiselect',
			'value' => $values,
			'compare' => $product,
			'class' => 'wpmem-product-select2',
		) );
	}

	/**
	 * Save custom post meta for access by product.
	 *
	 * @since 3.2.0
	 *
	 * @global object $wpmem
	 * @param  object $post
	 */
	function save_product_to_post( $post ) {
		global $wpmem;
		$products = wpmem_get( $wpmem->membership->post_meta );
		$products = ( $products ) ? $products : array();
		if ( empty( $products ) || ( 1 == count( $products ) && '' == $products[0] ) ) {
			delete_post_meta( $post->ID, $wpmem->membership->post_meta );
		} else {
			update_post_meta( $post->ID, $wpmem->membership->post_meta, $products );
		}
		foreach ( $wpmem->membership->products as $key => $value ) {
			if ( in_array( $key, $products ) ) {
				update_post_meta( $post->ID, $wpmem->membership->post_stem . $key, 1 );
			} else {
				delete_post_meta( $post->ID, $wpmem->membership->post_stem . $key );
			}
		}
	}
	
	/**
	 * Enqueue select2 JS.
	 *
	 * @since 3.2.3
	 */
	function enqueue_select2() { 
		$screen = get_current_screen();
		if ( $screen->base == 'post' && $screen->parent_base == 'edit' ) { ?>
			<script language="javascript">
				(function($) {
					$(document).ready(function() {
						$('.wpmem-product-select2').select2();
					});
				})(jQuery);
			</script><?php
		}
	}
	
	/**
	 * Add membership product column to post table.
	 *
	 * @since 3.2.4
	 *
	 * @global object $wpmem
	 * @param  array  $columns
	 * @return array  $columns
	 */
	function post_columns( $columns ){
		global $wpmem;
		$post_type = ( isset( $_REQUEST['post_type'] ) ) ? sanitize_text_field( $_REQUEST['post_type'] ) : 'post';
		if ( $post_type == 'page' || $post_type == 'post' || array_key_exists( $post_type, $wpmem->post_types ) ) {
			$product = array( 'wpmem_product' => __( 'Required Membership', 'wp-members' ) );
			$columns = wpmem_array_insert( $columns, $product, 'wpmem_block', 'before' );
		}
		return $columns;	
	}
	
	/**
	 * Membership product column data.
	 *
	 * @since 3.2.4
	 *
	 * @global object $wpmem
	 * @param  string $column_name
	 * @param  int    $post_id
	 */
	function post_columns_content( $column_name, $post_id ) {
		if ( 'wpmem_product' == $column_name ) {
			global $wpmem;
			$post_products = $wpmem->membership->get_post_products( $post_id );
			if ( $post_products ) {
				foreach ( $post_products as $meta ) {
					if ( isset( $wpmem->membership->products[ $meta ]['title'] ) ) {
						$display[] = $wpmem->membership->products[ $meta ]['title'];
					}
				}
				echo implode( ", ", $display );
			}
		}
	}
	
	/**
	 * Add membership product column to post table.
	 *
	 * @since 3.2.4
	 *
	 * @param  array $columns
	 * @return array $columns
	 */
	function user_columns( $columns ){
		$columns['wpmem_product'] = __( 'Membership', 'wp-members' );
		return $columns;	
	}
	
	/**
	 * Membership product column data.
	 *
	 * @since 3.2.4
	 *
	 * @global object $wpmem
	 * @param  string $column_name
	 * @param  int    $post_id
	 * @return array  $display
	 */
	function user_columns_content( $val, $column_name, $user_id ) {
		if ( 'wpmem_product' == $column_name ) {
			global $wpmem;
			$display = array();
			$user_products = $wpmem->user->get_user_products( $user_id );
			if ( $user_products ) {
				foreach ( $user_products as $meta => $value ) {
					if ( isset( $wpmem->membership->products[ $meta ]['title'] ) ) {
						$display[] = $wpmem->membership->products[ $meta ]['title'];
					}
				}
			}
			return implode( ", ", $display );
		}
		return $val;
	}
}