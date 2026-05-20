<?php

namespace Roots\Sage\Extras;

use Roots\Sage\Setup;

/**
 * Add <body> classes
 */
function body_class($classes) {
  // Add page slug if it doesn't exist
  if (is_single() || is_page() && !is_front_page()) {
    if (!in_array(basename(get_permalink()), $classes)) {
      $classes[] = basename(get_permalink());
    }
  }

  // Add class if sidebar is active
  if (Setup\display_sidebar()) {
    $classes[] = 'sidebar-primary';
  }

  return $classes;
}
add_filter('body_class', __NAMESPACE__ . '\\body_class');

/**
 * Clean up the_excerpt()
 */
function excerpt_more() {
  return ' ...';
}
add_filter('excerpt_more', __NAMESPACE__ . '\\excerpt_more');



//Add Custom Style on WP admin page
function admin_page_custom_style() {
  echo '<style>
    #menu-pages a.menu-top,
    #menu-posts a.menu-top,
    #menu-posts-event a.menu-top,
    #toplevel_page_wpcf7 a.menu-top,
    #toplevel_page_acf-options-general-settings a.menu-top {
        color: #f91d1d !important;
    }
    .acf-fc-layout-handle {
      color: #fff !important;
      background-color: #e64626;
    }
  </style>';
}
add_action('admin_head', __NAMESPACE__ . '\\admin_page_custom_style');


/**
 * Deletes all CSS classes and id's, except for those listed in the array below
 */
// function custom_wp_nav_menu($var) {
//   return is_array($var) ? array_intersect($var, array(
//     //List of allowed menu classes
//     'menu-item',
//     'current_page_item',
//     'current_page_parent',
//     'current_page_ancestor',
//     'menu-item-has-children',
//     'first',
//     'last',
//     'vertical',
//     'horizontal'
//     )
//   ) : '';
// }
// add_filter('nav_menu_css_class', __NAMESPACE__ . '\\custom_wp_nav_menu');
// add_filter('nav_menu_item_id', __NAMESPACE__ . '\\custom_wp_nav_menu');
// add_filter('page_css_class', __NAMESPACE__ . '\\custom_wp_nav_menu');


/**
 * Replace menu class names
 */
// function replace_menu_classes($text){
//   $replace = array(
//     //List of menu item classes that should be changed to "active"
//     'menu-item' => 'nav-item',
//     'current_page_item' => 'active',
//     'menu-item-has-children' => 'parent',
//     'current_page_ancestor' => 'selected',
//   );
//   $text = str_replace(array_keys($replace), $replace, $text);
//     return $text;
//   }
// add_filter ('wp_nav_menu', __NAMESPACE__ . '\\replace_menu_classes');


/**
 * Add class to menu anchor
 */
// function add_link_atts($atts) {
//   $atts['class'] .= " nav-link";
//   return $atts;
// }
// add_filter( 'nav_menu_link_attributes', __NAMESPACE__ . '\\add_link_atts');


/**
 * Options Page
 */
if( function_exists('acf_add_options_page') ) {

  acf_add_options_page(array(
    'page_title'  => 'Theme Settings',
    'menu_title'  => 'Theme Settings',
    'menu_slug'   => 'theme-general-settings',
    'capability'  => 'edit_posts',
    'redirect'    => true
  ));

  acf_add_options_sub_page(array(
    'page_title'  => 'General Settings',
    'menu_title'  => 'General Settings',
    'parent_slug' => 'theme-general-settings'
  ));

  acf_add_options_sub_page(array(
    'page_title'  => 'Header Settings',
    'menu_title'  => 'Header Settings',
    'parent_slug' => 'theme-general-settings'
  ));

  acf_add_options_sub_page(array(
    'page_title'  => 'Footer Settings',
    'menu_title'  => 'Footer Settings',
    'parent_slug' => 'theme-general-settings'
  ));
}

wp_enqueue_style( 'style', get_stylesheet_uri() );



function my_unregister_widgets() {
  unregister_widget('WP_Widget_Pages');
  unregister_widget('WP_Widget_Calendar');
  unregister_widget('WP_Widget_Archives');
  unregister_widget('WP_Widget_Links');
  unregister_widget('WP_Widget_Meta');
  unregister_widget('WP_Widget_Search');
  unregister_widget('WP_Widget_Text');
  unregister_widget('WP_Widget_Categories');
  unregister_widget('WP_Widget_Recent_Posts');
  unregister_widget('WP_Widget_Recent_Comments');
  unregister_widget('WP_Widget_RSS');
  unregister_widget('WP_Widget_Tag_Cloud');
  unregister_widget('WP_Nav_Menu_Widget');
  unregister_widget('Twenty_Eleven_Ephemera_Widget');
}
add_action( 'widgets_init', __NAMESPACE__ . '\\my_unregister_widgets' );
