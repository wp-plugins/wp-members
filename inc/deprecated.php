<?php
/**
 * WP-Members Deprecated Functions
 *
 * These functions have been deprecated and are now obsolete.
 * Use alternative functions as these will be removed in a 
 * future release.
 * 
 * This file is part of the WP-Members plugin by Chad Butler
 * You can find out more about this plugin at http://rocketgeek.com
 * Copyright (c) 2006-2017  Chad Butler
 * WP-Members(tm) is a trademark of butlerblog.com
 *
 * @package   WP-Members
 * @author    Chad Butler 
 * @copyright 2006-2017
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

if ( ! function_exists( 'wpmem_block' ) ):
/**
 * Determines if content is blocked.
 *
 * @since 2.6.0
 * @since 3.0.0 Now a wrapper for $wpmem->is_blocked().
 * @deprecated 3.1.1 Use wpmem_is_blocked() instead.
 *
 * @global object $wpmem The WP-Members object class.
 *
 * @return bool $block true if content is blocked, false otherwise.
 */
function wpmem_block() {
	wpmem_write_log( "wpmem_block() is deprecated as of WP-Members 3.1.1, use wpmem_is_blocked() instead" );
	global $wpmem;
	return $wpmem->is_blocked();
}
endif;

if ( ! function_exists( 'wpmem_inc_sidebar' ) ):
/**
 * Displays the sidebar.
 *
 * This function is a wrapper for wpmem_do_sidebar().
 *
 * @since 2.0.0
 * @deprecated Unknown
 */
function wpmem_inc_sidebar() {
	wpmem_write_log( "WP-Members function wpmem_inc_sidebar() is deprecated. No alternative function exists" );
	/**
	 * Load the sidebar functions.
	 */
	include_once( WPMEM_PATH . 'inc/sidebar.php' );
	// Render the sidebar.
	wpmem_do_sidebar();
}
endif;

if ( ! function_exists( 'wpmem_selected' ) ):
/**
 * Determines if a form field is selected (i.e. lists & checkboxes).
 *
 * @since 0.1.0
 * @deprecated 3.1.0 Use selected() or checked() instead.
 *
 * @param  string $value
 * @param  string $valtochk
 * @param  string $type
 * @return string $issame
 */
function wpmem_selected( $value, $valtochk, $type = null ) {
	wpmem_write_log( "wpmem_selected() is deprecated as of WP-Members 3.1.0. Use selected() or checked() instead" );
	$issame = ( $type == 'select' ) ? ' selected' : ' checked';
	return ( $value == $valtochk ) ? $issame : '';
}
endif;

if ( ! function_exists( 'wpmem_chk_qstr' ) ):
/**
 * Checks querystrings.
 *
 * @since 2.0.0
 * @deprecated 3.1.0 Use add_query_arg() instead.
 *
 * @param  string $url
 * @return string $return_url
 */
function wpmem_chk_qstr( $url = null ) {
	wpmem_write_log( "wpmem_chk_qstr() is deprecated as of WP-Members 3.1.0. Use add_query_arg() instead" );
	$permalink = get_option( 'permalink_structure' );
	if ( ! $permalink ) {
		$url = ( ! $url ) ? get_option( 'home' ) . "/?" . $_SERVER['QUERY_STRING'] : $url;
		$return_url = $url . "&";
	} else {
		$url = ( ! $url ) ? get_permalink() : $url;
		$return_url = $url . "?";
	}
	return $return_url;
}
endif;

if ( ! function_exists( 'wpmem_shortcode' ) ):
/**
 * Executes various shortcodes.
 *
 * This function executes shortcodes for pages (settings, register, login, user-list,
 * and tos pages), as well as login status and field attributes when the wp-members tag
 * is used.  Also executes shortcodes for login status with the wpmem_logged_in tags
 * and fields when the wpmem_field tags are used.
 *
 * @since 2.4.0
 * @deprecated 3.1.2 
 *
 * @global object $wpmem The WP_Members object.
 *
 * @param  array  $attr {
 *     The shortcode attributes.
 *
 *     @type string $page
 *     @type string $url
 *     @type string $status
 *     @type string $msg
 *     @type string $field
 *     @type int    $id
 * }
 * @param  string $content
 * @param  string $tag
 * @return string Returns the result of wpmem_do_sc_pages|wpmem_list_users|wpmem_sc_expmessage|$content.
 */
function wpmem_shortcode( $attr, $content = null, $tag = 'wp-members' ) {
	
	$error = "wpmem_shortcode() is deprecated as of WP-Members 3.1.2. The [wp-members] shortcode tag should be replaced. ";
	$error.= 'See replacement shortcodes: http://rkt.bz/logsc ';
	$error.= "post ID: " . get_the_ID() . " ";
	$error.= "page url: " . wpmem_current_url();
	wpmem_write_log( $error );

	global $wpmem;

	// Set all default attributes to false.
	$defaults = array(
		'page'        => false,
		'redirect_to' => null,
		'url'         => false,
		'status'      => false,
		'msg'         => false,
		'field'       => false,
		'id'          => false,
		'underscores' => 'off',
	);

	// Merge defaults with $attr.
	$atts = shortcode_atts( $defaults, $attr, $tag );

	// Handles the 'page' attribute.
	if ( $atts['page'] ) {
		if ( $atts['page'] == 'user-list' ) {
			if ( function_exists( 'wpmem_list_users' ) ) {
				$content = do_shortcode( wpmem_list_users( $attr, $content ) );
			}
		} elseif ( $atts['page'] == 'tos' ) {
			return $atts['url'];
		} else {
			$content = do_shortcode( wpmem_do_sc_pages( $atts, $content, $tag ) );
		}

		// Resolve any texturize issues.
		if ( strstr( $content, '[wpmem_txt]' ) ) {
			// Fixes the wptexturize.
			remove_filter( 'the_content', 'wpautop' );
			remove_filter( 'the_content', 'wptexturize' );
			add_filter( 'the_content', 'wpmem_texturize', 999 );
		}
		return $content;
	}

	// Handles the 'status' attribute.
	if ( ( $atts['status'] ) || $tag == 'wpmem_logged_in' ) {
		return wpmem_sc_logged_in( $atts, $content, $tag );
	}

	// Handles the 'field' attribute.
	if ( $atts['field'] || $tag == 'wpmem_field' ) {
		return wpmem_sc_fields( $atts, $content, $tag );
	}

}
endif;


if ( ! function_exists( 'wpmem_do_sc_pages' ) ):
/**
 * Builds the shortcode pages (login, register, user-profile, user-edit, password).
 *
 * Some of the logic here is similar to the wpmem_securify() function. 
 * But where that function handles general content, this function 
 * handles building specific pages generated by shortcodes.
 *
 * @since 2.6.0
 * @deprecated 3.1.8 Use wpmem_sc_user_profile() or wpmem_sc_forms() instead.
 *
 * @global object $wpmem        The WP_Members object.
 * @global string $wpmem_themsg The WP-Members message container.
 * @global object $post         The WordPress post object.
 *
 * @param  string $atts {
 *     The shortcode attributes.
 *
 *     @type string $page
 *     @type string $redirect_to
 *     @type string $register
 * }
 * @param  string $content
 * @param  string $tag
 * @return string $content
 */
function wpmem_do_sc_pages( $atts, $content, $tag ) {
	
	$page = ( isset( $atts['page'] ) ) ? $atts['page'] : $tag; 
	$redirect_to = ( isset( $atts['redirect_to'] ) ) ? $atts['redirect_to'] : null;
	$hide_register = ( isset( $atts['register'] ) && 'hide' == $atts['register'] ) ? true : false;

	global $wpmem, $wpmem_themsg, $post;
	include_once( WPMEM_PATH . 'inc/dialogs.php' );

	$content = '';

	// Deprecating members-area parameter to be replaced by user-profile.
	$page = ( $page == 'user-profile' ) ? 'members-area' : $page;

	if ( $page == 'members-area' || $page == 'register' ) {

		if ( $wpmem->regchk == "captcha" ) {
			global $wpmem_captcha_err;
			$wpmem_themsg = __( 'There was an error with the CAPTCHA form.' ) . '<br /><br />' . $wpmem_captcha_err;
		}

		if ( $wpmem->regchk == "loginfailed" ) {
			return wpmem_inc_loginfailed();
		}

		if ( ! is_user_logged_in() ) {
			if ( $wpmem->action == 'register' && ! $hide_register ) {

				switch( $wpmem->regchk ) {

				case "success":
					$content = wpmem_inc_regmessage( $wpmem->regchk,$wpmem_themsg );
					$content = $content . wpmem_inc_login();
					break;

				default:
					$content = wpmem_inc_regmessage( $wpmem->regchk,$wpmem_themsg );
					$content = $content . wpmem_inc_registration();
					break;
				}

			} elseif ( $wpmem->action == 'pwdreset' ) {

				$content = wpmem_page_pwd_reset( $wpmem->regchk, $content );

			} elseif( $wpmem->action == 'getusername' ) {
				
				$content = wpmem_page_forgot_username( $wpmem->regchk, $content );
				
			} else {

				$content = ( $page == 'members-area' ) ? $content . wpmem_inc_login( 'members' ) : $content;
				$content = ( ( $page == 'register' || $wpmem->show_reg[ $post->post_type ] != 0 ) && ! $hide_register ) ? $content . wpmem_inc_registration() : $content;
			}

		} elseif ( is_user_logged_in() && $page == 'members-area' ) {

			/**
			 * Filter the default heading in User Profile edit mode.
			 *
			 * @since 2.7.5
			 *
			 * @param string The default edit mode heading.
			 */
			$heading = apply_filters( 'wpmem_user_edit_heading', __( 'Edit Your Information', 'wp-members' ) );

			switch( $wpmem->action ) {

			case "edit":
				$content = $content . wpmem_inc_registration( 'edit', $heading );
				break;

			case "update":

				// Determine if there are any errors/empty fields.

				if ( $wpmem->regchk == "updaterr" || $wpmem->regchk == "email" ) {

					$content = $content . wpmem_inc_regmessage( $wpmem->regchk, $wpmem_themsg );
					$content = $content . wpmem_inc_registration( 'edit', $heading );

				} else {

					//Case "editsuccess".
					$content = $content . wpmem_inc_regmessage( $wpmem->regchk, $wpmem_themsg );
					$content = $content . wpmem_inc_memberlinks();

				}
				break;

			case "pwdchange":

				$content = wpmem_page_pwd_reset( $wpmem->regchk, $content );
				break;

			case "renew":
				$content = wpmem_renew();
				break;

			default:
				$content = wpmem_inc_memberlinks();
				break;
			}

		} elseif ( is_user_logged_in() && $page == 'register' ) {

			$content = $content . wpmem_inc_memberlinks( 'register' );

		}

	}

	if ( $page == 'login' ) {
		$content = ( $wpmem->regchk == "loginfailed" ) ? wpmem_inc_loginfailed() : $content;
		$content = ( ! is_user_logged_in() ) ? $content . wpmem_inc_login( 'login', $redirect_to ) : wpmem_inc_memberlinks( 'login' );
	}

	if ( $page == 'password' ) {
		$content = wpmem_page_pwd_reset( $wpmem->regchk, $content );
	}

	if ( $page == 'user-edit' ) {
		$content = wpmem_page_user_edit( $wpmem->regchk, $content );
	}

	return $content;
} // End wpmem_do_sc_pages.
endif;

/**
 * Add WP-Members fields to the WP user profile screen.
 *
 * @since 2.1
 *
 * @global array $current_screen The WordPress screen object
 * @global int   $user_ID The user ID
 */
function wpmem_admin_fields() {

	global $current_screen, $user_ID, $wpmem;
	$user_id = ( $current_screen->id == 'profile' ) ? $user_ID : $_REQUEST['user_id']; ?>

	<h3><?php
	/**
	 * Filter the heading for additional profile fields.
	 *
	 * @since 2.8.2
	 *
	 * @param string The default additional fields heading.
	 */
	echo apply_filters( 'wpmem_admin_profile_heading', __( 'WP-Members Additional Fields', 'wp-members' ) ); ?></h3>   
 	<table class="form-table">
		<?php
		// Get fields.
		$wpmem_fields = wpmem_fields( 'admin_profile' );
		// Get excluded meta.
		$exclude = wpmem_get_excluded_meta( 'admin-profile' );

		/**
		 * Fires at the beginning of generating the WP-Members fields in the user profile.
		 *
		 * @since 2.9.3
		 *
		 * @param int   $user_id      The user's ID.
		 * @param array $wpmem_fields The WP-Members fields.
		 */
		do_action( 'wpmem_admin_before_profile', $user_id, $wpmem_fields );

		// Assemble form rows array.
		$rows = array();
		foreach ( $wpmem_fields as $meta => $field ) {

			$valtochk = ''; $values = '';

			// Determine which fields to show in the additional fields area.
			$show = ( ! $field['native'] && ! in_array( $meta, $exclude ) ) ? true : false;
			$show = ( $field['label'] == 'TOS' && $field['register'] ) ? null : $show;

			if ( $show ) {

				$val = get_user_meta( $user_id, $meta, true );
				$val = ( $field['type'] == 'multiselect' || $field['type'] == 'multicheckbox' ) ? $val : htmlspecialchars( $val );
				if ( $field['type'] == 'checkbox' ) {
					$valtochk = $val;
					$val = $field['checked_value'];
				}
				
				if ( 'multicheckbox' == $field['type'] || 'select' == $field['type'] || 'multiselect' == $field['type'] || 'radio' == $field['type'] ) {
					$values = $field['values'];
					$valtochk = $val;
				}
				
				// Is this an image or a file?
				if ( 'file' == $field['type'] || 'image' == $field['type'] ) {
					$attachment_url = wp_get_attachment_url( $val );
					$empty_file = '<span class="description">' . __( 'None' ) . '</span>';
					if ( 'file' == $field['type'] ) {
						$input = ( $attachment_url ) ? '<a href="' . $attachment_url . '">' . $attachment_url . '</a>' : $empty_file;
					} else {
						$input = ( $attachment_url ) ? '<img src="' . $attachment_url . '">' : $empty_file;
					}
					$input.= '<br />' . $wpmem->get_text( 'profile_upload' ) . '<br />';
					$input.= wpmem_form_field( array(
						'name'    => $meta, 
						'type'    => $field['type'], 
						'value'   => $val, 
						'compare' => $valtochk,
					) );
				} else {
					if ( 'select' == $field['type'] || 'radio' == $field['type'] ) {
						$input = wpmem_create_formfield( $meta, $field['type'], $values, $valtochk );
					} elseif( 'multicheckbox' == $field['type'] || 'multiselect' == $field['type'] ) {
						$input = $wpmem->forms->create_form_field( array( 'name'=>$meta, 'type'=>$field['type'], 'value'=>$values, 'compare'=>$valtochk, 'delimiter'=>$field['delimiter'] ) );
					} else {
						$field['type'] = ( 'hidden' == $field['type'] ) ? 'text' : $field['type'];
						$input = wpmem_create_formfield( $meta, $field['type'], $val, $valtochk );
					}
				}
				
				// Is the field required?
				$req = ( $field['required'] ) ? ' <span class="description">' . __( '(required)' ) . '</span>' : '';
				$label = '<label>' . __( $field['label'], 'wp-members' ) . $req . '</label>';
				
				// Build the form rows for filtering.
				$rows[ $meta ] = array(
					'meta'         => $meta,
					'type'         => $field['type'],
					'value'        => $val,
					'values'       => $values,
					'label_text'   => __( $field['label'], 'wp-members' ),
					'row_before'   => '',
					'label'        => $label,
					'field_before' => '',
					'field'        => $input,
					'field_after'  => '',
					'row_after'    => '',
				);
			}
		}
		
		/**
		 * Filter for rows
		 *
		 * @since 3.1.0
		 * @since 3.1.6 Deprecated $order.
		 *
		 * @param array  $rows {
		 *     An array of the profile rows.
		 *
		 *     @type string $meta         The meta key.
		 *     @type string $type         The field type.
		 *     @type string $value        Value if set.
		 *     @type string $values       Possible values (select, multiselect, multicheckbox, radio).
		 *     @type string $label_text   Raw label text (no HTML).
		 *     @type string $row_before   HTML before the row.
		 *     @type string $label        HTML label.
		 *     @type string $field_before HTML before the field input tag.
		 *     @type string $field        HTML for field input.
		 *     @type string $field_after  HTML after the field.
		 *     @type string $row_after    HTML after the row.
		 * }
		 * @param string $toggle
		 */
		$rows = apply_filters( 'wpmem_register_form_rows_admin', $rows, 'adminprofile' );
		
		// Handle form rows display from array.
		foreach ( $rows as $row ) {
			$show_field = '
				<tr>
					<th>' . $row['label'] . '</th>
					<td>' . $row['field'] . '</td>
				</tr>';

			/**
			 * Filter the profile field.
			 * 
			 * @since 2.8.2
			 * @since 3.1.1 Added $user_id and $row
			 *
			 * @param string $show_field The HTML string for the additional profile field.
			 * @param string $user_id
			 * @param array  $row
			 */
			echo apply_filters( 'wpmem_admin_profile_field', $show_field, $user_id, $row );
		}

		/**
		 * Fires after generating the WP-Members fields in the user profile.
		 *
		 * @since 2.9.3
		 *
		 * @param int   $user_id      The user's ID.
		 * @param array $wpmem_fields The WP-Members fields.
		 */
		do_action( 'wpmem_admin_after_profile', $user_id, $wpmem_fields ); ?>

	</table><?php
}


/**
 * Updates WP-Members fields from the WP user profile screen.
 *
 * @since 2.1
 *
 * @global object $wpmem
 */
function wpmem_admin_update() {

	$user_id = wpmem_get( 'user_id', false, 'request' ); //$_REQUEST['user_id'];
	
	if ( ! $user_id ) {
		// With no user id, no user can be updated.
		return;
	}
	
	global $wpmem;
	$wpmem_fields = wpmem_fields( 'admin_profile_update' );

	/**
	 * Fires before the user profile is updated.
	 *
	 * @since 2.9.2
	 *
	 * @param int   $user_id      The user ID.
	 * @param array $wpmem_fields Array of the custom fields.
	 */
	do_action( 'wpmem_admin_pre_user_update', $user_id, $wpmem_fields );

	$fields = array();
	$chk_pass = false;
	foreach ( $wpmem_fields as $meta => $field ) {
		if ( ! $field['native']
		  && $field['type'] != 'password' 
		  && $field['type'] != 'checkbox' 
		  && $field['type'] != 'multiselect' 
		  && $field['type'] != 'multicheckbox' 
		  && $field['type'] != 'file' 
		  && $field['type'] != 'image' ) {
			( isset( $_POST[ $meta ] ) ) ? $fields[ $meta ] = $_POST[ $meta ] : false;
		} elseif ( $meta == 'password' && $field['register'] ) {
			$chk_pass = true;
		} elseif ( $field['type'] == 'checkbox' ) {
			$fields[ $meta ] = ( isset( $_POST[ $meta ] ) ) ? $_POST[ $meta ] : '';
		} elseif ( $field['type'] == 'multiselect' || $field['type'] == 'multicheckbox' ) {
			$fields[ $meta ] = ( isset( $_POST[ $meta ] ) ) ? implode( $field['delimiter'], $_POST[ $meta ] ) : '';
		}
	}
	
	/**
	 * Filter the submitted field values for backend profile update.
	 *
	 * @since 2.8.2
	 *
	 * @param array $fields An array of the posted form values.
	 * @param int   $user_id The ID of the user being updated.
	 */
	$fields = apply_filters( 'wpmem_admin_profile_update', $fields, $user_id );

	// Get any excluded meta fields.
	$exclude = wpmem_get_excluded_meta( 'admin-profile' );
	foreach ( $fields as $key => $val ) {
		if ( ! in_array( $key, $exclude ) ) {
			update_user_meta( $user_id, $key, $val );
		}
	}
	
	if ( ! empty( $_FILES ) ) {
		$wpmem->user->upload_user_files( $user_id, $wpmem->fields );
	}	

	if ( $wpmem->mod_reg == 1 ) {

		$wpmem_activate_user = ( isset( $_POST['activate_user'] ) == '' ) ? -1 : $_POST['activate_user'];
		
		if ( $wpmem_activate_user == 1 ) {
			wpmem_a_activate_user( $user_id, $chk_pass );
		} elseif ( $wpmem_activate_user == 0 ) {
			wpmem_a_deactivate_user( $user_id );
		}
	}

	if ( defined( 'WPMEM_EXP_MODULE' ) && $wpmem->use_exp == 1 ) {
		if ( function_exists( 'wpmem_a_extenduser' ) ) {
			wpmem_a_extend_user( $user_id );
		}
	}

	/**
	 * Fires after the user profile is updated.
	 *
	 * @since 2.9.2
	 *
	 * @param int $user_id The user ID.
	 */
	do_action( 'wpmem_admin_after_user_update', $user_id );

	return;
}

if ( ! function_exists( 'wpmem_user_profile' ) ):
/**
 * add WP-Members fields to the WP user profile screen.
 *
 * @since 2.6.5
 *
 * @global int $user_id
 */
function wpmem_user_profile() {

	global $wpmem, $user_id, $current_screen;
	/**
	 * Filter the heading for the user profile additional fields.
	 *
	 * @since 2.9.1
	 *
	 * @param string The default heading.
	 */?>
	<h3><?php echo apply_filters( 'wpmem_user_profile_heading', __( 'Additional Information', 'wp-members' ) ); ?></h3>
	<table class="form-table">
		<?php
		// Get fields.
		$wpmem_fields = wpmem_fields( 'dashboard_profile' );
		// Get excluded meta.
		$exclude = wpmem_get_excluded_meta( 'user-profile' );

		$rows = array();
		foreach ( $wpmem_fields as $meta => $field ) {

			$valtochk = ''; $values = '';
			
			// Do we exclude the row?
			$chk_pass = ( in_array( $meta, $exclude ) ) ? false : true;

			if ( $field['register'] && ! $field['native'] && $chk_pass ) {
				
				$val = get_user_meta( $user_id, $meta, true );

				if ( $field['type'] == 'checkbox' ) {
					$valtochk = $val; 
					$val = $field['checked_value'];
				}
				
				if ( 'multicheckbox' == $field['type'] || 'select' == $field['type'] || 'multiselect' == $field['type'] || 'radio' == $field['type'] ) {
					$values = $field['values'];
					$valtochk = $val;
				}

				// Is this an image or a file?
				if ( 'file' == $field['type'] || 'image' == $field['type'] ) {
					$attachment_url = wp_get_attachment_url( $val );
					$empty_file = '<span class="description">' . __( 'None' ) . '</span>';
					if ( 'file' == $field['type'] ) {
						$input = ( $attachment_url ) ? '<a href="' . $attachment_url . '">' . $attachment_url . '</a>' : $empty_file;
					} else {
						$input = ( $attachment_url ) ? '<img src="' . $attachment_url . '">' : $empty_file;
					}
					$input.= '<br />' . $wpmem->get_text( 'profile_upload' ) . '<br />';
					$input.= wpmem_form_field( array(
						'name'    => $meta, 
						'type'    => $field['type'], 
						'value'   => $val, 
						'compare' => $valtochk,
					) );
				} else {
					if ( $meta == 'tos' && $val == 'agree' ) {
						$input = wpmem_create_formfield( $meta, 'hidden', $val );
					} elseif ( 'multicheckbox' == $field['type'] || 'select' == $field['type'] || 'multiselect' == $field['type'] || 'radio' == $field['type'] ) {
						$input = wpmem_create_formfield( $meta, $field['type'], $values, $valtochk );
					} else {
						$input = wpmem_create_formfield( $meta, $field['type'], $val, $valtochk );
					}
				}

				// If there are any required fields.
				$req = ( $field['required'] ) ? ' <span class="description">' . __( '(required)' ) . '</span>' : '';
				$label = '<label>' . __( $field['label'], 'wp-members' ) . $req . '</label>';
				
				// Build the form rows for filtering.
				$rows[ $meta ] = array(
					'type'         => $field['type'],
					'value'        => $val,
					'values'       => $values,
					'label_text'   => __( $field['label'], 'wp-members' ),
					'row_before'   => '',
					'label'        => $label,
					'field_before' => '',
					'field'        => $input,
					'field_after'  => '',
					'row_after'    => '',
				);
			}
		}
				
		/**
		 * Filter for rows
		 *
		 * @since 3.1.0
		 * @since 3.1.6 Deprecated $order and $meta.
		 *
		 * @param array  $rows {
		 *     An array of the profile rows.
		 *
		 *     @type string $type         The field type.
		 *     @type string $value        Value if set.
		 *     @type string $values       Possible values (select, multiselect, multicheckbox, radio).
		 *     @type string $label_text   Raw label text (no HTML).
		 *     @type string $row_before   HTML before the row.
		 *     @type string $label        HTML label.
		 *     @type string $field_before HTML before the field input tag.
		 *     @type string $field        HTML for field input.
		 *     @type string $field_after  HTML after the field.
		 *     @type string $row_after    HTML after the row.
		 * }
		 * @param string $toggle
		 */
		$rows = apply_filters( 'wpmem_register_form_rows_profile', $rows, 'userprofile' );
		
		foreach ( $rows as $row ) {
				
			$show_field = '
				<tr>
					<th>' . $row['label'] . '</th>
					<td>' . $row['field'] . '</td>
				</tr>';

			/**
			 * Filter the field for user profile additional fields.
			 *
			 * @since 2.9.1
			 * @since 3.1.1 Added $user_id and $row.
			 *
			 * @param string $show_field The HTML string of the additional field.
			 * @param int    $user_id
			 * @param array  $rows
			 */
			echo apply_filters( 'wpmem_user_profile_field', $show_field, $user_id, $row );
			
		} ?>
	</table><?php
}
endif;


/**
 * updates WP-Members fields from the WP user profile screen.
 *
 * @since 2.6.5
 *
 * @global int $user_id
 */
function wpmem_profile_update() {

	global $wpmem, $user_id;
	// Get the fields.
	$wpmem_fields = wpmem_fields( 'dashboard_profile_update' );
	// Get any excluded meta fields.
	$exclude = wpmem_get_excluded_meta( 'user-profile' );
	foreach ( $wpmem_fields as $meta => $field ) {
		// If this is not an excluded meta field.
		if ( ! in_array( $meta, $exclude ) ) {
			// If the field is user editable.
			if ( $field['register'] 
			  && $field['type'] != 'password' 
			  && $field['type'] != 'file' 
			  && $field['type'] != 'image' 
			  && ! $field['native'] ) {

				// Check for required fields.
				$chk = '';
				if ( ! $field['required'] ) {
					$chk = 'ok';
				}
				if ( $field['required'] && $_POST[ $meta ] != '' ) {
					$chk = 'ok';
				}

				// Check for field value.
				if ( $field['type'] == 'multiselect' || $field['type'] == 'multicheckbox' ) {
					$field_val = ( isset( $_POST[ $meta ] ) ) ? implode( '|', $_POST[ $meta ] ) : '';
				} else {
					$field_val = ( isset( $_POST[ $meta ] ) ) ? $_POST[ $meta ] : '';
				}

				if ( $chk == 'ok' ) {
					update_user_meta( $user_id, $meta, $field_val );
				}
			}
		}
	}
	
	if ( ! empty( $_FILES ) ) {
		$wpmem->user->upload_user_files( $user_id, $wpmem_fields );
	}	
}

/**
 * Sets user profile update to multipart form data.
 *
 * If the fields array has a file or image field, this will echo the 
 * necessary "multipart/form-data" enctype for the form tag.
 *
 * @since 3.1.8
 */
function wpmem_user_profile_multipart() {
	global $wpmem;
	$has_file = false;
	foreach ( wpmem_fields() as $field ) {
		if ( $field['type'] == 'file' || $field['type'] == 'image' ) {
			$has_file = true;
			break;
		}
	}
	echo ( $has_file ) ? " enctype=\"multipart/form-data\"" : '';
}