<?php
if ( ! function_exists( 'et_custom_comments_display' ) ) :
function et_custom_comments_display($comment, $args, $depth) {
    $GLOBALS['comment'] = $comment; ?>
	<li <?php comment_class(); ?> id="li-comment-<?php comment_ID() ?>">
		<?php $et_right_answer = false; ?>
		<?php if ( in_array( 'et_right_answer', get_comment_class() ) ) { ?>
			<?php $et_right_answer = true; ?>
		<?php } ?>

		<?php if ( $et_right_answer ) { ?>
			<p id="right_answer_set"><?php esc_html_e( 'This solution has been deemed correct by the post author', 'AskIt' ); ?></p>
			<div class="clear"></div>
		<?php } ?>

		<div id="comment-<?php comment_ID(); ?>" class="comment-body clearfix">
			<div class="avatar-box">
				<div class="avatar">
					<?php echo get_avatar($comment,$size='53'); ?>
					<div class="overlay"></div>

					<?php if ( $comment->user_id <> 0 ) {
						$author_comments_num = (int) et_get_author_comments_num( $comment->comment_ID, $comment->user_id ); ?>
						<span class="author_comments_num<?php if ( $author_comments_num == 0 ) echo ' no_comments'; ?>"><?php echo esc_html( $author_comments_num ); ?></span>
					<?php } ?>
				</div>

				<?php if ( $comment->user_id <> 0 ) { ?>
					<?php $stars_on = 1;
					$stars_rating_options = apply_filters( 'et_rating_options', array(1,5,10,20) );
					$author_comments_num = (int) et_get_rightcomments_num( $comment->user_id );

					switch ( $author_comments_num ){
						case ( $author_comments_num > $stars_rating_options[0] && $author_comments_num <= $stars_rating_options[1] ):
							$stars_on = 1;
							break;
						case ( $author_comments_num > $stars_rating_options[1] && $author_comments_num <= $stars_rating_options[2] ):
							$stars_on = 2;
							break;
						case ( $author_comments_num > $stars_rating_options[2] && $author_comments_num <= $stars_rating_options[3] ):
							$stars_on = 3;
							break;
						case ( $author_comments_num > $stars_rating_options[3] ):
							$stars_on = 4;
							break;
					} ?>
					<div class="author-rating">
						<?php for ($i = 1; $i <= $stars_on; $i++ ) { ?>
							<img src="<?php echo get_template_directory_uri(); ?>/images/star-on.png" alt="" class="star" />
						<?php } ?>
						<?php for ($i = 1; $i <= ( 4-$stars_on ); $i++ ) { ?>
							<img src="<?php echo get_template_directory_uri(); ?>/images/star-off.png" alt="" class="star" />
						<?php } ?>
					</div> <!-- end .author-rating -->
				<?php } ?>

				<span class="comment-date"><?php comment_date( get_option( 'askit_comment_date_format' ) ); ?></span>
				<div class="comment_join"></div>
			</div>

			<div class="comment-wrap">
				<div class="comment-wrap-inner clearfix">
					<div class="comment-author vcard">
						<?php printf('<span class="fn">%s</span>', get_comment_author_link()) ?> <?php esc_html_e('says: ','AskIt'); ?> <?php edit_comment_link(esc_html__('(Edit)','AskIt'),'  ','') ?></span>
					</div>

					<?php if ($comment->comment_approved == '0') : ?>
						<em class="moderation"><?php esc_html_e('Your comment is awaiting moderation.','AskIt') ?></em>
						<br />
					<?php endif; ?>

					<div class="comment-content"><?php comment_text() ?></div> <!-- end comment-content-->

					<?php
						$et_comment_reply_link = get_comment_reply_link( array_merge( $args, array('reply_text' => esc_attr__('Reply','AskIt'),'depth' => $depth, 'max_depth' => $args['max_depth'])) );
						if ( $et_comment_reply_link ) echo '<div class="reply-container">' . $et_comment_reply_link . '</div>';
					?>
				</div> <!-- end comment-wrap-inner -->

				<?php $rating = et_get_comment_rating( $comment->comment_ID ); ?>
				<div class="comment_rating<?php if ( $rating < 0 ) echo ' rating-negative'; if ( $rating > 0 ) echo ' rating-positive'; ?>"><?php echo $rating; ?>
					<?php if ( et_user_can_rate_comment( $comment->comment_ID ) ) {
						$et_like_dislike = '<div class="et_like_dislike_box">';
						$et_like_dislike .= '<p>' . esc_html__('Was this answer helpful?','AskIt') . '</p>';
						$et_like_dislike .= '<a href="' . esc_url( add_query_arg( 'et_comment_like', $comment->comment_ID, get_permalink() ) ) . '" class="et_like_button">Like</a>';
						$et_like_dislike .= '<a href="' . esc_url( add_query_arg( 'et_comment_dislike', $comment->comment_ID, get_permalink() ) ) . '" class="et_dislike_button">Dislike</a>';
						$et_like_dislike .= '</div> <!-- .et_like_dislike_box -->';
						echo $et_like_dislike;
					} ?>
				</div>

			</div> <!-- end comment-wrap-->
		</div> <!-- end comment-body-->
<?php }
endif; ?>