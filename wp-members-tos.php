<?php
/**
 * WP-Members TOS Page
 *
 * Generates teh Terms of Service pop-up.
 * 
 * This file is part of the WP-Members plugin by Chad Butler
 * You can find out more about this plugin at http://butlerblog.com/wp-members
 * Copyright (c) 2006-2011  Chad Butler (email : plugins@butlerblog.com)
 * WP-Members(tm) is a trademark of butlerblog.com
 *
 * @package WordPress
 * @subpackage WP-Members
 * @version 2.5.3
 * @author Chad Butler
 * @copyright 2006-2011
 */

define('WP_USE_THEMES', false);
require('../../../wp-blog-header.php');
?>

<html>
<head>
<title>Terms of Service | <?php bloginfo('name'); ?></title>
</head>

<body>

<?php

$wpmem_tos = get_option('wpmembers_tos');

echo $wpmem_tos;

echo "[<a href=\"javascript:self.close()\">close</a>]\r\n";
echo "&nbsp;&nbsp;";
echo "[<a href=\"javascript:window.print()\">print</a>]\r\n";
?>

</body>
</html>