<?php
/**
 * The WP_Members Admin API Class.
 *
 * @package WP-Members
 * @subpackage WP_Members Admin API Object Class
 * @since 3.1.0
 */

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

		// Load default tabs.
		$tabs = $this->default_tabs();

		// Load default emails.
		$emails = $this->default_emails();
		
		// Load default dialogs.
		$dialogs = $this->default_dialogs();
	}

	/**
	 * Load dependencies.
	 *
	 * @since 3.1.0
	 * @since 3.1.1 Added tab-about.php.
	 * @since 3.1.7 Loads all admin dependent files.
	 */
	function load_dependencies() {
		if ( is_multisite() && current_user_can( 'edit_theme_options' ) ) {
			require_once(  WPMEM_PATH . 'admin/admin.php' );
		}
		if ( current_user_can( 'edit_users' ) ) { 
			require_once( WPMEM_PATH . 'admin/admin.php' );
			require_once( WPMEM_PATH . 'admin/user-profile.php' );
		}
		if ( current_user_can( 'manage_options' ) ) {
			require_once( WPMEM_PATH . 'admin/tab-options.php' );
			require_once( WPMEM_PATH . 'admin/tab-fields.php' );
			require_once( WPMEM_PATH . 'admin/tab-emails.php' );
			require_once( WPMEM_PATH . 'admin/tab-captcha.php' );
			require_once( WPMEM_PATH . 'admin/tab-about.php' );
			require_once( WPMEM_PATH . 'admin/dialogs.php' );
		}
		if ( current_user_can( 'edit_posts' ) ) {
			require_once( WPMEM_PATH . 'admin/post.php' );
		}
		require_once( WPMEM_PATH . 'admin/tab-dialogs.php' );
		require_once( WPMEM_PATH . 'inc/users.php' );
		require_once( WPMEM_PATH . 'admin/users.php' );
		require_once( WPMEM_PATH . 'admin/includes/api.php' );
		include_once( WPMEM_PATH . 'inc/wp-registration.php' );
	}

	/**
	 * Load admin.
	 *
	 * @since 3.1.0
	 * @since 3.1.7 Loads all admin hooks.
	 */
	function load_hooks() {
		
		add_action( 'admin_enqueue_scripts',         'wpmem_dashboard_enqueue_scripts' );
		add_action( 'wpmem_admin_do_tab',            'wpmem_admin_do_tab' );
		add_action( 'wp_ajax_wpmem_a_field_reorder', 'wpmem_a_do_field_reorder' );
		add_action( 'user_new_form',                 'wpmem_admin_add_new_user' );
		add_filter( 'plugin_action_links',           'wpmem_admin_plugin_links', 10, 2 );
		add_filter( 'wpmem_admin_tabs',              'wpmem_add_about_tab'       );
		add_action( 'wpmem_admin_do_tab',            'wpmem_a_about_tab', 999, 1 );
		
		// If user has a role that cannot edit users, set profile actions for non-admins.
		if ( ! current_user_can( 'edit_users' ) ) { 	
			// User actions and filters.
			add_action( 'show_user_profile',          'wpmem_user_profile'   );
			add_action( 'edit_user_profile',          'wpmem_user_profile'   );
			add_action( 'profile_update',             'wpmem_profile_update' );
		} else {
			add_action( 'show_user_profile',          'wpmem_admin_fields' );
			add_action( 'edit_user_profile',          'wpmem_admin_fields' );
			add_action( 'profile_update',             'wpmem_admin_update' );
			add_action( 'admin_footer-users.php',     'wpmem_bulk_user_action' );
			add_action( 'load-users.php',             'wpmem_users_page_load' );
			add_action( 'admin_notices',              'wpmem_users_admin_notices' );
			add_filter( 'views_users',                'wpmem_users_views' );
			add_filter( 'manage_users_columns',       'wpmem_add_user_column' );
			add_action( 'manage_users_custom_column', 'wpmem_add_user_column_content', 10, 3 );
			add_action( 'wpmem_post_register_data',   'wpmem_set_new_user_non_active' );
			add_action( 'wpmem_user_activated',       'wpmem_set_activated_user' );
			add_action( 'wpmem_user_deactivated',     'wpmem_set_deactivated_user' );
			add_filter( 'user_row_actions',           'wpmem_insert_activate_link', 10, 2 );
		}
	
		// If user has a role that can edit posts, add the block/unblock meta boxes and custom post/page columns.
		if ( current_user_can( 'edit_posts' ) ) {
			// Post actions and filters.
			add_action( 'add_meta_boxes',             'wpmem_block_meta_add' );
			add_action( 'save_post',                  'wpmem_block_meta_save' );
			add_filter( 'manage_posts_columns',       'wpmem_post_columns' );
			add_action( 'manage_posts_custom_column', 'wpmem_post_columns_content', 10, 2 );
			add_filter( 'manage_pages_columns',       'wpmem_post_columns' );
			add_action( 'manage_pages_custom_column', 'wpmem_post_columns_content', 10, 2 );
			
			add_action( 'wpmem_admin_after_profile',  'wpmem_profile_show_activate',   7 );
			add_action( 'wpmem_admin_after_profile',  'wpmem_profile_show_expiration', 8 );
			add_action( 'wpmem_admin_after_profile',  'wpmem_profile_show_ip',         9 );
			
			add_action( 'admin_footer-edit.php', 'wpmem_bulk_posts_action'   );
			add_action( 'load-edit.php',         'wpmem_posts_page_load'     );
			add_action( 'admin_notices',         'wpmem_posts_admin_notices' );
			add_action( 'load-post.php',         'wpmem_load_tinymce'        );
			add_action( 'load-post-new.php',     'wpmem_load_tinymce'        );
		}
		
		if ( ! is_multisite() && current_user_can( 'manage_options' ) ) {
			add_action('wp_dashboard_setup', 'butlerblog_dashboard_widget');
		}

	} // End of load_hooks()

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
        <tr valign="top"><td colspan="2"><strong><?php echo $args['heading']; ?></strong></td></tr>
        <tr valign="top">
            <th scope="row"><?php echo $args['subject_label']; ?></th>
            <td><input type="text" name="<?php echo $args['subject_input']; ?>" size="80" value="<?php echo wp_unslash( $args['subject_value'] ); ?>"></td> 
        </tr>
        <tr valign="top">
            <th scope="row"><?php echo $args['body_label']; ?></th>
            <td><textarea name="<?php echo $args['body_input']; ?>" rows="12" cols="50" id="" class="large-text code"><?php echo wp_unslash( $args['body_value'] ); ?></textarea></td>
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
			'subj' => wpmem_get( $args['subject_input'] ),
			'body' => wpmem_get( $args['body_input'] ),
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
            <th scope="row"><?php echo $args['label']; ?></th> 
            <td><textarea name="<?php echo $args['name'] . "_dialog"; ?>" rows="3" cols="50" id="" class="large-text code"><?php echo wp_unslash( $args['value'] ); ?></textarea></td> 
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
				$settings[ $dialog['name'] ] = $_POST[ $dialog['name'] . '_dialog' ];
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
	 * @todo Work on multi-form project for 3.1.2
	 */
	function get_form( $form = 'default' ) {
		/*
		$current_form = ( isset( $_GET['form'] ) ) ? $_GET['form'] : $form;
		$wpmem_forms = get_option( 'wpmembers_forms' );
		$fields = $wpmem_forms[ $current_form ];
		$this->current_form = $current_form;
		$this->current_form_fields = $fields;
		*/
		$current_form = wpmem_get( 'form', $form, 'get' ); //( isset( $_GET['form'] ) ) ? $_GET['form'] : $form;
		$this->current_form = $current_form;
		global $wpmem;
		// Add numeric array form fields as associative
		//foreach( $wpmem->fields as $field ) {
		//	$wpmem->fields[ $field[2] ] = $field;
		//}
		$this->current_form_fields = wpmem_fields();
	}
	
} // End of WP_Members_Admin_API class.

// End of file.