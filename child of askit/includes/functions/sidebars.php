<?php
if ( function_exists('register_sidebar') )
	register_sidebar(array(
		'name' => 'Sidebar',
		'id' => 'sidebar-1',
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget' => '</div></div><!-- end .widget -->',
		'before_title' => '<h4 class="widgettitle">',
		'after_title' => '</h4><div class="widget-content">',
	));

if ( function_exists('register_sidebar') )
    register_sidebar(array(
		'name' => 'Footers',
		'id' => 'sidebar-2',
		'before_widget' => '<div id="%1$s" class="footer-widget %2$s">',
		'after_widget' => '</div> <!-- end .footer-widget -->',
		'before_title' => '<h4 class="widgettitle">',
		'after_title' => '</h4>',
    ));

if ( function_exists('register_sidebar') )
	register_sidebar(array(
		'name' => 'Login Sidebar',
		'id' => 'sidebar-3',
		'before_widget' => '<div id="%1$s" class="login-widget">',
		'after_widget' => '</div></div><!-- end .widget -->',
		'before_title' => '<h4 class="widgettitle">',
		'after_title' => '</h4><div class="widget-content">',
	));
?>