<?php
/**
 * The WP_Members Menus Class.
 *
 * Allows for handling of menu items based on user access. Limit menu itmes
 * to logged in only, logged out only, or based on membership access.
 *
 * Modified from Nav Menu Roles by Kathy Darling (https://www.kathyisawesome.com/)
 * https://wordpress.org/plugins/nav-menu-roles/
 *
 * @package WP-Members
 * @subpackage WP_Members Menus Object Class
 * @since 3.3.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

class WP_Members_Menus {

	/**
	 * Meta for storing item settings.
	 *
	 * @since  3.3.0
	 * @access public
	 * @var    string
	 */
	public $post_meta   = "_wpmem_item_settings";

	/**
	 * Nonce name
	 *
	 * @since  3.3.0
	 * @access public
	 * @var    string
	 */
	public $nonce_name  = "wpmem_nav_menu_nonce";

	/**
	 * Nonce field
	 *
	 * @since  3.3.0
	 * @access public
	 * @var    string
	 */
	public $nonce_field = "wpmem_nav_menu_nonce";
	
	/**
	 * Initialize WP_Members_Menus.
	 * 
	 * @since  1.3.0
	 */
	public function __construct(){
		$this->load_hooks();
	}

	/**
	 * Loads hooks.
	 *
	 * @since 3.3.0
	 */
	public function load_hooks() {
		add_filter( 'wp_edit_nav_menu_walker', array( $this, 'edit_nav_menu_walker' ) );
		add_action( 'wp_update_nav_menu_item', array( $this, 'update_nav_menu_item' ), 10, 2 );
		add_filter( 'wp_setup_nav_menu_item',  array( $this, 'setup_nav_menu_item'  ) );
		add_action( 'admin_enqueue_scripts' ,  array( $this, 'enqueue_scripts'      ) );
		
		add_action( 'wp_nav_menu_item_custom_fields',    array( $this, 'nav_menu_item_fields' ), 5, 4 );
		add_action( 'wpmem_nav_menu_logged_in_criteria', array( $this, 'add_product_criteria' ) );
		
		// Handles removing front end menu items.
		if ( ! is_admin() ) {
			add_filter( 'wp_get_nav_menu_items', array( $this, 'exclude_menu_items' ), 20 );
		}
	}
	/**
	* Override the Admin Menu Walker
	* 
	* @since 3.3.0
	*
	* @global  object  $wpmem
	*
	* @return  object  WP_Members_Walker_Nav_Menu
	*/
	public function edit_nav_menu_walker( $walker ) {
		global $wpmem;
		require_once( $wpmem->path . 'includes/walkers/class-wp-members-walker-nav-menu.php' );
		return 'WP_Members_Walker_Nav_Menu';
	}

	/**
	* Add fields to hook added in Walker
	* 
	* @since 3.3.0
	*
	* @global  object  $wpmem
	* 
	* @param   string  $item_id
	* @param   object  $item
	* @param           $depth
	* @param   array   $args
	*/
	public function nav_menu_item_fields( $item_id, $item, $depth, $args ) {
		
		global $wpmem;

		$restrictions = get_post_meta( $item->ID, $this->post_meta, true );

		$logged_in_out = '';
		if ( is_array( $restrictions ) || $restrictions == 'in' ) {
			$logged_in_out = 'in';
		} else if ( $restrictions == 'out' ){
			$logged_in_out = 'out';
		}

		$hidden = $logged_in_out == 'in' ? '' : 'display: none;';
		?>
		<input type="hidden" name="<?php echo $this->nonce_name; ?>" value="<?php echo wp_create_nonce( $this->nonce_field ); ?>" />

		<div class="field-wpmem_nav_menu wpmem_logged_in_out_field description-wide" style="margin: 5px 0;">
		    <span class="description"><?php _e( "Display", 'wp-members' ); ?></span>
		    <br />

		    <input type="hidden" class="nav-menu-id" value="<?php echo $item->ID ;?>" />

		    <div class="wpmem-logged-in" style="float: left; width: 35%;">
		        <input type="radio" class="wpmem-logged-in-out" name="wpmem_logged_in_out[<?php echo $item->ID ;?>]" id="wpmem_logged_in-for-<?php echo $item->ID ;?>" <?php checked( 'in', $logged_in_out ); ?> value="in" />
		        <label for="wpmem_logged_in-for-<?php echo $item->ID ;?>">
		            <?php _e( 'Logged In Users', 'wp-members'); ?>
		        </label>
		    </div>

		    <div class="wpmem-logged-in" style="float: left; width: 35%;">
		        <input type="radio" class="wpmem-logged-in-out" name="wpmem_logged_in_out[<?php echo $item->ID ;?>]" id="wpmem_logged_out-for-<?php echo $item->ID ;?>" <?php checked( 'out', $logged_in_out ); ?> value="out" />
		        <label for="wpmem_logged_out-for-<?php echo $item->ID ;?>">
		            <?php _e( 'Logged Out Users', 'wp-members'); ?>
		        </label>
		    </div>

		    <div class="wpmem-logged-in" style="float: left; width: 30%;">
		        <input type="radio" class="wpmem-logged-in-out" name="wpmem_logged_in_out[<?php echo $item->ID ;?>]" id="wpmem_all_users-for-<?php echo $item->ID ;?>" <?php checked( '', $logged_in_out ); ?> value="" />
		        <label for="wpmem_all_users-for-<?php echo $item->ID ;?>">
		            <?php _e( 'All Users', 'wp-members'); ?>
		        </label>
		    </div>

		</div>

		<?php if ( 1 == $wpmem->enable_products ) { 
			$display_products = $wpmem->membership->products;
			$checked_products = ( isset( $restrictions['products'] ) && is_array( $restrictions['products'] ) ) ? $restrictions['products'] : false;
			?>
			<div class="field-wpmem_nav_menu wpmem_nav_menu_field description-wide" style="margin: 5px 0; <?php echo $hidden;?>">
			<?php if ( empty( $display_products ) ) { 
				$add_product_url = esc_url( admin_url( 'post-new.php?post_type=wpmem_product' ) );	
			?>
			<span class="description"><?php echo sprintf( esc_html__( "%sAdd membership products%s to restrict menu to a membership", 'wp-members' ), '<a href="' . $add_product_url . '">', '</a>' ); ?></span>	
			<?php } else { ?>
			<span class="description"><?php echo esc_html__( "Restrict menu item to a membership product", 'wp-members' ); ?></span>
			<br />
			<?php

			$i = 1;

			/* Loop through each of the available roles. */
			foreach ( $display_products as $key => $product ) {

				/* If the role has been selected, make sure it's checked. */
				$checked = checked( true, ( is_array( $checked_products ) && in_array( $key, $checked_products ) ), false );
				?>

				<div class="wpmem-product-input" style="float: left; width: 33.3%; margin: 2px 0;">
				<input type="checkbox" name="wpmem_product[<?php echo $item->ID ;?>][<?php echo $i; ?>]" id="wpmem_product-<?php echo $key; ?>-for-<?php echo $item->ID ;?>" <?php echo $checked; ?> value="<?php echo $key; ?>" />
				<label for="wpmem_product-<?php echo $key; ?>-for-<?php echo $item->ID ;?>">
				<?php echo esc_html( $product['title'] ); ?>
				<?php $i++; ?>
				</label>
				</div>

			<?php } 
			} ?>
			</div>
		<?php } ?>
		<?php
	}

	/**
	* Enqueues javascript.
	* 
	* @since 3.3.0
	*
	* @global object $wpmem
	*/
	public function enqueue_scripts( $hook ) {
		global $wpmem;
		if ( $hook == 'nav-menus.php' ){
			wp_enqueue_script( 'wp-members-menus', $wpmem->url . 'assets/js/wpmem-nav-menu' . wpmem_get_suffix() . '.js', array( 'jquery' ), $wpmem->version, true );
		}
	}

	/**
	* Save custom menu fields.
	* 
	* @since 3.3.0
	*
	* @global object $wpmem
	*/
	public function update_nav_menu_item( $menu_id, $menu_item_db_id ) {
		global $wpmem;
		$product_names = array();
		foreach ( $wpmem->membership->products as $key => $value ) {
			$product_names[ $key ] = $value['title'];
		}

		// Verify this came from our screen and with proper authorization.
		if ( ! isset( $_POST[ $this->nonce_name ] ) || ! wp_verify_nonce( $_POST[ $this->nonce_name ], $this->nonce_field ) ){
			return;
		}

		$saved_data = false;

		if ( isset( $_POST['wpmem_logged_in_out'][ $menu_item_db_id ] ) && 'in' == $_POST['wpmem_logged_in_out'][ $menu_item_db_id ] && isset( $_POST['wpmem_product'][ $menu_item_db_id ] ) ) {
			
			$custom_fields = array();
			
			foreach( (array) $_POST['wpmem_product'][ $menu_item_db_id ] as $product ) {

				if ( array_key_exists ( $product, $product_names ) ) {
					$custom_fields['products'][] = sanitize_text_field( $product );
				}
			}
			if ( ! empty ( $custom_fields ) ) {
				$saved_data = $custom_fields;
			}
		} elseif ( isset( $_POST['wpmem_logged_in_out'][ $menu_item_db_id ] ) && in_array( $_POST['wpmem_logged_in_out'][ $menu_item_db_id ], array( 'in', 'out' ) ) ) {
			$saved_data = sanitize_text_field( $_POST['wpmem_logged_in_out'][ $menu_item_db_id ] );
		}
 
		if ( $saved_data ) {
			update_post_meta( $menu_item_db_id, $this->post_meta, $saved_data );
		} else {
			delete_post_meta( $menu_item_db_id, $this->post_meta );
		}
	}

	/**
	* Add custom field to $item object.
	* 
	* @since 3.3.0
	*
	* @global object $wpmem
	*
	* @param  object  $menu_item
	* @return object  $menu_item
	*/
	public function setup_nav_menu_item( $menu_item ) {
		global $wpmem;
		if ( is_object( $menu_item ) && isset( $menu_item->ID ) ) {

			$restrictions = get_post_meta( $menu_item->ID, $this->post_meta, true );

			// If there are any restrictions, set them in the menu item.
			if ( ! empty( $restrictions ) ) {
				$menu_item->restrictions = $restrictions;
			}
		}
		return $menu_item;
	}

	/**
	 * Exclude menu items based on custom criteria.
	 *
	 * @since 3.3.0
	 *
	 * @global object $wpmem
	 *
	 * @param  array  $items
	 * @return array  $items
	 */
	public function exclude_menu_items( $items ) {
		global $wpmem;
		$hide_children_of = array();

		if ( 1 == $wpmem->enable_products && ! empty( $items ) ) {

			// Iterate and remove set items.
			foreach ( $items as $key => $item ) {

				$visible = true;

				// Hide any item that is the child of a hidden item.
				if ( isset( $item->menu_item_parent ) && in_array( $item->menu_item_parent, $hide_children_of ) ) {
					$visible = false;
				}

				// Check items that have products.
				if ( $visible && isset( $item->restrictions ) ) {

					// Check all logged in, all logged out, or role.
					switch( $item->restrictions ) {
						case 'in' :
							$visible = ( is_user_logged_in() ) ? true : false;
							break;
						case 'out' :
							$visible = ( ! is_user_logged_in() ) ? true : false;
							break;
						default:
							$visible = false;
							if ( is_array( $item->restrictions ) && ! empty( $item->restrictions ) ) {
								foreach ( $item->restrictions['products'] as $product ) {
									if ( wpmem_user_has_access( $product ) ) {
										$visible = true;
									}
								}
							}
							break;
					}
				}

				/**
				 * Filters item visibility value.
				 *
				 * @since 3.3.0
				 *
				 * @param  bool    $visible
				 * @param  object  $item
				 */ 
				$visible = apply_filters( 'wpmem_menu_item_visibility', $visible, $item );

				// Unset non-visible item.
				if ( ! $visible ) {
					if ( isset( $item->ID ) ) {
						$hide_children_of[] = $item->ID;
					}
					unset( $items[ $key ] ) ;
				}

			}

		}

		return $items;
	}
	
}