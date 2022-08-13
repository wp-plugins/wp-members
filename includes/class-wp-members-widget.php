<?php
/**
 * Class for the sidebar login widget.
 *
 * @since 2.7
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

class widget_wpmemwidget extends WP_Widget {

	/**
	 * Sets up the WP-Members login widget.
	 */
	function __construct() {
		parent::__construct(
			'widget_wpmemwidget',
			'WP-Members Login',
			array( 
				'classname'   => 'wp-members', 
				'description' => __( 'Displays the WP-Members sidebar login.', 'wp-members' ), 
			)
		);
	}

	/**
	 * Displays the WP-Members login widget settings 
	 * controls on the widget panel.
	 *
	 * @param array $instance
	 */
	function form( $instance ) {
	
		// Default widget settings.
		$defaults = array( 
			'title'       => __( 'Login Status', 'wp-members' ),
			'redirect_to' => '',
		);
		$instance = wp_parse_args( ( array ) $instance, $defaults );
		
		// Title input. ?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'wp-members' ); ?></label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:95%;" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'redirect_to' ); ?>"><?php _e( 'Redirect to (optional):', 'wp-members' ); ?></label>
			<input id="<?php echo $this->get_field_id( 'redirect_to' ); ?>" name="<?php echo $this->get_field_name( 'redirect_to' ); ?>" value="<?php echo $instance['redirect_to']; ?>" style="width:95%;" />
		</p>
		<?php
	}

	/**
	 * Update the WP-Members login widget settings.
	 *
	 * @param  array $new_instance
	 * @param  array $old_instance
	 * @return array $instance
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		// Strip tags for title to remove HTML.
		$instance['title']       = strip_tags( $new_instance['title'] );
		$instance['redirect_to'] = strip_tags( $new_instance['redirect_to'] );

		return $instance;
	}

	/**
	 * Displays the WP-Members login widget.
	 *
	 * @param array $args
	 * @param array $instance
	 */
	function widget( $args, $instance ) {

		$redirect_to = ( array_key_exists( 'redirect_to', $instance ) ) ? $instance['redirect_to'] : '';
		$title       = ( array_key_exists( 'title',       $instance ) ) ? $instance['title']       : __( 'Login Status', 'wp-members' );
		$customizer  = ( is_customize_preview() ) ? get_theme_mod( 'wpmem_show_logged_out_state', false ) : false;
		
		/** This filter is documented in wp-includes/widgets/class-wp-widget-pages.php */
		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );
		/**
		 * Filter the widget title.
		 *
		 * @since Unknown
		 * @since 3.2.0 Added instance and id_base params.
		 *
		 * @param string $title The widget title.
		 */
		$title = apply_filters( 'wpmem_widget_title', $title, $instance, $this->id_base );
		
		/**
		 * Filter the widget ID.
		 *
		 * @since Unknown
		 * @since 3.2.0 Added instance and id_base params.
		 *
		 * @param string The ID for the sidebar widget.
		 */
		$id = apply_filters( 'wpmem_widget_id', 'wp-members', $instance, $this->id_base  );
		
		echo $args['before_widget'];
		echo '<div id="' . $id . '">';
		echo $args['before_title'] . $title . $args['after_title'];
		// The Widget
		$this->do_sidebar( $redirect_to, $customizer ); 
		echo '</div>';
		echo $args['after_widget'];
	}
	
	/**
	 * Creates the sidebar login form and status.
	 *
	 * This function determines if the user is logged in and displays either
	 * a login form, or the user's login status. Typically used for a sidebar.		
	 * You can call this directly, or with the widget.
	 *
	 * @since 2.4.0
	 * @since 3.0.0 Added $post_to argument.
	 * @since 3.1.0 Changed $post_to to $redirect_to.
	 * @since 3.2.0 Moved to widget_wpmemwidget class as do_sidebar().
	 * @since 3.4.0 Revise form for consitency with main body form.
	 *
	 * @param  string $redirect_to  A URL to redirect to upon login, default null.
	 * @param  bool   $customizer   Whether to show the form for the customizer.
	 * @global string $wpmem_regchk
	 * @global string $user_login
	 */
	function do_sidebar( $redirect_to = null, $customizer = false ) {

		global $wpmem, $wpmem_regchk;

		 // Used here and in the logout.
		$url = get_bloginfo('url');

		if ( isset( $_REQUEST['redirect_to'] ) ) {
			$post_to = $_REQUEST['redirect_to'];
		} elseif ( is_home() || is_front_page() ) {
			$post_to = $_SERVER['REQUEST_URI'];
		} elseif ( is_single() || is_page() ) {
			$post_to = get_permalink();
		} elseif ( is_category() ) {
			global $wp_query;
			$cat_id  = get_query_var( 'cat' );
			$post_to = get_category_link( $cat_id );
		} elseif ( is_search() ) {
			$post_to = add_query_arg( 's', get_search_query(), $url );
		} else {
			$post_to = $_SERVER['REQUEST_URI'];
		}

		// Clean whatever the url is.
		$post_to = esc_url( $post_to );

		if ( ! is_user_logged_in() || ( '1' == $customizer && is_customize_preview() ) ) {

			// If the user is not logged in, we need the form.

			// Defaults.
			$defaults = array(
				// Wrappers.
				'error_before'    => '<p class="err">',
				'error_after'     => '</p>',
				'fieldset_before' => '<fieldset>',
				'fieldset_after'  => '</fieldset>',
				'row_before'      => '',
				'row_after'       => '',
				'inputs_before'   => '<div class="div_texbox">',
				'inputs_after'    => '</div>',
				'buttons_before'  => '<div class="button_div">',
				'buttons_after'   => '</div>',

				// Messages.
				'error_msg'  => wpmem_get_text( 'widget_login_failed' ),
				'status_msg' => wpmem_get_text( 'widget_not_logged_in' ) . '<br />',

				'form_id'         => 'wpmem_login_widget_form',
				'form_class'      => 'widget_form',
				'button_id'       => '',
				'button_class'    => 'buttons',
				
				// Other.
				'strip_breaks'    => true,
				'wrap_inputs'     => true,
				'n'               => "\n",
				't'               => "\t",
				'login_form_action' => true,
			);

			/**
			 * Filter arguments for the sidebar defaults.
			 *
			 * @since 2.9.0
			 * @deprecated 3.3.0 Use wpmem_login_widget_args instead.
			 *
			 * @param array An array of the defaults to be changed.
			 */
			$args = apply_filters( 'wpmem_sb_login_args', '' );

			// Merge $args with defaults.
			$args = wp_parse_args( $args, $defaults );
			
			/**
			 * Filter the defaults for the login widget.
			 *
			 * @since 3.3.0
			 *
			 * @param array And array of the defaults to be changed.
			 */
			$args = apply_filters( 'wpmem_login_widget_args', $args );

			$inputs = array(
				array(
					'name'  => wpmem_get_text( 'widget_login_username' ),
					'type'  => 'text',
					'tag'   => 'log',
					'class' => 'username',
					'div'   => 'div_text'
				),
				array(
					'name'  => wpmem_get_text( 'widget_login_password' ),
					'type'  => 'password',
					'tag'   => 'pwd',
					'class' => 'password',
					'div'   => 'div_text',
				),
			);
			
			// Build the input rows.
			foreach ( $inputs as $input ) {
				$label = '<label for="' . esc_attr( $input['tag'] ) . '">' . $input['name'] . '</label>';
				$field = wpmem_form_field( array(
					'name'     => $input['tag'], 
					'type'     => $input['type'],
					'class'    => $input['class'],
					'required' => true,
				) );
				$field_before = ( $args['wrap_inputs'] ) ? '<div class="' . wpmem_sanitize_class( $input['div'] ) . '">' : '';
				$field_after  = ( $args['wrap_inputs'] ) ? '</div>' : '';
				$rows[] = array( 
					'row_before'   => $args['row_before'],
					'label'        => $label,
					'field_before' => $field_before,
					'field'        => $field,
					'field_after'  => $field_after,
					'row_after'    => $args['row_after'],
				);
			}
			
			/**
			 * Filter the array of form rows. Works like wpmem_login_form_rows.
			 *
			 * @since 3.4.0
			 *
			 * @param array  $rows  An array containing the form rows.
			 */
			$rows = apply_filters( 'wpmem_login_widget_form_rows', $rows );

			// Put the rows from the array into $form.
			$form = '';
			foreach ( $rows as $row_item ) {
				$row  = ( $row_item['row_before']   != '' ) ? $row_item['row_before'] . $args['n'] . $row_item['label'] . $args['n'] : $row_item['label'] . $args['n'];
				$row .= ( $row_item['field_before'] != '' ) ? $row_item['field_before'] . $args['n'] . $args['t'] . $row_item['field'] . $args['n'] . $row_item['field_after'] . $args['n'] : $row_item['field'] . $args['n'];
				$row .= ( $row_item['row_after']    != '' ) ? $row_item['row_after'] . $args['n'] : '';
				$form.= $row;
			}

			// Handle outside elements added to the login form (currently ONLY for login).
			if ( $args['login_form_action'] ) {
				ob_start();
				do_action( 'login_form' );
				$add_to_form = ob_get_contents();
				ob_end_clean();
				$form.= $add_to_form;
			}

			$hidden = '<input type="hidden" name="rememberme" value="forever" />' . $args['n'] .
					'<input type="hidden" name="redirect_to" value="' . ( ( $redirect_to ) ? $redirect_to : $post_to ) . '" />' . $args['n'] .
					'<input type="hidden" name="a" value="login" />' . $args['n'] .
					'<input type="hidden" name="slog" value="true" />';
			/**
			 * Filter sidebar login form hidden fields.
			 *
			 * @since 2.9.0
			 * @deprecated 3.4.0 Use wpmem_login_widget_hidden_fields instead.
			 *
			 * @param string $hidden The HTML for the hidden fields.
			 */
			$hidden = apply_filters( 'wpmem_sb_hidden_fields', $hidden );
			/**
			 * Filter sidebar login form hidden fields.
			 *
			 * @since 3.4.0
			 *
			 * @param string $hidden The HTML for the hidden fields.
			 */
			$form = $form . apply_filters( 'wpmem_login_widget_hidden_fields', $hidden );

			$buttons = '<input type="submit" name="Submit" class="' . wpmem_sanitize_class( $args['button_class'] ) . '" value="' . wpmem_get_text( 'widget_login_button' ) . '" />';

			if ( $wpmem->user_pages['profile'] != null ) { 
				/** This filter is documented in wp-members/includes/class-wp-members-forms.php */
				$link = apply_filters( 'wpmem_forgot_link', wpmem_profile_url( 'pwdreset' ) );
				$link_html = ' <a href="' . $link . '">' . wpmem_get_text( 'widget_login_forgot' ) . '</a>&nbsp;';
				/**
				 * Filter the sidebar forgot password.
				 *
				 * @since 3.0.9
				 * @deprecated 3.4.0 Use wpmem_login_widget_forgot_link_str instead.
				 *
				 * @param string $link_html
				 * @param string $link
				 */
				$link_html = apply_filters( 'wpmem_sb_forgot_link_str', $link_html, $link );
				/**
				 * Filter the sidebar forgot password.
				 *
				 * @since 3.4.0
				 *
				 * @param string $link_html
				 * @param string $link
				 */
				$link_html = apply_filters( 'wpmem_login_widget_forgot_link_str', $link_html, $link );
				$buttons.= $link_html;
			} 			

			if ( $wpmem->user_pages['register'] != null ) {
				/** This filter is documented in wp-members/includes/class-wp-members-forms.php */
				$link = apply_filters( 'wpmem_reg_link', $wpmem->user_pages['register'] );
				$link_html = ' <a href="' . $link . '">' . wpmem_get_text( 'widget_login_register' ) . '</a>';
				/**
				 * Filter the sidebar register link.
				 *
				 * @since 3.0.9
				 * @deprecated 3.4.0 Use wpmem_login_widget_reg_link_str instead.
				 *
				 * @param string $link_html
				 * @param string $link
				 */
				$link_html = apply_filters( 'wpmem_sb_reg_link_str', $link_html, $link );
				/**
				 * Filter the sidebar register link.
				 *
				 * @since 3.4.0
				 *
				 * @param string $link_html
				 * @param string $link
				 */
				$link_html = apply_filters( 'wpmem_login_widget_reg_link_str', $link_html, $link );
				$buttons.= $link_html;
			}

			$form = $form . $args['n'] . $args['buttons_before'] . $buttons . $args['n'] . $args['buttons_after'];

			$form = $args['fieldset_before'] . $args['n'] . $form . $args['n'] . $args['fieldset_after'];

			$form = '<form name="form" method="post" action="' . $post_to . '" id="' . wpmem_sanitize_class( $args['form_id'] ) . '" class="' . wpmem_sanitize_class( $args['form_class'] ) . '">' . $args['n'] . $form . $args['n'] . '</form>';

			// Add status message, if one exists.
			if ( '' == $args['status_msg'] ) {
				$args['status_msg'] . $args['n'] . $form;
			}

			// Strip breaks.
			$form = ( $args['strip_breaks'] ) ? str_replace( array( "\n", "\r", "\t" ), array( '','','' ), $form ) : $form;

			/**
			 * Filter the sidebar form.
			 *
			 * @since unknown
			 * @deprecated 3.3.9 Use wpmem_login_widget_form instead.
			 *
			 * @param string $form The HTML for the sidebar login form.
			 */
			$form = apply_filters( 'wpmem_sidebar_form', $form );
			/**
			 * Filter the sidebar form.
			 *
			 * @since 3.3.9
			 *
			 * @param string $form The HTML for the sidebar login form.
			 */
			$form = apply_filters( 'wpmem_login_widget_form', $form );

			$do_error_msg = '';
			$error_msg = $args['error_before'] . $args['error_msg'] . $args['error_after'];
			
			if ( isset( $_POST['slog'] ) && $wpmem_regchk == 'loginfailed' ) {
				$do_error_msg = true;
			} elseif( is_customize_preview() && get_theme_mod( 'wpmem_show_form_message_dialog', false ) ) {
				$do_error_msg = true;
			}
			
			if ( $do_error_msg ) {
				/**
				 * Filter the sidebar login failed message.
				 *
				 * @since unknown
				 * @deprecated 3.4.0 Use wpmem_login_widget_login_failed instead.
				 *
				 * @param string $error_msg The error message.
				 */
				$error_msg = apply_filters( 'wpmem_login_failed_sb', $error_msg );
				/**
				 * Filter the sidebar login failed message.
				 *
				 * @since 3.4.0
				 *
				 * @param string $error_msg The error message.
				 */
				$error_msg = apply_filters( 'wpmem_login_widget_login_failed', $error_msg );
				$form = $error_msg . $form;
			}

			echo $form;

		} else {

			global $user_login; 

			/** This filter is defined in /includes/api/api.php */
			$logout = apply_filters( 'wpmem_logout_link', add_query_arg( 'a', 'logout', $url ) );

			// Defaults.
			$defaults = array(
				'user_login'     => $user_login,
				'wrapper_before' => '<p class="login_widget_status">',
				'status_text'    => sprintf( wpmem_get_text( 'widget_status' ), $user_login ) . '<br />',
				'link_text'      => wpmem_get_text( 'widget_logout' ),
				'wrapper_after'  => '</p>',
			);

			/**
			 * Filter sidebar login status arguments.
			 *
			 * @since 3.1.0
			 * @since 3.1.2 Pass default args.
			 * @deprecated 3.4.0 Use wpmem_login_widget_status_args instead.
			 *
			 * @param  array $defaults
			 * @return array
			 */
			$args = apply_filters( 'wpmem_sidebar_status_args', $defaults );
			/**
			 * Filter sidebar login status arguments.
			 *
			 * @since 3.4.0
			 *
			 * @param  array $defaults
			 * @return array
			 */
			$args = apply_filters( 'wpmem_login_widget_status_args', $defaults );

			// Merge $args with $defaults.
			$args = wp_parse_args( $args, $defaults );

			// Generate the message string.
			$str = $args['wrapper_before'] . $args['status_text'] . "<a href=\"$logout\">" . $args['link_text'] . '</a>' . $args['wrapper_after'];

			/**
			 * Filter the sidebar user login status.
			 *
			 * @since unknown
			 * @deprecated 3.4.0 Use wpmem_login_widget_status instead.
			 *
			 * @param string $str The login status for the user.
			 */
			$str = apply_filters( 'wpmem_sidebar_status', $str );
			/**
			 * Filter the sidebar user login status.
			 *
			 * @since 3.4.0
			 *
			 * @param string $str The login status for the user.
			 */
			$str = apply_filters( 'wpmem_login_widget_status', $str );

			echo $str;
		}
	}
}