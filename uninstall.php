<?php
if ( WP_UNINSTALL_PLUGIN ) {

	delete_option( 'wpmembers_settings' );
	delete_option( 'wpmembers_fields'   );
	delete_option( 'wpmembers_dialogs'  );
	delete_option( 'wpmembers_captcha'  );
	delete_option( 'wpmembers_tos'      );
	delete_option( 'wpmembers_export'   );
	delete_option( 'widget_wpmemwidget' );
	delete_option( 'wpmembers_msurl'    );
	
	if (WPMEM_EXP_MODULE == true) {
		delete_option( 'wpmembers_experiod' );
	}
	
}
?>