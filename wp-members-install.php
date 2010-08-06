<?php
/*
	This file is part of the WP-Members plugin by Chad Butler
	
	You can find out more about this plugin at http://butlerblog.com/wp-members
  
	Copyright (c) 2006-2010  Chad Butler (email : plugins@butlerblog.com)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as 
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

	You may also view the license here:
	http://www.gnu.org/licenses/gpl.html#SEC1
*/


function wpmem_do_install()
{
	if(!get_option('wpmembers_settings')) {

		// this is an upgrade from 2.1 or earlier
		
		$wpmem_settings = array(WP_MEM_VERSION,1,0,0);
		add_option('wpmembers_settings', $wpmem_settings, '', 'yes');
			
		$wpmem_fields_options_arr = array(
			// order, label, optionname, input type, display, required, native
			array (1,'First Name','first_name','text','y','y','y'),	
			array (2,'Last Name','last_name','text','y','y','y'),
			array (3,'Address 1','addr1','text','y','y','n'),
			array (4,'Address 2','addr2','text','y','n','n'),	
			array (5,'City','city','text','y','y','n'),
			array (6,'State','thestate','text','y','y','n'),
			array (7,'Zip','zip','text','y','y','n'),
			array (8,'Country','country','text','y','y','n'),
			array (9,'Day Phone','phone1','text','y','y','n'),
			array (10,'Email','user_email','text','y','y','y'),
			array (11,'Website','user_url','text','n','n','y'),
			array (12,'AIM','aim','text','n','n','y'),
			array (13,'Yahoo IM','yim','text','n','n','y'),
			array (14,'Jabber/Google Talk','jabber','text','n','n','y'),
			array (15,'Bio','description','textarea','n','n','y')
		);
		
		add_option('wpmembers_fields',$wpmem_fields_options_arr,'','yes');
		
		$wpmem_dialogs_arr = array(
			"Content is restricted to site members.  Site membership is free, register below. If you are an existing user, please login.",
			"Sorry, that username is taken, please try another.",
			"Sorry, that email address already has an account.<br />Please try another.",
			"Congratulations! Your registration was successful.<br /><br />You may now login using the password that was emailed to you.",
			"Your information was updated!",
			"Passwords did not match.<br /><br />Please try again.",
			"Password successfully changed!<br /><br />You will need to re-login with your new password.",
			"Either the username or email address do not exist in our records.",
			"Password successfully reset!<br /><br />An email containing a new password has been sent to the email address on file for your accont. You may change this random password when re-login with your new password."
		);
		
		add_option('wpmembers_dialogs',$wpmem_dialogs_arr,'','yes');
	
	} else {
		
		//update version, keep other settings
		$wpmem_settings = get_option('wpmembers_settings');
		$wpmem_newsettings = array(
			WP_MEM_VERSION,
			$wpmem_settings[1],
			$wpmem_settings[2],
			$wpmem_settings[3],
		);
		update_option('wpmembers_settings',$wpmem_newsettings);
		
	}
}

// that's all folks!
?>