<?php
/**
 * WP-Members Sidebar Functions
 *
 * Handles functions for the sidebar.
 * 
 * This file is part of the WP-Members plugin by Chad Butler
 * You can find out more about this plugin at http://butlerblog.com/wp-members
 * Copyright (c) 2006-2011  Chad Butler (email : plugins@butlerblog.com)
 * WP-Members(tm) is a trademark of butlerblog.com
 *
 * @package WordPress
 * @subpackage WP-Members
 * @author Chad Butler
 * @copyright 2006-2011
 */


/*****************************************************
LOGIN STATUS AND WIDGET FUNCTIONS
*****************************************************/


if ( ! function_exists( 'wpmem_inc_status' ) ):
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
	<p>".sprintf(__('You are logged in as %s', 'wp-members'), $user_login)."  | <a href=\"".$logout."\">".__('click here to logout', 'wp-members')."</a></p>";

	// end edits for function wpmem_inc_status()

	return $wpmem_login_status;
}
endif;


if ( ! function_exists( 'wpmem_do_sidebar' ) ):
function wpmem_do_sidebar()
{
	/*
	This function determines if the user is logged in
	and displays either a login form, or the user's 
	login status. Typically used for a sidebar.		
	You can call this directly, or with the widget
	*/
	global $wpmem_regchk;
	
	$url = get_bloginfo('url'); // used here and in the logout

	//this returns us to the right place
	if( is_home() ) {
		$post_to = home_url();
			
	} elseif( is_single() ) {
		$post_to = get_permalink();

	} elseif( is_page() ) {
		global $page_id;
		$post_to = get_page_uri( $page_id );

	} elseif( is_category() ) {
		global $wp_query;
		$cat_id = get_query_var('cat');
		$post_to = get_category_link( $cat_id );
		
	} elseif( is_search() ) {
		$post_to = $url."/?s=".get_search_query();

	//} elseif( is_archive() ) {
		//$post_to = "archive";
		
	} else {
		$post_to = home_url();

	}

	if (!is_user_logged_in()){

		if (WPMEM_OLD_FORMS == 1) {?>
			<ul>
				<?php if ($wpmem_regchk == 'loginfailed' && $_POST['slog'] == 'true') { ?><p><?php _e('Login Failed!<br />You entered an invalid username or password.', 'wp-members'); ?></p><?php }?>
				<p><?php _e('You are not currently logged in.', 'wp-members'); ?><br />
					<form name="form" method="post" action="<?php echo $post_to; ?>">
					<?php _e('Username'); ?><br />
					<input type="text" name="log" style="font:10px verdana,sans-serif;" /><br />
					<?php _e('Password'); ?><br />
					<input type="password" name="pwd" style="font:10px verdana,sans-serif;" /><br />
					<input type="hidden" name="rememberme" value="forever" />
					<input type="hidden" name="redirect_to" value="<?php echo $post_to; ?>" />
					<input type="hidden" name="a" value="login" />
					<input type="hidden" name="slog" value="true" />
					<input type="submit" name="Submit" value="<?php _e('login', 'wp-members'); ?>" style="font:10px verdana,sans-serif;" />
					<?php 			
						if ( WPMEM_MSURL != null ) { 
							$link = wpmem_chk_qstr( WPMEM_MSURL ); ?>
							<a href="<?php echo $link; ?>a=pwdreset"><?php _e('Forgot?', 'wp-members'); ?></a>&nbsp;
						<?php } 			
						if ( WPMEM_REGURL != null ) { 
							$link = wpmem_chk_qstr( WPMEM_REGURL ); ?>
							<a href="<?php echo $link; ?>"><?php _e('Register', 'wp-members'); ?></a>

						<?php } ?>
					</form>
				</p>
			</ul>
		<?php } else { ?>
		  
			<?php if ($wpmem_regchk == 'loginfailed' && $_POST['slog'] == 'true') { ?><p class="err"><?php _e('Login Failed!<br />You entered an invalid username or password.', 'wp-members'); ?></p><?php }?>
			<?php _e('You are not currently logged in.', 'wp-members'); ?><br />
			<fieldset>
				<form name="form" method="post" action="<?php echo $post_to; ?>">
				
					<label for="username"><?php _e('Username'); ?></label>
					<div class="div_texbox"><input type="text" name="log" class="username" id="username" /></div>
					<label for="password"><?php _e('Password'); ?></label>
					<div class="div_texbox"><input type="password" name="pwd" class="password" id="password" /></div>
					<input type="hidden" name="rememberme" value="forever" />
					<input type="hidden" name="redirect_to" value="<?php echo $post_to; ?>" />
					<input type="hidden" name="a" value="login" />
					<input type="hidden" name="slog" value="true" />
					<div class="button_div"><input type="submit" name="Submit" class="buttons" value="<?php _e('login', 'wp-members'); ?>" />
					<?php 		
					if ( WPMEM_MSURL != null ) { 
						$link = wpmem_chk_qstr( WPMEM_MSURL ); ?>
						<a href="<?php echo $link; ?>a=pwdreset"><?php _e('Forgot?', 'wp-members'); ?></a>&nbsp;
					<?php } 			
					if ( WPMEM_REGURL != null ) { 
						$link = wpmem_chk_qstr( WPMEM_REGURL ); ?>
						<a href="<?php echo $link; ?>"><?php _e('Register', 'wp-members'); ?></a>

					<?php } ?></div>
				</form>
			</fieldset>

		<?php } ?>

	<?php } else { 
	
		global $user_login; 
		$logout = $url."/?a=logout";
		/*
		This is the displayed when the user is logged in.
		You may edit below this line, but do not
		change the <?php ?> tags or their contents */?>
		<p>
		  <?php printf(__('You are logged in as %s', 'wp-members'), $user_login );?><br />
		  <a href="<?php echo $logout;?>"><?php _e('click here to logout', 'wp-members'); ?></a>
		</p>

	<?php }
}
endif;


function widget_wpmemwidget($args)
{
	extract($args);

	$options = get_option('widget_wpmemwidget');
	$title = $options['title'];

	 echo $before_widget;

		// Widget Title
		if (!$title) {$title = __('Login Status', 'wp-members');}
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
		$options = array('title'=>'', 'buttontext'=>__('WP-Members', 'widgets'));
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