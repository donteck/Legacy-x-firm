<?php
/**
 * Legacy X Firm optimized front page.
 *
 * The Consultio parent template is preserved, but the expensive Revolution
 * Slider first screen is replaced at render time with a lightweight static
 * hero. This keeps the existing homepage sections and WordPress editing model
 * intact while reducing above-the-fold JavaScript, layered images and slider
 * initialization work.
 */

if (!defined('ABSPATH')) {
    exit;
}

/** Load the lightweight hero stylesheet and remove Revolution Slider assets. */
add_action('wp_enqueue_scripts', function () {
    $hero_css = get_stylesheet_directory() . '/homepage-hero.css';

    wp_enqueue_style(
        'legacyx-homepage-hero',
        get_stylesheet_directory_uri() . '/homepage-hero.css',
        array('child-style'),
        file_exists($hero_css) ? filemtime($hero_css) : null
    );

    // Common Slider Revolution handles across versions 5, 6 and 7.
    $slider_scripts = array(
        'tp-tools',
        'revmin',
        'rs6',
        'sr7',
        'sr7-scripts',
        'revslider-front'
    );

    foreach ($slider_scripts as $handle) {
        wp_dequeue_script($handle);
        wp_deregister_script($handle);
    }

    $slider_styles = array(
        'rs-plugin-settings',
        'rs6',
        'sr7',
        'sr7css',
        'revslider-front'
    );

    foreach ($slider_styles as $handle) {
        wp_dequeue_style($handle);
    }
}, 2000);

/**
 * Convert the rendered Slider Revolution block to a single semantic hero.
 * If the slider markup changes in a future plugin update, the page falls back
 * to the original output rather than failing.
 */
function legacyx_replace_revolution_slider_with_fast_hero($html)
{
    $hero = '<section class="legacyx-fast-hero" aria-labelledby="legacyx-fast-hero-title">'
        . '<div class="legacyx-fast-hero__overlay"></div>'
        . '<div class="legacyx-fast-hero__inner">'
        . '<div class="legacyx-fast-hero__content">'
        . '<span class="legacyx-fast-hero__eyebrow">Welcome to Legacy X Firm</span>'
        . '<h1 id="legacyx-fast-hero-title">Build Stronger Credit. Build a Stronger Business.</h1>'
        . '<p>Strategic solutions for personal and business credit, business management, financial consulting, grants, tax services, and long-term growth.</p>'
        . '<div class="legacyx-fast-hero__actions">'
        . '<a class="legacyx-fast-hero__primary" href="' . esc_url(home_url('/#services')) . '">Explore Our Services</a>'
        . '<a class="legacyx-fast-hero__secondary" href="' . esc_url(home_url('/client-portal/')) . '">Client Portal</a>'
        . '</div>'
        . '<div class="legacyx-fast-hero__contact"><span>Business Support</span><strong>(424) 703-0312</strong></div>'
        . '</div>'
        . '</div>'
        . '</section>';

    // Slider Revolution 6/7 markup.
    $updated = preg_replace(
        '#<rs-module-wrap\b[^>]*>.*?</rs-module-wrap>#is',
        $hero,
        $html,
        1,
        $count
    );

    if (!empty($count)) {
        return $updated;
    }

    // Slider Revolution 5 fallback markup.
    $updated = preg_replace(
        '#<div\b[^>]*class=["\'][^"\']*rev_slider_wrapper[^"\']*["\'][^>]*>.*?</div>\s*</div>#is',
        $hero,
        $html,
        1,
        $count
    );

    return !empty($count) ? $updated : $html;
}

ob_start('legacyx_replace_revolution_slider_with_fast_hero');

$parent_front_page = get_template_directory() . '/front-page.php';
$parent_page       = get_template_directory() . '/page.php';

if (file_exists($parent_front_page)) {
    require $parent_front_page;
} elseif (file_exists($parent_page)) {
    require $parent_page;
} else {
    get_header();
    while (have_posts()) {
        the_post();
        the_content();
    }
    get_footer();
}

ob_end_flush();
