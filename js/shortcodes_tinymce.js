(function () {
	"use strict";

	tinymce.create('tinymce.plugins.wpmemShortcodeMce', {
		init : function(ed, url){
			tinymce.plugins.wpmemShortcodeMce.theurl = url;
		},
		createControl : function(btn, e) {
			if ( btn === "wpmem_shortcodes_button" ) {
				var a = this;

				// out puts an js error when clicking on button
				// btn = e.createSplitButton('wpmem_shortcodes_button', {

				btn = e.createMenuButton('wpmem_shortcodes_button', {
					title: "Insert Shortcode",
					image: tinymce.plugins.wpmemShortcodeMce.theurl +"/images/shortcodes.png",
					icons: false,
				});
				btn.onRenderMenu.add(function (c, b) {
					
					b.add({title : 'WP-Members Shortcodes', 'class' : 'mceMenuItemTitle'}).setDisabled(1);
					
					
					// Login Forms
					c = b.addMenu({title:"Login Forms"});
					
						a.render( c, "login", "login" );
					
					b.addSeparator();
					
					
					// Registration Forms
					c = b.addMenu({title:"Registration Forms"});
									
						a.render( c, "register", "register" );
					
					b.addSeparator();
					
					// Other Forms
					c = b.addMenu({title:"Other Forms"});
						
					b.addSeparator();
					
					// Content Restriction
					c = b.addMenu({title:"Content Restriction"});
						
					b.addSeparator();
					
					
					// Logout Link
					c = b.addMenu({title:"Logout Link"});
					
						
					b.addSeparator();
					
					
					// User Fields
					c = b.addMenu({title:"User Fields"});
				
					
					b.addSeparator();
					
					
					// User Count
					c = b.addMenu({title:"User Count"});
					
					
				});

				return btn;
			}
			return null;
		},
		render : function(ed, title, id) {
			ed.add({
				title: title,
				onclick: function () {
					
					var mceSelected = tinyMCE.activeEditor.selection.getContent();
		
					var wpmemDummyContent;
					if ( mceSelected ) {
						wpmemDummyContent = mceSelected;
					}
				
					if(id === "login") {
						tinyMCE.activeEditor.selection.setContent('[wpmem_form login]');
					}
					if(id === "register") {
						tinyMCE.activeEditor.selection.setContent('[wpmem_form register]');
					}
	

					return false;
				}
			});
		}
	
	});
	tinymce.PluginManager.add("wpmem_shortcodes", tinymce.plugins.wpmemShortcodeMce);
})();
