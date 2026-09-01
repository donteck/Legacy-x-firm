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

/** Load the Legacy X Firm Client Portal template. */
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

/** Add an isolated body class to the Client Portal. */
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

function legacyx_optimize_woocommerce_assets()
{
    if (is_admin() || !class_exists('WooCommerce')) {
        return;
    }

    $commerce_page = false;

    if (function_exists('is_woocommerce') && is_woocommerce()) {
        $commerce_page = true;
    }
    if (function_exists('is_cart') && is_cart()) {
        $commerce_page = true;
    }
    if (function_exists('is_checkout') && is_checkout()) {
        $commerce_page = true;
    }
    if (function_exists('is_account_page') && is_account_page()) {
        $commerce_page = true;
    }

    if (!$commerce_page) {
        wp_dequeue_script('wc-cart-fragments');
        wp_deregister_script('wc-cart-fragments');
    }
}
add_action('wp_enqueue_scripts', 'legacyx_optimize_woocommerce_assets', 100);

function legacyx_remove_public_dashicons()
{
    if (!is_user_logged_in()) {
        wp_dequeue_style('dashicons');
    }
}
add_action('wp_enqueue_scripts', 'legacyx_remove_public_dashicons', 100);

/* ==========================================================
 * LEGACY X FIRM DEMO CONTENT CLEANUP
 * Removes Consultio starter/demo output from the public site without
 * deleting legitimate WordPress pages, posts, customers, orders or files.
 * ========================================================== */

/** Remove imported Consultio demo menu items and demo-domain links. */
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

/**
 * Hide imported starter widgets from the homepage only. This removes the old
 * Archives/Categories blocks and known Consultio placeholder contact widgets.
 */
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
        '380 st kilda road',
        'melbourne, australia',
        '(210) 123-451',
        'at vero eos et accusamus',
        'merchay.com',
        'demo.casethemes.net'
    );

    foreach ($demo_markers as $marker) {
        if (strpos($serialized, $marker) !== false) {
            return false;
        }
    }

    return $instance;
}
add_filter('widget_display_callback', 'legacyx_hide_demo_home_widgets', 50, 3);

/**
 * Final targeted HTML sanitation for known imported Consultio remnants.
 * Only the public homepage is filtered, and only exact demo markers are touched.
 */
function legacyx_start_homepage_cleanup_buffer()
{
    if (!is_admin() && is_front_page()) {
        ob_start('legacyx_clean_homepage_html');
    }
}
add_action('template_redirect', 'legacyx_start_homepage_cleanup_buffer', 1);

function legacyx_clean_homepage_html($html)
{
    // Route the three imported hero "View services" links to Legacy X Firm services.
    $html = preg_replace_callback(
        '#<a([^>]+href=["\'][^"\']*demo\.casethemes\.net[^"\']*["\'][^>]*)>(\s*View services\s*)</a>#i',
        function ($matches) {
            return '<a href="' . esc_url(home_url('/#services')) . '">' . $matches[2] . '</a>';
        },
        $html
    );

    // Remove any remaining imported demo-domain anchors from the homepage output.
    $html = preg_replace(
        '#<a\b[^>]*href=["\'][^"\']*demo\.casethemes\.net[^"\']*["\'][^>]*>.*?</a>#is',
        '',
        $html
    );

    // Remove exact old theme/demo footer strings if the parent theme prints them directly.
    $replacements = array(
        '2024 © All rights reserved by' => '',
        '380 St Kilda Road, Melbourne, Australia' => '',
        'Call Us: (210) 123-451 (Sat - Thursday)' => '',
        'Monday - Friday (10am - 05 pm)' => '',
        'At vero eos et accusamus et iusto odio digni goikussimos ducimus qui to bonfo blanditiis praese. Ntium voluum deleniti atque.' => ''
    );

    return str_replace(array_keys($replacements), array_values($replacements), $html);
}
