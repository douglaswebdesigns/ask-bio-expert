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

// add_filter('nav_menu_css_class' , 'active_nav_class' , 10 , 2);

// function active_nav_class($classes, $item){
//      if(is_single() && $item->title == "Dairy"){ 
//             $classes[] = "current-menu-item-active";
//      }
//      return $classes;
// }

add_filter('nav_menu_css_class' , 'special_nav_class' , 10 , 2);

function special_nav_class ($classes, $item) {
    if ('taxonomy' == $menu_item->type && ( $wp_query->is_category) ){
        $classes[] = 'current-menu-item-' . $item->object_id;
    }
    return $classes;
}

add_filter( 'nav_menu_css_class', 'wpa_category_nav_class', 10, 2 );

function wpa_category_nav_class( $classes, $item ){
    if( 'category' == $item->object ){
        $classes[] = 'menu-category-' . $item->object_id;
    }
    return $classes;
}

add_filter( 'nav_menu_css_class', 'my_special_nav_class', 10, 2 );

function my_special_nav_class( $classes, $item ) { 
	if( 'page' == $item->object ){ $page = get_post( $item->object_id );
		$classes[] = $page->post_name; } 

		return $classes; 
} 


