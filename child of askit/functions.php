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

