<?php
/*
	This file is part of the WP-Members plugin by Chad Butler
	
	You can find out more about this plugin at http://butlerblog.com/wp-members
  
	Copyright (c) 2006-2010  Chad Butler (email : plugins@butlerblog.com)
	
	WP-Members(tm) is a trademark of butlerblog.com
*/


/*************************************************************************
	ADMIN WARNING MESSAGES
**************************************************************************/

function wpmem_a_warning_msg($msg)
{

	switch ($msg) {

	case 1: 

		$strong_msg = __("Your WP settings allow anyone to register - this is not the recommended setting.");
		$remain_msg = "You can <a href=\"options-general.php\">change this here</a> making sure the box next to \"Anyone can register\" is unchecked.";
		$span_msg   = __("This setting allows a link on the /wp-login.php page to register using the WP native registration process thus circumventing any registration you are using with WP-Members. In some cases, this may suit the users wants/needs, but most users should uncheck this option. If you do not change this setting, you can choose to ignore these warning messages under WP-Members Settings.");

		break;
	
	case 2:

		$strong_msg = __("Your WP settings allow anyone to comment - this is not the recommended setting.");
		$remain_msg = "You can <a href=\"options-discussion.php\">change this here</a> by checking the box next to \"Users must be registered and logged in to comment.\"";
		$span_msg   = __("This setting allows any users to comment, whether or not they are registered. Depending on how you are using WP-Members will determine whether you should change this setting or not. If you do not change this setting, you can choose to ignore these warning messages under WP-Members Settings.");

		break; 

	case 3: 

		$strong_msg = __("Your WP settings allow full text rss feeds - this is not the recommended setting.");
		$remain_msg = "You can <a href=\"options-reading.php\">change this here</a> by changing \"For each article in a feed, show\" to \"Summary.\"";
		$span_msg   = __("Leaving this set to full text allows anyone to read your protected content in an RSS reader. Changing this to Summary prevents this as your feeds will only show summary text.");

		break;
	
	case 4: 
	
		$strong_msg = __("You have set WP-Members to hold registrations for approval");
		$remain_msg = ", but you have not changed the default message for \"Registration Completed\" under \"WP-Members Dialogs and Error Messages.\"  You should change this message to let users know they are pending approval.";
	
		break;

	case 5: 

		$strong_msg = __("You have set WP-Members to turn off the registration process");
		$remain_msg = ", but you also set to moderate and/or email admin new registrations.  Turning registrations off overrides the other two settings since no registrations are allowed.";	

		break;
		
	case 6:
	
		$strong_msg = __("You have turned on reCAPTCHA");
		$remain_msg = ", however, you have not entered API keys.  You will need both a public and private key.  The CAPTCHA will not display unless a valid API key is included.";
		
		break;

	}
	
	if ( $span_msg ) { $span_msg = " [<span title=\"".$span_msg."\">why is this?</span>]"; }
	echo "<div class=\"error\"><p><strong>".$strong_msg."</strong> ".$remain_msg.$span_msg."</div>";

}
?>