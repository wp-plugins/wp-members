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
/**
 * Generate users login status if logged in and give logout link
 *
 * @since ?.?
 * @deprecated
 */
function wpmem_inc_status()
{ 	
	/*
	reminder email was successfully sent message.  
	you can customize this to fit your theme, etc.
	*/

	global $user_login;
	$logout = get_bloginfo('url')."/?a=logout";

	//You may edit below this line

	$status = "
	<p>".sprintf(__('You are logged in as %s', 'wp-members'), $user_login)."  | <a href=\"".$logout."\">".__('click here to logout', 'wp-members')."</a></p>";

	// end edits for function wpmem_inc_status()

	return $status;
}
endif;


if ( ! function_exists( 'wpmem_do_sidebar' ) ):
/**
 * Creates the sidebar login form and status
 *
 * This function determines if the user is logged in
 * and displays either a login form, or the user's 
 * login status. Typically used for a sidebar.		
 * You can call this directly, or with the widget
 *
 * @since 2.4
 *
 * @global string $wpmem_regchk
 */
function wpmem_do_sidebar()
{
	global $wpmem_regchk;
	
	$url = get_bloginfo('url'); // used here and in the logout

	//this returns us to the right place
	if( is_home() ) {
		$post_to = $_SERVER['PHP_SELF'];
			
	} elseif( is_single() || is_page() ) {
		$post_to = get_permalink();

	} elseif( is_category() ) {
		global $wp_query;
		$cat_id = get_query_var('cat');
		$post_to = get_category_link( $cat_id );
		
	} elseif( is_search() ) {
		$post_to = $url."/?s=".get_search_query();
		
	} else {
		
		$post_to = $_SERVER['PHP_SELF'];

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
						if ( WPMEM_REGURL != null ) { ?>
							<a href="<?php echo WPMEM_REGURL; ?>"><?php _e('Register', 'wp-members'); ?></a>

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
					if ( WPMEM_REGURL != null ) { ?>
						<a href="<?php echo WPMEM_REGURL; ?>"><?php _e('Register', 'wp-members'); ?></a>

					<?php } ?></div>
				</form>
			</fieldset>

		<?php } ?>

	<?php } else { 
	
		global $user_login; 
		$logout = $url."/?a=logout"; 
		?>
		<p>
		  <?php printf(__('You are logged in as %s', 'wp-members'), $user_login );?><br />
		  <a href="<?php echo $logout;?>"><?php _e('click here to logout', 'wp-members'); ?></a>
		</p>

	<?php }
}
endif;


/**
 * Class for the sidebar login widget
 *
 * @since 2.7
 */
class widget_wpmemwidget extends WP_Widget 
{

    /**
	 * Sets up the WP-Members login widget.
	 */
    function widget_wpmemwidget() 
	{
        $widget_ops = array( 
			'classname'   => 'wp-members', 
			'description' => 'Display the WP-Members sidebar login.' 
			); 
        $this->WP_Widget( 'widget_wpmemwidget', 'WP-Members Login', $widget_ops );
    }
 
    /**
	 * Displays the WP-Members login widget settings 
	 * controls on the widget panel.
	 */
    function form( $instance ) 
	{
	
		/* Default widget settings. */
		$defaults = array( 'title' => __('Login Status', 'wp-members') );
		$instance = wp_parse_args( ( array ) $instance, $defaults );
		
		/* Title input */ ?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title:', 'wp-members'); ?></label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:95%;" />
		</p>
		<?php
    }
 
	/**
	 * Update the WP-Members login widget settings.
	 */
    function update( $new_instance, $old_instance ) 
	{
		$instance = $old_instance;
		
		/* Strip tags for title to remove HTML. */
		$instance['title'] = strip_tags( $new_instance['title'] );
		
        return $instance;
    }
 
    /**
	 * Displays the WP-Members login widget.
	 */
    function widget( $args, $instance ) 
	{
		extract( $args );

		$title = apply_filters('widget_title', $instance['title'] );

		echo $before_widget;
		echo '<div id="wp-members">';
			// Widget Title
			if( ! $title ) { $title = __( 'Login Status', 'wp-members' ); }
			echo $before_title . $title . $after_title;

			// The Widget
			if( function_exists( 'wpmem' ) ) { wpmem_do_sidebar(); }
		echo '</div>';
		echo $after_widget;
    }
}
?>