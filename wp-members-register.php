<?php
/*
	This file is part of the WP-Members plugin by Chad Butler
	
	You can find out more about this plugin at http://butlerblog.com/wp-members
  
	Copyright (c) 2006-2010  Chad Butler (email : plugins@butlerblog.com)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 3, as 
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

	You may also view the license here:
	http://www.gnu.org/licenses/gpl.html
*/


function wpmem_registration($toggle)
{
	// make sure native WP registration functions are loaded
	require_once( ABSPATH . WPINC . '/registration-functions.php');

	global $wpdb,$user_ID,$userdata,$wpmem_regchk,$wpmem_themsg,$username,$user_email,$wpmem_fieldval_arr;

	if($toggle=='register'){ $username = $_POST['log']; }
	$user_email = $_POST['user_email'];

	//build array of the posts
	$wpmem_fields = get_option('wpmembers_fields');
	for ($row = 0; $row < count($wpmem_fields); $row++) {
		$wpmem_fieldval_arr[$row] = $_POST[$wpmem_fields[$row][2]];
	}

	// check for required fields	
	$wpmem_fields_rev = array_reverse($wpmem_fields);
	$wpmem_fieldval_arr_rev = array_reverse($wpmem_fieldval_arr);

	for ($row = 0; $row < count($wpmem_fields); $row++) {
		if ( $wpmem_fields_rev[$row][5] == 'y' ) {
			if ( !$wpmem_fieldval_arr_rev[$row] ) { $wpmem_themsg = $wpmem_fields_rev[$row][1]." is a required field."; }
		}
	} 

	switch($toggle) {

	case "register":
	
		// new in 2.3, toggle off registration
		if (WPMEM_NO_REG != 1) {

			if ( !$username ) { $wpmem_themsg = "username is a required field"; } 
			if ( $wpmem_themsg ) {

				$wpmem_regchk = "empty";

			} else {

				if (username_exists($username)) {

					$wpmem_regchk = "user";

				} else {

					$email_exists = $wpdb->get_var("SELECT user_email FROM $wpdb->users WHERE user_email = '$user_email'");
					if ( $email_exists) {

						$wpmem_regchk = "email";

					} else {

					//everything checks out, so go ahead and insert

						//The main insertion process was taken from the WP core, the rest is modified to accomodate WP-Members user defined fields.

						$password = substr( md5( uniqid( microtime() ) ), 0, 7);
						$hashpassword = md5($password);
						$user_registered = gmdate('Y-m-d H:i:s');

						$query = "INSERT INTO $wpdb->users 
							(user_login, user_pass, user_email, user_registered, user_nicename, display_name) VALUES 
							('$username', '$hashpassword', '$user_email', '$user_registered', '$username', '$username')";

						$query = apply_filters('create_user_query', $query);
						$wpdb->query( $query );
						$user_id = $wpdb->insert_id;

						//Sets the user to the default role.
						$user = new WP_User($user_id);
						$user->set_role(get_option('default_role'));

						update_usermeta( $user_id, 'nickname', $username); // gotta have this whether it's used or not; if it's included w/ custom, value should be overwritten below.
						for ($row = 0; $row < count($wpmem_fields); $row++) {

							/*there are two native wp fields that throw a sticky wicket into our clean array - email and website.
							  they go into the wp_users table.  email is already done above, we need to then screen for putting in 
							  website, if used, and screen out email, since it's already done. */
							if ($wpmem_fields[$row][2] == 'user_url') {
								$wpdb->update( $wpdb->users, array('user_url'=>$wpmem_fieldval_arr[$row]), array('ID'=>$user_id) );
							} else {
								if ($wpmem_fields[$row][2] != 'user_email') {update_usermeta( $user_id, $wpmem_fields[$row][2], $wpmem_fieldval_arr[$row]);}
							}
						} 

						require_once('wp-members-email.php');

						//if this was successful, and you have email properly
						//configured, send a notification email to the user
						wpmem_inc_regemail($user_id,$password,WPMEM_MOD_REG);
						
						//notify admin of new reg, if needed;
						if (WPMEM_NOTIFY_ADMIN == 1) { wpmem_notify_admin($user_id, $wpmem_fields); }

						// successful registration message
						$wpmem_regchk = "success";											

					}
				}
			}
		}

		break;

	case "update":

		if ( $wpmem_themsg ) {

			$wpmem_regchk = "updaterr";

		} else {

			for ($row = 0; $row < count($wpmem_fields); $row++) {

				/*there are two native wp fields that throw a sticky wicket into our clean array - email and website.
				  they go into the wp_users table.  we need to then screen for these and put them in a different way*/
				switch ($wpmem_fields[$row][2]) {

				case ('user_url'):
					$wpdb->update( $wpdb->users, array('user_url'=>$wpmem_fieldval_arr[$row]), array('ID'=>$user_ID) );
					break;

				case ('user_email'):
					$wpdb->update( $wpdb->users, array('user_email'=>$wpmem_fieldval_arr[$row]), array('ID'=>$user_ID) );
					break;

				default:
					update_usermeta( $user_ID, $wpmem_fields[$row][2], $wpmem_fieldval_arr[$row]);
					break;
				}
			} 

			$wpmem_regchk = "editsuccess";

		}

		break;

	}

} // end registration function

?>