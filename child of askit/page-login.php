<?php
/*
Template Name: Login Page
*/
?>
<?php
	$et_ptemplate_settings = array();
	$et_ptemplate_settings = maybe_unserialize( get_post_meta(get_the_ID(),'et_ptemplate_settings',true) );

	$fullwidth = isset( $et_ptemplate_settings['et_fullwidthpage'] ) ? (bool) $et_ptemplate_settings['et_fullwidthpage'] : false;
?>

<?php get_header(); ?>

<div id="main-area"<?php if ($fullwidth) echo ' class="fullwidth"'; ?>>

	<?php // get_template_part('includes/breadcrumbs'); ?>

	<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

		<?php if (get_option('askit_integration_single_top') <> '' && get_option('askit_integrate_singletop_enable') == 'on') echo(get_option('askit_integration_single_top')); ?>

		<div class="entry page">
			<div class="entry-top">

				<div class="entry-content">
					<h2 class="title"><?php the_title(); ?></h2>
					<div class="clear"></div>

					<div class="page-separator"></div>

					<div class="post-content">
						<?php the_content(); ?>

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
							
								<div class="question-area">
									<?php require_once( ABSPATH . '/wp-content/plugins/dw-question-answer/inc/templates/default/question-submit-form.php' ); ?>
								</div>
							</div><!-- end #et-login -->
						</div> <!-- end #login-area -->

					
						<?php edit_post_link(esc_html__('Edit this page','AskIt')); ?>

					</div><!-- .post-content -->
				</div> <!-- end .entry-content -->
			</div> <!-- end .entry-top -->
		</div> <!-- end .entry -->

		<div class="clear"></div>

		<?php if (get_option('askit_integration_single_bottom') <> '' && get_option('askit_integrate_singlebottom_enable') == 'on') echo(get_option('askit_integration_single_bottom')); ?>

	<?php endwhile; endif; ?>

</div> <!-- end #main-area -->


<?php get_footer(); ?>