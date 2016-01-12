<div class="container-fluid">
	<div class="row">
		<div class="col-xs-11 col-lg-push-1"><div class="negative-space"></div><!-- end negative-space -->
			<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
				<div class="entry">
					<div class="entry-top">
						<div class="entry-content">							
							<?php $comments_num = get_comments_number(); ?>
							<span class="comment-number<?php if ( $comments_num == 0 ) echo ' unanswered'; ?>"><a href="<?php the_permalink(); ?>#comment-wrap"><?php echo esc_html($comments_num); ?></a></span>
							<h2 class="title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
							<span class="quote"></span>
							<span class="author">- <?php the_author_posts_link(); ?></span>

							<div class="clear"></div>
							<?php if ( get_option('askit_blog_style') == 'on' ) get_template_part('includes/postinfo'); ?>

							<?php $posttags = get_the_tags();
							$i = 0;
							if ($posttags) { ?>
								<div class="tags">
									<?php foreach($posttags as $tag) {
										$i++;
										if ( $i > 5) break; ?>
										<a href="<?php echo esc_url( get_tag_link( $tag->term_id ) ); ?>"><span><?php echo esc_html( $tag->name ); ?></span></a>
									<?php } ?>
								</div> <!-- end .tags -->
							<?php } ?>

							<?php if ( get_option('askit_blog_style') == 'on' ) { ?>
								<div class="post-content">
									<?php the_content(); ?>
									<?php edit_post_link(esc_html__('Edit this page','AskIt')); ?>

									<div class="clear"></div>
								</div>
							<?php } ?>							
						</div> <!-- end .entry-content -->
					</div> <!-- end .entry-top -->
				</div> <!-- end .entry -->
			<?php endwhile; ?>
				<div class="clear"></div>
				<?php if(function_exists('wp_pagenavi')) { wp_pagenavi(); }
				else { ?>
					 <?php get_template_part('includes/navigation'); ?>
				<?php } ?>
			<?php else : ?>
				<?php get_template_part('includes/no-results'); ?>
			<?php endif; ?>
		</div>
	</div>
</div>