<?php
/**
 * WP-Members Email Functions
 *
 * Generates emails sent by the plugin.
 * 
 * This file is part of the WP-Members plugin by Chad Butler
 * You can find out more about this plugin at http://butlerblog.com/wp-members
 * Copyright (c) 2006-2012  Chad Butler (email : plugins@butlerblog.com)
 * WP-Members(tm) is a trademark of butlerblog.com
 *
 * @package WordPress
 * @subpackage WP-Members
 * @author Chad Butler
 * @copyright 2006-2012
 */


if ( ! function_exists( 'wpmem_inc_regemail' ) ):
/**
 * Builds emails for the user
 *
 * @since 1.8
 */
function wpmem_inc_regemail( $user_id, $password, $toggle )
{
	$user       = new WP_User( $user_id );
	$user_login = stripslashes( $user->user_login );
	$user_email = stripslashes( $user->user_email );
	$blogname   = wp_specialchars_decode( get_option ( 'blogname' ), ENT_QUOTES );
	
	if( WPMEM_USE_EXP == 1 ) {
		$exp_type = get_user_meta( $user_id, 'exp_type', 'true' );
		$exp_date = get_user_meta( $user_id, 'expires', 'true' );
	}
	
	$wpmem_msurl = get_option( 'wpmembers_msurl', null );
	$reg_link    = get_user_meta( $user_id, 'wpmem_reg_url', true );

	$shortcd = array( '[blogname]', '[username]', '[password]', '[reglink]', '[members-area]', '[exp-type]', '[exp-data]' );
	$replace = array( $blogname, $user_login, $password, $reg_link, $wpmem_msurl, $exp_type, $exp_date );

	switch ($toggle) {
	
	case 0: 
		//this is a new registration
		$arr = get_option( 'wpmembers_email_newreg' );
		break;
		
	case 1:
		//registration is moderated
		$arr = get_option( 'wpmembers_email_newmod' );
		break;

	case 2:
		//registration is moderated, user is approved
		$arr = get_option( 'wpmembers_email_appmod' );
		break;

	case 3:
		//this is a password reset
		$arr = get_option( 'wpmembers_email_repass' );
		break;
		
	}
	
	$subj = str_replace( $shortcd, $replace, $arr['subj'] );
	$body = str_replace( $shortcd, $replace, $arr['body'] );
	
	$foot = get_option ( 'wpmembers_email_footer' );
	$foot = str_replace( $shortcd, $replace, $foot );
	
	$body.= $foot;

	wp_mail( $user_email, stripslashes( $subj ), stripslashes( $body ), $headers = '' );

}
endif;


if( ! function_exists( 'wpmem_notify_admin' ) ):
/**
 * Builds the email for admin notification of new user registration
 *
 * @since 2.3
 */
function wpmem_notify_admin( $user_id, $wpmem_fields )
{
	$user     = new WP_User( $user_id );
	$blogname = wp_specialchars_decode( get_option ( 'blogname' ), ENT_QUOTES );
	
	$user_ip  = get_user_meta( $user_id, 'wpmem_reg_ip', true );
	$reg_link = get_user_meta( $user_id, 'wpmem_reg_url', true );
	$act_link = get_bloginfo ( 'wpurl' ) . "/wp-admin/user-edit.php?user_id=".$user_id;

	if( WPMEM_USE_EXP == 1 ) {
		$exp_type = get_user_meta( $user_id, 'exp_type', 'true' );
		$exp_date = get_user_meta( $user_id, 'expires', 'true' );
	}	
	
	for( $row = 0; $row < count( $wpmem_fields ); $row++ ) {
		if( $wpmem_fields[$row][4] == 'y' ) {
			$name = $wpmem_fields[$row][1];
			
			if( $wpmem_fields[$row][2] != 'user_email' ) {
				if( $wpmem_fields[$row][2] == 'user_url' ) {
					$val  = $user->user_url;
				} else {
					$val  = get_user_meta( $user_id,$wpmem_fields[$row][2], 'true' );
				}
			
				$field_str.= "$name: $val \r\n";
			}
		}
	}
	
	$shortcd = array( 
		'[blogname]', 
		'[username]',
		'[email]',
		'[reglink]',  
		'[exp-type]', 
		'[exp-data]',
		'[user-ip]',
		'[activate-user]',
		'[fields]'
	);
	
	$replace = array( 
		$blogname, 
		$user->user_login, 
		$user->user_email,
		$reg_link,  
		$exp_type, 
		$exp_date,
		$user_ip,
		$act_link,
		$field_str
	);
	
	$arr  = get_option( 'wpmembers_email_notify' );
	
	$subj = str_replace( $shortcd, $replace, $arr['subj'] );
	$body = str_replace( $shortcd, $replace, $arr['body'] );
	
	$foot = get_option ( 'wpmembers_email_footer' );
	$foot = str_replace( $shortcd, $replace, $foot );
	
	$body.= $foot;
	
	$admin_email = get_option( 'admin_email' );
	wp_mail($admin_email, stripslashes( $subj ), stripslashes( $body ), $headers = '');

}
endif;


add_filter( 'wp_mail_from', 'wpmem_mail_from' );
/**
 * Filters the wp_mail from address (if set)
 *
 * @since 2.7
 */
function wpmem_mail_from( $email )
{
	if( get_option( 'wpmembers_email_wpfrom' ) ) {
		$email = get_option( 'wpmembers_email_wpfrom' );
	}
    return $email;
}


add_filter( 'wp_mail_from_name', 'wpmem_mail_from_name' );
/**
 * Filters the wp_mail from name (if set)
 *
 * @since 2.7
 */
function wpmem_mail_from_name( $name )
{
	if( get_option( 'wpmembers_email_wpname' ) ) {
		$name = get_option( 'wpmembers_email_wpname' );
	}
    return $name;
}
?>