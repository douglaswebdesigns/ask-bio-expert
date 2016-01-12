
<?php 

?>
<?php $marquee_loop = new WP_Query(
		array(
			'post_type' => 'marquee_panel',
			'posts_per_page' => 10,
			'post_status' => 'publish'
		)
	); ?>

			<div class="img-responsive">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>">
					<?php $logo = (get_option('askit_logo') <> '') ? get_option('askit_logo') : get_template_directory_uri().'/images/logo.png'; ?>
					<img src="/askabioexpert/wp-content/uploads/2016/01/the-expert-center-header-image-1.png" alt="<?php echo esc_attr(get_bloginfo('name')); ?>" id="logo"/>
				</a>

			</div> <?php // <!-- end .img-responsive --> ?>

<?php
				echo '<div class="marquee">';
					echo '<div class="marquee_data">'; 
						while ( $marquee_loop -> have_posts() ) : $marquee_loop -> the_post();
							$image_id = get_post_thumbnail_id( $post_id );
							$image_url_full = wp_get_attachment_image_src($image_id,'full');
							$image_url_large = wp_get_attachment_image_src($image_id,'large');
							echo '<div class="marquee_panel" data-image-full="'.$image_url_full[0].'" data-image-large="'.$image_url_large[0].'">';
								echo '<div class="panel_caption">';
									the_title('<h3>','</h3>');
									echo '<div class="panel_content">';
										the_content();
									echo '</div>'; // <!- end .panel_content --> 
								echo '</div>'; // <!- end .panel_caption --> 
							echo '</div>'; // <!- end .marquee_panel --> 
						endwhile;
					echo '</div>'; // <!- end .marquee_data --> 
				echo '</div>'; // <!- end .marquee --> 

?>
	
	
<!-- Reset post data so that the header marquee does not pull in question posts -->
	<?php wp_reset_postdata(); ?>

				
