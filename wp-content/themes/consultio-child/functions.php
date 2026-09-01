<?php

/**
 * Add child styles.
 * 
 * @author CaseThemes
 */
function consultio_enqueue_styles()
{
    $parent_style = 'consultio-style';
    
    wp_enqueue_style($parent_style, get_template_directory_uri() . '/style.css');
    wp_enqueue_style('child-style', get_stylesheet_directory_uri() . '/style.css', array(
        $parent_style
    ));
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
