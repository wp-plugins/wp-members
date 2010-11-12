<?php
/*
	This file is part of the WP-Members plugin by Chad Butler
	
	You can find out more about this plugin at http://butlerblog.com/wp-members
  
	Copyright (c) 2006-2010  Chad Butler (email : plugins@butlerblog.com)
	
	WP-Members(tm) is a trademark of butlerblog.com
*/


/*****************************************************
INSTALLATION PROCESS
*****************************************************/


function wpmem_do_install()
{
	if(!get_option('wpmembers_settings')) {

		// this is an upgrade from 2.1 or earlier
		
		$wpmem_settings = array(WPMEM_VERSION,1,0,1,0,0,0,0,0,0);
		add_option('wpmembers_settings', $wpmem_settings, '', 'yes');
			
		$wpmem_fields_options_arr = array(
			// order, label, optionname, input type, display, required, native
			array (1,__('First Name'),'first_name','text','y','y','y'),	
			array (2,__('Last Name'),'last_name','text','y','y','y'),
			array (3,__('Address 1'),'addr1','text','y','y','n'),
			array (4,__('Address 2'),'addr2','text','y','n','n'),	
			array (5,__('City'),'city','text','y','y','n'),
			array (6,__('State'),'thestate','text','y','y','n'),
			array (7,__('Zip'),'zip','text','y','y','n'),
			array (8,__('Country'),'country','text','y','y','n'),
			array (9,__('Day Phone'),'phone1','text','y','y','n'),
			array (10,__('Email'),'user_email','text','y','y','y'),
			array (11,__('Website'),'user_url','text','n','n','y'),
			array (12,__('AIM'),'aim','text','n','n','y'),
			array (13,__('Yahoo IM'),'yim','text','n','n','y'),
			array (14,__('Jabber/Google Talk'),'jabber','text','n','n','y'),
			array (15,__('Bio'),'description','textarea','n','n','y')
		);
		
		add_option('wpmembers_fields',$wpmem_fields_options_arr,'','yes');
		
		$wpmem_dialogs_arr = array(
			__("This content is restricted to site members.  If you are an existing user, please login.  New users may register below."),
			__("Sorry, that username is taken, please try another."),
			__("Sorry, that email address already has an account.<br />Please try another."),
			__("Congratulations! Your registration was successful.<br /><br />You may now login using the password that was emailed to you."),
			__("Your information was updated!"),
			__("Passwords did not match.<br /><br />Please try again."),
			__("Password successfully changed!<br /><br />You will need to re-login with your new password."),
			__("Either the username or email address do not exist in our records."),
			__("Password successfully reset!<br /><br />An email containing a new password has been sent to the email address on file for your accont. You may change this random password when re-login with your new password.")
		);
		
		add_option('wpmembers_dialogs',$wpmem_dialogs_arr,'','yes');
	
	} else {
	
		$wpmem_settings = get_option('wpmembers_settings');
		
		if ( count($wpmem_settings) == 4 ) {
		
			// upgrading from 2.2.x
			// update version, insert new toggles, keep other settings
			$wpmem_newsettings = array(
				WPMEM_VERSION, 			//  0 version
				$wpmem_settings[1],		//  1 block posts
				$wpmem_settings[2],		//  2 block pages
				'0', 					//  3 show excerpts on posts/pages
				'0',					//  4 notify admin
				'0',					//  5 moderate registration
				'0',					//  6 toggle captcha
				'0',					//  7 turn off registration
				'0',					//  8 time based expiration
				'0',					//  9 offer trial period
				$wpmem_settings[3]		// 10 ignore warnings
			);
			update_option('wpmembers_settings',$wpmem_newsettings);
		
		} elseif ( count($wpmem_settings) == 10 ) {
		
			// upgrading from 2.3.0, 2.3.1, or 2.3.2
			// update version, insert captcha toggle, keep other settings
			$wpmem_newsettings = array(
				WPMEM_VERSION, 			//  0 version
				$wpmem_settings[1],		//  1 block posts
				$wpmem_settings[2],		//  2 block pages
				$wpmem_settings[3],		//  3 show excerpts on posts/pages
				$wpmem_settings[4],		//  4 notify admin
				$wpmem_settings[5],		//  5 moderate registration
				'0',					//  6 toggle captcha
				$wpmem_settings[6],		//  7 turn off registration
				$wpmem_settings[7],		//  8 time based expiration
				$wpmem_settings[8],		//  9 offer trial period
				$wpmem_settings[9]		// 10 ignore warnings
			);
			update_option('wpmembers_settings',$wpmem_newsettings);
		
		}		
	}
}

// that's all folks!
?>