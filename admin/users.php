<?php
/**
 * WP-Members Admin Functions
 *
 * Functions to manage the bulk user management page.
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


/**
 * User management panel
 *
 * Creates the bulk user management panel for the
 * Users > WP-Members page.
 *
 * @since 2.4
 */
function wpmem_admin_users()
{	
	// define variables
	$col_phone = ''; $col_country = ''; $user_action_msg = '';
	
	// check to see if we need phone and country columns
	$wpmem_fields = get_option( 'wpmembers_fields' );
	for( $row = 0; $row < count( $wpmem_fields ); $row++ )
	{ 
		if( $wpmem_fields[$row][2] == 'country' && $wpmem_fields[$row][4] == 'y' ) { $col_country = true; }
		if( $wpmem_fields[$row][2] == 'phone1' && $wpmem_fields[$row][4] == 'y' ) { $col_phone = true; }
	}
	
	// should run other checks for expiration, activation, etc...
	
	// here is where we handle actions on the table...
	
	if( $_POST ) { 

		$action = ( isset( $_POST['action'] ) ) ? $_POST['action'] : false;
		$users  = ( isset( $_POST['users'] ) ) ? $_POST['users'] : false;

		switch( $action ) {
		
		case "activate":
			// find out if we need to set passwords
			$wpmem_fields = get_option( 'wpmembers_fields' );
			for ( $row = 0; $row < count( $wpmem_fields ); $row++ ) {
				//if( $wpmem_fields[$row][2] == 'password' ) { $chk_pass = true; }
				$chk_pass = ( $wpmem_fields[$row][2] == 'password' ) ? true : false;
			}
			$x = 0;
			foreach( $users as $user ) {
				// check to see if the user is already activated, if not, activate
				if( ! get_user_meta( $user, 'active', true ) ) {
					wpmem_a_activate_user( $user, $chk_pass );
					$x++;
				}
			}
			$user_action_msg = sprintf( __( '%d users were activated.', 'wp-members' ), $x );
			break;
			
		case "export":
			update_option( 'wpmembers_export', $users );
			$user_action_msg = sprintf( __( 'Users ready to export, %s click here %s to generate and download a CSV.', 'wp-members' ),  '<a href="' . //WP_PLUGIN_URL . '/wp-members/wp-members-export.php" target="_blank">', '</a>' );
			WPMEM_DIR . '/admin/export.php" target="_blank">', '</a>' );
			break;
		
		}
		
	} ?>

	<div class="wrap">

		<div id="icon-users" class="icon32"><br /></div>
		<h2><?php _e( 'WP-Members Users', 'wp-members' ); ?>  <a href="user-new.php" class="button add-new-h2"><?php _e( 'Add New', 'wp-members' ); ?></a></h2>
		
	<?php if( $user_action_msg ) { ?>

		<div id="message" class="updated fade"><p><strong><?php echo $user_action_msg; ?></strong></p></div>

	<?php } ?>

		<form id="posts-filter" action="<?php echo $_SERVER['REQUEST_URI']?>" method="post">
	
		<div class="filter">
			<ul class="subsubsub">
			
			<?php
			
			// For now, I don't see a good way of working this for localization without a 
			// huge amount of additional programming (like a multi-dimensional array)
			$show = $lcas = $curr = '';
			$tmp  = array( "All", "Not Active", "Trial", "Subscription", "Expired", "Not Exported" );
			$show = ( isset( $_GET['show'] ) ) ? $_GET['show'] : false; //if( isset( $_GET['show'] ) ) { $show = $_GET['show']; }
			
			for( $row = 0; $row < count( $tmp ); $row++ )
			{
				
				$link = "users.php?page=wpmem-users";
				if( $row != 0 ) {
				
					$lcas = strtolower( $tmp[$row] );
					$lcas = str_replace( " ", "", $lcas );
					$link.= "&#038;show=";
					$link.= $lcas;
					
					$curr = "";
						$curr = ( $show == $lcas ) ? ' class="current"' : ''; //if( $show == $lcas ) { $curr = ' class="current"'; }
					
				} else {
				
					$curr = ( ! $show ) ? ' class="current"' : ''; //if( ! $show ) { $curr = ' class="current"'; }
					
				}
				
				$end = "";
				if( $row != 5 ) { $end = " |"; }

				$echolink = true;
				if( $lcas == "notactive" && WPMEM_MOD_REG != 1 ) { $echolink = false; }
				if( $lcas == "trial"     && WPMEM_USE_TRL != 1 ) { $echolink = false; }
				
				if( ( $lcas == "subscription" || $lcas == "expired" ) && WPMEM_USE_EXP != 1 ) { $echolink = false; }
				
				if( $echolink ) { echo "<li><a href=\"$link\"$curr>$tmp[$row] <span class=\"count\"></span></a>$end</li>"; }
			}

			?>
			</ul>
		</div>

		<?php // NOT YET... ?><!--
			<p class="search-box">
				<label class="screen-reader-text" for="user-search-input">Search Users:</label>
				<input type="text" id="user-search-input" name="usersearch" value="" />

				<input type="submit" value="Search Users" class="button" />
			</p>-->
	<?php
	
		// done with the action items, now build the page
		$users_per_page = 10;

		// workout the different queries, etc...
		if( ! $show ) {

			$result = count_users();

			/* if( isset( $_REQUEST['paged'] ) ) { 
				$paged = $_REQUEST['paged']; 
			} else {
				$paged = 1;
			} */
			$paged = ( isset( $_REQUEST['paged'] ) ) ? $_REQUEST['paged'] : 1;

			$arr = array(
				'show' => 'Total Users',
				'show_num' => $users_per_page,
				'total_users' =>  $result['total_users'],
				'pages' => ( ceil( ( $result['total_users'] ) / $users_per_page ) ),
				'paged' => $paged,
				'link' => "users.php?page=wpmem-users"
				);

			/* if( $paged == 1 ) { 
				$offset = 0;
			} else {
				$offset = ( ( $arr['paged'] * $users_per_page ) - $users_per_page );
			} */
			$offset = ( $paged == 1 ) ? 0 : ( ( $arr['paged'] * $users_per_page ) - $users_per_page );
			
			$args = array(
				'offset' => $offset,
				'number' => $users_per_page,
				'fields' => 'all'
				);
		
		} elseif( $show == 'notactive' ) {

			/*global $wpdb;
			
			$sql = "SELECT user_id FROM `wp32`.`wp_usermeta` WHERE meta_key = 'active' AND meta_value = 1;";
			$user = $wpdb->get_results($sql, OBJECT);
			
			*/

		} elseif( $show == 'notexported' ) {

		}
		// http://codex.wordpress.org/Function_Reference/get_users
			
		$users = get_users( $args ); // get_users_of_blog(); ?>		


		<?php wpmem_a_build_user_action( true, $arr ); ?>

		<table class="widefat fixed" cellspacing="0">
			<thead>
			<?php //$colspan = wpmem_a_build_user_tbl_head( $col_phone, $col_country ); ?>
			<?php $colspan = wpmem_a_build_user_tbl_head( array( 'phone'=>$col_phone, 'country'=>$col_country ) ); ?>
			</thead>

			<tfoot>
			<?php //$colspan = wpmem_a_build_user_tbl_head( $col_phone, $col_country ); ?>
			<?php $colspan = wpmem_a_build_user_tbl_head( array( 'phone'=>$col_phone, 'country'=>$col_country ) ); ?>
			</tfoot>

			<tbody id="users" class="list:user user-list">

			<?php	
			if( WPMEM_DEBUG == true ) { echo "<pre>\n"; print_r( $users ); echo "</pre>\n"; }
			$x=0; $class = '';
			foreach( $users as $user )
			{
				// are we filtering results? (active, trials, etc...)
				
				$chk_show = false; 
				switch( $show ) {
				case "notactive":
					$chk_show = ( get_user_meta( $user->ID, 'active', 'true' ) != 1 ) ? true : false; // if( get_user_meta( $user->ID, 'active', 'true' ) != 1 ) { $chk_show = true; }
					break;
				case "trial":
					$chk_exp_type = get_user_meta( $user->ID, 'exp_type', 'true' );
					$chk_show = ( $chk_exp_type == 'trial' ) ? true : false; // if( $chk_exp_type == 'trial' ) { $chk_show = true; }
					break;
				case "subscription":
					$chk_exp_type = get_user_meta( $user->ID, 'exp_type', 'true' );
					$chk_show = ( $chk_exp_type == 'subscription' ) ? true : false; // if( $chk_exp_type == 'subscription' ) { $chk_show = true; }
					break;
				case "expired":
					$chk_show = ( wpmem_chk_exp( $user->ID ) ) ? true : false; // if( wpmem_chk_exp( $user->ID ) ) { $chk_show = true; }
					break;
				case "notexported":
					$chk_show = ( get_user_meta( $user->ID, 'exported', 'true' ) != 1 ) ? true : false; // if( get_user_meta( $user->ID, 'exported', 'true' ) != 1 ) { $chk_show = true; }
					break;
				}

				if( !$show || $chk_show == true ) {
					
					$class = ( $class == 'alternate' ) ? '' : 'alternate';

					echo "<tr id=\"{$user->ID}\" class=\"$class\">\n";
					echo "	<th scope='row' class='check-column'><input type='checkbox' name='users[]' id=\"user_{$user->ID}\" class='administrator' value=\"{$user->ID}\" /></th>\n";
					echo "	<td class=\"username column-username\" nowrap>\n";
					echo "		<strong><a href=\"user-edit.php?user_id={$user->ID}&#038;" . esc_attr( stripslashes( $_SERVER['REQUEST_URI'] ) ) . "\">" . $user->user_login . "</a></strong><br />\n";
					echo "	</td>\n";
					echo "	<td class=\"name column-name\" nowrap>" . htmlspecialchars( get_user_meta( $user->ID, 'first_name', 'true' ) ) . "&nbsp" . htmlspecialchars( get_user_meta( $user->ID, 'last_name', 'true' ) ) . "</td>\n";
					echo "	<td class=\"email column-email\" nowrap><a href='mailto:" . $user->user_email . "' title='E-mail: " . $user->user_email . "'>" . $user->user_email . "</a></td>\n";
					
					if( $col_phone == true ) {
						echo "	<td class=\"email column-email\" nowrap>" . htmlspecialchars( get_user_meta( $user->ID, 'phone1', 'true' ) ) . "</td>\n";
					}
					
					if( $col_country == true ) {
						echo "	<td class=\"email column-email\" nowrap>" . htmlspecialchars( get_user_meta( $user->ID, 'country', 'true' ) ) . "</td>\n";
					}
					
					if( WPMEM_MOD_REG == 1 ) { 
						echo "	<td class=\"role column-role\" nowrap>";
						if( get_user_meta( $user->ID, 'active', 'true' ) != 1 ) { _e( 'No', 'wp-members' ); }
						echo "</td>\n";
					}
					
					if( WPMEM_USE_EXP == 1 ) {
						if( WPMEM_USE_TRL == 1 ) {
							echo "	<td class=\"email column-email\" nowrap>"; echo ucfirst( get_user_meta( $user->ID, 'exp_type', true ) ); echo "</td>\n";
						}
						echo "	<td class=\"email column-email\" nowrap>"; echo get_user_meta( $user->ID, 'expires', true ); echo "</td>\n";
					}
					echo "</tr>\n"; $x++;
				}
			} 
			
			if( $x == 0 ) { echo "<tr><td colspan=\"$colspan\">"; _e( 'No users matched your criteria', 'wp-members' ); echo "</td></tr>"; } ?>

		</table>
		
		<?php //wpmem_a_build_user_action( false, $arr ); ?>

		</form>
		
		<br />
		<p>This button exports the full user list. To export based on other criteria, use the form above.</p>
		<form method="link" action="<?php echo WPMEM_DIR . 'admin/export-full.php'; ?>">
			<input type="submit" class="button-secondary" value="Export All Users">
		</form>
	</div>
<?php
}


/**
 * Activates a user
 *
 * If registration is moderated, sets the activated flag 
 * in the usermeta. Flag prevents login when WPMEM_MOD_REG
 * is true (1). Function is fired from bulk user edit or
 * user profile update.
 *
 * @since 2.4
 *
 * @uses do_action Calls 'wpmem_user_activated' action
 *
 * @param int  $user_id
 * @param bool $chk_pass
 * @uses $wpdb WordPress Database object
 */
function wpmem_a_activate_user( $user_id, $chk_pass = false )
{
	// define new_pass
	$new_pass = '';
	
	// If passwords are user defined skip this
	if( ! $chk_pass ) {
		// generates a password to send the user
		$new_pass = wp_generate_password();
		$new_hash = wp_hash_password( $new_pass );
		
		// update the user with the new password
		global $wpdb;
		$wpdb->update( $wpdb->users, array( 'user_pass' => $new_hash ), array( 'ID' => $user_id ), array( '%s' ), array( '%d' ) );
	}
	
	// if subscriptions can expire, set the user's expiration date
	if( WPMEM_USE_EXP == 1 ) { wpmem_set_exp( $user_id ); }

	// generate and send user approved email to user
	require_once( WPMEM_PATH . 'wp-members-email.php' );
	wpmem_inc_regemail( $user_id, $new_pass, 2 );
	
	// set the active flag in usermeta
	update_user_meta( $user_id, 'active', 1 );
	
	do_action( 'wpmem_user_activated', $user_id );
	
	return;
}


/**
 * Deactivates a user
 *
 * Reverses the active flag from the activation process
 * preventing login when registration is moderated.
 *
 * @since 2.7.1
 *
 * @param int $user_id
 */
function wpmem_a_deactivate_user( $user_id ) {
	update_user_meta( $user_id, 'active', 0 );
}


/**
 * builds the user action dropdown
 *
 * @param bool  $top
 * @param array $arr
 *
 * @since 2.4
 */
function wpmem_a_build_user_action( $top, $arr )
{ ?>
	<div class="tablenav<?php if( $top ){ echo ' top'; }?>">
		<div class="alignleft actions">
			<select name="action<?php if( !$top ) { echo '2'; } ?>">
				<option value="" selected="selected"><?php _e('Bulk Actions', 'wp-members'); ?></option>
			<?php if (WPMEM_MOD_REG == 1) { ?>
				<option value="activate"><?php _e('Activate', 'wp-members'); ?></option>
			<?php } ?>
				<option value="export"><?php _e('Export', 'wp-members'); ?></option>
			</select>
			<input type="submit" value="<?php _e('Apply', 'wp-members'); ?>" name="doaction" id="doaction" class="button-secondary action" />
		</div>
		<?php if( $arr['show'] == 'Total Users' ) { ?>
		<div class="tablenav-pages">
			<span class="displaying-num"><?php echo $arr['show'] . ': ' . $arr['total_users']; ?></span>
		<?php if( $arr['total_users'] > $arr['show_num'] ) { ?>
			<span class="pagination-links">
				<a class="first-page<?php if( $arr['paged'] == 1 ){ echo ' disabled'; } ?>" title="Go to the first page" href="<?php echo $arr['link']; ?>">&laquo;</a>
				<a class="prev-page<?php if( $arr['paged'] == 1 ){ echo ' disabled'; } ?>" title="Go to the previous page" href="<?php echo $arr['link']; ?>&#038;paged=<?php echo ( $arr['paged'] - 1 ); ?>">&lsaquo;</a>
				
				<span class="paging-input"><input class="current-page" title="Current page" type="text" name="paged" value="<?php echo $arr['paged']; ?>" size="1" /> of <span class="total-pages"><?php echo $arr['pages']; ?></span></span>
				
				<a class="next-page<?php if( $arr['paged'] == $arr['pages'] ){ echo ' disabled'; } ?>" title="Go to the next page" href="<?php echo $arr['link']; ?>&#038;paged=<?php echo ( $arr['paged'] + 1 ); ?>">&rsaquo;</a>
				<a class="last-page<?php if( $arr['paged'] == $arr['pages'] ){ echo ' disabled'; } ?>" title="Go to the last page" href="<?php echo $arr['link']; ?>&#038;paged=<?php echo $arr['pages']; ?>">&raquo;</a>
			</span>
		<?php } ?>
		</div>
		<?php } ?>
		<br class="clear" />
	</div>
<?php 	
}


/**
 * builds the user management table heading
 *
 * @since 2.4
 *
 * @param array $args
 */
function wpmem_a_build_user_tbl_head( $args )
{
	$arr = array( 'Username', 'Name', 'E-mail', 'Phone', 'Country', 'Activated?', 'Subscription', 'Expires' ); ?>

	<tr class="thead">
		<th scope="col" class="manage-column column-cb check-column" style=""><input type="checkbox" /></th>
	<?php $c = 1; 
	foreach( $arr as $val ) { 

		$showcol = false;
		switch ($val) {
		case "Phone":
			$showcol = ( $args['phone'] == true ) ? true : false; // if( $args['phone'] == true ) { $showcol = true; $c++; }
			break;
		case "Country":
			$showcol = ( $args['country'] == true ) ? true : false; // if( $args['country'] == true ) { $showcol = true; $c++; }
			break;
		case "Activated?":
			$showcol = ( WPMEM_MOD_REG == 1 ) ? true : false; // if( WPMEM_MOD_REG == 1 ) { $showcol = true; $c++; }
			break;
		case "Subscription":
			$showcol = ( WPMEM_USE_EXP == 1 && WPMEM_USE_TRL == true ) ? true : false; // if( WPMEM_USE_EXP == 1 && WPMEM_USE_TRL == true ) { $showcol = true; $c++; }
			break;
		case "Expires":
			$showcol = ( WPMEM_USE_EXP == 1 ) ? true : false; // if( WPMEM_USE_EXP == 1 ) { $showcol = true; $c++; }
			break;
		default:
			$showcol = true;
			break;
		} 		
		
		echo ( $showcol == true ) ? '<th scope="col" class="manage-column" style="">' .  $val . '</th>' : '';

		$c++; 
	} ?>
	</tr><?php 
	return $c;
}
?>