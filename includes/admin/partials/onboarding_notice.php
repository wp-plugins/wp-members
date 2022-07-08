<?php $which = ( 'update_pending' == $install_state ) ? 'update_pending_notice_args' : 'new_install_notice_args'; ?>
<div class="notice notice-info">
<form action="index.php?page=<?php echo $this->menu_slug; ?>" method="post">
    <p style="font-weight:bold;"><?php echo $this->{$which}['notice_heading']; ?></p>

<?php if ( $this->{$which}['show_release_notes'] ) { ?>
    <p class="description"><a href="<?php echo $this->{$which}['release_notes_link']; ?>" target="_blank"><?php _e( 'Read the release notes', 'wp-members' ); ?></a></p>
<?php } ?>
<?php if ( false == $this->has_user_opted_in() ) { ?>
    <h3><?php _e( 'Never miss an important update!', 'wp-members' ); ?></h3>
    <p><input type="checkbox" name="optin" value="1" checked />
        <?php _e( 'Opt-in to our security and feature updates notifications and non-sensitive diagnostic tracking.', 'wp-members' );?>
    </p>
    <p class="description">
        <?php _e( 'This is only so we know how the plugin is being used so we can make it better and more secure.', 'wp-members' ); ?><br />
        <?php _e( 'We do not track any personal information, and no data is ever shared with third parties!', 'wp-members' ); ?>
    </p>
    <?php } ?>
    <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="<?php echo $this->{$which}['notice_button']; ?> &raquo;"></p>
    <input type="hidden" name="page" value="wp-members-onboarding" />
    <input type="hidden" name="step" value="finalize">
</form>
</div>