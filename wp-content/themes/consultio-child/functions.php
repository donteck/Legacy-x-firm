<?php

/**
 * Load the Consultio parent theme and Legacy X Firm child-theme assets.
 * File modification times are used as versions so browsers receive updated
 * CSS immediately after deployment while still allowing long-term caching.
 */
function consultio_enqueue_styles()
{
    $parent_style = 'consultio-style';
    $child_css    = get_stylesheet_directory() . '/style.css';

    wp_enqueue_style($parent_style, get_template_directory_uri() . '/style.css');
    wp_enqueue_style(
        'child-style',
        get_stylesheet_directory_uri() . '/style.css',
        array($parent_style),
        file_exists($child_css) ? filemtime($child_css) : null
    );

    if (is_page('client-portal')) {
        $portal_css = get_stylesheet_directory() . '/client-portal-layout.css';
        wp_enqueue_style(
            'legacyx-client-portal-layout',
            get_stylesheet_directory_uri() . '/client-portal-layout.css',
            array('child-style'),
            file_exists($portal_css) ? filemtime($portal_css) : null
        );
    }
}
add_action('wp_enqueue_scripts', 'consultio_enqueue_styles');

function legacyx_client_portal_template($template)
{
    if (is_page('client-portal')) {
        $portal_template = get_stylesheet_directory() . '/client-portal.php';
        if (file_exists($portal_template)) {
            return $portal_template;
        }
    }
    return $template;
}
add_filter('template_include', 'legacyx_client_portal_template', 99);

function legacyx_client_portal_body_class($classes)
{
    if (is_page('client-portal')) {
        $classes[] = 'legacyx-client-portal-page';
    }
    return $classes;
}
add_filter('body_class', 'legacyx_client_portal_body_class');

/* ==========================================================
 * LEGACY X FIRM PERFORMANCE CLEANUP
 * ========================================================== */

function legacyx_disable_emojis()
{
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_action('admin_print_scripts', 'print_emoji_detection_script');
    remove_action('admin_print_styles', 'print_emoji_styles');
    remove_filter('the_content_feed', 'wp_staticize_emoji');
    remove_filter('comment_text_rss', 'wp_staticize_emoji');
    remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
}
add_action('init', 'legacyx_disable_emojis');

remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'wlwmanifest_link');
remove_action('wp_head', 'wp_generator');
remove_action('wp_head', 'wp_shortlink_wp_head');
remove_action('template_redirect', 'wp_shortlink_header', 11);
remove_action('wp_head', 'rest_output_link_wp_head');
remove_action('wp_head', 'wp_oembed_add_discovery_links');

function legacyx_remove_wp_embed_script()
{
    if (!is_admin()) {
        wp_deregister_script('wp-embed');
    }
}
add_action('wp_footer', 'legacyx_remove_wp_embed_script', 1);

/**
 * Strip WooCommerce frontend assets from pages that do not use commerce UI.
 * Cart, checkout, account and WooCommerce catalog/product pages are untouched.
 */
function legacyx_optimize_woocommerce_assets()
{
    if (is_admin() || !class_exists('WooCommerce')) {
        return;
    }

    $commerce_page =
        (function_exists('is_woocommerce') && is_woocommerce()) ||
        (function_exists('is_cart') && is_cart()) ||
        (function_exists('is_checkout') && is_checkout()) ||
        (function_exists('is_account_page') && is_account_page());

    if ($commerce_page) {
        return;
    }

    $scripts = array(
        'wc-cart-fragments',
        'woocommerce',
        'wc-add-to-cart',
        'wc-add-to-cart-variation',
        'wc-single-product',
        'wc-checkout',
        'wc-country-select',
        'wc-address-i18n',
        'wc-password-strength-meter',
        'selectWoo'
    );

    foreach ($scripts as $handle) {
        wp_dequeue_script($handle);
        wp_deregister_script($handle);
    }

    $styles = array(
        'woocommerce-layout',
        'woocommerce-smallscreen',
        'woocommerce-general',
        'woocommerce-inline',
        'wc-blocks-style',
        'wc-blocks-vendors-style',
        'wc-blocks-packages-style'
    );

    foreach ($styles as $handle) {
        wp_dequeue_style($handle);
    }
}
add_action('wp_enqueue_scripts', 'legacyx_optimize_woocommerce_assets', 999);

function legacyx_remove_public_dashicons()
{
    if (!is_user_logged_in()) {
        wp_dequeue_style('dashicons');
    }
}
add_action('wp_enqueue_scripts', 'legacyx_remove_public_dashicons', 999);

/** Disable self-pingbacks so internal links do not create needless requests. */
function legacyx_disable_self_pingbacks(&$links)
{
    $home = home_url();
    foreach ($links as $key => $link) {
        if (strpos($link, $home) === 0) {
            unset($links[$key]);
        }
    }
}
add_action('pre_ping', 'legacyx_disable_self_pingbacks');

/**
 * WordPress already applies native lazy-loading heuristics. Do not force lazy
 * loading on every attachment, because the hero/LCP image should load early.
 * Async decoding remains safe for regular attachment images.
 */
function legacyx_optimize_image_attributes($attr)
{
    if (!is_admin() && empty($attr['decoding'])) {
        $attr['decoding'] = 'async';
    }
    return $attr;
}
add_filter('wp_get_attachment_image_attributes', 'legacyx_optimize_image_attributes', 20);

/**
 * The Client Portal is a custom PHP/CSS screen and does not use Elementor,
 * Revolution Slider, CF7, WooCommerce blocks, or Gutenberg frontend styles.
 * Remove those assets only on this page to make authentication/dashboard
 * rendering substantially lighter without affecting the rest of the site.
 */
function legacyx_optimize_client_portal_assets()
{
    if (is_admin() || !is_page('client-portal')) {
        return;
    }

    $scripts = array(
        'elementor-webpack-runtime',
        'elementor-frontend-modules',
        'elementor-frontend',
        'elementor-pro-frontend',
        'contact-form-7',
        'wpcf7-recaptcha',
        'google-recaptcha',
        'tp-tools',
        'revmin',
        'rs6',
        'wc-blocks-runtime',
        'wc-blocks-middleware',
        'wc-blocks-data-store'
    );

    foreach ($scripts as $handle) {
        wp_dequeue_script($handle);
    }

    $styles = array(
        'elementor-frontend',
        'elementor-post-0',
        'elementor-pro',
        'contact-form-7',
        'wp-block-library',
        'wp-block-library-theme',
        'global-styles',
        'classic-theme-styles',
        'rs-plugin-settings',
        'rs6',
        'wc-blocks-style',
        'wc-blocks-vendors-style',
        'wc-blocks-packages-style'
    );

    foreach ($styles as $handle) {
        wp_dequeue_style($handle);
    }
}
add_action('wp_enqueue_scripts', 'legacyx_optimize_client_portal_assets', 1000);

/**
 * Add lightweight connection hints for common external font hosts used by
 * Consultio/Elementor. Browsers can establish these connections earlier.
 */
function legacyx_resource_hints($urls, $relation_type)
{
    if ('preconnect' === $relation_type) {
        $urls[] = array(
            'href'        => 'https://fonts.gstatic.com',
            'crossorigin' => 'anonymous',
        );
    }

    if ('dns-prefetch' === $relation_type) {
        $urls[] = '//fonts.googleapis.com';
        $urls[] = '//fonts.gstatic.com';
    }

    return $urls;
}
add_filter('wp_resource_hints', 'legacyx_resource_hints', 10, 2);

/* ==========================================================
 * LEGACY X FIRM DEMO CONTENT CLEANUP
 * ========================================================== */

function legacyx_clean_demo_menu_items($items, $args)
{
    $demo_titles = array(
        'demos', 'demosnew', 'pages', 'elements', 'portfolio',
        'interactive elements', 'standard elements', 'infographics', 'typography'
    );

    foreach ($items as $key => $item) {
        $title = strtolower(trim(wp_strip_all_tags($item->title)));
        $url   = isset($item->url) ? strtolower($item->url) : '';
        if (in_array($title, $demo_titles, true) || strpos($url, 'demo.casethemes.net') !== false) {
            unset($items[$key]);
        }
    }
    return array_values($items);
}
add_filter('wp_nav_menu_objects', 'legacyx_clean_demo_menu_items', 50, 2);

function legacyx_hide_demo_home_widgets($instance, $widget, $args)
{
    if (is_admin() || !is_front_page()) {
        return $instance;
    }

    if (in_array($widget->id_base, array('archives', 'categories'), true)) {
        return false;
    }

    $serialized = strtolower(wp_json_encode($instance));
    $demo_markers = array(
        '380 st kilda road', 'melbourne, australia', '(210) 123-451',
        'at vero eos et accusamus', 'merchay.com', 'demo.casethemes.net'
    );

    foreach ($demo_markers as $marker) {
        if (strpos($serialized, $marker) !== false) {
            return false;
        }
    }
    return $instance;
}
add_filter('widget_display_callback', 'legacyx_hide_demo_home_widgets', 50, 3);

function legacyx_start_homepage_cleanup_buffer()
{
    if (!is_admin() && is_front_page()) {
        ob_start('legacyx_clean_homepage_html');
    }
}
add_action('template_redirect', 'legacyx_start_homepage_cleanup_buffer', 1);

function legacyx_clean_homepage_html($html)
{
    $html = preg_replace_callback(
        '#<a([^>]+href=["\'][^"\']*demo\.casethemes\.net[^"\']*["\'][^>]*)>(\s*View services\s*)</a>#i',
        function ($matches) {
            return '<a href="' . esc_url(home_url('/#services')) . '">' . $matches[2] . '</a>';
        },
        $html
    );

    $html = preg_replace(
        '#<a\b[^>]*href=["\'][^"\']*demo\.casethemes\.net[^"\']*["\'][^>]*>.*?</a>#is',
        '',
        $html
    );

    $replacements = array(
        '2024 © All rights reserved by' => '',
        '380 St Kilda Road, Melbourne, Australia' => '',
        'Call Us: (210) 123-451 (Sat - Thursday)' => '',
        'Monday - Friday (10am - 05 pm)' => '',
        'At vero eos et accusamus et iusto odio digni goikussimos ducimus qui to bonfo blanditiis praese. Ntium voluum deleniti atque.' => ''
    );

    return str_replace(array_keys($replacements), array_values($replacements), $html);
}
