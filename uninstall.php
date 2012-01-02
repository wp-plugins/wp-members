<?php
/**
 * WP-Members Uninstall
 *
 * Removes all settings WP-Members added to the WP options table
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
 
/**
 * Unistall process removes WP-Members settings from the WordPress database (_options table)
 */
if ( WP_UNINSTALL_PLUGIN ) {

	delete_option( 'wpmembers_settings' );
	delete_option( 'wpmembers_fields'   );
	delete_option( 'wpmembers_dialogs'  );
	delete_option( 'wpmembers_captcha'  );
	delete_option( 'wpmembers_tos'      );
	delete_option( 'wpmembers_export'   );
	delete_option( 'widget_wpmemwidget' );
	delete_option( 'wpmembers_msurl'    );
	delete_option( 'wpmembers_regurl'   );
	delete_option( 'wpmembers_cssurl'   );
	
	if (WPMEM_EXP_MODULE == true) {
		delete_option( 'wpmembers_experiod' );
	}
	
}
?>