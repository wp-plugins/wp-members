<?php

class WP_Members_Admin_User_Tools {

    static function admin_page() {
		
        global $wpmem;
		
		$activate_all_complete = false;
		$confirm_all_complete  = false;
		
        if ( isset( $_GET['page'] ) && 'wpmem-user-utilities' == sanitize_text_field( $_GET['page'] ) ) {
			
			if ( isset( $_POST['activate-all'] ) && 1 == $_POST['activate-all'] ) {
				$users = get_users( array( 'fields'=>'ID' ) );
				foreach ( $users as $user_id ) {
					update_user_meta( $user_id, 'active', 1 );
					wpmem_set_user_status( $user_id, 0 );
				}
				$activate_all_complete = true;
			}
			
			if ( isset( $_POST['confirm-all'] ) && 1 == $_POST['confirm-all'] ) {
				$users = get_users( array( 'fields'=>'ID' ) );
				foreach ( $users as $user_id ) {
					wpmem_set_user_as_confirmed( $user_id );
				}
				$confirm_all_complete = true;
				
			}
			
        }
		
        echo "<h1>" . __( 'Bulk User Utilities', 'wp-members' ) . "</h1>";
        if ( $activate_all_complete ) {
            echo '<p><strong>' . __( '<p>All users were set as activated', 'wp-members' ) . '</strong></p>';
        } 
		if ( $confirm_all_complete ) {
			echo '<p><strong>' . __( 'All users were marked as confirmed', 'wp-members' ) . '</strong></p>';
		}
		
		$form_post = ( function_exists( 'wpmem_admin_form_post_url' ) ) ? wpmem_admin_form_post_url() : false;
			
		if ( false !== $form_post ) {

			echo '<form name="activate-all-users" id="activate-all-users" method="post" action="' . $form_post . '">';

			if ( 1 == $wpmem->mod_reg && true != $activate_all_complete ) {

				echo '<h2>' . __( 'Moderated registration', 'wp-members' ) . '</h2>';
				echo '<input type="checkbox" name="activate-all" value="1" /><label for="activate-all">' . __( 'Process all existing users as activated?', 'wp-members' ) . '</label></p>';
				echo '<p class="description">' . __( 'This will not change any passwords or send any emails to users', 'wp-members' ) . '</p>';
				echo '<p><strong>' . __( 'This process cannot be undone!', 'wp-members' ) . '</strong></p>';
				echo '<p>' . sprintf( __( 'Note: You can activate individual users via the %s All Users screen %s', 'wp-members' ), '<a href="' . admin_url() . 'users.php">', '</a>' ) . '</p>';
			}

			if ( 1 == $wpmem->act_link && true != $confirm_all_complete ) {

				echo '<h2>' . __( 'User email confirmation', 'wp-members' ) . '</h2>';
				echo '<input type="checkbox" name="confirm-all" value="1" /><label for="confirm-all">' . __( 'Process all existing users as confirmed?', 'wp-members' ) . '</label></p>';
				echo '<p class="description">' . __( 'This will not change any passwords or send any emails to users', 'wp-members' ) . '</p>';
				echo '<p><strong>' . __( 'This process cannot be undone!', 'wp-members' ) . '</strong></p>';
				echo '<p>' . sprintf( __( 'Note: You can mark individual users as confirmed via the %s All Users screen %s', 'wp-members' ), '<a href="' . admin_url() . 'users.php">', '</a>' ) . '</p>';
			}

			if ( false == $activate_all_complete || false == $confirm_all_complete ) {
				echo '<p><input type="submit" name="submit" value="Submit" /></p>';
			}
			
			echo '</form>';

		} else {
			_e( 'There was an unresolved error.', 'wp-members' );
		}

    }
}
// End of My_Activate_All_Users_Class