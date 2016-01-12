<?php

function theme_enqueue_styles() {

    $parent_style = 'parent-style';

    wp_enqueue_style( $parent_style, get_template_directory_uri() . '/style.css' );
    wp_enqueue_style( 'child-style',
        get_stylesheet_directory_uri() . '/style.css',
        array( $parent_style )
    );
}
add_action( 'wp_enqueue_scripts', 'theme_enqueue_styles' );

if ( function_exists('register_sidebar') )
	register_sidebar(array(
		'name' => 'Login',
		'id' => 'sidebar-3',
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget' => '</div></div><!-- end .widget -->',
		'before_title' => '<h4 class="widgettitle">',
		'after_title' => '</h4><div class="widget-content">',
	));


// function reg_scripts() {
//    wp_enqueue_style( 'bootstrapstyle',  '//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css' );
//    wp_enqueue_script( 'bootstrap-script',  '//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js', array(), true );
// }
// add_action('wp_enqueue_scripts', 'reg_scripts');


add_filter( 'nav_menu_css_class', 'add_current_menu_item_menu_class', 10, 2 );

function add_current_menu_item_menu_class( $classes = array(), $item = false ) {
    // Get current URL
    $current_url = current_url();
    
    // Get homepage URL
    $homepage_url = trailingslashit( get_bloginfo( 'url' ) );
        
    // Exclude 404 and homepage
    if( is_404() or $item->url == $homepage_url ) return $classes;
    
    if ( strstr( $current_url, $item->url) ) {
        // Add the 'current_menu_item' class
        $classes[] = 'current-menu-item';
    }
    
    return $classes;
}

function current_url() {
    // Protocol
    $url = ( 'on' == $_SERVER['HTTPS'] ) ? 'https://' : 'http://';
    
    $url .= $_SERVER['SERVER_NAME'];
    
    // Port
    $url .= ( '80' == $_SERVER['SERVER_PORT'] ) ? '' : ':' . $_SERVER['SERVER_PORT'];
    
    $url .= $_SERVER['REQUEST_URI'];
    
    return trailingslashit( $url );
}

// Display full username
class myUsers {
    static function init() {
        // Change the user's display name after insertion
        add_action( 'user_register', array( __CLASS__, 'change_display_name' ) );
    }

    static function change_display_name( $user_id ) {
        $info = get_userdata( $user_id );

        $args = array(
            'ID' => $user_id,
            'display_name' => $info->first_name . ' ' . $info->last_name
        );

        wp_update_user( $args );
    }
}

myUsers::init();

if ( is_user_logged_in() ) {
    $page = get_page_by_title( 'register' );
    update_option( 'page_on_front', $page->ID );
    update_option( 'show_on_front', 'page' );
} else {
    // different front page...
}

include (STYLESHEETPATH . '/myMarquee/php/marquee_functions_include.php'); 

function load_bootstrap() {
    wp_enqueue_script('bootstrap-min', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js', array('jquery'), '', true );
    wp_enqueue_style( 'bootstrap-min', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css' );
}

add_action( 'wp_enqueue_scripts', 'load_bootstrap', 10);

