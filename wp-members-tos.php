<?php
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