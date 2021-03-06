<?php get_header(); ?>
<div class="container-fluid">
	<div class="row">
		<div class="col-xs-11 col-lg-push-1">
			<div id="main-area">
				<div class="negative-space">
				<?php // get_template_part('includes/breadcrumbs'); ?>

				<div id='cssmenu'>
					<?php $menuClass = 'nav clearfix';
						$menuID = 'category-menu';
						$secondaryNav = '';
						if (function_exists('wp_nav_menu')) {
							$secondaryNav = wp_nav_menu( array( 
								'theme_location' 	=> 'category-menu', 
								'container' 		=> '', 
								'fallback_cb' 		=> '', 
								'menu_class' 		=> $menuClass, 
								'menu_id' 			=> $menuID,
								'depth' 			=> '1', 
								'echo' 				=> false ) );
						};
						if ($secondaryNav == '') { ?>
							<ul id="<?php echo esc_attr( $menuID ); ?>" class="<?php echo esc_attr( $menuClass ); ?>">
								<?php if (get_option('askit_home_link') == 'on') { ?>
										<li <?php if (is_home()) echo('class="current_page_item"') ?>><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e('Home','AskIt') ?></a></li>
								<?php }; ?>


							</ul> <!-- end ul#nav -->
						<?php }
						else echo($secondaryNav); ?>
				</div>

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
									<?php wp_link_pages(array('before' => '<p><strong>'.esc_html__('Pages','AskIt').':</strong> ', 'after' => '</p>', 'next_or_number' => 'number')); ?>
									<?php edit_post_link(esc_html__('Click to edit this page','AskIt')); ?>

									<div class="clear"></div>
								</div>
							</div> <!-- end .entry-content -->
						</div> <!-- end .entry-top -->
					</div> <!-- end .entry -->

					<div class="clear"></div>

					<?php if (get_option('askit_integration_single_bottom') <> '' && get_option('askit_integrate_singlebottom_enable') == 'on') echo(get_option('askit_integration_single_bottom')); ?>

					<?php if (get_option('askit_show_postcomments') == 'on') comments_template('', true); ?>

				<?php endwhile; endif; ?>

			</div> <!-- end #main-area -->
		</div>
	</div>
</div>

<?php get_sidebar(); ?>

<?php get_footer(); ?>