<?php
/**
 * Legacy X Firm — V2.4 Executive Luxury Homepage
 * Approved WordPress conversion: 2026-09-01
 * Standalone homepage template. Client Portal and all other WP templates remain separate.
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$legacyx_payload = '';
for ( $legacyx_i = 1; $legacyx_i <= 7; $legacyx_i++ ) {
    $legacyx_part = get_stylesheet_directory() . '/home-v24-payload/part' . str_pad( (string) $legacyx_i, 2, '0', STR_PAD_LEFT ) . '.txt';
    if ( is_readable( $legacyx_part ) ) {
        $legacyx_payload .= trim( (string) file_get_contents( $legacyx_part ) );
    }
}

$legacyx_binary = base64_decode( $legacyx_payload, true );
$legacyx_home   = ( false !== $legacyx_binary && function_exists( 'gzdecode' ) ) ? gzdecode( $legacyx_binary ) : false;
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="description" content="Legacy X Firm provides integrated business, credit, capital, financial, tax, nonprofit, intelligence and operating solutions.">
<?php wp_head(); ?>
</head>
<body <?php body_class( 'legacyx-luxury-home legacyx-home-v24' ); ?>>
<?php wp_body_open(); ?>
<?php
if ( false !== $legacyx_home ) {
    echo $legacyx_home; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted version-controlled homepage markup.
} else {
    echo '<main style="max-width:900px;margin:120px auto;padding:40px;font-family:Arial,sans-serif"><h1>Legacy X Firm</h1><p>The homepage is temporarily unavailable. Please contact info@legacyxfirm.us.</p></main>';
}
?>
<?php wp_footer(); ?>
</body>
</html>
