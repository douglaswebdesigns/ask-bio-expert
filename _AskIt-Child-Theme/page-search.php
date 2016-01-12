<?php
/*
Template Name: Search Page
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
			<div id="main-area"<?php if ($fullwidth) echo ' class="fullwidth"'; ?>>
				<div class="negative-space">
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

										<div id="et-search">
											<div id="et-search-inner" class="clearfix">
												<p id="et-search-title"><span><?php esc_html_e('search this website','AskIt'); ?></span></p>
												<form action="<?php echo esc_url( home_url() ); ?>" method="get" id="et_search_form">
													<div id="et-search-left">
														<p id="et-search-word"><input type="text" id="et-searchinput" name="s" value="<?php esc_attr_e('search this site...','AskIt'); ?>" /></p>

														<p id="et_choose_posts"><label><input type="checkbox" id="et-inc-posts" name="et-inc-posts" /> <?php esc_html_e('Posts','AskIt'); ?></label></p>
														<p id="et_choose_pages"><label><input type="checkbox" id="et-inc-pages" name="et-inc-pages" /> <?php esc_html_e('Pages','AskIt'); ?></label></p>
														<p id="et_choose_date">
															<select id="et-month-choice" name="et-month-choice">
																<option value="no-choice"><?php esc_html_e('Select a month','AskIt'); ?></option>
																<?php
																	global $wpdb, $wp_locale;

																	$selected = '';
																	$arcresults = $wpdb->get_results(
																		$wpdb->prepare( "SELECT YEAR(post_date) AS %s, MONTH(post_date) AS %s, count(ID) as posts FROM $wpdb->posts GROUP BY YEAR(post_date), MONTH(post_date) ORDER BY post_date DESC", 'year', 'month' )
																	);

																	foreach ( (array) $arcresults as $arcresult ) {
																		if ( isset($_POST['et-month-choice']) && ( $_POST['et-month-choice'] == ($arcresult->year . $arcresult->month) ) ) {
																			$selected = ' selected="selected"';
																		}
																		echo "<option value='{$arcresult->year}{$arcresult->month}'{$selected}>{$wp_locale->get_month($arcresult->month)}" . ", {$arcresult->year}</option>";
																		if ( $selected <> '' ) $selected = '';
																	}
																?>
															</select>
														</p>

														<p id="et_choose_cat"><?php wp_dropdown_categories('show_option_all=Choose a Category&show_count=1&hierarchical=1&id=et-cat&name=et-cat'); ?></p>
													</div> <!-- #et-search-left -->

													<div id="et-search-right">
														<input type="hidden" name="et_searchform_submit" value="et_search_proccess" />
														<input class="et_search_submit" type="submit" value="<?php esc_attr_e('Submit','AskIt'); ?>" id="et_search_submit" />
													</div> <!-- #et-search-right -->
												</form>
											</div> <!-- end #et-search-inner -->
										</div> <!-- end #et-search -->

										<div class="clear"></div>

										<?php edit_post_link(esc_html__('Edit this page','AskIt')); ?>

										<div class="clear"></div>
									</div>
								</div> <!-- end .entry-content -->
							</div> <!-- end .entry-top -->
						</div> <!-- end .entry -->

						<div class="clear"></div>

						<?php if (get_option('askit_integration_single_bottom') <> '' && get_option('askit_integrate_singlebottom_enable') == 'on') echo(get_option('askit_integration_single_bottom')); ?>


						<?php if (get_option('askit_show_postcomments') == 'on') comments_template('', true); ?>

					<?php endwhile; endif; ?>
				</div>
			</div> <!-- end #main-area -->
		</div>
	</div>
</div>

<?php get_footer(); ?>