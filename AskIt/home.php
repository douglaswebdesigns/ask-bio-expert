<?php get_header(); ?>

<?php
$active_class = ' class="active"';
$popular_class = $unanswered_class = $random_class = $recent_class = '';

if ( ( isset( $_GET['homeq'] ) && $_GET['homeq'] == 'recent' ) || !isset( $_GET['homeq'] ) ) {
	$recent_class = $active_class;
}

if ( isset( $_GET['homeq'] ) && $_GET['homeq'] == 'popular' ) {
	$popular_class = $active_class;
}

if ( isset( $_GET['homeq'] ) && $_GET['homeq'] == 'unanswered' ) {
	$unanswered_class = $active_class;
}

if ( isset( $_GET['homeq'] ) && $_GET['homeq'] == 'random' ) {
	$random_class = $active_class;
}
?>

<div id="main-area">
	<div id="main-tabs">
		<ul id="main-tabbed-area" class="clearfix">
			<li<?php echo $recent_class; ?>><a href="<?php echo esc_url( add_query_arg( 'homeq', 'recent', get_bloginfo( 'url' ) ) ); ?>"><span><?php esc_html_e('Recent','AskIt'); ?></span></a><span class="arrow"></span></li>
			<li<?php echo $popular_class; ?>><a href="<?php echo esc_url( add_query_arg( 'homeq', 'popular', get_bloginfo( 'url' ) ) ); ?>"><span><?php esc_html_e('Popular','AskIt'); ?></span></a><span class="arrow"></span></li>
			<li<?php echo $unanswered_class; ?>><a href="<?php echo esc_url( add_query_arg( 'homeq', 'unanswered', get_bloginfo( 'url' ) ) ); ?>"><span><?php esc_html_e('Unanswered','AskIt'); ?></span></a><span class="arrow"></span></li>
			<li<?php echo $random_class; ?>><a href="<?php echo esc_url( add_query_arg( 'homeq', 'random', get_bloginfo( 'url' ) ) ); ?>"><span><?php esc_html_e('Random','AskIt'); ?></span></a><span class="arrow"></span></li>
		</ul>
	</div> <!-- end #main-tabs -->

	<div id="main-recent">
		<?php get_template_part('includes/entry','home'); ?>
	</div> <!-- end #main-recent -->
</div> <!-- end #main-area -->

<?php get_sidebar(); ?>

<?php get_footer(); ?>