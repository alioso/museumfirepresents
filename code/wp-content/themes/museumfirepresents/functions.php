<?php

add_action( 'wp_enqueue_scripts', 'customInclude' );

function customInclude() {
  wp_enqueue_style(
    'my-style',
    get_stylesheet_directory_uri() . '/css/main.css',
    false,
    '1.0',
    'all' );
  wp_enqueue_script(
    'custom',
    get_stylesheet_directory_uri() . '/js/custom.js',
    array( 'jquery' ),
    '1.0.0',
    true
  );
}

//Page Slug Body Class
function add_slug_body_class( $classes ) {
  global $post;
  if ( isset( $post ) ) {
    $classes[] = $post->post_type . '-' . $post->post_name;
  }
  return $classes;
}
add_filter( 'body_class', 'add_slug_body_class' );
