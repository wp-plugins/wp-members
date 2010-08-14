<?php
/*
	This file is part of the WP-Members plugin by Chad Butler
	
	You can find out more about this plugin at http://butlerblog.com/wp-members
  
	Copyright (c) 2006-2010  Chad Butler (email : plugins@butlerblog.com)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 3, as 
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

	You may also view the license here:
	http://www.gnu.org/licenses/gpl.html
*/


/*****************************************************
DIALOG OUTPUT FUNCTIONS
*****************************************************/


function wpmem_inc_login($page='page')
{ 	
	global $wpmem_regchk;

	$wpmem_dialogs = get_option('wpmembers_dialogs');

	if($page == "page"){
	     if($wpmem_regchk!="success"){
		
		//this shown above blocked content ?>
		<p><?php echo $wpmem_dialogs[0]; ?></p>

	<?php } 	
	} 

    $wpmem_login_form_arr = array('Existing users Login', 'Username', 'text', 'log', 'Password', 'password', 'pwd', 'login', 'Login');
    wpmem_login_form( $page, $wpmem_login_form_arr );
}


function wpmem_inc_changepassword()
{ 
	$wpmem_login_form_arr = array('Change Password', 'New Password', 'password', 'pass1', 'Repeat Password', 'password', 'pass2', 'pwdchange', 'Update Password');
    wpmem_login_form( 'page', $wpmem_login_form_arr );
}


function wpmem_inc_resetpassword()
{ 
	$wpmem_login_form_arr = array('Reset Forgotten Password', 'Username', 'text', 'user', 'Email', 'text', 'email', 'pwdreset', 'Reset Password');
    wpmem_login_form( 'page', $wpmem_login_form_arr );
}


function wpmem_login_form( $page, $wpmem_login_form_arr ) 
{ ?>	
	  <div class="wpmem_login">
		  <form name="form" method="post" action="<?php the_permalink();?>">
			  <table width="400" border="0" cellspacing="0" cellpadding="4">
				<tr align="left"> 
				  <td colspan="2"><h2><?php echo $wpmem_login_form_arr[0]; ?></h2></td>
				</tr>
				<tr> 
				  <td width="118" align="right"><?php echo $wpmem_login_form_arr[1]; ?></td>
				  <td width="166"><?php wpmem_create_formfield( $wpmem_login_form_arr[3], $wpmem_login_form_arr[2], '' ); ?></td>
				</tr>
				<tr> 
				  <td width="118" align="right"><?php echo $wpmem_login_form_arr[4]; ?></td>
				  <td width="166"><?php wpmem_create_formfield( $wpmem_login_form_arr[6], $wpmem_login_form_arr[5], '' ); ?></td>
				</tr>
			<?php if ( $wpmem_login_form_arr[7] == 'login' ) { ?>
				<tr>
				  <td width="118">&nbsp;</td>
				  <td width="166"><input name="rememberme" type="checkbox" id="rememberme" value="forever" /> Remember me</td>
				</tr>
			<?php } ?>
				<tr> 
				  <td width="118">&nbsp;</td>
				  <td width="166">
					<input type="hidden" name="redirect_to" value="<?php the_permalink(); ?>" /><?php
					if ( $wpmem_login_form_arr[7] != 'login' ) { wpmem_create_formfield( 'formsubmit', 'hidden', '1' ); }
					wpmem_create_formfield( 'a', 'hidden', $wpmem_login_form_arr[7] ); ?>
					<input type="submit" name="Submit" value="<?php echo $wpmem_login_form_arr[8]; ?>" />
				  </td>
				</tr>
			<?php if ( $page = 'members' && $wpmem_login_form_arr[7] == 'login' ) { 
				$link = wpmem_chk_qstr(); ?>
				<tr>
				  <td colspan="2">Forgot password? <a href="<?php echo $link; ?>a=pwdreset">Click here to reset</a></td>
				</tr>
			<?php } ?>
			  </table> 
		  </form>
	  </div><?php
}


function wpmem_inc_loginfailed() 
{ 
	/* 
	failed login message.  
	you can customize this to fit your theme, etc.

	You may edit below this line */?>

	<div align="center">
		<h2>Login Failed!</h2>
		<p>You entered an invalid username or password.</p>
		<p><a href="<?php echo $_SERVER['REQUEST_URI'];?>">Click here to continue.</a></p>
	</div>

	<?php  // end edits for function wpmem_inc_loginfailed()
}


function wpmem_inc_registration($fields,$toggle = 'new',$heading = '')
{
	global $wpdb,$user_ID, $userdata,$securify,$wpmem_regchk,$username,$wpmem_fieldval_arr;

	if (!$heading) { $heading = "<h2>New Users Registration</h2>"; }
	if (is_user_logged_in()) { get_currentuserinfo(); }	?>

	<div class="wpmem_reg">
		<form name="form2" method="post" action="<?php the_permalink();//wpmem_chk_qstr();?>">

		  <table width="400" border="0" cellspacing="0" cellpadding="4">
			<tr align="left"> 
			  <td colspan="2"><?php echo $heading; ?></td>
			</tr>
			<?php if ($toggle == 'edit') { ?>
			<tr> 
			  <td width="49%" align="right">Username:</td>
			  <td width="51%" align="left"><?php echo $userdata->user_login?></td>
			</tr>			
			<?php } else { ?>
			<tr> 
			  <td width="49%" align="right">Choose a Username <font color="red">*</font></td>
			  <td width="51%"><input name="log" type="text" value="<?php echo $username;?>" /></td>
			</tr>
			<?php } ?>
			<tr> 
			  <td colspan="2">&nbsp;</td>
			</tr>

			<?php
			$wpmem_fields = get_option('wpmembers_fields');
			for ($row = 0; $row < count($wpmem_fields); $row++)
			{ 
				if ($wpmem_fields[$row][4] == 'y') { ?>
					<tr<?php if( $wpmem_fields[$row][2] == 'description' ){ echo " valign=\"top\""; } ?>>
						<td align="right"><?php 
							echo $wpmem_fields[$row][1].":";
							if ($wpmem_fields[$row][5] == 'y') { ?><font color="red">*</font><?php } ?>
						</td>
						<td>
						<?php 
						if (($toggle == 'edit') && ($wpmem_regchk != 'updaterr')) {
							switch ($wpmem_fields[$row][2]) {
							case('description'):
								$val = get_usermeta($user_ID,'description');
								break;

							case('user_email'):
								$val = $userdata->user_email;
								break;

							case('user_url'):
								$val = $userdata->user_url;
								break;

							default:				
								$val = get_usermeta($user_ID,$wpmem_fields[$row][2]);
								break;
							}

						} else {

							$val = $wpmem_fieldval_arr[$row]; 

						}

						wpmem_create_formfield($wpmem_fields[$row][2],$wpmem_fields[$row][3],$val,'');
						?>
						</td>
					</tr>
				<?php } 
			} ?>
			<tr><td colspan="2">&nbsp;</td></tr>
			<tr> 
			  <td align="right">&nbsp;</td>
			  <td>
			  <?php if ($toggle == 'edit') { ?>
				<input name="a" type="hidden" value="update" />
			  <?php } else { ?>
				<input name="a" type="hidden" value="register" />
			  <?php } ?>
				<input name="redirect_to" type="hidden" value="<?php the_permalink();?>" />
				<input name="Submit" type="submit" value="submit" /> 
				&nbsp;&nbsp; 
				<input name="Reset" type="reset" value="Clear Form" />
			  </td>
			</tr>
			<tr>
			  <td>&nbsp;</td>
			  <td><font color="red">*</font> Required field</td>
			</tr>
			<tr>
			  <td>&nbsp;</td>
			  <td align="center"><!-- Attribution keeps this plugin free!! -->
				<small>Powered by <a href="http://butlerblog.com/wp-members" target="_blank">WP-Members</a><small>
			  </td>
			</tr>
		  </table>
		</form>
	</div>
	<?php
}


function wpmem_inc_memberlinks()
{
	$link = wpmem_chk_qstr();
	$str  = "<ul>\n<li><a href=\"".$link."a=edit\">Edit My Information</a></li>\n
			<li><a href=\"".$link."a=pwdchange\">Change Password</a></li>\n</ul>";
	return $str;
}


function wpmem_inc_regmessage($toggle,$themsg='')
{ 
	$wpmem_dialogs = get_option('wpmembers_dialogs');
	$wpmem_dialogs_toggle = array('user','email','success','editsuccess','pwdchangerr','pwdchangesuccess','pwdreseterr','pwdresetsuccess');

	for ($row = 0; $row < count($wpmem_dialogs_toggle); $row++) {

		if ($toggle == $wpmem_dialogs_toggle[$row]) { ?>

			<div class="wpmem_msg" align="center">
				<p>&nbsp;</p>
				<p><b><?php echo $wpmem_dialogs[$row+1]; ?></b></p>
				<p>&nbsp;</p>
			</div>

			<?php
			$didtoggle = "true";
		}	
	}

	if ($didtoggle != "true") { ?>

		<div class="wpmem_msg" align="center">
			<p>&nbsp;</p>
			<p><b>Sorry, <?php echo $themsg; ?></b></p>
			<p>&nbsp;</p>
		</div>

	<?php }		
}


function wpmem_inc_dialog_title()
{
	$wpmem_dialog_title_arr = array(
    	"Restricted post (or page), displays above the login/registration form",
        "Username is taken",
        "Email is registered",
        "Registration completed",
        "User update",
        "Passwords did not match",
        "Password changes",
        "Username or email do not exist when trying to reset forgotten password",
        "Password reset"  
    );
	return $wpmem_dialog_title_arr;
}


/*****************************************************
END DIALOG OUTPUT FUNCTIONS
*****************************************************/
?>