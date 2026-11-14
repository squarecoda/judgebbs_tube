<div class="bhs-login-form-wrapper">

	<?php if(!empty($_GET['password_reset_sent'])) : ?>
		<?php $message = 'Check your email for the confirmation link, then visit the <a href="%s">login page</a>.' ?>
		<?php echo sc_get_message($message, 'info'); ?>
	<?php endif ?>

	<div class="bhs-login-form">
		<div class="bhs-login-form-inner">
			<div class="form-header">
				<?php //echo $this->get_svg_markup('logo-no-border') ?>
				<div class="member-menu-title">Judge Login</div>
			</div>

			<div class="form-body">
				<div class="login-form">
					<?php echo wp_login_form(['echo' => false]) ?>
					<a class="reset-password-link" href="#">Forgot your password?</a>
				</div>
				<div class="reset-password">
					<?php $message = 'Please enter your username or email address. You will receive an email message with instructions on how to reset your password.' ?>
					<?php echo sc_get_message($message, 'info'); ?>
					<?php include 'password-reset-form.php' ?>
					<a class="login-form-link" href="#">Return to Login Form</a>
				</div>
			</div>
		</div>
	</div>
	
</div>
