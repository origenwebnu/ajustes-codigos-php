<?php
/**
 * CraftRootsMP - Assets del carrito WooCommerce
 *
 * En functions.php del child theme:
 * require get_stylesheet_directory() . '/craftrootsmp/craftrootsmp-cart-hooks.php';
 */

if (!defined('ABSPATH')) {
    exit;
}

function craftrootsmp_enqueue_cart_assets() {
    if (!is_cart()) {
        return;
    }

    $base_uri = get_stylesheet_directory_uri() . '/craftrootsmp';
    $base_dir = get_stylesheet_directory() . '/craftrootsmp';

    $css_version = file_exists($base_dir . '/cart.css')
        ? filemtime($base_dir . '/cart.css')
        : '1.0.0';

    $js_version = file_exists($base_dir . '/cart.js')
        ? filemtime($base_dir . '/cart.js')
        : '1.0.0';

    wp_enqueue_style(
        'craftrootsmp-cart',
        $base_uri . '/cart.css',
        [],
        $css_version
    );

    wp_enqueue_script(
        'craftrootsmp-cart',
        $base_uri . '/cart.js',
        ['jquery'],
        $js_version,
        true
    );
}
add_action('wp_enqueue_scripts', 'craftrootsmp_enqueue_cart_assets', 40);
