<?php
/**
 * The WP_Members Admin API Class.
 *
 * @package WP-Members
 * @subpackage WP_Members Admin API Object Class
 * @since 3.1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

class WP_Members_Admin_API {
	
	/**
	 * Container for tabs.
	 *
	 * @since 3.1.0
	 * @access public
	 * @var array
	 */
	public $tabs = array();
	
	/**
	 * Container for emails.
	 *
	 * @since 3.1.0
	 * @access public
	 * @var array
	 */
	public $emails = array();
	
	/**
	 * Container for dialogs.
	 *
	 * @since 3.1.1
	 * @access public
	 * @var array
	 */
	public $dialogs = array();

	/**
	 * Plugin initialization function.
	 *
	 * @since 3.1.0
	 */
	function __construct() {
		
		// Load dependencies.
		$this->load_dependencies();
		
		// Load admin hooks.
		$this->load_hooks();

		// The following is only needed if we are on the WP-Members settings screen.
		$is_wpmem_admin = wpmem_get( 'page', false, 'get' );
		if ( false !== $is_wpmem_admin && 'wpmem-settings' == $is_wpmem_admin ) {
			$tabs    = $this->default_tabs();    // Load default tabs.
			$emails  = $this->default_emails();  // Load default emails.
			$dialogs = $this->default_dialogs(); // Load default dialogs.
		}

		global $wpmem;
		$wpmem->membership->admin = new WP_Members_Products_Admin();
	}

	/**
	 * Load dependencies.
	 *
	 * @since 3.1.0
	 * @since 3.1.1 Added tab-about.php.
	 * @since 3.1.7 Loads all admin dependent files.
	 * @since 3.2.9 Removed tab-about.php until we can re-do it.
	 *
	 * @global object $wpmem
	 */
	function load_dependencies() {
		
		global $wpmem;
		
		include_once( $wpmem->path . 'includes/admin/admin.php' );
		include_once( $wpmem->path . 'includes/admin/class-wp-members-admin-users.php' );
		include_once( $wpmem->path . 'includes/admin/class-wp-members-admin-posts.php' );
		include_once( $wpmem->path . 'includes/admin/class-wp-members-products-admin.php' );
		include_once( $wpmem->path . 'includes/admin/class-wp-members-user-search.php' );
		include_once( $wpmem->path . 'includes/admin/dialogs.php' );
		include_once( $wpmem->path . 'includes/admin/api.php' );
		include_once( $wpmem->path . 'includes/admin/tabs/class-wp-members-admin-tab-fields.php' ); // Fields tab is used for field reorder (which is ! wpmem-settings).
		if ( 'wpmem-settings' == wpmem_get( 'page', false, 'get' ) ) {
			include_once( $wpmem->path . 'includes/admin/tabs/class-wp-members-admin-tab-options.php' );
			include_once( $wpmem->path . 'includes/admin/tabs/class-wp-members-admin-tab-emails.php'  );
			include_once( $wpmem->path . 'includes/admin/tabs/class-wp-members-admin-tab-captcha.php' );
			// include_once( $wpmem->path . 'admin/tab-about.php' );
			include_once( $wpmem->path . 'includes/admin/tabs/class-wp-members-admin-tab-dialogs.php' );
			include_once( $wpmem->path . 'includes/admin/tabs/class-wp-members-admin-tab-dropins.php' );
			
			if ( ! class_exists( 'WP_List_Table' ) ) {
				require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
			}
			include_once( $wpmem->path . 'includes/admin/tabs/class-wp-members-fields-table.php' );
		}
	}

	/**
	 * Load admin.
	 *
	 * @since 3.1.0
	 * @since 3.1.7 Loads all admin hooks.
	 *
	 * @global object $wpmem
	 */
	function load_hooks() {
		
		global $wpmem;
		
		add_action( 'admin_enqueue_scripts',          array( $this, 'dashboard_enqueue_scripts' ) );
		add_action( 'user_new_form',                  'wpmem_admin_add_new_user' );
		add_filter( 'plugin_action_links',            array( $this, 'plugin_links' ), 10, 2 );
		// add_filter( 'wpmem_admin_tabs',              'wpmem_add_about_tab'       );
		
		add_action( 'wp_ajax_wpmem_do_field_reorder',  array( 'WP_Members_Admin_Tab_Fields', 'do_field_reorder' ) );
		
		add_action( 'wpmem_admin_do_tab',             array( 'WP_Members_Admin_Tab_Options', 'do_tab' ),  1 );
		add_action( 'wpmem_admin_do_tab',             array( 'WP_Members_Admin_Tab_Fields',  'do_tab' ),  5 );
		add_action( 'wpmem_admin_do_tab',             array( 'WP_Members_Admin_Tab_Dialogs', 'do_tab' ), 10 );
		add_action( 'wpmem_admin_do_tab',             array( 'WP_Members_Admin_Tab_Emails',  'do_tab' ), 15 );
		// add_action( 'wpmem_admin_do_tab',            'wpmem_a_about_tab', 999, 1 );
		
		// If user has a role that cannot edit users, set profile actions for non-admins.
		
		// User actions and filters.
		add_action( 'user_edit_form_tag',             array( 'WP_Members_User_Profile', 'add_multipart' ) );
		add_action( 'show_user_profile',              array( 'WP_Members_User_Profile', 'profile' ) );
		add_action( 'edit_user_profile',              array( 'WP_Members_User_Profile', 'profile' ) );
		add_action( 'profile_update',                 array( 'WP_Members_User_Profile', 'update' ) );
		add_action( 'edit_user_profile',              array( 'WP_Members_User_Profile', '_profile_tabs' ), 99 );
		add_action( 'show_user_profile',              array( 'WP_Members_User_Profile', '_profile_tabs' ), 99 );

		if ( current_user_can( 'list_users' ) ) {
			add_action( 'admin_footer-users.php',     array( 'WP_Members_Admin_Users', 'bulk_user_action' ) );
			add_action( 'load-users.php',             array( 'WP_Members_Admin_Users', 'page_load' ) );
			add_action( 'admin_notices',              array( 'WP_Members_Admin_Users', 'admin_notices' ) );
			add_filter( 'views_users',                array( 'WP_Members_Admin_Users', 'views' ) );
			add_filter( 'manage_users_columns',       array( 'WP_Members_Admin_Users', 'add_user_column' ) );
			add_filter( 'manage_users_custom_column', array( 'WP_Members_Admin_Users', 'add_user_column_content' ), 10, 3 );
			add_action( 'wpmem_user_activated',       array( 'WP_Members_Admin_Users', 'set_activated_user' ) );
			add_action( 'wpmem_user_deactivated',     array( 'WP_Members_Admin_Users', 'set_deactivated_user' ) );
			add_filter( 'user_row_actions',           array( 'WP_Members_Admin_Users', 'insert_activate_link' ), 10, 2 );
			add_action( 'wpmem_admin_after_profile',  array( 'WP_Members_User_Profile', '_show_activate'   ), 7 );
			add_action( 'wpmem_admin_after_profile',  array( 'WP_Members_User_Profile', '_show_expiration' ), 8 );
			add_action( 'wpmem_admin_after_profile',  array( 'WP_Members_User_Profile', '_show_ip'         ), 9 );
			if ( 1 == $wpmem->enable_products ) {
				//add_action( 'wpmem_admin_after_profile',  array( 'WP_Members_User_Profile', '_show_product' ), 10 );
			}
		}
	
		// If user has a role that can edit posts, add the block/unblock meta boxes and custom post/page columns.
		if ( current_user_can( 'edit_posts' ) ) {
			// Post actions and filters.
			add_action( 'add_meta_boxes',             array( 'WP_Members_Admin_Posts', 'block_meta_add'  ) );
			add_action( 'save_post',                  array( 'WP_Members_Admin_Posts', 'block_meta_save' ) );
			add_filter( 'manage_posts_columns',       array( 'WP_Members_Admin_Posts', 'columns'     ) );
			add_action( 'manage_posts_custom_column', array( 'WP_Members_Admin_Posts', 'columns_content' ), 10, 2 );
			add_filter( 'manage_pages_columns',       array( 'WP_Members_Admin_Posts', 'columns'     ) );
			add_action( 'manage_pages_custom_column', array( 'WP_Members_Admin_Posts', 'columns_content' ), 10, 2 );
			add_action( 'admin_footer-edit.php',      array( 'WP_Members_Admin_Posts', 'bulk_action' ) );
			add_action( 'load-edit.php',              array( 'WP_Members_Admin_Posts', 'page_load'   ) );
			add_action( 'admin_notices',              array( 'WP_Members_Admin_Posts', 'notices'     ) );
			add_action( 'load-post.php',              array( 'WP_Members_Admin_Posts', 'load_tinymce' ) );
			add_action( 'load-post-new.php',          array( 'WP_Members_Admin_Posts', 'load_tinymce' ) );
		}
		
		if ( ! is_multisite() && current_user_can( 'manage_options' ) ) {
			add_action('wp_dashboard_setup', 'butlerblog_dashboard_widget');
		}

		add_action( 'wpmem_after_admin_init', array( 'WP_Members_Admin_Tab_Fields', 'update'          ) );
		add_action( 'admin_print_styles',     array( 'WP_Members_Admin_Tab_Fields', 'enqueue_scripts' ) );
		add_action( 'admin_footer',           array( 'WP_Members_Admin_Tab_Fields', 'bulk_actions'    ) );
		
		if ( current_user_can( 'manage_options' ) ) {
			add_action( 'admin_notices', array( $this, 'do_admin_notices' ) );
		}
	} // End of load_hooks()

	/**
	 * Add admin notices.
	 *
	 * @since 3.3.0
	 */
	function do_admin_notices() {
		global $wpmem; //echo '<pre>'; print_r( $wpmem ); exit();
		if ( $wpmem->admin_notices ) {
			foreach ( $wpmem->admin_notices as $key => $value ) {
				echo '<div class="notice notice-' . $value['type'] . ' is-dismissible"> 
					<p><strong>' . $value['notice'] . '</strong></p>
				</div>';
				
			}
		}
	}

	/**
	 * Display admin tabs.
	 *
	 * @since 3.1.0
	 *
	 * @param string $current The current tab being displayed (default: options).
	 */	
	function do_tabs( $current = 'options' ) {

		/**
		 * Filter the admin tabs for the plugin settings page.
		 *
		 * @since 2.8.0
		 *
		 * @param array $tabs An array of the tabs to be displayed on the plugin settings page.
		 */
		$this->tabs = apply_filters( 'wpmem_admin_tabs', $this->tabs );
	
		$links = array();
		foreach ( $this->tabs as $tab => $name ) {
			$link_args = array( 'page' => 'wpmem-settings', 'tab'  => $tab );
			$link = add_query_arg( $link_args, admin_url( 'options-general.php' ) );
			$class = ( $tab == $current ) ? 'nav-tab nav-tab-active' : 'nav-tab';
			$links[] = sprintf( '<a class="%s" href="%s">%s</a>', $class, $link, $name );
		}
	
		echo '<h2 class="nav-tab-wrapper">';
		foreach ( $links as $link ) {
			echo $link;
		}
		echo '</h2>';
	}

	/**
	 * Adds custom email dialog to the Emails tab.
	 *
	 * @since 3.1.0
	 *
	 * @param array $args Settings array for the email.
	 */
	function do_email_input( $args ) { ?>
        <tr valign="top"><td colspan="2"><strong><?php echo esc_html( $args['heading'] ); ?></strong></td></tr>
        <tr valign="top">
            <th scope="row"><?php echo esc_html( $args['subject_label'] ); ?></th>
            <td><input type="text" name="<?php echo esc_attr( $args['subject_input'] ); ?>" size="80" value="<?php echo esc_attr( wp_unslash( $args['subject_value'] ) ); ?>"></td> 
        </tr>
        <tr valign="top">
            <th scope="row"><?php echo esc_html( $args['body_label'] ); ?></th>
            <td><textarea name="<?php echo esc_attr( $args['body_input'] ); ?>" rows="12" cols="50" id="" class="large-text code"><?php echo esc_textarea( wp_unslash( $args['body_value'] ) ); ?></textarea></td>
        </tr>
        <tr><td colspan="2"><hr /></td></tr><?php
	}

	/**
	 * Saves custom email settings.
	 *
	 * @since 3.1.0
	 *
	 * @param array $args Settings array for the email.
	 */
	function email_update( $args ) {
		$settings = array(
			'subj' => sanitize_text_field( wpmem_get( $args['subject_input'] ) ),
			'body' => wp_kses( wpmem_get( $args['body_input'] ), 'post' ),
		);
		update_option( $args['name'], $settings, true );
		$this->emails[ $args['name'] ]['subject_value'] = $settings['subj'];
		$this->emails[ $args['name'] ]['body_value']    = $settings['body'];
		return;
	}

	/**
	 * Handles custom email settings.
	 *
	 * @since 3.1.0
	 *
	 * @param  array $args Settings array for the email.
	 * @return array $args
	 */
	function add_email( $args ) {
		
		// Get saved settings.
		$settings = get_option( $args['name'] );
		
		$defaults = array(
			'name'          => $args['name'],
			'heading'       => __( 'Custom email', 'wp-members' ),
			'subject_label' => __( 'Subject', 'wp-members' ),
			'subject_input' => $args['name'] . '_subject',
			'subject_value' => ( $settings ) ? $settings['subj'] : __( 'Subject', 'wp-members' ),
			'body_label'    => __( 'Body', 'wp-members' ),
			'body_input'    => $args['name'] . '_body',
			'body_value'    => ( $settings ) ? $settings['body'] : __( 'Your custom email message content.', 'wp-members' ),
		);
		
		// Merge args with settings.
		$args = wp_parse_args( $args, $defaults );
		
		$this->emails[ $args['name'] ] = $args;
		
		return $args;
	}

	/**
	 * Adds dialogs to the Dialogs tab.
	 *
	 * @since 3.1.1
	 *
	 * @param array $args Settings array for the dialog.
	 */
	function do_dialog_input( $args ) { ?>
        <tr valign="top"> 
            <th scope="row"><?php echo esc_html( $args['label'] ); ?></th> 
            <td><textarea name="<?php echo esc_attr( $args['name'] . "_dialog" ); ?>" rows="3" cols="50" id="" class="large-text code"><?php echo esc_textarea( wp_unslash( $args['value'] ) ); ?></textarea></td> 
        </tr><?php
	}

	/**
	 * Saves custom dialog settings.
	 *
	 * @since 3.1.1
	 */
	function dialog_update() {
		$settings = array();
		foreach ( $this->dialogs as $dialog ) {
			if ( isset( $_POST[ $dialog['name'] . '_dialog' ] ) ) {
				$settings[ $dialog['name'] ] = wp_kses( $_POST[ $dialog['name'] . '_dialog' ], 'post' );
			}
		}
		update_option( 'wpmembers_dialogs', $settings, true );
		// Refresh settings
		$this->default_dialogs();
		return;
	}	
		
	/**
	 * Handles custom dialog settings.
	 *
	 * @since 3.1.1
	 *
	 * @param  array $args Settings array for the dialog.
	 * @return array $args
	 */
	function add_dialog( $args ) {
		global $wpmem;
		if ( is_array( $args ) && isset( $args['label'] ) ) {
			$defaults = array(
				'name'  => $args['name'],
				'label' => $args['label'],
				//'input' => $args['name'] . '_dialog',
				'value' => $args['value'],
				//'value' => ( $args['value'] ) ? $args['value'] : $wpmem->get_text( $key ),
			);

			// Merge args with settings.
			$args = wp_parse_args( $args, $defaults );

			$this->dialogs[ $args['name'] ] = $args;
		}
		
		//return $args;
	}

	/**
	 * Settings for default tabs.
	 *
	 * @since 3.1.0
	 */
	function default_tabs() {
		$this->tabs = array(
			'options' => 'WP-Members ' . __( 'Options', 'wp-members' ),
			'fields'  => __( 'Fields', 'wp-members' ),
			'dialogs' => __( 'Dialogs', 'wp-members' ),
			'emails'  => __( 'Emails', 'wp-members' ),
		);
	}

	/** 
	 * Settings for default emails.
	 *
	 * @since 3.1.0
	 */	
	function default_emails() {
		global $wpmem;
		
		if ( $wpmem->mod_reg == 0 ) {
	
			$this->add_email( array(
				'name'          => 'wpmembers_email_newreg',
				'heading'       => __( "New Registration", 'wp-members' ),
				'subject_input' => 'wpmembers_email_newreg_subj',
				'body_input'    => 'wpmembers_email_newreg_body',	
			) );
			
		} else {
	
			$this->add_email( array(
				'name'          => 'wpmembers_email_newmod',
				'heading'       => __( "Registration is Moderated", 'wp-members' ),
				'subject_input' => 'wpmembers_email_newmod_subj',
				'body_input'    => 'wpmembers_email_newmod_body',	
			) );
			$this->add_email( array(
				'name'          => 'wpmembers_email_appmod',
				'heading'       => __( "Registration is Moderated, User is Approved", 'wp-members' ),
				'subject_input' => 'wpmembers_email_appmod_subj',
				'body_input'    => 'wpmembers_email_appmod_body',	
			) );
		}
	
		$this->add_email( array(
			'name'          => 'wpmembers_email_repass',
			'heading'       => __( "Password Reset", 'wp-members' ),
			'subject_input' => 'wpmembers_email_repass_subj',
			'body_input'    => 'wpmembers_email_repass_body',	
		) );
	
		$this->add_email( array(
			'name'          => 'wpmembers_email_getuser',
			'heading'       => __( "Retrieve Username", 'wp-members' ),
			'subject_input' => 'wpmembers_email_getuser_subj',
			'body_input'    => 'wpmembers_email_getuser_body',	
		) );	
	
		if ( $wpmem->notify == 1 ) {
			$this->add_email( array(
				'name'          => 'wpmembers_email_notify',
				'heading'       => __( "Admin Notification", 'wp-members' ),
				'subject_input' => 'wpmembers_email_notify_subj',
				'body_input'    => 'wpmembers_email_notify_body',	
			) );
		}
	
	}
	
	/** 
	 * Settings for default dialogs.
	 *
	 * @since 3.1.1
	 */	
	function default_dialogs() {
		global $wpmem;
		
		/**
		 * Filter the dialog array to add custom dialogs.
		 *
		 * @since 3.1.1
		 *
		 * @param array $dialog_array
		 */
		$dialogs = apply_filters( 'wpmem_dialogs', get_option( 'wpmembers_dialogs' ) );
		
		$dialog_labels = array(
			'restricted_msg'   => __( "Restricted post (or page), displays above the login/registration form", 'wp-members' ),
			'user'             => __( "Username is taken", 'wp-members' ),
			'email'            => __( "Email is registered", 'wp-members' ),
			'success'          => __( "Registration completed", 'wp-members' ),
			'editsuccess'      => __( "User update", 'wp-members' ),
			'pwdchangerr'      => __( "Passwords did not match", 'wp-members' ),
			'pwdchangesuccess' => __( "Password changes", 'wp-members' ),
			'pwdreseterr'      => __( "Username or email do not exist when trying to reset forgotten password", 'wp-members' ),
			'pwdresetsuccess'  => __( "Password reset", 'wp-members' ),
		);
		
		foreach ( $dialogs as $key => $val ) {
			if ( array_key_exists( $key, $dialog_labels ) ) {
				$dialogs[ $key ] = array(
					'name'  => $key,
					'label' => $dialog_labels[ $key ],
					'value' => $dialogs[ $key ],
				);
			}
		}

		foreach ( $dialogs as $val ) {
			$this->add_dialog( $val );
		}
	}
	
	
	/**
	 * Get the current form.
	 *
	 * @since 3.1.2
	 *
	 * @todo Work on multi-form project, no current milestone.
	 */
	function get_form( $form = 'default' ) {
		/*
		$current_form = ( isset( $_GET['form'] ) ) ? $_GET['form'] : $form;
		$wpmem_forms = get_option( 'wpmembers_forms' );
		$fields = $wpmem_forms[ $current_form ];
		$this->current_form = $current_form;
		$this->current_form_fields = $fields;
		*/
		$this->current_form = sanitize_text_field( wpmem_get( 'form', $form, 'get' ) ); //( isset( $_GET['form'] ) ) ? $_GET['form'] : $form;
		global $wpmem;
		// Add numeric array form fields as associative
		//foreach( $wpmem->fields as $field ) {
		//	$wpmem->fields[ $field[2] ] = $field;
		//}
		$this->current_form_fields = wpmem_fields();
	}
	
	/**
	 * Build admin panel form action url.
	 *
	 * @since 3.1.8
	 *
	 * @global string $pagenow
 	 * @global string $plugin_page
 	 * @global object $wpmem         The WP_Members Object.
	 * @param  mixed  $args          Array of additional arguments|boolean. Default: false.
	 * @return string $url
	 */
	function form_post_url( $args = false ) {
		global $pagenow, $plugin_page, $wpmem;
		$tab = sanitize_text_field( wpmem_get( 'tab', false, 'get' ) );
		$params = array( 'page' => $plugin_page );
		if ( $tab ) {
			$params['tab'] = $tab;
		}
		if ( $args ) {
			foreach( $args as $key => $val ) {
				$params[ $key ] = $val;
			}
		}
		$url = add_query_arg( $params, admin_url( $pagenow ) );
		return esc_url( $url );
	}
	
	/**
	 * Enqueues the admin javascript and css files.
	 *
	 * Replaces wpmem_admin_enqueue_scripts().
	 * Only loads the js and css on admin screens that use them.
	 *
	 * @since 3.1.7
	 * @since 3.2.0 Moved into admin object, renamed dashboard_enqueue_scripts().
	 * @since 3.2.1 Load js for post.php hook.
	 * @since 3.3.0 Everything loads from /assets/ folder.
	 *
	 * @global object $current_screen
	 * @global object $wpmem
	 * @param  string $hook The admin screen hook being loaded.
	 */
	function dashboard_enqueue_scripts( $hook ) {
		global $current_screen, $wpmem;
		if ( 'edit.php' == $hook || 'settings_page_wpmem-settings' == $hook || 'post.php' == $hook || 'post-new.php' == $hook || 'user-edit.php' == $hook || 'profile.php' == $hook ) {
			wp_enqueue_style( 'wpmem-admin', $wpmem->url . 'assets/css/admin' . wpmem_get_suffix() . '.css', '', $wpmem->version );
		} 
		if ( 'settings_page_wpmem-settings' == $hook || 'post.php' == $hook || 'post-new.php' == $hook  ) {
			wp_enqueue_script( 'wpmem-admin', $wpmem->url . 'assets/js/admin' . wpmem_get_suffix() . '.js', '', $wpmem->version );
		}
		if ( ( 'post.php' == $hook || 'post-new.php' == $hook ) && 1 == $wpmem->enable_products ) {
			if ( ! wp_script_is( 'select2', 'enqueued' ) ) {
				wp_register_style( 'select2-style', $wpmem->url . 'assets/css/select2' . wpmem_get_suffix() . '.css', false, '4.0.5', 'all' );
				wp_register_script( 'select2',   $wpmem->url . 'assets/js/select2' . wpmem_get_suffix() . '.js', array( 'jquery' ), '4.0.5', true );
				wp_enqueue_style( 'select2-style' );
				wp_enqueue_script( 'select2' );
			}
			
			//if ( ( 'post-new.php' == $hook && isset( $_GET['post_type'] ) && 'wpmem_product' == $_GET['post_type'] ) ||  )
			if ( 'wpmem_product' == get_post_type() ) {
				wp_enqueue_script( 'jquery' );
				wp_enqueue_script( 'jquery-ui-core' );// enqueue jQuery UI Core
				wp_enqueue_script( 'jquery-ui-datepicker' ); // enqueue jQuery UI Datepicker
				if ( ! wp_style_is( 'jquery-ui-style', 'enqueued' ) ) {
					wp_register_style( 'jquery-ui-style', $wpmem->url . 'assets/css/jquery-ui' . wpmem_get_suffix() . '.css' );
				}
				wp_enqueue_style( 'jquery-ui-style' ); 
			}
		}
		if ( 'user-edit' == $current_screen->id || 'profile' == $current_screen->id ) {
			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'jquery-ui-core' ); // enqueue jQuery UI Core
			wp_enqueue_script( 'jquery-ui-tabs' ); // enqueue jQuery UI Tabs
			wp_enqueue_script( 'jquery-ui-datepicker' ); // enqueue jQuery UI Datepicker

			if ( ! wp_style_is( 'jquery-ui-style', 'enqueued' ) ) {
				wp_register_style( 'jquery-ui-style', $wpmem->url . 'assets/css/jquery-ui' . wpmem_get_suffix() . '.css' );
			}
			wp_enqueue_style( 'jquery-ui-style' ); 
		}
		if ( 'settings_page_wpmem-settings' == $hook ) {
			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'jquery-ui-core' );// enqueue jQuery UI Core
			wp_enqueue_script( 'jquery-ui-dialog' );
			wp_enqueue_script( 'jquery-ui-datepicker' ); // enqueue jQuery UI Datepicker
			
			if ( ! wp_style_is( 'jquery-ui-style', 'enqueued' ) ) {
				wp_register_style( 'jquery-ui-style', $wpmem->url . 'assets/css/jquery-ui' . wpmem_get_suffix() . '.css' );
			}
			wp_enqueue_style( 'jquery-ui-style' ); 
			 
		}
	}

	/**
	 * Filter to add link to settings from plugin panel.
	 *
	 * @since 2.4.0
	 * @since 3.2.0 Moved to admin API class, renamed from wpmem_admin_plugin_links().
	 *
	 * @global object $wpmem
	 *
	 * @param  array  $links
	 * @param  string $file
	 * @return array  $links
	 */
	function plugin_links( $links, $file ) {
		global $wpmem;
		static $wpmem_plugin;
		if ( ! $wpmem_plugin ) {
			$wpmem_plugin = plugin_basename( $wpmem->path . '/wp-members.php' );
		}
		if ( $file == $wpmem_plugin ) {
			$settings_link = '<a href="' . add_query_arg( 'page', 'wpmem-settings', 'options-general.php' ) . '">' . __( 'Settings', 'wp-members' ) . '</a>';
			$links = array_merge( array( $settings_link ), $links );
		}
		return $links;
	}

	/**
	 * Returns an array of WordPress reserved terms.
	 *
	 * @since 3.0.2
	 * @since 3.2.3 Moved to WP_Members_Admin_API class.
	 *
	 * @return array An array of WordPress reserved terms.
	 */
	function wp_reserved_terms() {
		$reserved_terms = array( 'attachment', 'attachment_id', 'author', 'author_name', 'calendar', 'cat', 'category', 'category__and', 'category__in', 'category__not_in', 'category_name', 'comments_per_page', 'comments_popup', 'customize_messenger_channel', 'customized', 'cpage', 'day', 'debug', 'error', 'exact', 'feed', 'fields', 'hour', 'link_category', 'm', 'minute', 'monthnum', 'more', 'name', 'nav_menu', 'nonce', 'nopaging', 'offset', 'order', 'orderby', 'p', 'page', 'page_id', 'paged', 'pagename', 'pb', 'perm', 'post', 'post__in', 'post__not_in', 'post_format', 'post_mime_type', 'post_status', 'post_tag', 'post_type', 'posts', 'posts_per_archive_page', 'posts_per_page', 'preview', 'robots', 'role', 's', 'search', 'second', 'sentence', 'showposts', 'static', 'subpost', 'subpost_id', 'tag', 'tag__and', 'tag__in', 'tag__not_in', 'tag_id', 'tag_slug__and', 'tag_slug__in', 'taxonomy', 'tb', 'term', 'theme', 'type', 'w', 'withcomments', 'withoutcomments', 'year' );

		/**
		 * Filter the array of reserved terms.
		 *
		 * @since 3.0.2
		 *
		 * @param array $reserved_terms
		 */
		$reserved_terms = apply_filters( 'wpmem_wp_reserved_terms', $reserved_terms );

		return $reserved_terms;
	}
	
	function settings( $which ) {
		switch ( $which ) {
			case 'content':
				return array( 'block' => 'Content Blocking', 'show_excerpt' => 'Show Excerpts', 'show_login' => 'Show Login Form', 'show_reg' => 'Show Registration Form', 'autoex' => 'Auto Excerpt' );
				break;
			case 'options':
				return array( 'notify' => 'Notify admin', 'mod_reg' => 'Moderate registration', 'captcha' => 'Enable registration CAPTCHA', 'warnings' => 'Ignore warning messages', 'dropins' => 'Enable dropins', 'enable_products' => 'Enable membership products', 'clone_menus' => 'Clone menus' );
				break;
		}
	}
	
	function post_types() {
		return get_post_types( array( 'public' => true, '_builtin' => false ), 'names', 'and' );
	}
} // End of WP_Members_Admin_API class.

// End of file.