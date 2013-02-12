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
 * @global array $current_screen The WordPress screen object
 * @global int   $user_ID The user ID
 */
function wpmem_admin_fields()
{
	global $current_screen, $user_ID;
	$user_id = ( $current_screen->id == 'profile' ) ? $user_ID : $_REQUEST['user_id']; ?>

	<h3><?php _e( 'WP-Members Additional Fields', 'wp-members' ); ?></h3>   
 	<table class="form-table">
		<?php
		$wpmem_fields = get_option( 'wpmembers_fields' );
		for( $row = 0; $row < count( $wpmem_fields ); $row++ ) {

			/** determine which fields to show in the additional fields area */	
			$show = ( $wpmem_fields[$row][6] == 'n' && $wpmem_fields[$row][2] != 'password' ) ? true : false;
			$show = ( $wpmem_fields[$row][1] == 'TOS' && $wpmem_fields[$row][4] != 'y' ) ? null : $show;
			
			if( $show ) { ?>  

				<tr>
					<th><label><?php echo $wpmem_fields[$row][1]; ?></label></th>
					<td><?php
						$val = htmlspecialchars( get_user_meta( $user_id, $wpmem_fields[$row][2], 'true' ) );
						if( $wpmem_fields[$row][3] == 'checkbox' || $wpmem_fields[$row][3] == 'select' ) {
							$valtochk = $val; 
							$val = $wpmem_fields[$row][7];
						}
						echo wpmem_create_formfield( $wpmem_fields[$row][2], $wpmem_fields[$row][3], $val, $valtochk );
						$valtochk = ''; // empty for the next field in the loop
					?></td>
				</tr>

			<?php } 

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
 */
function wpmem_admin_update()
{
	$user_id = $_REQUEST['user_id'];	
	$wpmem_fields = get_option( 'wpmembers_fields' );
	for( $row = 0; $row < count( $wpmem_fields ); $row++ ) {
		if( $wpmem_fields[$row][6] == "n" && $wpmem_fields[$row][2] != 'password' ) {
			//update_user_meta( $user_id, $wpmem_fields[$row][2], $_POST[$wpmem_fields[$row][2]] );
			( isset( $_POST[$wpmem_fields[$row][2]] ) ) ? update_user_meta( $user_id, $wpmem_fields[$row][2], $_POST[$wpmem_fields[$row][2]] ) : false;
		}
	}

	if( WPMEM_MOD_REG == 1 ) {
	
		// $wpmem_activate_user = $_POST['activate_user'];
		// if( $wpmem_activate_user == '' ) { $wpmem_activate_user = -1; }
		$wpmem_activate_user = ( isset( $_POST['activate_user'] ) == '' ) ? $wpmem_activate_user = -1 : $_POST['activate_user'];
		
		if( $wpmem_activate_user == 1 ) {
			wpmem_a_activate_user( $user_id, $chk_pass );
		} elseif( $wpmem_activate_user == 0 ) {
			wpmem_a_deactivate_user( $user_id );
		}
	}

	if( WPMEM_USE_EXP == 1 ) { wpmem_a_extend_user( $user_id ); }
}
?>