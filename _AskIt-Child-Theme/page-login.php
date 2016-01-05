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

<div id="main-area"<?php if (!$fullwidth) echo ' class="fullwidth container-fluid"'; ?>>

	<?php // get_template_part('includes/breadcrumbs'); ?>

	<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

		<?php if (get_option('askit_integration_single_top') <> '' && get_option('askit_integrate_singletop_enable') == 'on') echo(get_option('askit_integration_single_top')); ?>

		<div class="entry page col-lg-12 col-md-10 col-sm-8 col-xs-6">
			<div class="row margin-neg">
			<div id='cssmenu'>
				<?php $menuClass = 'nav clearfix';
					$menuID = 'category-menu';
					$secondaryNav = '';
					if (function_exists('wp_nav_menu')) {
						$secondaryNav = wp_nav_menu( array( 'theme_location' => 'category-menu',
										    'container' => '', 
										  'fallback_cb' => 'wp_page_menu',
										   'menu_class' => $menuClass,
										      'menu_id' => $menuID, 
										         'echo' => false ) );
					};
					if ($secondaryNav == '') { ?>
						<ul id="<?php echo esc_attr( $menuID ); ?>" class="<?php echo esc_attr( $menuClass ); ?>">
							<?php if (get_option('askit_home_link') == 'on') { ?>
									<li <?php if (is_front_page()) echo('class="current_page_item"') ?>><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e('Home','AskIt') ?></a></li>
							<?php }; ?>


						</ul> <!-- end ul#nav -->
					<?php }
					else echo($secondaryNav); ?>
			</div>
			<div class="entry-top">
				<div class="entry-content">
					<div class="login-area">
						<h2 class="title"></h2>

					</div>			


					<div id="entry-content">
						<div class="post-content">
							<?php the_content(); ?>									
						</div><!-- end #et-login -->
					</div>

					
					<div class="the-question-area">
						<?php echo do_shortcode("[cma-index]"); ?>
					</div>
						<?php edit_post_link(esc_html__('Edit this page','AskIt')); ?>
				</div>
			</div>
		</div> <!-- end .entry-content -->

</div>
</div> <!-- end #main-area -->
		<?php if (get_option('askit_integration_single_bottom') <> '' && get_option('askit_integrate_singlebottom_enable') == 'on') echo(get_option('askit_integration_single_bottom')); ?>

			<?php if (get_option('askit_468_enable') == 'on') { ?>
			<?php if(get_option('askit_468_adsense') <> '') echo ( get_option('askit_468_adsense') );
			else { ?>
				<a href="<?php echo esc_url(get_option('askit_468_url')); ?>"><img src="<?php echo esc_url(get_option('askit_468_image')); ?>" alt="Microsys Ad" class="foursixeight" /></a>
			<?php } ?>
		<?php } ?>

		<?php endwhile; endif; ?>




<?php get_footer(); ?>