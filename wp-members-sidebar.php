<?php
/*
	This file is part of the WP-Members plugin by Chad Butler
	
	You can find out more about this plugin at http://butlerblog.com/wp-members
  
	Copyright (c) 2006-2010  Chad Butler (email : plugins@butlerblog.com)
	
	WP-Members(tm) is a trademark of butlerblog.com
*/


/*****************************************************
LOGIN STATUS AND WIDGET FUNCTIONS
*****************************************************/


function wpmem_inc_status()
{ 	
	/*
	reminder email was successfully sent message.  
	you can customize this to fit your theme, etc.
	*/

	global $user_login;
	$logout = get_bloginfo('url')."/?a=logout";

	//You may edit below this line

	$wpmem_login_status = "
	<p>".__('You are logged in as')." $user_login | <a href=\"".$logout."\">".__('click here to logout')."</a></p>";

	// end edits for function wpmem_inc_status()

	return $wpmem_login_status;
}


function wpmem_do_sidebar()
{
	/*
	This function determines if the user is logged in
	and displays either a login form, or the user's 
	login status. Typically used for a sidebar.		
	You can call this directly, or with the widget
	*/
	global $user_login, $wpmem_regchk;
	$url = get_bloginfo('url');
	$logout = $url."/?a=logout";

	//this returns us to the right place
	if(is_home()) {
		$post_to = $_SERVER['PHP_SELF'];
	}else{
		$post_to = get_permalink();
	}

	if (!is_user_logged_in()){
	/*
	This is the login form.
	You may edit below this line, but do not
	change the <?php ?> tags or their contents */?>
	<ul>
		<?php if ($wpmem_regchk == 'loginfailed' && $_POST['slog'] == 'true') { ?><p><?php _e('Login Failed!<br />You entered an invalid username or password.'); ?></p><?php }?>
		<p><?php _e('You are not currently logged in.'); ?><br />
			<form name="form" method="post" action="<?php echo $post_to; ?>">
			<?php _e('Username'); ?><br />
			<input type="text" name="log" style="font:10px verdana,sans-serif;" /><br />
			<?php _e('Password'); ?><br />
			<input type="password" name="pwd" style="font:10px verdana,sans-serif;" /><br />
			<input type="hidden" name="rememberme" value="forever" />
			<input type="hidden" name="redirect_to" value="<?php echo $post_to; ?>" />
			<input type="hidden" name="a" value="login" />
			<input type="hidden" name="slog" value="true" />
			<input type="submit" name="Submit" value="login" style="font:10px verdana,sans-serif;" />
			</form>
		</p>
	</ul>
	<?php } else { 
	/*
	This is the displayed when the user is logged in.
	You may edit below this line, but do not
	change the <?php ?> tags or their contents */?>
	<ul>
		<p>
		  <?php _e('You are logged in as');?>&nbsp;<?php echo $user_login; ?><br />
		  <a href="<?php echo $logout;?>"><?php _e('click here to logout'); ?></a>
		</p>
	</ul>

	<?php }
}


function widget_wpmemwidget($args)
{
	extract($args);

	$options = get_option('widget_wpmemwidget');
	$title = $options['title'];

	echo $before_widget;

		// Widget Title
		if (!$title) {$title = __("Login Status");}
		echo $before_title . $title . $after_title;

		// The Widget
		if (function_exists('wpmem')) { wpmem_inc_sidebar($widget);}

	echo $after_widget;
}

function widget_wpmemwidget_control()
{
	// Get our options and see if we're handling a form submission.
	$options = get_option('widget_wpmemwidget');
	if ( !is_array($options) )
		$options = array('title'=>'', 'buttontext'=>__('WP Members', 'widgets'));
	if ( $_POST['wpmemwidget-submit'] ) {

		// Remember to sanitize and format use input appropriately.
		$options['title'] = strip_tags(stripslashes($_POST['wpmemwidget-title']));
		update_option('widget_wpmemwidget', $options);
	}

	// Be sure you format your options to be valid HTML attributes.
	$title = htmlspecialchars($options['title'], ENT_QUOTES);

	// Here is our little form segment. Notice that we don't need a
	// complete form. This will be embedded into the existing form.
	echo '<p style="text-align:left;"><label for="wpmemwidget-title">' . __('Title:') . ' <input style="width: 200px;" id="wpmemwidget-title" name="wpmemwidget-title" type="text" value="'.$title.'" /></label></p>';
	echo '<input type="hidden" id="wpmemwidget-submit" name="wpmemwidget-submit" value="1" />';
}
?>