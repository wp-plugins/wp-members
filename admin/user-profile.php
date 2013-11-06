<?php
/**
 * WP-Members Admin Functions
 *
 * Functions to manage the user profile screen.
 * 
 * This file is part of the WP-Members plugin by Chad Butler
 * You can find out more about this plugin at http://rocketgeek.com
 * Copyright (c) 2006-2013  Chad Butler (email : plugins@butlerblog.com)
 * WP-Members(tm) is a trademark of butlerblog.com
 *
 * @package WordPress
 * @subpackage WP-Members
 * @author Chad Butler
 * @copyright 2006-2013
 */

 
/** Actions */
add_action( 'show_user_profile', 'wpmem_admin_fields' );
add_action( 'edit_user_profile', 'wpmem_admin_fields' );
add_action( 'profile_update',    'wpmem_admin_update' );


/**
 * add WP-Members fields to the WP user profile screen
 *
 * @since 2.1
 *
 * @uses apply_filters Calls wpmem_admin_profile_field
 & @uses apply_filters Calls wpmem_admin_profile_heading
 *
 * @global array $current_screen The WordPress screen object
 * @global int   $user_ID The user ID
 */
function wpmem_admin_fields()
{
	global $current_screen, $user_ID;
	$user_id = ( $current_screen->id == 'profile' ) ? $user_ID : $_REQUEST['user_id']; ?>

	<h3><?php echo apply_filters( 'wpmem_admin_profile_heading', __( 'WP-Members Additional Fields', 'wp-members' ) ); ?></h3>   
 	<table class="form-table">
		<?php
		$wpmem_fields = get_option( 'wpmembers_fields' ); $valtochk = '';
		for( $row = 0; $row < count( $wpmem_fields ); $row++ ) {

			/** determine which fields to show in the additional fields area */	
			$show = ( $wpmem_fields[$row][6] == 'n' && $wpmem_fields[$row][2] != 'password' ) ? true : false;
			$show = ( $wpmem_fields[$row][1] == 'TOS' && $wpmem_fields[$row][4] != 'y' ) ? null : $show;
			
			if( $show ) {   
				$show_field = '
					<tr>
						<th><label>' . $wpmem_fields[$row][1] . '</label></th>
						<td>';
				$val = htmlspecialchars( get_user_meta( $user_id, $wpmem_fields[$row][2], 'true' ) );
				if( $wpmem_fields[$row][3] == 'checkbox' || $wpmem_fields[$row][3] == 'select' ) {
					$valtochk = $val; 
					$val = $wpmem_fields[$row][7];
				}
				$show_field.=  wpmem_create_formfield( $wpmem_fields[$row][2], $wpmem_fields[$row][3], $val, $valtochk ) . '
						</td>
					</tr>';
				$valtochk = ''; // empty for the next field in the loop

				echo apply_filters( 'wpmem_admin_profile_field', $show_field );
			}
		}

		// see if reg is moderated, and if the user has been activated
		if( WPMEM_MOD_REG == 1 ) { 
			$user_active_flag = get_user_meta( $user_id, 'active', 'true' );
			switch( $user_active_flag ) {
			
				case '':
					$label  = __( 'Activate this user?', 'wp-members' );
					$action = 1;
					break;
				
				case 0: 
					$label  = __( 'Reactivate this user?', 'wp-members' );
					$action = 1;
					break;
				
				case 1:
					$label  = __( 'Deactivate this user?', 'wp-members' );
					$action = 0;
					break;
				
			}?>

			<tr>
				<th><label><?php echo $label; ?></label></th>
				<td><input id="activate_user" type="checkbox" class="input" name="activate_user" value="<?php echo $action; ?>" /></td>
			</tr>

		<?php }  

		// if using subscription model, show expiration
		// if registration is moderated, this doesn't show if user is not active yet.
		if( WPMEM_USE_EXP == 1 ) {
			if( ( WPMEM_MOD_REG == 1 &&  get_user_meta( $user_id, 'active', 'true' ) == 1 ) || ( WPMEM_MOD_REG != 1 ) ) { 
				wpmem_a_extenduser( $user_id );
			} 
		} ?>
		<tr>
			<th><label><?php _e( 'IP @ registration', 'wp-members' ); ?></label></th>
			<td><?php echo get_user_meta( $user_id, 'wpmem_reg_ip', 'true' ); ?></td>
		</tr>
	</table><?php
}


/**
 * updates WP-Members fields from the WP user profile screen
 *
 * @since 2.1
 *
 * @uses apply_filters Calls wpmem_admin_profile_update
 */
function wpmem_admin_update()
{
	$user_id = $_REQUEST['user_id'];	
	$wpmem_fields = get_option( 'wpmembers_fields' );
	$fields = array();
	$chk_pass = false;
	for( $row = 0; $row < count( $wpmem_fields ); $row++ ) {
		if( $wpmem_fields[$row][6] == "n" && $wpmem_fields[$row][2] != 'password' && $wpmem_fields[$row][3] != 'checkbox' ) {
			( isset( $_POST[$wpmem_fields[$row][2]] ) ) ? $fields[$wpmem_fields[$row][2]] = $_POST[$wpmem_fields[$row][2]] : false;
		} elseif( $wpmem_fields[$row][2] == 'password' ) {
			$chk_pass = true;
		} elseif( $wpmem_fields[$row][3] == 'checkbox' ) {
			( isset( $_POST[$wpmem_fields[$row][2]] ) ) ? $fields[$wpmem_fields[$row][2]] = $_POST[$wpmem_fields[$row][2]] : $fields[$wpmem_fields[$row][2]] = '';
		}
	}
	
	$fields = apply_filters( 'wpmem_admin_profile_update', $fields ); 
	
	foreach( $fields as $key => $val ) {
		update_user_meta( $user_id, $key, $val );
	}

	if( WPMEM_MOD_REG == 1 ) {

		$wpmem_activate_user = ( isset( $_POST['activate_user'] ) == '' ) ? $wpmem_activate_user = -1 : $_POST['activate_user'];
		
		if( $wpmem_activate_user == 1 ) {
			wpmem_a_activate_user( $user_id, $chk_pass );
		} elseif( $wpmem_activate_user == 0 ) {
			wpmem_a_deactivate_user( $user_id );
		}
	}

	( WPMEM_USE_EXP == 1 ) ? wpmem_a_extend_user( $user_id ) : '';
}

/** End of File **/