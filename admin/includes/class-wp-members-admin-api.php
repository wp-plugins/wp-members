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
	}

	/**
	 * Load dependencies.
	 *
	 * @since 3.1.0
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
			require_once( WPMEM_PATH . 'admin/tab-dialogs.php' );
			require_once( WPMEM_PATH . 'admin/tab-emails.php' );
			require_once( WPMEM_PATH . 'admin/tab-captcha.php' );
			require_once( WPMEM_PATH . 'admin/tab-about.php' );
			require_once( WPMEM_PATH . 'admin/dialogs.php' );
			//require_once( WPMEM_PATH . 'admin/tab-about.php' );
		}
		if ( current_user_can( 'edit_posts' ) ) {
			require_once( WPMEM_PATH . 'admin/post.php' );
		}
		require_once( WPMEM_PATH . 'inc/users.php' );
		require_once( WPMEM_PATH . 'admin/users.php' );
	}

	/**
	 * Load admin.
	 *
	 * @since 3.1.0
	 */
	function load_hooks() {
		
		// If user has a role that cannot edit users, set profile actions for non-admins.
		if ( ! current_user_can( 'edit_users' ) ) { 	
			// User actions and filters.
			add_action( 'show_user_profile', 'wpmem_user_profile'   );
			add_action( 'edit_user_profile', 'wpmem_user_profile'   );
			add_action( 'profile_update',    'wpmem_profile_update' );
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
            <td><input type="text" name="<?php echo $args['subject_input']; ?>" size="80" value="<?php echo $args['subject_value']; ?>"></td> 
        </tr>
        <tr valign="top">
            <th scope="row"><?php echo $args['body_label']; ?></th>
            <td><textarea name="<?php echo $args['body_input']; ?>" rows="12" cols="50" id="" class="large-text code"><?php echo $args['body_value']; ?></textarea></td>
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
			'subj' => ( isset( $_POST[ $args['subject_input'] ] ) ) ? $_POST[ $args['subject_input'] ] : '',
			'body' => ( isset( $_POST[ $args['body_input'] ]    ) ) ? $_POST[ $args['body_input'] ]    : '',
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
            'heading'       => 'Custom email',
            'subject_label' => 'Subject',
			'subject_input' => $args['name'] . '_subject',
			'subject_value' => ( $settings ) ? $settings['subj'] : 'Subject',
            'body_label'    => 'Body',
			'body_input'    => $args['name'] . '_body',
			'body_value'    => ( $settings ) ? $settings['body'] : 'Your custom email message content.',
        );
		
		// Merge args with settings.
		$args = wp_parse_args( $args, $defaults );
		
		$this->emails[ $args['name'] ] = $args;
		
		return $args;
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
	
} // End of WP_Members_Admin_API class.

// End of file.