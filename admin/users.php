<?php
/**
 * WP-Members Admin Functions
 *
 * Functions to manage the Users > All Users page.
 * 
 * This file is part of the WP-Members plugin by Chad Butler
 * You can find out more about this plugin at https://rocketgeek.com
 * Copyright (c) 2006-2018  Chad Butler
 * WP-Members(tm) is a trademark of butlerblog.com
 *
 * @package WP-Members
 * @author Chad Butler
 * @copyright 2006-2018
 *
 * Functions included:
 * - wpmem_bulk_user_action
 * - wpmem_insert_activate_link
 * - wpmem_users_page_load
 * - wpmem_users_admin_notices
 * - wpmem_users_views
 * - wpmem_add_user_column
 * - wpmem_add_user_column_content
 * - wpmem_activate_user
 * - wpmem_deactivate_user
 * - wpmem_a_pre_user_query
 * - wpmem_set_new_user_non_active
 * - wpmem_set_activated_user
 * - wpmem_set_deactivated_user
 * - wpmem_set_user_status
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

/**
 * Function to add activate/export to the bulk dropdown list.
 *
 * @since 2.8.2
 */
function wpmem_bulk_user_action() {
	global $wpmem; ?>
	<script type="text/javascript">
		var $j = jQuery.noConflict();
		$j(document).ready(function() {
	<?php if( $wpmem->mod_reg == 1 ) { ?>
		$j('<option>').val('activate').text('<?php _e( 'Activate', 'wp-members' )?>').appendTo("select[name='action']");
		$j('<option>').val('deactivate').text('<?php _e( 'Deactivate', 'wp-members' )?>').appendTo("select[name='action']");
	<?php } ?>
		$j('<option>').val('export').text('<?php _e( 'Export', 'wp-members' )?>').appendTo("select[name='action']");
	<?php if( $wpmem->mod_reg == 1 ) { ?>
		$j('<option>').val('activate').text('<?php _e( 'Activate', 'wp-members' )?>').appendTo("select[name='action2']");
		$j('<option>').val('deactivate').text('<?php _e( 'Deactivate', 'wp-members' )?>').appendTo("select[name='action2']");
	<?php } ?>
		$j('<option>').val('export').text('<?php _e( 'Export', 'wp-members' )?>').appendTo("select[name='action2']");
		$j('<input id="export_all" name="export_all" class="button action" type="submit" value="<?php _e( 'Export All Users', 'wp-members' ); ?>" />').appendTo(".bottom .bulkactions");
	});
	</script><?php
}

/**
 * Function to add activate link to the user row action.
 *
 * @since 2.8.2
 *
 * @param  array $actions
 * @param  $user_object
 * @return array $actions
 */
function wpmem_insert_activate_link( $actions, $user_object ) {
	global $wpmem;
	if ( 1 == $wpmem->mod_reg ) {

		$var = get_user_meta( $user_object->ID, 'active', true );

		if ( $var != 1 ) {
			$action = 'activate';
			$term   = __( 'Activate', 'wp-members' );
		} elseif ( 1 == $var ) {
			$action = 'deactivate';
			$term   = __( 'Deactivate', 'wp-members' );
		}
		$url = add_query_arg( array( 'action' => $action . '-single', 'user' => $user_object->ID ), "users.php" );
		$url = wp_nonce_url( $url, 'activate-user' );
		$actions[ $action ] = '<a href="' . $url . '">' . $term . '</a>';
	}
	return $actions;
}

/**
 * Function to handle bulk actions at page load.
 *
 * @since 2.8.2
 *
 * @uses WP_Users_List_Table
 *
 * @global object $wpmem
 */
function wpmem_users_page_load() {
	
	global $wpmem;
	if ( current_user_can( 'list_users' ) ) {
		$wpmem->admin->user_search = new WP_Members_Admin_User_Search();
	}

	// If exporting all users, do it, then exit.
	if ( isset( $_REQUEST['export_all'] ) && $_REQUEST['export_all'] == __( 'Export All Users', 'wp-members' ) ) {
		include_once( WPMEM_PATH . 'admin/user-export.php' );
		$today = date( "m-d-y" ); 
		wpmem_export_users( array( 'export'=>'all', 'filename'=>'user-export-' . $today . '.csv' ), '' );
		exit();
	}

	$wp_list_table = _get_list_table( 'WP_Users_List_Table' );
	$action = $wp_list_table->current_action();
	$sendback = '';

	if ( $action == 'activate' || 'activate-single' ) {
		// Find out if we need to set passwords.
		$chk_pass = false;
		$wpmem_fields = wpmem_fields();
		foreach ( $wpmem_fields as $field ) {
			if ( $field['type'] == 'password' && $field['register'] ) {
				$chk_pass = true;
				break;
			}
		}
	}

	switch ( $action ) {

	case 'activate':
	case 'deactivate':

		// Validate nonce.
		check_admin_referer( 'bulk-users' );

		// Get the users.
		if ( isset( $_REQUEST['users'] ) ) {
			
			$users = $_REQUEST['users'];
	
			// Update the users.
			$x = 0;
			foreach ( $users as $user ) {
				$user = filter_var( $user, FILTER_VALIDATE_INT );
				// Check to see if the user is already activated, if not, activate.
				if ( 'activate' == $action && 1 != get_user_meta( $user, 'active', true ) ) {
					wpmem_activate_user( $user, $chk_pass );
				} elseif( 'deactivate' == $action ) {
					wpmem_deactivate_user( $user );
				}
				
				$x++;
			}
			$msg = ( 'activate' == $action ) ? urlencode( sprintf( __( '%s users activated', 'wp-members' ), $x ) ) : urlencode( sprintf( __( '%s users deactivated', 'wp-members' ), $x ) );
		
		} else {
			$msg = urlencode( __( 'No users selected', 'wp-members' ) );
		}

		// Set the return message.
		$sendback = add_query_arg( array( 'activated' => $msg ), $sendback );
		break;

	case 'activate-single':
	case 'deactivate-single':

		// Validate nonce.
		check_admin_referer( 'activate-user' );

		// Get the users.
		$users = $_REQUEST['user'];

		// Check to see if the user is already activated, if not, activate.
		if ( 'activate-single' == $action && 1 != get_user_meta( $users, 'active', true ) ) {
			wpmem_activate_user( $users, $chk_pass );
			$user_info = get_userdata( $users );
			$msg = urlencode( sprintf( __( "%s activated", 'wp-members' ), $user_info->user_login ) );
		
		} elseif ( 'deactivate-single' == $action ) {
			wpmem_deactivate_user( $users );
			$user_info = get_userdata( $users );
			$msg = urlencode( sprintf( __( "%s deactivated", 'wp-members' ), $user_info->user_login ) );
			
		} else {
			// Set the return message.
			$msg = urlencode( __( "That user is already active", 'wp-members' ) );
		}
		$sendback = add_query_arg( array( 'activated' => $msg ), $sendback );
		break;

	case 'show':
		
		add_action( 'pre_user_query', 'wpmem_a_pre_user_query' );
		return;
		break;

	case 'export':

		$users  = wpmem_get( 'users', false, 'request' );
		$sanitized_users = array();
		foreach ( $users as $user ) {
			$sanitized_users[] = filter_var( $user, FILTER_VALIDATE_INT );
		}
		include_once( WPMEM_PATH . 'admin/user-export.php' );
		wpmem_export_users( array( 'export'=>'selected' ), $sanitized_users );
		return;
		break;

	default:
		return;
		break;

	}

	// If we did not return already, we need to wp_redirect.
	wp_redirect( $sendback );
	exit();

}

/**
 * Function to echo admin update message.
 *
 * @since 2.8.2
 */
function wpmem_users_admin_notices() {

	global $pagenow, $user_action_msg;
	 if( $pagenow == 'users.php' && isset( $_REQUEST['activated'] ) ) {
		$message = esc_html( $_REQUEST['activated'] );
		echo "<div class=\"updated\"><p>{$message}</p></div>";
	}

	if ( $user_action_msg ) {
		echo "<div class=\"updated\"><p>{$user_action_msg}</p></div>";
	}
}

/**
 * Function to add user views to the top list.
 *
 * @since 2.8.2
 * @since 3.1.2 Added user view counts as transient.
 *
 * @global object $wpdb
 * @global object $wpmem
 * @param  array  $views
 * @return array  $views
 */
function wpmem_users_views( $views ) {
	
	global $wpmem;
	
	// Get the cached user counts.
	$user_counts = get_transient( 'wpmem_user_counts' );
	
	// check to see if data was successfully retrieved from the cache
	if ( false === $user_counts ) {
		
		// @todo For now, 30 seconds.  We'll see how things go.
		$transient_expires = 30; // Value in seconds, 1 day: ( 60 * 60 * 24 );

		global $wpdb;
		
		// We need a count of total users.
		// @todo - need a more elegant way of this entire process.
		$sql = "SELECT COUNT(*) FROM " . $wpdb->users;
		$users = $wpdb->get_var( $sql );

		// What needs to be counted?		
		$count_metas = array();
		if ( defined( 'WPMEM_EXP_MODULE' ) && $wpmem->use_exp == 1 ) {
			$count_metas['pending'] = 'pending';
		}
		if ( $wpmem->use_trial == 1 ) {
			$count_metas['trial'] = 'trial';
		}
		if ( defined( 'WPMEM_EXP_MODULE' ) && $wpmem->use_exp == 1 ) {
			$count_metas['subscription'] = 'subscription';
			$count_metas['expired'] = 'expired';
		}
		if ( $wpmem->mod_reg == 1 ) {
			$count_metas['notactive'] = 'active';
			$count_metas['deactivated'] = 'deactivated';
		}
		$count_metas['notexported'] = 'exported';
		
		// Handle various counts.
		$user_counts = array();
		foreach ( $count_metas as $key => $meta_key ) {
			if ( 'notactive' == $key || 'notexported' == $key ) {
				$users_with_meta = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->usermeta WHERE meta_key='$meta_key' AND meta_value=1" );
				$count = $users - $users_with_meta;
			}
			if ( 'deactivated' == $key ) {
				$count = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->usermeta WHERE meta_key = 'active' AND meta_value = 0" );
			}
			if ( 'trial' == $key || 'subscription' == $key || 'pending' == $key ) {
				$count = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->usermeta WHERE meta_key = 'exp_type' AND meta_value = \"$key\"" );
			}
			if ( 'expired' == $key ) {
				$count = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->usermeta WHERE meta_key = 'expires' AND STR_TO_DATE( meta_value, '%m/%d/%Y' ) < CURDATE() AND meta_value != '01/01/1970'" );
			}
			$user_counts[ $key ] = $count;
		}
		set_transient( 'wpmem_user_counts', $user_counts, $transient_expires );
	}

	$arr = array();
	if ( defined( 'WPMEM_EXP_MODULE' ) && $wpmem->use_exp == 1 ) {
		$arr['pending']      = __( 'Pending',       'wp-members' );
		$arr['trial']        = __( 'Trial',         'wp-members' );
		$arr['subscription'] = __( 'Subscription',  'wp-members' );
		$arr['expired']      = __( 'Expired',       'wp-members' );
	}
	if ( $wpmem->mod_reg == 1 ) {
		$arr['notactive']    = __( 'Not Activated', 'wp-members' );
		$arr['deactivated']  = __( 'Deactivated',   'wp-members' );
	}
	$arr['notexported']      = __( 'Not Exported',  'wp-members' );
	$show = sanitize_text_field( wpmem_get( 'show', false, 'get' ) );

	foreach ( $arr as $key => $val ) {
		$link = "users.php?action=show&amp;show=" . $key;
		$curr = ( $show == $key ) ? ' class="current"' : '';
		$views[$key] = sprintf(
				'<a href="%s" %s>%s <span class="count">(%d)</span></a>',
				esc_url( $link ),
				$curr,
				$val,
				isset( $user_counts[ $key ] ) ? $user_counts[ $key ] : ''
		   );
	}

	return $views;
}

/**
 * Function to add custom user columns to the user table.
 *
 * @since 2.8.2
 *
 * @param  array $columns
 * @return array $columns
 */
function wpmem_add_user_column( $columns ) {

	global $wpmem_user_columns, $wpmem;

	// Get any columns to be added to the Users > All Users screen.
	$wpmem_user_columns = get_option( 'wpmembers_utfields' );

	if ( $wpmem_user_columns ) {
		if ( $wpmem->mod_reg != 1 ) {
			unset( $wpmem_user_columns['active'] );
		}

		$columns = array_merge( $columns, $wpmem_user_columns );
	}
	
	// Makes WP-Members columns sortable.
	// @todo - finish debugging class or add sortable functions to users.php.
	// require_once( WPMEM_PATH . 'admin/includes/class-wp-members-sortable-user-columns.php' );
	// new WP_Members_Sortable_User_Columns( $wpmem_user_columns );

	return $columns;
} 

/**
 * Function to add the user content to the custom column.
 *
 * @since 2.8.2
 * 
 * @param  $value
 * @param  $column_name
 * @param  $user_id
 * @return The user value for the custom column.
 */
function wpmem_add_user_column_content( $value, $column_name, $user_id ) {

	// Is the column a WP-Members column?
	global $wpmem_user_columns, $wpmem;
	$is_wpmem = ( is_array( $wpmem_user_columns ) && array_key_exists( $column_name, $wpmem_user_columns ) ) ? true : false;

	if ( $is_wpmem ) {
	
		switch ( $column_name ) {
		
		case 'active':
			if ( $wpmem->mod_reg == 1 ) {
			/*
			 * If the column is "active", then return the value or empty.
			 * Returning in here keeps us from displaying another value.
			 */
				return ( get_user_meta( $user_id , 'active', 'true' ) != 1 ) ? __( 'No', 'wp-members' ) : '';
			} else {
				return;
			}
			break;

		case 'user_url':
		case 'user_registered':
			// Unlike other fields, website/url is not a meta field.
			$user_info = get_userdata( $user_id );
			return $user_info->$column_name;
			break;

		case 'user_id':
			return $user_id;

		default:
			return get_user_meta( $user_id, $column_name, true );
			break;
		}

	}

	return $value;
}

/**
 * Activates a user.
 *
 * If registration is moderated, sets the activated flag 
 * in the usermeta. Flag prevents login when $wpmem->mod_reg
 * is true (1). Function is fired from bulk user edit or
 * user profile update.
 *
 * @since 2.4
 * @since 3.1.6 Dependencies now loaded by object.
 * @since 3.2.4 Renamed from wpmem_a_activate_user().
 *
 * @param int   $user_id
 * @param bool  $chk_pass
 * @uses  $wpdb WordPress Database object.
 */
function wpmem_activate_user( $user_id, $chk_pass = false ) {

	global $wpmem;

	// Define new_pass.
	$new_pass = '';

	// If passwords are user defined skip this.
	if ( ! $chk_pass ) {
		// Generates a password to send the user.
		$new_pass = wp_generate_password();
		$new_hash = wp_hash_password( $new_pass );

		// Update the user with the new password.
		global $wpdb;
		$wpdb->update( $wpdb->users, array( 'user_pass' => $new_hash ), array( 'ID' => $user_id ), array( '%s' ), array( '%d' ) );
	}

	// If subscriptions can expire, and the user has no expiration date, set one.
	if ( $wpmem->use_exp == 1 && ! get_user_meta( $user_id, 'expires', true ) ) {
		if ( function_exists( 'wpmem_set_exp' ) ) {
			wpmem_set_exp( $user_id );
		}
	}

	// Generate and send user approved email to user.
	$wpmem->email->to_user( $user_id, $new_pass, 2 );

	// Set the active flag in usermeta.
	update_user_meta( $user_id, 'active', 1 );

	/**
	 * Fires after the user activation process is complete.
	 *
	 * @since 2.8.2
	 *
	 * @param int $user_id The user's ID.
	 */
	do_action( 'wpmem_user_activated', $user_id );

	return;
}

/**
 * Deactivates a user.
 *
 * Reverses the active flag from the activation process
 * preventing login when registration is moderated.
 *
 * @since 2.7.1
 * @since 3.2.4 Renamed from wpmem_a_deactivate_user().
 *
 * @param int $user_id
 */
function wpmem_deactivate_user( $user_id ) {
	update_user_meta( $user_id, 'active', 0 );

	/**
	 * Fires after the user deactivation process is complete.
	 *
	 * @since 2.9.9
	 *
	 * @param int $user_id The user's ID.
	 */
	do_action( 'wpmem_user_deactivated', $user_id );
}

/**
 * Adjusts user query based on custom views.
 *
 * @since 2.8.3
 *
 * @todo Currently, not activated query returns users who are deactivated. This
 *       may be confusing for admins, so work on a query that displays only
 *       users who have never been activated.
 *
 * @param $user_search
 */
function wpmem_a_pre_user_query( $user_search ) {

	global $wpdb;
	$show = sanitize_text_field( wpmem_get( 'show', '', 'get' ) );
	switch ( $show ) {

		case 'notactive':
		case 'notexported':
		case 'deactivated':
			$key = ( 'notactive' == $show || 'deactivated' == $show  ) ? 'active' : 'exported';
			$in  = ( 'deactivated' == $show ) ? 'IN' : 'NOT IN';
			$val = ( 'deactivated' == $show ) ? '0'  : '1';
			$replace_query = "WHERE 1=1 AND {$wpdb->users}.ID " . esc_sql( $in ) . " (
			 SELECT {$wpdb->usermeta}.user_id FROM $wpdb->usermeta 
				WHERE {$wpdb->usermeta}.meta_key = \"" . esc_sql( $key ) . "\"
				AND {$wpdb->usermeta}.meta_value = \"" . esc_sql( $val ) . "\" )";
			break;

		case 'trial':
		case 'subscription':
		case 'pending':
			$replace_query = "WHERE 1=1 AND {$wpdb->users}.ID IN (
			 SELECT {$wpdb->usermeta}.user_id FROM $wpdb->usermeta 
				WHERE {$wpdb->usermeta}.meta_key = 'exp_type'
				AND {$wpdb->usermeta}.meta_value = \"" . esc_sql( $show ) . "\" )";
			break;

		case 'expired':
			$replace_query = "WHERE 1=1 AND {$wpdb->users}.ID IN (
			 SELECT {$wpdb->usermeta}.user_id FROM $wpdb->usermeta 
				WHERE {$wpdb->usermeta}.meta_key = 'expires'
				AND STR_TO_DATE( {$wpdb->usermeta}.meta_value, '%m/%d/%Y' ) < CURDATE()
				AND {$wpdb->usermeta}.meta_value != '01/01/1970' )";
			break;
	}

	$user_search->query_where = str_replace( 'WHERE 1=1', $replace_query, $user_search->query_where );
}

/**
 * Use wpmem_post_register_data to set the user_status field to 2 using wp_update_user.
 * http://codex.wordpress.org/Function_Reference/wp_update_user
 *
 * @uses  wpmem_set_user_status
 * @param $fields
 */
function wpmem_set_new_user_non_active( $fields ) {
	wpmem_set_user_status( $fields['ID'], 2 );
	return;
}

/**
 * Use wpmem_user_activated to set the user_status field to 0 using wp_update_user.
 *
 * @uses  wpmem_set_user_status
 * @param $user_id
 */
function wpmem_set_activated_user( $user_id ) {
	wpmem_set_user_status( $user_id, 0 );
	return;
}

/**
 * Use wpmem_user_deactivated to set the user_status field to 2 using wp_update_user.
 *
 * @uses  wpmem_set_user_status
 * @param $user_id
 */
function wpmem_set_deactivated_user( $user_id ) {
	wpmem_set_user_status( $user_id, 2 );
	return;
}

/**
 * Updates the user_status value in the wp_users table.
 *
 * @param $user_id
 * @param $status
 */
function wpmem_set_user_status( $user_id, $status ) {
	global $wpdb;
	$wpdb->update( $wpdb->users, array( 'user_status' => $status ), array( 'ID' => $user_id ) );
	return;
}

// End of file.