<?php

class WP_Members_CLI_User {
	
	public function activate( $args ) {
		
	}
	
	public function deactivate( $args ) {
		
	}
}

WP_CLI::add_command( 'mem user', 'WP_Members_CLI_User' );