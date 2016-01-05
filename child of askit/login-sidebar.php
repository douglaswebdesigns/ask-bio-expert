<?php


?>
<div class="login-area">
	<div id="et-login">
		<div class='et-protected'>
			<div class='et-protected-form'>
			
				<?php $scheme = apply_filters( 'et_forms_scheme', null ); ?>
					<form action='<?php echo esc_url( home_url( '', $scheme ) ); ?>/wp-login.php' method='post'>
						<p><label><span><?php esc_html_e('Username','AskIt'); ?>: </span><input type='text' name='log' id='log' value='<?php echo esc_attr($user_login); ?>' size='20' /><span class='et_protected_icon'></span></label></p>
						<p><label><span><?php esc_html_e('Password','AskIt'); ?>: </span><input type='password' name='pwd' id='pwd' size='20' /><span class='et_protected_icon et_protected_password'></span></label></p>
						<a href="http://www.your-new-wp-site.com/askabioexpert/register/"><img src="http://www.your-new-wp-site.com/askabioexpert/wp-content/uploads/2015/12/sign-up-buttons.png" style="float: left"></a>
						<input type='submit' name='submit' value='Login' class='etlogin-button' />
					</form>
			</div> <!-- .et-protected-form -->
		</div> <!-- .et-protected -->
	</div><!-- .et-login -->
</div><!-- .login-area -->

?>