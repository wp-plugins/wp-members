<?php
$utms = array( 
    'utm_source'   => get_site_url(),
    'utm_medium'   => 'wp-members-core-plugin',
    'utm_campaign' => 'plugin-install',
);

$action_complete = ( 'update_pending' == $install_state ) ? __( 'Plugin update complete', 'wp-members' ) : __( 'Plugin installation complete', 'wp-members' );
?>
<div id="wpmem_onboarding">
    <div class="wrapper">
        <h1><?php echo $onboarding_title; ?></h1>
        <p class="section_lead"><?php echo $action_complete; ?></p>

        <?php if ( 'update_pending' == $install_state ) { ?>
            <ul>
                <li>&raquo; <a href="<?php echo $onboarding_release_notes . "?" . http_build_query( $utms ); ?>" target="_blank"><?php printf( __( 'WP-Members version %s release notes', 'wp-members' ), $onboarding_version ); ?></a></li>
                <li>&raquo; <a href="https://rocketgeek.com/plugins/wp-members/docs/?<?php echo http_build_query( $utms ); ?>" target="_blank"><?php _e( 'WP-Members documentation', 'wp-members' ); ?></a></li>
            </ul>
        <?php } else { ?>
            <p>WP-Members installs some basic defaults to get you started. Be sure to review <a href="<?php echo admin_url(); ?>options-general.php?page=wpmem-settings">the plugin's default setup here</a>.
            There are links to related documentation in the plugin settings.  There are also some helpful links below.</p>
            <ul>
                <li>&raquo; <a href="<?php echo $onboarding_release_notes . "?" . http_build_query( $utms ); ?>" target="_blank"><?php printf( __( 'WP-Members version %s release notes', 'wp-members' ), $onboarding_version ); ?></a></li>
                <li>&raquo; <a href="https://rocketgeek.com/plugins/wp-members/docs/?<?php echo http_build_query( $utms ); ?>" target="_blank"><?php _e( 'WP-Members documentation', 'wp-members' ); ?></a></li>
            </ul>       
        <?php } ?>
        <h2><?php _e( 'Want more features?', 'wp-members' ); ?></h2>
        <p>There are <a href="https://rocketgeek.com/store/?<?php echo http_build_query( $utms ); ?>" target="_blank">premium plugin add-ons</a> available as well as a <a href="https://rocketgeek.com/plugins/wp-members/support-options/?<?php echo http_build_query( $utms ); ?>" target="_blank">discounted bundle</a>.</p>
        <h2>Need more help?</h2>
        <p>If you need additional assistance, consider a <a href="https://rocketgeek.com/plugins/wp-members/support-options/?<?php echo http_build_query( $utms ); ?>" target="_blank">premium support subscription</a>.</p>
        <p>&nbsp;</p>
        <ul>
            <li>&raquo; <a href="<?php echo admin_url() . 'options-general.php?page=wpmem-settings'; ?>"><?php _e( 'Go to WP-Members settings', 'wp-members' ); ?></a></li>
            <li>&raquo; <a href="<?php echo admin_url() . 'plugins.php'; ?>"><?php _e( 'Go to Plugins page', 'wp-members' ); ?></a></li>
            <li>&raquo; <a href="<?php echo admin_url() . 'update-core.php'; ?>"><?php _e( 'Go to WordPress Updates page', 'wp-members' ); ?></a></li>
        </ul>
    </div>
</div>