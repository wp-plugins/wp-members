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
			
			add_filter( 'wpmem_user_profile_tabs',         array( $this, 'user_profile_tabs' ), 1 );
			add_action( 'wpmem_user_profile_tabs_content', array( $this, 'user_profile_tab_content' ), 10 );
		}
		
		$this->default_products = $wpmem->membership->get_default_products();
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
		if ( $this->default_products ) {
			$columns['default_products'] = __( 'Default', 'wp-members' );
		}
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
				$role_slug = $this->get_meta( 'wpmem_product_role' );
				if ( $role_slug ) {
					$wp_roles  = new WP_Roles;
					$names     = $wp_roles->get_names();

					$role      = $names[ $role_slug ] . ' (' . __( 'slug:', 'wp-members' ) . ' ' . $role_slug . ')';
					echo esc_html( $role );
				} else {
					__( 'No role required', 'wp-members' );
				}
				break;
			case 'expires':
				$expires = $this->get_meta( 'wpmem_product_expires' );
				$period = ( false !== $expires ) ? explode( "|", $expires[0] ) : __( 'Does not expire', 'wp-members' );
				echo ( is_array( $period ) ) ? esc_html( $period[0] . ' ' . $period[1] ) : esc_html( $period );
				break;
			case 'default_products':
				echo ( in_array( $post->post_name, $this->default_products ) ) ? __( 'Yes', 'wp-members' ) : '';
				break;
			case 'last_updated':
				echo date_i18n( get_option( 'date_format' ), strtotime( esc_attr( $post->post_modified ) ) );
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
	 * @since 3.3.4 Added message meta box.
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
		add_meta_box(
			'membership_product_message',
			__( 'Membership Product Message (optional)', 'wp-members' ),
			array( $this, 'message_meta_box_detail' ),
			'wpmem_product'
		);
	}

	/**
	 * Gets an array of post types.
	 *
	 * @since 3.3.3
	 *
	 * @return array $post_types
	 */
	function get_post_types() {
		global $wpmem;
		// @todo This comes from option tab. Should consider it being an api function.
		$post_arr = array(
			'post' => __( 'Posts' ),
			'page' => __( 'Pages' ),
		);
		if ( ! empty( $wpmem->post_types ) ) {
			foreach ( $wpmem->post_types as $key => $post_type ) {
				$post_arr[ $key ] = $post_type;
			}
		}
		return $post_arr;
	}
	
	/**
	 * Outputs HTML for CPT editor.
	 *
	 * @since 3.2.0
	 *
	 * @param  object $post
	 */
	function details_html( $post ) {
		
		$product_default = $this->get_meta( 'wpmem_product_default' );
		$product_expires = $this->get_meta( 'wpmem_product_expires' );
		$product_role    = $this->get_meta( 'wpmem_product_role'    );
		$product_no_gap  = $this->get_meta( 'wpmem_product_no_gap'  );
		$product_fixed_period = $this->get_meta( 'wpmem_product_fixed_period' );

		$product_expires = ( false !== $product_expires ) ? $product_expires[0] : $product_expires;
		
		$periods = array( __( 'Period', 'wp-members' ) . '|', __( 'Day', 'wp-members' ) . '|day', __( 'Week', 'wp-members' ) . '|week', __( 'Month', 'wp-members' ) . '|month', __( 'Year', 'wp-members' ) . '|year' ); 
		$show_role_detail = ( false !== $product_role    ) ? 'show' : 'hide';
		$show_exp_detail  = ( false !== $product_expires ) ? 'show' : 'hide';
		$show_exp_fixed   = ( false !== $product_fixed_period ) ? 'show' : 'hide';

		/**
		 * Fires before membership product detail meta box.
		 *
		 * @since 3.3.5
		 */
		do_action( 'wpmem_membership_product_meta_before' ); ?>

			<?php wp_nonce_field( '_wpmem_product_nonce', 'wpmem_product_nonce' ); ?>
			<h3><?php _e( 'Name (slug)', 'wp-members' ); ?></h3>
			<p><strong><?php echo esc_attr( $post->post_name ); ?></strong></p>
			<h3><?php esc_html_e( 'Optional Defaults', 'wp-members' ); ?></h3>
			<p>
				<input type="checkbox" name="wpmem_product_default" id="wpmem_product_default" value="1" <?php echo ( 1 == $product_default  ) ? 'checked' : ''; ?> />
				<label for="wpmem_product_default"><?php _e( 'Assign as default at registration? (optional)', 'wp-members' ); ?></label>
			</p>
			<p>
			<?php
		
			foreach( $this->get_post_types() as $key => $post_type ) {
				echo '<div>';
				echo '<input type="checkbox" name="wpmem_product_set_default_' . $key . '" id="wpmem_product_set_default_' . $key . '" value ="1" '; echo ( 1 == $this->get_meta( "wpmem_product_set_default_" . $key ) ) ? 'checked' : ''; echo ' />';
				echo '<label for="wpmem_product_set_default_' . $key . '">' . sprintf( esc_html__( 'Pre-selected by default for new %s', 'wp-members' ), $post_type ) . '</label>';
				echo '</div>';
			}
		
			?>
			</p>
			<h3><?php esc_html_e( 'Optional Properties', 'wp-members' ); ?></h3>
			<p>
				<input type="checkbox" name="wpmem_product_role_required" id="wpmem_product_role_required" value="role-required" <?php echo ( false !== $product_role ) ? 'checked' : ''; ?> />
				<label for="wpmem_product_role_required"><?php _e( 'Role Required? (optional)', 'wp-members' ); ?></label>
				<label for="wpmem_product_role"></label>
				<select name="wpmem_product_role" id="wpmem_product_role">
					<option value=""><?php _e( 'No Role', 'wp-members' ); ?></option>
					<?php wp_dropdown_roles( $this->get_meta( 'wpmem_product_role' ) ); ?>
				</select>
			</p>
			<p>
				<input type="checkbox" name="wpmem_product_expires" id="wpmem_product_expires" value="expires" <?php echo ( false !== $product_expires ) ? 'checked' : ''; ?> />
				<label for="wpmem_product_expires"><?php _e( 'Expires (optional)', 'wp-members' ); ?></label>
				<span id="wpmem_product_expires_wrap">
					<label for="wpmem_product_number_of_periods" style="display:none;"><?php _e( 'Number', 'wp-members' ); ?></label>
					<?php $period = explode( '|', $product_expires ); ?>
					<input type="text" name="wpmem_product_number_of_periods" id="wpmem_product_number_of_periods" value="<?php echo esc_attr( $period[0] ); ?>" class="small-text" placeholder="<?php _e( 'Number', 'wp-members' ); ?>" style="width:66px;height:28px;vertical-align:middle;">
					<label for="wpmem_product_time_period" style="display:none;"><?php _e( 'Period', 'wp-members' ); ?></label>
					<?php echo wpmem_form_field( array( 'name'=>'wpmem_product_time_period', 'type'=>'select', 'value'=>$periods, 'compare'=>( ( isset( $period[1] ) ) ? $period[1] : '' ) ) ); ?>
					<br />

						<?php echo wpmem_form_field( array( 'name'=>'wpmem_product_no_gap', 'type'=>'checkbox', 'value'=>'1', 'compare'=>( ( isset( $product_no_gap ) ) && 1 == $product_no_gap ) ? $product_no_gap : '' ) ); ?>
						<label for="wpmem_product_no_gap"><?php esc_html_e( 'Use "no gap" renewal', 'wp-members' ); ?></label>

					<br />
						<?php echo wpmem_form_field( array( 'name'=>'wpmem_product_fixed_period', 'type'=>'checkbox', 'value'=>'1', 'compare'=>( false != $product_fixed_period ) ? 1 : '' ) ); ?>
						<label for="wpmem_product_fixed_period"><?php esc_html_e( 'Use a fixed period (such as Jan 1 - Dec 31, or Sept 1 - Aug 31)', 'wp-members' ); ?></label>
					<br />
					<div id="wpmem_product_fixed_period_select">
						<style>.ui-datepicker-year {
						  display: none;
						}</style>
						<?php
						if ( false !== $product_fixed_period ) {
							$period_parts = ( isset( $product_fixed_period ) ) ? explode( "-", $product_fixed_period ) : false;
							$period_start = ( $period_parts ) ? $period_parts[0] . '-' . $period_parts[1] : '';
							$period_end   = ( $period_parts ) ? $period_parts[2] . '-' . $period_parts[3] : '';
							$period_grace_num = ( $period_parts && isset( $period_parts[4] ) ) ? $period_parts[4] : '';
							$period_grace_per = ( $period_parts && isset( $period_parts[5] ) ) ? $period_parts[5] : '';
						} else {
							$period_start = $period_end = $period_grace_num = $period_grace_per = '';
						}
						?>
						<label for="wpmem_product_fixed_period_start"><?php esc_html_e( 'Period Start (dd-mm)', 'wp-members' ); ?></label><br />
						<input type="text" class="datepicker" name="wpmem_product_fixed_period_start" value="<?php echo $period_start; ?>" placeholder="<?php esc_html_e( 'Period Start (dd-mm)', 'wp-members' ); ?>" /><br />
						<label for="wpmem_product_fixed_period_end"><?php esc_html_e( 'Period End (dd-mm)', 'wp-members' ); ?></label><br />
						<input type="text" class="datepicker" name="wpmem_product_fixed_period_end" value="<?php echo $period_end; ?>" placeholder="<?php esc_html_e( 'Period End (dd-mm)', 'wp-members' ); ?>" />
						<script>
						jQuery(function() {
							jQuery( ".datepicker" ).datepicker({
								minDate: new Date((new Date()).getFullYear(), 0, 1),
								maxDate: new Date((new Date()).getFullYear(), 11, 31),
								hideIfNoPrevNext: true,
								dateFormat: 'dd-mm'
							});
						});
						</script>
						<br /><br />
						<span id="fixed_period_grace_period">
							<label style="margin-left: 24px;"><?php esc_html_e( "Fixed period grace period", 'wp-members' ); ?></label>

							<label for="wpmem_product_fixed_period_grace_number" style="display:none;"><?php esc_html_e( 'Number', 'wp-members' ); ?></label>
							<?php $period = explode( '|', $product_expires ); ?>
							<input type="text" name="wpmem_product_fixed_period_grace_number" id="wpmem_product_fixed_period_grace_number" value="<?php echo esc_attr( $period_grace_num ); ?>" class="small-text" placeholder="<?php _e( 'Number', 'wp-members' ); ?>" style="width:66px;margin-left:3px;margin-top:0px;">
							<label for="wpmem_product_fixed_period_grace_period" style="display:none;"><?php _e( 'Period', 'wp-members' ); ?></label>
							<?php echo wpmem_form_field( array( 'name'=>'wpmem_product_fixed_period_grace_period', 'type'=>'select', 'value'=>$periods, 'compare'=>( ( isset( $period_grace_per ) ) ? $period_grace_per : '' ) ) ); ?>
						</span>
						<br /><br />
						<span id="wpmem_product_fixed_period_explanation" class="description" style="margin-left: 24px;"><?php esc_html_e( "Grace period is the point at which expiration date is for following time period. For example, if user who register August 1st would be part of the following year's Sept 1 - Aug 31 membership, set this at 1 Month. Leave blank for no grace period.", 'wp-members' ); ?></span>
						<br />
					</div>
				</span>
			</p>
		<script>
			(function($) {
				$(document).ready(function() {
					$("#wpmem_product_role").<?php echo ( $show_role_detail ); ?>();
					$("#wpmem_product_expires_wrap").<?php echo ( $show_exp_detail ); ?>();
					$("#wpmem_product_fixed_period_select").<?php echo ( $show_exp_fixed ); ?>();
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
				 $('#wpmem_product_fixed_period').on('change', function (){
					if ($(this).is(':checked')) {
						$('#wpmem_product_fixed_period_select').show();
						} else {
						$('#wpmem_product_fixed_period_select').hide();
						}
				  });
				});
			})(jQuery);
		</script><?php
		
		/**
		 * Fires after membership product detail meta box.
		 *
		 * @since 3.3.5
		 */
		do_action( 'wpmem_membership_product_meta_after' );
	}

	/**
	 * Outputs HTML for CPT editor.
	 *
	 * @since 3.3.4
	 *
	 * @param  object $post
	 */
	function message_meta_box_detail( $post ) {
		$product_message = get_post_meta( $post->ID, 'wpmem_product_message', true );
		$message = ( $product_message ) ? $product_message : '';
		/**
		 * Fires before membership product message meta box.
		 *
		 * @since 3.3.5
		 */
		do_action( 'wpmem_membership_product_message_meta_before' );
		echo '<label for="product_message">' . __( 'Restricted Message (displays when a user does not have access to a membership)', 'wp-members' ) . '</label>';
		echo '<textarea name="product_message" id="product_message" rows="3" cols="50" id="" class="large-text code">' . $message . '</textarea>';
		/**
		 * Fires after membership product messate meta box.
		 *
		 * @since 3.3.5
		 */
		do_action( 'wpmem_membership_product_message_meta_after' );
	}
	
	/**
	 * Saves meta fields for CPT
	 *
	 * @since 3.2.0
	 * @since 3.3.4 Added message meta.
	 *
	 * @param  int $post_id
	 */
	function save_details( $post_id ) {
		
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
		if ( ! isset( $_POST['wpmem_product_nonce'] ) || ! wp_verify_nonce( $_POST['wpmem_product_nonce'], '_wpmem_product_nonce' ) ) return;
		if ( ! current_user_can( 'edit_posts', $post_id ) ) return;
		
		$post = get_post( $post_id );
		
		$product_name = wpmem_get( 'wpmem_product_name', false );
		$product_name = ( $product_name ) ? $product_name : $post->post_name;
		update_post_meta( $post_id, 'wpmem_product_name', sanitize_text_field( $product_name ) );
		
		$product_default = wpmem_get( 'wpmem_product_default', false );
		update_post_meta( $post_id, 'wpmem_product_default', ( ( $product_default ) ? true : false ) );
		
		$role_required = wpmem_get( 'wpmem_product_role_required', false );
		if ( ! $role_required ) {
			update_post_meta( $post_id, 'wpmem_product_role', false );
		} else {
			update_post_meta( $post_id, 'wpmem_product_role', sanitize_text_field( wpmem_get( 'wpmem_product_role' ) ) );
		}
		
		$expires = wpmem_get( 'wpmem_product_expires', false );
		if ( ! $expires ) {
			update_post_meta( $post_id, 'wpmem_product_expires', false );
		} else {
			$number  = sanitize_text_field( wpmem_get( 'wpmem_product_number_of_periods' ) );
			$period  = sanitize_text_field( wpmem_get( 'wpmem_product_time_period' ) );
			$no_gap  = sanitize_text_field( wpmem_get( 'wpmem_product_no_gap' ) );
			$expires_array = array( $number . "|" . $period );
			update_post_meta( $post_id, 'wpmem_product_expires', $expires_array );
			if ( $no_gap ) {
				update_post_meta( $post_id, 'wpmem_product_no_gap', 1 );
			} else {
				delete_post_meta( $post_id, 'wpmem_product_no_gap' );
			}
			
			$fixed_period = sanitize_text_field( wpmem_get( 'wpmem_product_fixed_period' ) );
			if ( $fixed_period ) {
				
				// Start and end.
				$period_start = wpmem_get( 'wpmem_product_fixed_period_start' );
				$period_end   = wpmem_get( 'wpmem_product_fixed_period_end' );
				
				// Is there an entry grace period?
				$grace_number = wpmem_get( 'wpmem_product_fixed_period_grace_number', false );
				$grace_period = wpmem_get( 'wpmem_product_fixed_period_grace_period', false );
				$save_fixed_period = $period_start . '-' . $period_end;
				if ( $grace_number && $grace_period ) {
					$save_fixed_period .= '-' . $grace_number . '-' . $grace_period;
				}
				update_post_meta( $post_id, 'wpmem_product_fixed_period', $save_fixed_period );
			} else {
				delete_post_meta( $post_id, 'wpmem_product_fixed_period' );
			}
		}
		
		foreach( $this->get_post_types() as $key => $post_type ) {
			if ( false !== wpmem_get( 'wpmem_product_set_default_' . $key, false ) ) {
				update_post_meta( $post_id, 'wpmem_product_set_default_' . $key, 1 );
			} else {
				delete_post_meta( $post_id, 'wpmem_product_set_default_' . $key );
			}
		}

		$product_message = wpmem_get( 'product_message', false );
		if ( false !== $product_message ) {
			if ( '' != $product_message ) {
				update_post_meta( $post_id, 'wpmem_product_message', $product_message );
			} else {
				delete_post_meta( $post_id, 'wpmem_product_message' );
			}
		}
	}

	/**
	 * Add dropdown to post and page meta box for marking access by product..
	 *
	 * @since 3.2.0
	 * @since 3.3.3 Added defaults.
	 *
	 * @global object $pagenow
	 * @global object $wpmem
	 * @param  object $post
	 * @param  string $block
	 */
	function add_product_to_post( $post, $block ) {
		global $pagenow, $wpmem;
		
		// For checking default memberships.
		$is_new = ( 'post-new.php' == $pagenow ) ? true : false;
		
		$product  = $wpmem->membership->get_post_products( $post->ID ); //get_post_meta( $post->ID, $wpmem->membership->post_meta, true );
		$product  = ( $product ) ? $product : array();
		$values[] = __( 'None', 'wp-members' ) . '|';
		
		foreach ( $wpmem->membership->products as $key => $value ) {
			
			if ( $is_new ) {
				if ( isset( $value[ 'set_default_' . $post->post_type ] ) && 1 == $value[ 'set_default_' . $post->post_type ] ) {
					$product[] = $key;
				}
			}
			
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
	function user_columns( $columns ) {
		/**
		 * Filters the membership column name.
		 *
		 * @since 3.3.5
		 *
		 * @param string $column_name
		 */
		$columns['wpmem_product'] = apply_filters( 'wpmem_user_columns_membership_title', __( 'Membership', 'wp-members' ) );
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
						$expires = ( $user_products[ $meta ] > 1 ) ? ' expires: ' . date_i18n( get_option( 'date_format' ), $user_products[ $meta ] ) : '';
						$display[] = $wpmem->membership->products[ $meta ]['title'] . $expires;
					}
				}
			}
			/**
			 * Filter the membership column value (per user).
			 *
			 * @since 3.3.5
			 *
			 * @param string $display
			 * @param array  $display_rows
			 * @param int    $user_id
			 * @param array  $user_products
			 */
			return apply_filters( 'wpmem_user_columns_membership_value', implode( "<br />", $display ), $display, $user_id, $user_products );
		}
		return $val;
	}

	/**
	 * Creates tab for user profile.
	 *
	 * @since 
	 *
	 * @param  array $tabs
	 */
	function user_profile_tabs( $tabs ) {
		$tabs['memberships'] = array(
			'tab' => __( 'Memberships', 'wp-members' ),
		);
		return $tabs;
	}
	
	/**
	 * Add user product access to user profile.
	 *
	 * @since 3.2.0
	 *
	 * @global string $pagenow
	 * @global object $wpmem
	 * @param  string $key
	 */
	public function user_profile_tab_content( $key ) { 
		// If product enabled
		if ( 'memberships' == $key ) {
			global $pagenow, $wpmem;
			/*
			 * If an admin is editing their provile, we need their ID,
			 * otherwise, it's the user_id param from the URL. It's a 
			 * little more complicated than it sounds since you can't just
			 * check if the user is logged in, because the admin is always
			 * logged in when checking profiles.
			 */
			$user_id = ( 'profile' == $pagenow && current_user_can( 'edit_users' ) ) ? get_current_user_id() : sanitize_text_field( wpmem_get( 'user_id', false, 'get' ) );
			$user_products = $wpmem->user->get_user_products( $user_id );
			echo '<h3>' . __( 'Product Access', 'wp-members' ) . '</h3>';
			echo '<table>
				<tr>
					<th>' . __( 'Status',     'wp-members' ) . '</th>
					<th>' . __( 'Membership', 'wp-members' ) . '</th>
					<th>' . __( 'Enabled?',   'wp-members' ) . '</th>
					<th>' . __( 'Expires',    'wp-members' ) . '</th>
				</tr>'; ?>	
			<?php
			foreach ( $wpmem->membership->products as $key => $value ) {
				$checked = ( $user_products && array_key_exists( $key, $user_products ) ) ? "checked" : "";
				echo "<tr>";
				echo '<td style="padding:5px 5px;">
				<select name="_wpmem_membership_product[' . $key . ']">
					<option value="">----</option>
					<option value="enable">'  . __( 'Enable', 'wp-members'  ) . '</option>
					<option value="disable">' . __( 'Disable', 'wp-members' ) . '</option>
				</select></td><td style="padding:0px 0px;">' . $value['title'] . '</td>';

				// If user has date, display that; otherwise placeholder
				$date_value  = ( isset( $user_products[ $key ] ) && 1 != $user_products[ $key ] && 0 != $user_products[ $key ] && '' != $user_products[ $key ] ) ? date( 'Y-m-d', $user_products[ $key ] ) : "";
				$placeholder = ( ! isset( $user_products[ $key ] ) ) ? 'placeholder="' . __( 'Expiration date (optional)', 'wp-members' ) . '" ' : '';
				$product_date_field = ' <input type="text" name="_wpmem_membership_expiration_' . $key . '" value="' . $date_value . '" class="wpmem_datepicker" ' . $placeholder . ' />';

				if ( isset( $user_products[ $key ] ) ) {
					echo '<td align="center"><span id="wpmem_product_enabled" class="dashicons dashicons-yes"></span></td>';
					if ( $user_products[ $key ] != 1 ) {
						echo '<td>' . $product_date_field . '</td>';
					} else {
						echo '<td>&nbsp;</td>';
					}
				} else {
					if ( isset( $value['expires'] ) && ! empty( $value['expires'] ) ) {
						echo '<td><span id="wpmem_product_enabled" class="dashicons"></span></td>';
						echo '<td>' . $product_date_field . '</td>';
					} else {
						echo '<td>&nbsp;</td>';
					}
				}				
				echo '</tr>';
			}

				?></table>
			<script>
			jQuery(function() {
				jQuery( ".wpmem_datepicker" ).datepicker({
					dateFormat : "yy-mm-dd"
				});
			});
			</script>
			<?php
		}
	}
}