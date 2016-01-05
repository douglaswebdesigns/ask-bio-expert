<?php
add_action( 'after_setup_theme', 'et_setup_theme' );
if ( ! function_exists( 'et_setup_theme' ) ){
	function et_setup_theme(){
		global $themename, $shortname, $default_colorscheme;
		$themename = "AskIt";
		$shortname = "askit";
		$default_colorscheme = "Default";

		$template_dir = get_template_directory();

		require_once($template_dir . '/epanel/custom_functions.php');

		require_once($template_dir . '/includes/functions/comments.php');

		require_once($template_dir . '/includes/functions/sidebars.php');

		load_theme_textdomain('AskIt', $template_dir.'/lang');

		require_once($template_dir . '/epanel/core_functions.php');

		require_once($template_dir . '/epanel/post_thumbnails_askit.php');

		include($template_dir . '/includes/widgets.php');

		add_action( 'pre_get_posts', 'et_home_posts_query' );

		add_theme_support( 'title-tag' );
	}
}

if ( ! function_exists( '_wp_render_title_tag' ) ) :
/**
 * Manually add <title> tag in head for WordPress 4.1 below for backward compatibility
 * Title tag is automatically added for WordPress 4.1 above via theme support
 * @return void
 */
	function et_add_title_tag_back_compat() { ?>
		<title><?php wp_title( '-', true, 'right' ); ?></title>
<?php
	}
	add_action( 'wp_head', 'et_add_title_tag_back_compat' );
endif;

add_action('wp_head','et_portfoliopt_additional_styles',100);
function et_portfoliopt_additional_styles(){ ?>
	<style type="text/css">
		#et_pt_portfolio_gallery { margin-left: -15px; }
		.et_pt_portfolio_item { margin-left: 18px; }
		.et_portfolio_small { margin-left: -40px !important; }
		.et_portfolio_small .et_pt_portfolio_item { margin-left: 27px !important; }
		.et_portfolio_large { margin-left: -50px !important; }
		.et_portfolio_large .et_pt_portfolio_item { margin-left: 11px !important; }
	</style>
<?php }

function insertThumbnailRSS($content) {
	global $post;

	$thumb = ''; $thumb = get_post_meta($post->ID, 'Thumbnail',true);

	if ( has_post_thumbnail( $post->ID ) ){
		$content = '<p>' . get_the_post_thumbnail( $post->ID, 'medium' ) . '</p>' . $content;
	} else if ($thumb <> '') {
		$content = '<p>' . '<img src="'. et_new_thumb_resize( et_multisite_thumbnail($thumb), 300, 200, '', true ) .'"/>' . '</p>' . $content;
	}

	return $content;
}
add_filter('the_excerpt_rss', 'insertThumbnailRSS');
add_filter('the_content_feed', 'insertThumbnailRSS');

function register_main_menus() {
	register_nav_menus(
		array(
			'primary-menu' => __( 'Primary Menu', 'AskIt' ),
			'secondary-menu' => __( 'Secondary Menu', 'AskIt' ),
			'footer-menu' => __( 'Footer Menu', 'AskIt' ),
		)
	);
}
if (function_exists('register_nav_menus')) add_action( 'init', 'register_main_menus' );

if ( ! function_exists( 'et_list_pings' ) ){
	function et_list_pings($comment, $args, $depth) {
		$GLOBALS['comment'] = $comment; ?>
		<li id="comment-<?php comment_ID(); ?>"><?php comment_author_link(); ?> - <?php comment_excerpt(); ?>
	<?php }
}

//-------- Additional Functions ---------- //

if (class_exists('Walker_Nav_Menu')) {
	class description_walker extends Walker_Nav_Menu
	{
		function start_el(&$output, $item, $depth, $args) {
			global $wp_query;
			$indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';

			$class_names = $value = '';

			$classes = empty( $item->classes ) ? array() : (array) $item->classes;

			$class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item ) );
			$class_names = ' class="'. esc_attr( $class_names ) . '"';

			$output .= $indent . '<li id="menu-item-'. $item->ID . '"' . $value . $class_names .'>';

			$attributes  = ! empty( $item->attr_title ) ? ' title="'  . esc_attr( $item->attr_title ) .'"' : '';
			$attributes .= ! empty( $item->target )     ? ' target="' . esc_attr( $item->target     ) .'"' : '';
			$attributes .= ! empty( $item->xfn )        ? ' rel="'    . esc_attr( $item->xfn        ) .'"' : '';
			$attributes .= ! empty( $item->url )        ? ' href="'   . esc_attr( $item->url        ) .'"' : '';

			$prepend = '<strong>';
			$append = '</strong>';

			$description  = ! empty( $item->description ) ? esc_attr( $item->description ) : '';
			if (strlen($description) > 22) $description = substr($description,0,21);

			if($depth != 0) { $description = $append = $prepend = ""; }

			$item_output = $args->before;
			$item_output .= '<a'. $attributes .'>';
			$item_output .= $args->link_before .$prepend.apply_filters( 'the_title', $item->title, $item->ID ).$append;
			$item_output .= '<span>' . $description. '</span>' . $args->link_after;
			$item_output .= '</a>';
			$item_output .= $args->after;

			$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
		}
	}
}

// add Home link to the custom menu WP-Admin page
function et_add_home_link( $args ) {
	$args['show_home'] = true;
	return $args;
}
add_filter( 'wp_page_menu_args', 'et_add_home_link' );

// add db query for homepage loop
function et_homepage_filter_where($where = '') {
	if ( !isset( $_GET['homeq'] ) ) return $where;

	if ( $_GET['homeq'] == 'popular' ) $where .= " AND comment_count > 0";
	if ( $_GET['homeq'] == 'unanswered' ) $where .= " AND comment_count = 0";

	return $where;
}
add_filter('posts_where', 'et_homepage_filter_where');

///

add_action( 'init', 'askit_qainit' );
function askit_qainit(){
	global $wpdb, $table_prefix, $post;

	$table_name = $table_prefix . "etcomment_rating";
	if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name)
		et_create_tables();

	$right_answer_comment_id = 0;

	add_action('template_redirect','et_right_answer');
	add_action('template_redirect','et_comment_rated');
	add_action('comment_post', 'et_new_comment');
	add_filter('comment_text', 'et_comment_text', 9000);
	add_action('et_show_like_dislike', 'et_show_comment_rating', 10, 2);
}

// (un)sets the right answer
function et_right_answer(){
	global $wpdb, $table_prefix, $post, $right_answer_comment_id;

	if (is_single()) {
		$table_name = $table_prefix . "etright_answer";

		$results = $wpdb->get_results($wpdb->prepare("SELECT comment_id FROM $table_name WHERE post_id = %d", $post->ID),ARRAY_N);

		if ( empty($results) ) $wpdb->query( $wpdb->prepare("INSERT INTO $table_name (comment_id, post_id) VALUES (0, %d)", $post->ID) ); //set default value for newly published posts
		else $right_answer_comment_id = $results[0][0];

		if ( isset($_GET['et_right_answer']) && $right_answer_comment_id == 0 ) {
			$wpdb->query( $wpdb->prepare("UPDATE $table_name SET comment_id = %d WHERE post_id = %d", $_GET['et_right_answer'], $post->ID) );
			$right_answer_comment_id = $_GET['et_right_answer'];
		}

		if ( isset($_GET['et_unset_right_answer']) && $right_answer_comment_id <> 0 ) {
			$wpdb->query( $wpdb->prepare("UPDATE $table_name SET comment_id = %d WHERE post_id = %d", 0, $post->ID) );
			$right_answer_comment_id = 0;
		}

		if ($right_answer_comment_id <> 0) {
			add_filter('comment_class', 'et_addcomment_class', 10, 4);
		}
	}
}

//checks if comment is rated
function et_comment_rated(){
	global $wpdb, $table_prefix, $user_ID;

	if ( is_single() && ( isset($_GET['et_comment_like']) || isset($_GET['et_comment_dislike']) ) ) {
		$comment_ips = array();

		$comment_id = isset($_GET['et_comment_like']) ? $_GET['et_comment_like'] : $_GET['et_comment_dislike'];
		if ( !et_user_can_rate_comment( $comment_id ) ) return;
		$table_name = $table_prefix . "etcomment_rating";

		$results = $wpdb->get_row( $wpdb->prepare( "SELECT et_author_ips, et_rating_up, et_rating_down, et_user_ids FROM $table_name WHERE et_comment_id = %d", $comment_id ), ARRAY_A );

		$comment_ips = empty($results['et_author_ips']) ? array() : maybe_unserialize( $results['et_author_ips'] );
		if ( $user_ID == 0 ) $comment_ips = array_merge($comment_ips, et_get_comment_author_ip());
		$results['et_author_ips'] = maybe_serialize( $comment_ips );

		$results['et_user_ids'] = empty($results['et_user_ids']) ? array() : maybe_unserialize( $results['et_user_ids'] );
		if ( $user_ID != 0 ) $results['et_user_ids'][] = $user_ID;
		$results['et_user_ids'] = maybe_serialize( $results['et_user_ids'] );

		if ( isset($_GET['et_comment_like']) ) {
			$wpdb->query( $wpdb->prepare("UPDATE $table_name SET et_rating_up = %d, et_author_ips = %s, et_user_ids = %s WHERE et_comment_id = %d", $results['et_rating_up'] + 1, $results['et_author_ips'], $results['et_user_ids'], $comment_id) );
		} else {
			$wpdb->query( $wpdb->prepare("UPDATE $table_name SET et_rating_down = %d, et_author_ips = %s, et_user_ids = %s WHERE et_comment_id = %d", $results['et_rating_down'] + 1, $results['et_author_ips'], $results['et_user_ids'], $comment_id) );
		}

	}
}


// runs when new comment is posted
// by default comment authors can't rate their comment - et_new_comment_user_ip and et_new_comment_author_id filters can be used to change it
function et_new_comment($comment_id){
	global $table_prefix, $wpdb;
	$table_name = $table_prefix . "etcomment_rating";

	$user_ip = maybe_serialize( et_get_comment_author_ip() );
	$user_ip = apply_filters( 'et_new_comment_user_ip', $user_ip, $comment_id );

	$com_id = get_comment($comment_id);
	$user_id = (string) $com_id->user_id;
	$comment_author_id = array();
	if ( $user_id <> 0 ) {
		$comment_author_id[] = $user_id;
		//$comment_author_id = apply_filters( 'et_new_comment_author_id', $user_id, $comment_id );
	};

	$comment_author_id = maybe_serialize( $comment_author_id );

	$wpdb->query( $wpdb->prepare( "INSERT INTO $table_name (et_comment_id, et_author_ips, et_rating_up, et_rating_down, et_user_ids) VALUES (%d, %s, 0, 0, %s)", $comment_id, $user_ip, $comment_author_id ) );
}


// adds (un)set right answer, (dis)like links to every comment
function et_comment_text($comment_text)
{
	global $post, $right_answer_comment_id, $user_ID;

	$comment_id = get_comment_ID();

	if ( $user_ID == $post->post_author ) {
		if ( $right_answer_comment_id == 0 )
			$comment_text = $comment_text . '<p><a class="right_answer" href="'. esc_url( add_query_arg('et_right_answer', $comment_id, get_permalink()) ) .'">Choose as the right answer</a></p>';
		elseif ( isset($right_answer_comment_id) && $right_answer_comment_id <> 0 && $right_answer_comment_id == $comment_id )
			$comment_text = $comment_text . '<p><a class="right_answer" href="'. esc_url( add_query_arg('et_unset_right_answer', $comment_id, get_permalink()) ) .'">Choose another answer as the right one</a></p>';
	}

	$et_like_dislike = '';
	//add like/dislike links
	//

	/*if ( et_user_can_rate_comment( $comment_id ) ) {
		$et_like_dislike = '<div class="et_like_dislike_box">';
		$et_like_dislike .= '<p>Was this answer helpful?</p>';
		$et_like_dislike .= '<a href="' . esc_url( add_query_arg( 'et_comment_like', $comment_id, get_permalink() ) ) . '" class="et_like_button">Like</a>';
		$et_like_dislike .= '<a href="' . esc_url( add_query_arg( 'et_comment_dislike', $comment_id, get_permalink() ) ) . '" class="et_dislike_button">Dislike</a>';
		$et_like_dislike .= '</div> <!-- .et_like_dislike_box -->';
	}*/

	$comment_text = $comment_text . apply_filters( 'et_show_like_dislike', $et_like_dislike, $comment_id );

	return $comment_text;
}


//show comment rating after like/dislike links
function et_show_comment_rating ( $et_like_dislike, $comment_id ) {
	//$et_like_dislike = $et_like_dislike . et_get_comment_rating( $comment_id );

	//$et_like_dislike = print_r(et_get_comment_author_ips($comment_id), true) . et_get_comment_rating($comment_id) . $et_like_dislike;

	//show total author comments (only for registered users)
	//if ( get_comment($comment_id)->user_id != 0 )
	//	$et_like_dislike = $et_like_dislike . '<br/>Author total comments: ' . et_get_author_comments_num( $comment_id );

	return $et_like_dislike;
}


// creates tables for ratings and right answers
if ( ! function_exists( 'et_create_tables' ) ){
	function et_create_tables(){
		global $wpdb, $table_prefix;

		$table_name = $table_prefix . "etcomment_rating";

		if ( $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name ){
			$sql = 'DROP TABLE `' . $table_name . '`';
			$wpdb->query($sql);
		}

		$sql = 'CREATE TABLE `' . $table_name . '` ('
		  . ' `et_comment_id` BIGINT(20) NOT NULL, '
		  . ' `et_author_ips` LONGTEXT, '
		  . ' `et_rating_up` INT,'
		  . ' `et_rating_down` INT,'
		  . ' `et_user_ids` LONGTEXT'
		  . ' )'
		  . ' ENGINE = myisam;';
		$wpdb->query($sql);

		$sql = 'ALTER TABLE `' . $table_name . '` ADD INDEX (`et_comment_id`);';
		$wpdb->query($sql);

		$results = $wpdb->get_results("SELECT comment_ID FROM $wpdb->comments",ARRAY_A);

		foreach($results as $result_row)
		{
			//save comment author IP, user ID
			$default_values = $wpdb->get_results( $wpdb->prepare( "SELECT comment_author_IP, user_id FROM $wpdb->comments WHERE comment_ID = %d", $result_row['comment_ID'] ), ARRAY_A );

			$default_values['comment_author_IP'] = ( $default_values[0]['comment_author_IP'] <> '' ) ? maybe_serialize( array( $default_values[0]['comment_author_IP'] ) ) : '';
			$default_values['user_id'] = ( $default_values[0]['user_id'] <> 0 ) ? maybe_serialize( array( $default_values[0]['user_id'] ) ) : '';

			$wpdb->query( $wpdb->prepare( "INSERT INTO $table_name (et_comment_id, et_author_ips, et_rating_up, et_rating_down, et_user_ids) VALUES ('%d', '%s', 0, 0, '%s')", $result_row['comment_ID'],$default_values['comment_author_IP'],$default_values['user_id'] ) );
		}

		////////////////////////////////

		$table_name = $table_prefix . "etright_answer";

		if ( $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name ){
			$sql = 'DROP TABLE `' . $table_name . '`';
			$wpdb->query($sql);
		}

		$sql = 'CREATE TABLE `' . $table_name . '` ('
		  . ' `comment_id` BIGINT(20) NOT NULL, '
		  . ' `post_id` BIGINT(20) NOT NULL '
		  . ' )'
		  . ' ENGINE = myisam;';
		$wpdb->query($sql);

		$sql = 'ALTER TABLE `' . $table_name . '` ADD INDEX (`comment_id`);';  // add index
		$wpdb->query($sql);

		$results = $wpdb->get_results("SELECT ID FROM $wpdb->posts WHERE post_type = 'post' AND post_status = 'publish' ORDER BY ID",ARRAY_A);

		foreach($results as $result_row)
		{
			$wpdb->query("INSERT INTO $table_name (comment_id, post_id) VALUES (0,'" . $result_row['ID'] . "')");
		}
	}
}

// gets comment author IP
// returns array or string
if ( ! function_exists( 'et_get_comment_author_ip' ) ){
	function et_get_comment_author_ip( $string = false ){
		if (!$string)
			return (array) preg_replace( '/[^0-9a-fA-F:., ]/', '',$_SERVER['REMOTE_ADDR'] );
		else
			return preg_replace( '/[^0-9a-fA-F:., ]/', '',$_SERVER['REMOTE_ADDR'] );
	}
}

// gets number of comments the author posted
// use if you set et_get_author_comments_num( $user_id = 1 ) to get number of posts by author with ID = 1
// if you don't set $user_id - make sure you set $comment_id
if ( ! function_exists( 'et_get_author_comments_num' ) ){
	function et_get_author_comments_num( $comment_id = 0, $user_id = 0 ){
		global $wpdb;

		$comment_user_id = get_comment( $comment_id );

		if ( $user_id == 0 )
			$user_id = $comment_user_id->user_id;

		$comments_num = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->comments WHERE user_id = %d", $user_id ) );

		return $comments_num;
	}
}

// gets number of right answers the author posted
if ( ! function_exists( 'et_get_rightcomments_num' ) ){
	function et_get_rightcomments_num( $user_id ){
		global $wpdb, $table_prefix;

		$table_name = $table_prefix . "etright_answer";

		$comments_num = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->comments INNER JOIN $table_name ON $wpdb->comments.comment_ID = $table_name.comment_id AND $wpdb->comments.user_id = %d", $user_id ) );

		return $comments_num;
	}
}

// detect if the user can rate the comment, based on IP for unregisted users and author ID for registered users
if ( ! function_exists( 'et_user_can_rate_comment' ) ){
	function et_user_can_rate_comment( $comment_id ) {
		global $user_ID, $wpdb, $table_prefix;

		$table_name = $table_prefix . "etcomment_rating";
		$temp_array = array();

		$results = $wpdb->get_row( $wpdb->prepare( "SELECT et_author_ips, et_user_ids FROM $table_name WHERE et_comment_id = %d", $comment_id ), ARRAY_A );

		if ( $user_ID != 0 ) {
			if ( empty($results['et_user_ids']) ) return true;

			$temp_array = maybe_unserialize( $results['et_user_ids'] );
			//print_r($results);
			if ( in_array( $user_ID, $temp_array ) ) return false;
		} else {
			if ( empty($results['et_author_ips']) ) return true;

			$temp_array = maybe_unserialize( $results['et_author_ips'] );
			if ( in_array( et_get_comment_author_ip( $string = 'true' ), $temp_array ) ) return false;
		}

		return true;
	}
}

// detect comment rating
// use et_get_comment_rating( $comment_id, $like_dislike_num = 'likes' ) to get likes number
// use et_get_comment_rating( $comment_id, $like_dislike_num = 'dislikes' ) to get dislikes number
if ( ! function_exists( 'et_get_comment_rating' ) ){
	function et_get_comment_rating( $comment_id, $like_dislike_num = '' ) {
		global $table_prefix, $wpdb;
		$table_name = $table_prefix . "etcomment_rating";

		$results = $wpdb->get_row( $wpdb->prepare( "SELECT et_rating_up, et_rating_down FROM $table_name WHERE et_comment_id = %d", $comment_id ), ARRAY_A );

		if ( !isset($results) ) return false;

		if ( $like_dislike_num == '' ) {
			return apply_filters( 'et_post_rating', $results['et_rating_up'] - $results['et_rating_down'] );
		} elseif ( $like_dislike_num == 'likes' ) {
			return $results['et_rating_up'];
		} elseif ( $like_dislike_num == 'dislikes' ) {
			return $results['et_rating_down'];
		}

		return false;
	}
}

// add "et_right_answer" class to the right answer
if ( ! function_exists( 'et_addcomment_class' ) ){
	function et_addcomment_class($classes, $class, $comment_id, $page_id){
		global $wpdb, $table_prefix, $right_answer_comment_id;

		if ( get_comment_ID() == $right_answer_comment_id )
			$classes[] = apply_filters( 'et_right_answer_class', 'et_right_answer', get_comment_ID() );

		return $classes;
	}
}

// move to AskIt functions.php file
add_filter('et_post_rating','et_add_rating_plus_sign');
function et_add_rating_plus_sign( $rating ){
	if ( $rating > 0 ) $rating = '+' . $rating;

	return $rating;
}
//

if ( ! function_exists( 'et_get_comment_author_ips' ) ){
	function et_get_comment_author_ips($comment_id) {
		global $table_prefix, $wpdb;
		$table_name = $table_prefix . "etcomment_rating";

		$results = $wpdb->get_row( $wpdb->prepare( "SELECT et_author_ips FROM $table_name WHERE et_comment_id = %d", $comment_id ), ARRAY_A );

		if ( !isset($results) ) return false;

		return maybe_unserialize($results['et_author_ips']);
	}
}

add_filter( 'et_rating_options', 'et_new_options' );
function et_new_options($options){
	return $options;
}

/**
 * Filters the main query on homepage
 */
function et_home_posts_query( $query = false ) {
	/* Don't proceed if it's not homepage or the main query */
	if ( ! is_home() || ! is_a( $query, 'WP_Query' ) || ! $query->is_main_query() ) return;

	/* Set the amount of posts per page on homepage */
	$query->set( 'posts_per_page', (int) et_get_option( 'askit_homepage_posts', '6' ) );

	if ( ( isset( $_GET['homeq'] ) && $_GET['homeq'] == 'recent' ) || !isset( $_GET['homeq'] ) ){
		$exclude_categories = et_get_option( 'askit_exlcats_recent', false );
		if ( $exclude_categories ) $query->set( 'category__not_in', array_map( 'intval', et_generate_wpml_ids( $exclude_categories, 'category' ) ) );
	} else {
		$query->set( 'ignore_sticky_posts', 1 );
	}

	if ( isset( $_GET['homeq'] ) && $_GET['homeq'] == 'popular' )
		$query->set( 'orderby', 'comment_count' );

	if ( isset( $_GET['homeq'] ) && $_GET['homeq'] == 'random' )
		$query->set( 'orderby', 'rand' );
}

function et_epanel_custom_colors_css(){
	global $shortname; ?>

	<style type="text/css">
		body { color: #<?php echo esc_html(get_option($shortname.'_color_mainfont')); ?>; }
		#container, #container2 { background: #<?php echo esc_html(get_option($shortname.'_color_bgcolor')); ?>; }
		.post a:link, .post a:visited { color: #<?php echo esc_html(get_option($shortname.'_color_mainlink')); ?>; }
		ul.nav li a { color: #<?php echo esc_html(get_option($shortname.'_color_pagelink')); ?>; }
		#sidebar h3.widgettitle { color:#<?php echo esc_html(get_option($shortname.'_color_sidebar_titles')); ?>; }
		#footer h3.title { color:#<?php echo esc_html(get_option($shortname.'_color_footer_titles')); ?>; }
		#footer .widget, #footer .widget a { color:#<?php echo esc_html(get_option($shortname.'_color_footer_links')); ?> !important; }
	</style>

<?php }