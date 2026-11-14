<form name="lostpasswordform" id="lostpasswordform" action="<?php echo esc_url( network_site_url( 'wp-login.php?action=lostpassword', 'login_post' ) ); ?>" method="post">
	<p>
		<label for="user_login"><?php _e( 'Username or Email Address' ); ?></label>
		<input type="text" name="user_login" id="user_login" class="input" value="" size="20" autocapitalize="off" />
	</p>

	<input type="hidden" name="redirect_to" value="<?php echo get_the_permalink() . '?password_reset_sent=true' ?>" />
	<p class="submit">
		<input type="submit" name="wp-submit" id="wp-submit" class="button button-primary button-large" value="<?php esc_attr_e( 'Get New Password' ); ?>" />
	</p>
</form>