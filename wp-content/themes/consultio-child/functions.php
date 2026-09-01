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

/**
 * Load the Legacy X Firm Client Portal template for the /client-portal/ page.
 */
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

/**
 * Add an isolated body class so the Client Portal can override Consultio's
 * page/content container rules without affecting any other page.
 */
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
 * Safe front-end reductions only. No database, uploads, orders,
 * customer records, Elementor data, or plugin settings are deleted.
 * ========================================================== */

/** Remove WordPress emoji detection scripts/styles from the public site. */
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

/** Remove small legacy/discovery tags that are unnecessary for this site. */
remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'wlwmanifest_link');
remove_action('wp_head', 'wp_generator');
remove_action('wp_head', 'wp_shortlink_wp_head');
remove_action('template_redirect', 'wp_shortlink_header', 11);
remove_action('wp_head', 'rest_output_link_wp_head');
remove_action('wp_head', 'wp_oembed_add_discovery_links');

/** Disable the WordPress embed JavaScript while preserving normal embeds. */
function legacyx_remove_wp_embed_script()
{
    if (!is_admin()) {
        wp_deregister_script('wp-embed');
    }
}
add_action('wp_footer', 'legacyx_remove_wp_embed_script', 1);

/**
 * WooCommerce cart fragments continuously refresh the mini-cart through AJAX.
 * Keep them on commerce/account pages, but do not load them across ordinary
 * marketing pages or the Client Portal when they are not needed.
 */
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

/** Remove Dashicons for logged-out visitors when WordPress admin UI is absent. */
function legacyx_remove_public_dashicons()
{
    if (!is_user_logged_in()) {
        wp_dequeue_style('dashicons');
    }
}
add_action('wp_enqueue_scripts', 'legacyx_remove_public_dashicons', 100);
