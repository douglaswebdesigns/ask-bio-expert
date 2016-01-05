<?php

class CMA_StickyQuestion
{

    function __construct()
    {
        add_action('add_meta_boxes', array(get_class(), 'register_sticky_box'));
        add_action('save_post', array(get_class(), 'save_postdata'));
        add_action('update_post', array(get_class(), 'save_postdata'));
        add_action('pre_get_posts', array(get_class(), 'add_sticky_routines'));
    }

    public static function add_sticky_routines($query)
    {

        if( $query->get('post_type') == 'cma_thread' )
        {
            add_filter('posts_join', array(get_class(), 'join_sticky'));
            add_filter('posts_orderby', array(get_class(), 'orderby_sticky'));
        }
    }

    public static function register_sticky_box()
    {
        add_meta_box('cma-sticky-box', 'Question properties', array(get_class(), 'render_my_meta_box'), 'cma_thread', 'side');
    }

    public static function render_my_meta_box($post)
    {
        $sticky = get_post_meta($post->ID, '_sticky_post');

        $sticky_value = 0;
        if( isset($sticky[0]) && !empty($sticky[0]) && $sticky[0] == 1 )
        {
            $sticky_value = 1;
        }
        echo '<ul>';
        echo '<li><label for="cma_sticky_box">Sticky question&nbsp;&nbsp;&nbsp;<input type="checkbox" name="cma_sticky_box" id="cma_sticky_box" value="1" ' . ($sticky_value != 0 ? ' checked ' : '') . '></label></li>';

        $answerThread = CMA_Thread::getInstance($post->ID);
        $views = $answerThread->getViews();

        echo '<li><label for="cma_views_box">Views: <input type="text" name="cma_views_box" id="cma_views_box" value="' . $views . '"></label></li>';
        
        // Post rating handicap
        printf('<li><label for="%s">Rating handicap: <input type="text" name="%s" id="%s" value="%d"></label></li>',
        	'cma_post_rating_box',
        	'cma_post_rating_box',
        	'cma_post_rating_box',
        	$answerThread->getRatingHandicap()
        );
        
        // Votes rating handicap
        printf('<li><label for="%s">Total votes handicap: <input type="text" name="%s" id="%s" value="%d"></label></li>',
        	'cma_post_votes_handicap_box',
        	'cma_post_votes_handicap_box',
        	'cma_post_votes_handicap_box',
        	$answerThread->getVotesHandicap()
        );
        
        echo '</ul>';
    }

    public static function save_postdata($post_id)
    {
        $postType = isset($_POST['post_type']) ? $_POST['post_type'] : '';
        if( 'cma_thread' != $postType ) return;
        
        $answerThread = CMA_Thread::getInstance($post_id);
        if(empty($answerThread)) return;

        $sticky = 0;
        if( isset($_POST["cma_sticky_box"]) and (isset($_POST["cma_sticky_box"])) ) $sticky = 1;

        update_post_meta($post_id, '_sticky_post', $sticky);

        /*
         * Update views count
         */
        $viewsAmount = isset($_POST['cma_views_box']) ? $_POST['cma_views_box'] : 0;

        $answerThread->setViews($viewsAmount);
        
        if (isset($_POST['cma_post_rating_box']) AND is_numeric($_POST['cma_post_rating_box'])) {
        	$answerThread->setRatingHandicap($_POST['cma_post_rating_box']);
        }
        
    	if (isset($_POST['cma_post_votes_handicap_box']) AND is_numeric($_POST['cma_post_votes_handicap_box'])) {
        	$answerThread->setVotesHandicap($_POST['cma_post_votes_handicap_box']);
        }
        
    }

    public static function orderby_sticky($original_orderby_statement)
    {
        remove_filter('posts_orderby', array(get_class(), 'orderby_sticky'));
        return " IFNULL(DD.sticky,0) DESC, " . $original_orderby_statement;
    }

    public static function join_sticky($wp_join)
    {
        global $wpdb;
        remove_filter('posts_join', array(get_class(), 'join_sticky'));
        $wp_join .= " LEFT JOIN (
				SELECT cmastickyjoin.post_id, CAST(cmastickyjoin.meta_value AS UNSIGNED) as sticky
				FROM $wpdb->postmeta cmastickyjoin
				WHERE cmastickyjoin.meta_key =  '_sticky_post' ) AS DD
				ON $wpdb->posts.ID = DD.post_id ";
        return $wp_join;
    }

}
$sticky = new CMA_StickyQuestion();
