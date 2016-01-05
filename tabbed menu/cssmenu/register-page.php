<?php
/*
Template Name: Register Page Template
*/
?>
<?php get_header(); ?>

<div id="main-area">

	<div id='cssmenu'>
		<ul>
		   <li class='active'><a href='#'><span>Dairy</span></a></li>
		   <li><a href='#'><span>Bev Acid Food</span></a></li>
		   <li><a href='#'><span>Applied Methods</span></a></li>
		   <li><a href='#'><span>Molecular Analytical</span></a></li>
		   <li><a href='#'><span>Food Law</span></a></li>
		   <li><a href='#'><span>Fruit &amp; Vegetable</span></a></li>
		   <li class='last'><a href='#'><span>Food Safety</span></a></li>
		</ul>
	</div>

	<?php // get_template_part('includes/breadcrumbs'); ?>

	<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

		<?php if (get_option('askit_integration_single_top') <> '' && get_option('askit_integrate_singletop_enable') == 'on') echo(get_option('askit_integration_single_top')); ?>

		<div class="entry page">
			<div class="entry-top">
				<div class="entry-content">
					<h2 class="title">User Control</h2>
					<div class="clear"></div>

					<div class="page-separator"></div>

					<div class="post-content">
						<?php if ( current_user_can('manage_options') && !get_option('users_can_register') ) { ?>
							<p><?php esc_html_e('Note: You can activate users registration on WP-Admin / Setting / General page', 'AskIt'); ?></p>
						<?php } ?>

						<?php if ( !is_user_logged_in() ) { ?>

							<?php if ( !get_option('users_can_register') ) { ?>
								<h3><?php esc_html_e('Users cannot currently register themselves, only administrator can manually create users.', 'AskIt' ); ?></h3>
							<?php } else { ?>
								<div id="register-form">
									<div class="title">
										<h2><?php esc_html_e('Registered User Login', 'AskIt'); ?></h2>
									</div>
									<?php wp_login_form( array( 'redirect' => get_permalink() ) ); ?>

									<div class="clear"></div>

									<div class="page-separator"></div>

									<div class="title">
										<h2><?php esc_html_e('Register Your Account', 'AskIt'); ?></h2>
									</div>
									<form action="<?php echo esc_url( site_url( 'wp-login.php?action=register', 'login_post' ) ); ?>" method="post">
										<p class="clearfix">
											<label for="user_login" class="inputlable"><?php esc_html_e('Requested Username','AskIt'); ?>:</label>
											<input type="text" name="user_login" value="" id="user_login" class="input" />
										</p>

										<p class="clearfix">
											<label for="user_email" class="inputlable"><?php esc_html_e('E-Mail','AskIt'); ?>:</label>
											<input type="text" name="user_email" value="" id="user_email" class="input"  />
										</p>

										<?php do_action('register_form'); ?>
										<p><input type="submit" value="<?php esc_attr_e('SUBMIT','AskIt'); ?>" id="register" class="register" /></p>

										<p class="statement"><?php esc_html_e('A password will be e-mailed to you.', 'AskIt');?></p>
									</form>
								</div>
							<?php } ?>

						<?php } else { ?>

							<?php
								$post_created_error = '';
								$result = false;

								if ( isset( $_POST['et_add_answer'] ) && isset( $_POST['_wpnonce-et-register-form-submitted'] ) && wp_verify_nonce( $_POST['_wpnonce-et-register-form-submitted'], 'et-register-form-submit' ) ) {
									if ( $_POST['et_newpost_title'] <> '' && $_POST['et_newpost_content'] <> '' ) {
										$my_post = array(
											'post_title' => esc_attr( $_POST['et_newpost_title'] ),
											'post_content' =>  wp_kses_post( $_POST['et_newpost_content'] ),
											'tags_input' => esc_attr( $_POST['et_newpost_tags'] ),
											'post_category' => array( (int) $_POST['et_newpost_category'] ),
											'post_status' => 'publish'
										);

										$result = wp_insert_post( $my_post );
									} else {
										$post_created_error = '<p id="create_done">'.esc_html__('Make sure you fill all fields', 'AskIt').'</p>';
									}
								} ?>

								<div id="create_new_post">
									<?php if ($result == false) { ?>
										<?php echo $post_created_error; ?>
										<form action="<?php echo esc_url( get_permalink( get_the_ID() ) ); ?>#create_new_post" method="post">
											<p class="clearfix">
												<label for="et_newpost_title" class="inputlable"><?php esc_html_e('Title','AskIt'); ?>:</label>
												<input type="text" name="et_newpost_title" value="<?php if ( isset( $_POST['et_newpost_title'] ) ) esc_attr_e( $_POST['et_newpost_title'] ); ?>" id="et_newpost_title" class="input" />
											</p>

											<p class="clearfix">
												<label for="et_newpost_tags" class="inputlable"><?php _e('Tags','AskIt'); ?>:</label>
												<input type="text" name="et_newpost_tags" value="<?php if ( isset( $_POST['et_newpost_tags'] ) ) esc_attr_e( $_POST['et_newpost_tags'] ); ?>" id="et_newpost_tags" class="input" />
												<small><?php esc_html_e('* separated with commas','AskIt'); ?></small>
											</p>

											<p class="clearfix">
												<label for="et_newpost_category" class="inputlable"><?php _e('Category', 'AskIt'); ?>:</label>
												<?php wp_dropdown_categories( apply_filters( 'et_register_category_dropdown', array('hide_empty' => 0, 'hierarchical' => 1, 'id' =>'et_newpost_category', 'name' => 'et_newpost_category') ) ); ?>
											</p>

											<p class="clearfix">
												<label for="et_newpost_content" class="inputlable"><?php esc_html_e('Content', 'AskIt');?>:</label>
												<textarea name="et_newpost_content" id="et_newpost_content" class="input"><?php if ( isset( $_POST['et_newpost_content'] ) ) echo esc_textarea( $_POST['et_newpost_content'] ); ?></textarea>
											</p>

											<input type="hidden" name="et_add_answer" value="proccess" />
											<input class="register" type="submit" value="<?php esc_attr_e('Submit','AskIt'); ?>" id="submit" />

											<?php wp_nonce_field( 'et-register-form-submit', '_wpnonce-et-register-form-submitted' ); ?>
										</form>
									<?php } else { ?>
										<p id="create_done"><?php esc_html_e('The post was created.','AskIt'); ?> <a href="<?php echo( esc_url( get_permalink($result) ) ); ?>"><?php esc_html_e('View the post','AskIt'); ?></a> | <a href="<?php echo esc_url(get_permalink(get_pageId(get_option('askit_answer_page')))); ?>"><?php esc_html_e('Add another.','AskIt'); ?></a></p>
									<?php } ?>
								</div> <!-- end #create_new_post -->

						<?php } ?>
						<div class="clear"></div>
					</div>
				</div> <!-- end .entry-content -->
			</div> <!-- end .entry-top -->
		</div> <!-- end .entry -->

		<div class="clear"></div>

		<?php if (get_option('askit_integration_single_bottom') <> '' && get_option('askit_integrate_singlebottom_enable') == 'on') echo(get_option('askit_integration_single_bottom')); ?>

	<?php endwhile; endif; ?>

</div> <!-- end #main-area -->

 <?php // get_sidebar('category-sidebar'); ?>

<?php get_footer(); ?>