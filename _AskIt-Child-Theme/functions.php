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

//Filtering a Class in Navigation Menu Item
add_filter('nav_menu_css_class' , 'special_nav_class' , 10 , 2);
function special_nav_class($classes, $item){
     if(is_single() && $item->title == 'Dairy'){
             $classes[] = 'active';
     }
     return $classes;
}

