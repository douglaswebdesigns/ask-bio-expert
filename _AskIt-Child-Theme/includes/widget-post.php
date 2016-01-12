<li class="clearfix">
	<span class="title"><a href="<?php the_permalink(); ?>"><?php truncate_title(28); ?></a></span>
	<p class="post-info"><?php esc_html_e('Posted','AskIt'); ?> <?php esc_html_e('by','AskIt'); ?> <?php the_author_posts_link(); ?> <?php esc_html_e('on','AskIt'); ?> <?php the_time(get_option('askit_date_format')) ?></p>

	<?php $comments_num = (int) get_comments_number(); ?>
	<span class="widget-comment-number<?php if ( $comments_num == 0 ) echo ' widget-unanswered'; ?>"><?php echo esc_html($comments_num); ?></span>
</li>