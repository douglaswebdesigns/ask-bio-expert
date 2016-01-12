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

<div class="container-fluid">
	<div class="row">
		<div class="col-xs-11 col-lg-push-1">
			<div id="main-area"<?php if (!$fullwidth) echo ' class="fullwidth"'; ?>>
				
				<div class="login-table">

				<?php if( !is_user_logged_in() ) { ?>

				<?php } ?>

					<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

						<?php if (get_option('askit_integration_single_top') <> '' && get_option('askit_integrate_singletop_enable') == 'on') echo(get_option('askit_integration_single_top')); ?>
		
						<div class="hidden-xs">
							<div id="cssmenu">
								<?php $menuClass = 'nav clearfix';
									$menuID = 'category-menu';
									$secondaryNav = '';
									if (function_exists('wp_nav_menu')) {
										$secondaryNav = wp_nav_menu( array( 
											'theme_location' 	=> 'category-menu',
							    			'container' 		=> '', 
							  				'fallback_cb' 		=> 'wp_page_menu',
							   				'menu_class' 		=> $menuClass,
							      			'menu_id' 			=> $menuID, 
							         		'echo' 				=> false,
							         		'walker' 			=> new description_walker() ) );
									};

									if ($secondaryNav == '') { ?>
										<ul id="<?php echo esc_attr( $menuID ); ?>" class="<?php echo esc_attr( $menuClass ); ?>">
											<?php if (get_option('askit_home_link') == 'on') { ?>
													<li <?php if (is_home()) echo('class="current_page_item"') ?>><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e('Home','AskIt') ?></a></li>
											<?php }; ?>
										</ul> <!-- end ul#nav -->
					
									<?php }
									else echo($secondaryNav); ?>

								<?php endwhile; endif; ?>

						</div><!-- end #cssmenu -->	
						<div class="entry page">
							<div class="entry-top">
								<div class="entry-content fullwidth">

										<?php the_content(); ?>		

									<div class="">

										<div class="row">

											<?php echo do_shortcode("[cma-questions]"); ?>
					
										</div><!-- end .row -->

										<?php edit_post_link(esc_html__('Edit this page','AskIt')); ?>

											<div classs="entry-bottom"></div>

									</div><!-- .col-md-12 .entry-content  -->
								</div> <!-- end .col-md-12 .entry-top -->
			
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
		<div class="clearfix"></div>
		
			<?php  get_template_part('includes/breadcrumbs'); ?>

	</div> <!-- end #main-area -->


<?php get_footer(); ?>