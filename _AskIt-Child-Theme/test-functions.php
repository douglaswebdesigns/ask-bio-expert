<?php

add_filter('nav_menu_css_class' , 'active_nav_class' , 10 , 2);
function active_nav_class($classes, $item){
     if(is_single() && $item->title == "Dairy"){ 
             $classes[] = "active";
     }
     return $classes;
}

add_filter('nav_menu_css_class' , 'active_nav_class' , 10 , 2);
function active_nav_class($classes, $item){
     if(is_single() && $item->title == category_description() ){ 
             $classes[] = "active";
             $classes[] = "current-menu-item";
     }
     return $classes;
}

add_filter('nav_menu_css_class' , 'active_nav_class' , 10 , 2);
function active_nav_class($classes, $item){
     $term = get_term_by( 'slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) );
     if(is_single() && $item->title == $term->name ){ 
             $classes[] = "active";
             $classes[] = "current-menu-item";
     }
     return $classes;
}