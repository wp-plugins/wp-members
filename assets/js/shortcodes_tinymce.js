(function () {
	"use strict";

	var wcShortcodeManager = function(editor, url) {
		var wcDummyContent = 'Sample Content';
		var wcParagraphContent = '<p>Sample Content</p>';


		editor.addButton('wpmem_shortcodes_button', function() {
			return {
				title: "WP-Members Shortcodes",
				tooltip: "WP-Members Shortcodes",
				icon: "user",
				type: 'menubutton',
				icons: false,
				menu: [
					{
						text: 'WP-Members Shortcodes',
					},
					{
						text: 'Login Forms',
						menu: [
							{
								text: "basic login",
								onclick: function(){
									editor.insertContent('[wpmem_form login]');
								}
							},
							{
								text: "login + redirect",
								onclick: function(){
									editor.insertContent('[wpmem_form login redirect_to="https://mysite.com/my-page/"]');
								}
							},
							{
								text: "login + logged in content",
								onclick: function(){
									editor.insertContent('[wpmem_form login]<br />This displays when logged in<br />[/wpmem_form]');
								}
							},
							{
								text: "login + redirect + content",
								onclick: function(){
									editor.insertContent('[wpmem_form login redirect_to="https://mysite.com/my-page/"]<br />This displays when logged in<br />[/wpmem_form]');
								}
							},
						]
					},
					{
						text: 'Registration Forms',
						menu: [
							{
								text: "basic registration",
								onclick: function(){
									editor.insertContent('[wpmem_form register]');
								}
							},
							{
								text: "registration + redirect",
								onclick: function(){
									editor.insertContent('[wpmem_form register redirect_to="https://mysite.com/my-page/"]');
								}
							},
							{
								text: "registration + logged in content",
								onclick: function(){
									editor.insertContent('[wpmem_form register]<br />This displays when logged in<br />[/wpmem_form]');
								}
							},
							{
								text: "registration + redirect + content",
								onclick: function(){
									editor.insertContent('[wpmem_form register redirect_to="https://mysite.com/my-page/"]<br />This displays when logged in<br />[/wpmem_form]');
								}
							},
						]
					},
					{
						text: 'User Profile',
						menu: [
							{
								text: "User Profile Page",
								onclick: function(){
									editor.insertContent('[wpmem_profile]');
								}
							},
							{
								text: "|"
							},
							{
								text: "Individual Components (optional):"
							},
							{
								text: "Password Reset/Change",
								onclick: function(){
									editor.insertContent('[wpmem_form password]');
								}
							},
							{
								text: "Password Reset Only",
								onclick: function(){
									editor.insertContent('[wpmem_logged_out][wpmem_form password][/wpmem_logged_out]');
								}
							},
							{
								text: "Password Change Only",
								onclick: function(){
									editor.insertContent('[wpmem_logged_in][wpmem_form password][/wpmem_logged_in]');
								}
							},
							{
								text: "User Data Edit",
								onclick: function(){
									editor.insertContent('[wpmem_logged_in][wpmem_form user_edit][/wpmem_logged_in]');
								}
							},
							{
								text: "Forgot Username",
								onclick: function(){
									editor.insertContent('[wpmem_logged_out][wpmem_form forgot_username][/wpmem_logged_out]');
								}
							},
						]
					},
					{
						text: 'Content Restriction',
						menu: [
							{
								text: "logged in content",
								onclick: function(){
									editor.insertContent('[wpmem_logged_in]<br />This displays when logged in<br />[/wpmem_logged_in]');
								}
							},
							{
								text: "logged out content",
								onclick: function(){
									editor.insertContent('[wpmem_logged_out]<br />This displays when logged out<br />[/wpmem_logged_out]');
								}
							},
						]
					},
					{
						text: 'Links',
						menu: [
							{
								text: "log in/log out link",
								onclick: function(){
									editor.insertContent('[wpmem_loginout]');
								}
							},
							{
								text: "|"
							},
							{
								text: "basic logout link",
								onclick: function(){
									editor.insertContent('[wpmem_logout]');
								}
							},
							{
								text: "logout link + custom link text",
								onclick: function(){
									editor.insertContent('[wpmem_logout]This the link text[/wpmem_logout]');
								}
							},
							{
								text: "|"
							},
							{
								text: "log in link",
								onclick: function(){
									editor.insertContent('[wpmem_login_link]');
								}
							},
							{
								text: "log in link + custom link text",
								onclick: function(){
									editor.insertContent('[wpmem_login_link]Link Text[/wpmem_login_link]');
								}
							},
							{
								text: "|"
							},
							{
								text: "register link",
								onclick: function(){
									editor.insertContent('[wpmem_reg_link]');
								}
							},
							{
								text: "register link + custom link text",
								onclick: function(){
									editor.insertContent('[wpmem_reg_link]Link Text[/wpmem_reg_link]');
								}
							}
						]
					},
					{
						text: 'User Fields',
						onclick: function(){
							editor.insertContent('[wpmem_field field=user_login]');
						}
					},
					{
						text: 'User Count',
						onclick: function(){
							editor.insertContent('[wpmem_show_count label="Active Users: " key=active value=1]');
						}
					},
					{
						text: 'User Avatar',
						onclick: function(){
							editor.insertContent('[wpmem_avatar]');
						}
					},
				]
			}
		});
	};
	
	tinymce.PluginManager.add( "wpmem_shortcodes", wcShortcodeManager );
})();
