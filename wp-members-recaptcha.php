 <?php
/*
	This file is part of the WP-Members plugin by Chad Butler
	
	You can find out more about this plugin at http://butlerblog.com/wp-members
  
	Copyright (c) 2006-2011  Chad Butler (email : plugins@butlerblog.com)
	
	WP-Members(tm) is a trademark of butlerblog.com
*/


function wpmem_show_captcha()
{
	$wpmem_captcha = get_option('wpmembers_captcha'); 

	

				if ( $wpmem_captcha[0] && $wpmem_captcha[1] ) { ?>
						
					<div class="clear"></div>
					<div align="right" >
						<script type="text/javascript" src="http://www.google.com/recaptcha/api/js/recaptcha_ajax.js"></script>
						<script type="text/javascript">
							function showRecaptcha(element) 
							{
								Recaptcha.create("<?php echo $wpmem_captcha[0]; ?>", element, {
									theme: "<?php echo $wpmem_captcha[2]; ?>",
									callback: Recaptcha.focus_response_field});
							}
						</script>
						<div id="recaptcha_div"></div>
						<script type="text/javascript">showRecaptcha('recaptcha_div');</script>
					</div>
            
				<?php } 
?>
 <div id="recaptcha_widget" style="display:none">

   <div id="recaptcha_image"></div>
   <div class="recaptcha_only_if_incorrect_sol" style="color:red">Incorrect please try again</div>

   <span class="recaptcha_only_if_image">Enter the words above:</span>
   <span class="recaptcha_only_if_audio">Enter the numbers you hear:</span>

   <input type="text" id="recaptcha_response_field" name="recaptcha_response_field" />

   <div><a href="javascript:Recaptcha.reload()">Get another CAPTCHA</a></div>
   <div class="recaptcha_only_if_image"><a href="javascript:Recaptcha.switch_type('audio')">Get an audio CAPTCHA</a></div>
   <div class="recaptcha_only_if_audio"><a href="javascript:Recaptcha.switch_type('image')">Get an image CAPTCHA</a></div>

   <div><a href="javascript:Recaptcha.showhelp()">Help</a></div>

 </div>

 <script type="text/javascript"
    src="http://www.google.com/recaptcha/api/challenge?k=your_public_key">
 </script>
 <noscript>
   <iframe src="http://www.google.com/recaptcha/api/noscript?k=your_public_key"
        height="300" width="500" frameborder="0"></iframe><br>
   <textarea name="recaptcha_challenge_field" rows="3" cols="40">
   </textarea>
   <input type="hidden" name="recaptcha_response_field"
        value="manual_challenge">
 </noscript>
 
<?php } ?>