<?php
/**
 * WP-Members Admin Dialog Functions
 *
 * Handles functions that output admin dialogs to
 * adminstrative users.
 * 
 * This file is part of the WP-Members plugin by Chad Butler
 * You can find out more about this plugin at http://rocketgeek.com
 * Copyright (c) 2006-2012  Chad Butler (email : plugins@butlerblog.com)
 * WP-Members(tm) is a trademark of butlerblog.com
 *
 * @package WordPress
 * @subpackage WP-Members
 * @author Chad Butler
 * @copyright 2006-2012
 */


/*************************************************************************
	ADMIN WARNING MESSAGES
**************************************************************************/

function wpmem_a_warning_msg($msg)
{

	switch ($msg) {

	case 1: 

		$strong_msg = __( 'Your WP settings allow anyone to register - this is not the recommended setting.', 'wp-members' );
		$remain_msg = sprintf( __( 'You can %s change this here %s making sure the box next to "Anyone can register" is unchecked.', 'wp-members'), '<a href="options-general.php">', '</a>' );
		$span_msg   = __( 'This setting allows a link on the /wp-login.php page to register using the WP native registration process thus circumventing any registration you are using with WP-Members. In some cases, this may suit the users wants/needs, but most users should uncheck this option. If you do not change this setting, you can choose to ignore these warning messages under WP-Members Settings.', 'wp-members' );

		break;
	
	case 2:

		$strong_msg = __( 'Your WP settings allow anyone to comment - this is not the recommended setting.', 'wp-members' );
		$remain_msg = sprintf( __( 'You can %s change this here %s by checking the box next to "Users must be registered and logged in to comment."', 'wp-members' ), '<a href="options-discussion.php">', '</a>' );
		$span_msg   = __( 'This setting allows any users to comment, whether or not they are registered. Depending on how you are using WP-Members will determine whether you should change this setting or not. If you do not change this setting, you can choose to ignore these warning messages under WP-Members Settings.', 'wp-members' );

		break; 

	case 3: 

		$strong_msg = __( 'Your WP settings allow full text rss feeds - this is not the recommended setting.', 'wp-members' );
		$remain_msg = sprintf( __( 'You can %s change this here %s by changing "For each article in a feed, show" to "Summary."', 'wp-members' ), '<a href="options-reading.php">' , '</a>' );
		$span_msg   = __( 'Leaving this set to full text allows anyone to read your protected content in an RSS reader. Changing this to Summary prevents this as your feeds will only show summary text.', 'wp-members' );

		break;
	
	case 4: 
	
		$strong_msg = __( 'You have set WP-Members to hold registrations for approval', 'wp-members' );
		$remain_msg = __( 'but you have not changed the default message for "Registration Completed" under "WP-Members Dialogs and Error Messages."  You should change this message to let users know they are pending approval.', 'wp-members' );
	
		break;

	case 5: 

		$strong_msg = __( 'You have set WP-Members to turn off the registration process', 'wp-members' );
		$remain_msg = __( 'but you also set to moderate and/or email admin new registrations.  You will need to set up a registration page for users to register.', 'wp-members' );	

		break;
		
	case 6:
	
		$strong_msg = __( 'You have turned on reCAPTCHA', 'wp-members');
		$remain_msg = __( 'but you have not entered API keys.  You will need both a public and private key.  The CAPTCHA will not display unless a valid API key is included.', 'wp-members' );
		
		break;

	}
	
	if ( $span_msg ) { $span_msg = ' [<span title="' . $span_msg . '">why is this?</span>]'; }
	echo '<div class="error"><p><strong>' . $strong_msg . '</strong> ' . $remain_msg . $span_msg . '</div>';

}


function wpmem_a_help_msg($contextual_help, $screen_id) 
{
  
	switch ($screen_id) {

	case 'settings_page_wpmem-settings':
		$contextual_help = "this is custom for WP-Members";
		break;

	case 'page':
		$contextual_help = $contextual_help."<h5>WP-Members Custom Features</h5>
		<p>WP-Members contextual help for pages.</p>";
		break;

	case 'post':
		$contextual_help = $contextual_help."<h5>WP-Members Plugin Help</h5>
		<p><strong>Protecting a post</strong> - 
		Use the 'more' tag to protect a post. In the post editor tool bar, click the 
		'Insert more tag' button.  Everything before the 'more' tag is viewable by 
		anyone. Everything after is protected.</p>
		<p><strong>Block/Unblock a specific post</strong> - 
		No matter what your default settings for blocking/unblocking posts, you can 
		override this at the post level by using a custom field. If you have set 
		blocking posts as the default, you can unblock any given post by creating a 
		custom field called 'unblock' and setting the value to 'true' (note: this is 
		case sensitive). To block a post when the default is to unblock, set a custom 
		field of 'block' with a value of 'true'.</p>";
		break;
		
	case 'options-general':
		$contextual_help = $contextual_help."<h5>WP-Members Custom Features</h5>
		<p>information about e-mail address and \"anyone can register\".</p>";
		break;
		
	case 'options-reading':
		$contextual_help = $contextual_help."<h5>WP-Members Custom Features</h5>
		<p>information about feed set to summary.</p>";
		break;
		
	case 'options-discussion':
		$contextual_help = $contextual_help."<h5>WP-Members Custom Features</h5>
		<p>information about comments.</p>";
		break;
	
	case 'options-permalink':
		$contextual_help = $contextual_help."<h5>WP-Members Custom Features</h5>
		<p>information about permalinks.</p>";
		break;
		
	case 'users_page_wp-members':
		$contextual_help = "<h5>WP-Members User Management</h5>
		<p>information about wp-members user management</p>";
		break;
		
	}
   
   return $contextual_help;
}
?>