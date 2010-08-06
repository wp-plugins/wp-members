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


/*****************************************************
BEGIN ADMIN FEATURES
*****************************************************/


add_action('edit_user_profile', 'wpmem_admin_fields');
function wpmem_admin_fields()
{
	$user_id = $_REQUEST['user_id']; ?>
	
	<h3>WP-Members Additional Fields</h3>
 	<table class="form-table">
		<?php
		$wpmem_fields = get_option('wpmembers_fields');
		for ($row = 0; $row < count($wpmem_fields); $row++) {
		
			if($wpmem_fields[$row][6] == "n" && $wpmem_fields[$row][4] == "y") { ?>    
			
				<tr>
					<th><label><?php echo $wpmem_fields[$row][1]; ?></label></th>
					<td><input id="<?php echo $wpmem_fields[$row][2]; ?>" type="text" class="input" name="<?php echo $wpmem_fields[$row][2]; ?>" value="<?php echo get_usermeta($user_id,$wpmem_fields[$row][2]);?>" size="25" /></td>
				</tr>
			
			<?php } 
		
		} ?>
	</table><?php
}


add_action('profile_update', wpmem_admin_update);
function wpmem_admin_update()
{
	$user_id = $_REQUEST['user_id'];	
	$wpmem_fields = get_option('wpmembers_fields');
	for ($row = 0; $row < count($wpmem_fields); $row++) {
		if ($wpmem_fields[$row][6] == "n") {update_usermeta($user_id,$wpmem_fields[$row][2],$_POST[$wpmem_fields[$row][2]]);}
	}
}


function wpmem_a_build_options($wpmem_settings)
{ ?>
	<h3>Manage Options</h3>
		<form name="updatesettings" id="updatesettings" method="post" action="<?php echo $_SERVER['PHP_SELF']?>?page=wp-members.php">
		<?php if ( function_exists('wp_nonce_field') ) { wp_nonce_field('wpmem-update-settings'); } ?>
		<table class="form-table">
		<?php $arr = array(
			array('Block Posts by default?','wpmem_settings_block_posts'),
			array('Block Pages by default?','wpmem_settings_block_pages'),
			array('Ignore admin warning messages?','wpmem_settings_ignore_warnings') ); ?>
		<?php for ($row = 0; $row < count($arr); $row++) { ?>
		  <tr valign="top">
			<th align="left" scope="row"><?php echo $arr[$row][0]; ?></th>
			<td><select name="<?php echo $arr[$row][1]; ?>">
				<option value="1" <?php if ($wpmem_settings[$row+1]==1) {echo "selected";}?>>Yes</option>
				<option value="0"  <?php if ($wpmem_settings[$row+1]==0) {echo "selected";}?>>No</option>
			</select></td>
		  </tr>
		  <?php if ($row == 1) { ?>
		  <tr valign="top">
			<td colspan="2"><small><i>(Posts and Pages can be individually blocked or unblocked at the article level)</i></small></td>
		  </tr>
		  <?php } ?>
		<?php } ?>
		  <tr valign="top">
			<td>&nbsp;</td>
			<td><input type="hidden" name="wpmem_admin_a" value="update_settings">
				<input type="submit" name="UpdateSettings" value="Update Settings &raquo;" style="font-weight: bold;" tabindex="4" class="button" />    </td>
		  </tr>
		</table>
	</form>
	<?php
}


function wpmem_a_build_fields ($wpmem_fields) 
{ ?>

	<h3><?php _e('Manage Fields'); ?></h3>
    <p><?php _e('Determine which fields will display and which are required.  This includes all fields, both native WP fields and WP-Members custom fields.'); ?>
		&nbsp;<strong><?php _e('(Note: Email is always mandatory. and cannot be changed.)'); ?></strong></p>
    <form name="updatefieldform" id="updatefieldform" method="post" action="<?php echo $_SERVER['PHP_SELF']?>?page=wp-members.php">
	<?php if ( function_exists('wp_nonce_field') ) { wp_nonce_field('wpmem-update-fields'); } ?>
	<table class="widefat">
		<thead><tr class="head">
        	<th scope="col" align="right"><?php _e('Field Label') ?></th>
			<th scope="col" align="center"><?php _e('Display?') ?></th>
            <th scope="col" align="center"><?php _e('Required?') ?></th>
            <th scope="col" align="center"><?php _e('WP Native?') ?></th>
        </tr></thead>
	<?php
	// order, label, optionname, input type, display, required, native
	$class = '';
	for ($row = 0; $row < count($wpmem_fields); $row++) {
		if ($chkreq == "err" && $wpmem_fields[$row][5] == 'y' && $wpmem_fields[$row][4] != 'y') {
			$class = "updated fade";
		} else {
			$class = ($class == 'alternate') ? '' : 'alternate';
		}
		?><tr class="<?php echo $class; ?>" valign="top">
        	<td><?php 
				echo $wpmem_fields[$row][1];
				if ($wpmem_fields[$row][5] == 'y'){ ?><font color="red">*</font><?php }
				?>
            </td>
		  <?php if ($wpmem_fields[$row][2]!='user_email'){ ?>
			<td><?php wpmem_create_formfield($wpmem_fields[$row][2]."_display",'checkbox', 'y', $wpmem_fields[$row][4]); ?></td>
            <td><?php wpmem_create_formfield($wpmem_fields[$row][2]."_required",'checkbox', 'y', $wpmem_fields[$row][5]); ?></td>
		  <?php } else { ?>
			<td colspan="2"><small><i>(The email field is mandatory and cannot be removed)</i></small></td>
		  <?php } ?>
			<td><?php if ($wpmem_fields[$row][6] == 'y') { echo "yes"; }?></td>
          </tr><?php
	}	?>
    	<tr>
        	<td colspan="6">
            	<input type="hidden" name="wpmem_admin_a" value="update_fields" />
        		<input type="submit" name="save" value="<?php _e('Update Fields'); ?> &raquo;" style="font-weight: bold;" class="button" />
            </td>
        </tr>
    </table>
    </form>
	<?php
}


function wpmem_a_build_dialogs($wpmem_dialogs)
{ 
	$wpmem_dialog_title_arr = wpmem_inc_dialog_title(); 
	?>
	<h3>WP-Members Dialogs and Error Messages</h3>
	<form name="updatedialogform" id="updatedialogform" method="post" action="<?php echo $_SERVER['PHP_SELF']?>?page=wp-members.php"> 
	<?php if ( function_exists('wp_nonce_field') ) { wp_nonce_field('wpmem-update-dialogs'); } ?>
		<table class="form-table">
		<tr>
			<td colspan="2">You can customize the following text.  Simple HTML is allowed - &lt;p&gt;, &lt;b&gt;, &lt;i&gt;, etc.</td>
		</tr>        
        <?php for ($row = 0; $row < count($wpmem_dialog_title_arr); $row++) { ?>
			<tr valign="top"> 
				<th scope="row"><?php echo $wpmem_dialog_title_arr[$row]; ?></th> 
				<td><textarea name="<?php echo "dialogs_".$row; ?>" rows="3" cols="50" id="" class="large-text code"><?php echo $wpmem_dialogs[$row]; ?></textarea></td> 
			</tr>
		<?php } ?>
			<tr valign="top"> 
				<th scope="row">&nbsp;</th> 
				<td>
					<input type="hidden" name="wpmem_admin_a" value="update_dialogs" />
					<input type="submit" name="save" value="<?php _e('Update Dialogs'); ?> &raquo;" style="font-weight: bold;" class="button" />
				</td> 
			</tr>				
		</table> 
	</form>
	<?php
}


function wpmem_admin()
{
	$wpmem_settings         = get_option('wpmembers_settings');
	$wpmem_fields           = get_option('wpmembers_fields');
	$wpmem_dialogs          = get_option('wpmembers_dialogs');
	
	switch ($_POST['wpmem_admin_a']) {
	
	case ("update_settings"):
		
		check_admin_referer('wpmem-update-settings');
		
		$wpmem_newsettings = array(
			WP_MEM_VERSION,
			$_POST['wpmem_settings_block_posts'],
			$_POST['wpmem_settings_block_pages'],
			$_POST['wpmem_settings_ignore_warnings']
		);
		
		update_option('wpmembers_settings',$wpmem_newsettings);
		$wpmem_settings = $wpmem_newsettings;
		$did_update = "true";
		break;
	
	case ("update_fields"):
	
		check_admin_referer('wpmem-update-fields');
	
		//rebuild the array, don't touch user_email - it's always mandatory
		for ($row = 0; $row < count($wpmem_fields); $row++) {
			
			for ($i = 0; $i < 4; $i++) {
				$wpmem_newfields[$row][$i] = $wpmem_fields[$row][$i];
			}
			
			$display_field = $wpmem_fields[$row][2]."_display"; 
			$require_field = $wpmem_fields[$row][2]."_required";
			
			if ($wpmem_fields[$row][2]!='user_email'){
				if ($_POST[$display_field] == "on") {$wpmem_newfields[$row][4] = 'y';}
				if ($_POST[$require_field] == "on") {$wpmem_newfields[$row][5] = 'y';}
			} else {
				$wpmem_newfields[$row][4] = 'y';
				$wpmem_newfields[$row][5] = 'y';		
			}
			
			if ($wpmem_newfields[$row][4] != 'y' && $wpmem_newfields[$row][5] == 'y') { $chkreq = "err"; }
			$wpmem_newfields[$row][6] = $wpmem_fields[$row][6];
		}
		
		update_option('wpmembers_fields',$wpmem_newfields);
		$wpmem_fields = $wpmem_newfields;
		$did_update = "true";
		break;
		
	case ("update_dialogs"):
	
		check_admin_referer('wpmem-update-dialogs');
	
		for ($row = 0; $row < count($wpmem_dialogs); $row++) {
			$dialog = "dialogs_".$row;
			$wpmem_newdialogs[$row] = $_POST[$dialog];
		}
		
		update_option('wpmembers_dialogs',$wpmem_newdialogs);
		$wpmem_dialogs = $wpmem_newdialogs;
		$did_update = "true";		
		break;

	}

	?>
    <div class="wrap">
	<div id="icon-options-general" class="icon32"><br /></div>
    <h2>WP-Members <?php _e('Settings'); ?></h2>
    
    <?php
	if ($did_update == "true") {
		
		if ($chkreq == "err") { ?>
			<div class="error"><p><strong>Settings were saved, but you have required field that are not set to display!</strong><br /><br />Note: 
				This will not cause an error for the end user, as only displayed fields are validated.  However, you should still check that 
				your displayed and required fields match up.  Mismatched fields are highlighted below.</p></div>
		<?php } else { ?>
			<div id="message" class="updated fade"><p><strong>Settings saved.</strong></p></div>
		<?php }
			
	}
	
	if (get_option('users_can_register') != 0 && $wpmem_settings[3] == 0) { ?>
	
		<div class="error"><p><strong>Your WP settings allow anyone to register - this is not the recommended setting.</strong>  You can <a href="options-general.php">change this here</a> making sure the box next to "Anyone can register" is unchecked.</p> [<span title="This setting allows a link on the /wp-login.php page to register using the WP native registration process thus circumventing any registration you are using with WP-Members. In some cases, this may suit the users wants/needs, but most users should uncheck this option. If you do not change this setting, you can choose to ignore these warning messages under WP-Members Settings.">why is this?</span>]</div>
	
	<?php }
	
	if (get_option('comment_registration') !=1 && $wpmem_settings[3] == 0) { ?>
	
		<div class="error"><p><strong>Your WP settings allow anyone to comment - this is not the recommended setting.</strong>  You can <a href="options-discussion.php">change this here</a> by checking the box next to "Users must be registered and logged in to comment."</p> [<span title="This setting allows any users to comment, whether or not they are registered. Depending on how you are using WP-Members will determine whether you should change this setting or not. If you do not change this setting, you can choose to ignore these warning messages under WP-Members Settings.">why is this?]</div>
	
	<?php } ?>
	
	<p><strong><a href="http://butlerblog.com/wp-members/" target="_blank">WP-Members</a> Version: <?php echo $wpmem_settings[0]; ?></strong>
		[ Follow ButlerBlog: <a href="http://feeds.butlerblog.com/butlerblog" target="_blank">RSS</a> | <a href="http://www.twitter.com/butlerblog" target="_blank">Twitter</a> ]
		<br />
		If you find this plugin useful, please consider making a donation <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
	<input type="hidden" name="cmd" value="_s-xclick">
	<input type="hidden" name="hosted_button_id" value="QC2W6AM9WUZML">
	<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
	<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
	</form>
	</p>
	
	<?php wpmem_a_build_options($wpmem_settings); ?>
	
	<p>&nbsp;</p>

	<?php wpmem_a_build_fields($wpmem_fields); ?>
	
	<p>&nbsp;</p>

	<?php wpmem_a_build_dialogs($wpmem_dialogs); ?>
	
	<p>&nbsp;</p>
	<p><i>Thank you for using WP-Members! You are using version <?php echo WP_MEM_VERSION; ?>. If you find this plugin useful, please consider a <a href="http://butlerblog.com/wp-members">donation</a>.<br />
	  WP-Members is copyright &copy; 2006-2010 by Chad Butler, <a href="http://butlerblog.com">butlerblog.com</a> | 
	  <a href="http://feeds.butlerblog.com/butlerblog" target="_blank">RSS</a> | <a href="http://www.twitter.com/butlerblog" target="_blank">Twitter</a></i></p>
	<p>&nbsp;</p>
</div>
<?php
}


/*****************************************************
END ADMIN FEATURES
*****************************************************/
?>