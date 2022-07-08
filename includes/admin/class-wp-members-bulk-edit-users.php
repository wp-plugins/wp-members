<?php
/**
 * Set process to update user accordingly.
 *
 * This function will run for each user being updated in the main
 * framework script below. Breaking out the actual update process
 * makes the framework more modular so it can be adapted to multiple
 * use cases.
 *
 * For this use case, identify the actual meta keys used for the fields
 * imported with the membership slug and expiration date. The default
 * set up below is "membership" and "expires". The script will get
 * the meta values for those fields and run wpmem_set_user_product().
 * It will clean up for itself by deleting the meta values after it
 * is done.
 *
 * Because of the clean up process (deleting the meta keys it has
 * processed), if the script crashes from too many users, it can
 * be run again and will not overwrite existing processed users.
 */
function my_update_selected_user( $user_id ) {
    
    // Set specific criteria.
    $membership_key = "membership";
    $expiration_key = "expires";
    
    // Get the user's membership product info.
    $membership = get_user_meta( $user_id, $membership_key, true );
    $expiration = get_user_meta( $user_id, $expiration_key, true );
    
    // Only process users who have not been processed already.
    if ( $membership ) {

        // Set expiration date - either "false" or MySQL timestamp.
        if ( $expiration ) {
            $date = ( 'none' == $expiration ) ? false : date( "Y-m-d H:i:s", strtotime( $expiration ) );
        } else {
            $date = false;
        }

        // Set user product access.
        wpmem_set_user_product( $membership, $user_id, $date );

        // Clean up after yourself.
        delete_user_meta( $user_id, $membership_key );
        delete_user_meta( $user_id, $expiration_key );
        
    }
}

/**
 * A drop-in code snippet to update all users' membership
 * access and any applicable expiration date.
 *
 * To Use:
 * 1. Save the code snippet to your theme's functions.php
 * 2. Go to Tools > Update All Users.
 * 3. Follow prompts on screen.
 * 4. Remove the code snippet when completed.
 */

class WP_Members_Bulk_Edit_Users {

    public $settings = array(
        'enable_products' => "Membership",
        'mod_reg' => "Activation",
        'act_link' => "Confirmation",
    );
 
    function __construct() {
        add_action( 'admin_menu', array( $this, 'admin_menu' ) );
    }

    function admin_menu() {
        global $wpmem;
        if ( 1 == $wpmem->act_link || 1 == $wpmem->mod_reg || 1 == $wpmem->enable_products ) {
            $hook = add_users_page( 'WP-Members Bulk Edit Users', 'Bulk Edits', 'edit_users', 'wpmem-bulk-user-update', array( $this, 'admin_page' ) );
            add_action( "load-$hook", array( $this, 'admin_page_load' ) );
        }
    }

    function admin_page_load() {
        global $update_all_complete;
        $update_all_complete = false;

        $utility_state = wpmem_get( 'wpmem_bulk_utility_state', false, 'request' );

        if ( isset( $_GET['page'] ) && 'wpmem-bulk-user-update' == $_GET['page'] ) {
            
        }

        if ( isset( $_GET['page'] ) && 'update-all-users' == $_GET['page'] && isset( $_POST['update-all-confirm'] ) && 1 == $_POST['update-all-confirm'] ) {
            $users = get_users( array( 'fields'=>'ID' ) );
            // This is where we loop through users and update them.
            foreach ( $users as $user_id ) {
                
                // This is the custom process.
                my_update_selected_user( $user_id );
                
            }
            $update_all_complete = true;
        }
    }

    function admin_page() {
        global $wpmem, $update_all_complete;

        $utility_state = wpmem_get( 'wpmem_bulk_utility_state', false, 'request' );
        $form_post = ( function_exists( 'wpmem_admin_form_post_url' ) ) ? wpmem_admin_form_post_url() : '';

        echo '<div class="wrap">';
        echo "<h2>" . __( 'WP-Members Bulk User Update', 'wp-members' ) . "</h2>";
        echo '<form name="wpmem-bulk-update-all-users" id="wpmem-bulk-update-all-users" method="post" action="' . $form_post . '">';

        switch ( $utility_state ) {

            case false:
                
                echo '<p>This utility allows you to run various bulk edits to all users.</p>';
                echo '<p>Select the utility to run:</p>';
                echo '<select name="wpmem_bulk_utility_state">
                        <option value="">Select option</option>';
                foreach ( $this->settings as $setting => $label ) {
                    if ( 1 == $wpmem->{$setting} ) {
                       echo '<option value="start_' . strtolower( $label ) . '">' . $label . '</option>';
                    }
                }
                echo '</select>
                    <input type="submit" name="submit" value="Submit" />';
                break;

            case 'start_activation':
            case 'start_confirmation':
                echo '<p>';
                echo ( 'start_activation' == $utility_state ) ? 'This process will set ALL users as activated.' : 'This process will set ALL users as confirmed.';
                echo '</p>';
                echo '<p><input name="wpmem_bulk_utility_state" type="checkbox" value="activation_confirm" /> ';
                echo ( 'start_activation' == $utility_state ) ? 'Activate all users' : 'Confirm all users';
                echo '</p>';
                echo '<input type="submit" name="submit" value="Submit" />';
                break;

            case 'activation_confirm':
            case 'confirmation_confirm':
                echo '<p>';
                echo ( 'start_activation' == $utility_state ) ? 'All users have been set as activated.' : 'All users have been set as confirmed.'; 
                echo '</p>';
                break;

            case 'start_membership':
                echo '<p>';
                echo 'This will set all users to a valid membership based on imported values.';
                echo '<p>';
                break;

            case 'membership_confirm':
                echo '<p>';
                echo 'All user memberships have been set.';
                echo '</p>';
                break;
        }

        if ( $update_all_complete ) {
            echo '<p>All users were updated.<br />';
            echo 'You may now remove this code snippet if desired.</p>';
        } else {

        }

        echo '</form>';
        echo '</div>';
    }
}
// End of My_Update_All_Users_Class