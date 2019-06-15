<?php
/**
 * WP-Members TOS Page
 *
 * Generates teh Terms of Service pop-up.
 * 
 * This file is part of the WP-Members plugin by Chad Butler
 * You can find out more about this plugin at https://rocketgeek.com
 * Copyright (c) 2006-2019  Chad Butler
 * WP-Members(tm) is a trademark of butlerblog.com
 *
 * @package WP-Members
 * @author Chad Butler
 * @copyright 2006-2019
 */
?>

<html>
<head>
	<title><?php _e( 'Terms of Service', 'wp-members' ); ?> | <?php bloginfo( 'name' ); ?></title>
</head>

<body>

<?php

$wpmem_tos = get_option( 'wpmembers_tos' );

echo stripslashes( $wpmem_tos );

print ( '<br /><br />' );
printf( __('%sclose%s', 'wp-members'), '[<a href="javascript:self.close()">', '</a>]' );
print ( '&nbsp;&nbsp;' );
printf( __('%sprint%s', 'wp-members'), '[<a href="javascript:window.print()">', '</a>]' );

?>

</body>
</html>