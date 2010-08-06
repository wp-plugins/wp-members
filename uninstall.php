<?php
if ( WP_UNINSTALL_PLUGIN ) {

	delete_option( 'wpmembers_settings' );
	delete_option( 'wpmembers_fields' );
	delete_option( 'wpmembers_dialogs' );
	
}
?>