<?php
if (!defined('ABSPATH')) { exit; }

function legacyx_theme_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', array('search-form','comment-form','comment-list','gallery','caption','style','script'));
    register_nav_menus(array('primary' => __('Primary Navigation','legacy-x-firm')));
}
add_action('after_setup_theme','legacyx_theme_setup');

function legacyx_enqueue_assets() {
    $css = get_stylesheet_directory() . '/style.css';
    $polish = get_stylesheet_directory() . '/assets/css/polish.css';
    wp_enqueue_style('legacyx-style', get_stylesheet_uri(), array(), file_exists($css) ? filemtime($css) : '1.0.0');
    wp_enqueue_style('legacyx-polish', get_stylesheet_directory_uri() . '/assets/css/polish.css', array('legacyx-style'), file_exists($polish) ? filemtime($polish) : '1.0.0');
}
add_action('wp_enqueue_scripts','legacyx_enqueue_assets');

function legacyx_client_portal_template($template) {
    if (is_page('client-portal')) {
        $portal = get_theme_file_path('/client-portal.php');
        if (file_exists($portal)) return $portal;
    }
    return $template;
}
add_filter('template_include','legacyx_client_portal_template',99);

function legacyx_body_classes($classes) {
    if (is_page('client-portal')) $classes[] = 'legacyx-client-portal-page';
    return $classes;
}
add_filter('body_class','legacyx_body_classes');

function legacyx_disable_emojis() {
    remove_action('wp_head','print_emoji_detection_script',7);
    remove_action('wp_print_styles','print_emoji_styles');
    remove_action('admin_print_scripts','print_emoji_detection_script');
    remove_action('admin_print_styles','print_emoji_styles');
}
add_action('init','legacyx_disable_emojis');

remove_action('wp_head','rsd_link');
remove_action('wp_head','wlwmanifest_link');
remove_action('wp_head','wp_generator');
remove_action('wp_head','wp_shortlink_wp_head');
remove_action('template_redirect','wp_shortlink_header',11);
remove_action('wp_head','wp_oembed_add_discovery_links');

function legacyx_remove_embed() {
    if (!is_admin()) wp_deregister_script('wp-embed');
}
add_action('wp_footer','legacyx_remove_embed',1);

function legacyx_remove_dashicons() {
    if (!is_user_logged_in()) wp_dequeue_style('dashicons');
}
add_action('wp_enqueue_scripts','legacyx_remove_dashicons',100);

function legacyx_disable_self_pingbacks(&$links) {
    $home = home_url();
    foreach ($links as $key => $link) {
        if (strpos($link,$home) === 0) unset($links[$key]);
    }
}
add_action('pre_ping','legacyx_disable_self_pingbacks');

function legacyx_async_images($attr) {
    if (!is_admin() && empty($attr['decoding'])) $attr['decoding'] = 'async';
    return $attr;
}
add_filter('wp_get_attachment_image_attributes','legacyx_async_images',20);
