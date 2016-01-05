<?php
/*
Template Name: Sitemap Page
*/
?>
<?php get_header(); ?>

<?php
$et_ptemplate_settings = array();
$et_ptemplate_settings = maybe_unserialize( get_post_meta(get_the_ID(),'et_ptemplate_settings',true) );

$fullwidth = isset( $et_ptemplate_settings['et_fullwidthpage'] ) ? (bool) $et_ptemplate_settings['et_fullwidthpage'] : false;
?>

<div id="main-area"<?php if ($fullwidth) echo ' class="fullwidth"'; ?>>

	<?php get_template_part('includes/breadcrumbs'); ?>

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

						<div id="sitemap">
							<div class="sitemap-col">
								<h2><?php esc_html_e('Pages','AskIt'); ?></h2>
								<ul id="sitemap-pages"><?php wp_list_pages('title_li='); ?></ul>
							</div> <!-- end .sitemap-col -->

							<div class="sitemap-col">
								<h2><?php esc_html_e('Categories','AskIt'); ?></h2>
								<ul id="sitemap-categories"><?php wp_list_categories('title_li='); ?></ul>
							</div> <!-- end .sitemap-col -->

							<div class="sitemap-col">
								<h2><?php esc_html_e('Tags','AskIt'); ?></h2>
								<ul id="sitemap-tags">
									<?php $tags = get_tags();
									if ($tags) {
										foreach ($tags as $tag) {
											echo '<li><a href="' . esc_url( get_tag_link( $tag->term_id ) ) . '">' . esc_html( $tag->name ) . '</a></li> ';
										}
									} ?>
								</ul>
							</div> <!-- end .sitemap-col -->

							<div class="sitemap-col<?php echo ' last'; ?>">
								<h2><?php esc_html_e('Authors','AskIt'); ?></h2>
								<ul id="sitemap-authors" ><?php wp_list_authors('show_fullname=1&optioncount=1&exclude_admin=0'); ?></ul>
							</div> <!-- end .sitemap-col -->
						</div> <!-- end #sitemap -->

						<div class="clear"></div>

						<?php edit_post_link(esc_html__('Edit this page','AskIt')); ?>

						<div class="clear"></div>
					</div>
				</div> <!-- end .entry-content -->
			</div> <!-- end .entry-top -->
		</div> <!-- end .entry -->

		<div class="clear"></div>

		<?php if (get_option('askit_integration_single_bottom') <> '' && get_option('askit_integrate_singlebottom_enable') == 'on') echo(get_option('askit_integration_single_bottom')); ?>

	<?php endwhile; endif; ?>

</div> <!-- end #main-area -->

<?php if (!$fullwidth) get_sidebar(); ?>

<?php get_footer(); ?>