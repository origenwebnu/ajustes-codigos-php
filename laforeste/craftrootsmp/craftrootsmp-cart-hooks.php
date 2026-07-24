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

/**
 * Usa la primera imagen de la galería del producto en el miniatura del carrito.
 */
function craftrootsmp_cart_item_gallery_thumbnail($thumbnail, $cart_item, $cart_item_key) {
    if (empty($cart_item['data']) || !is_a($cart_item['data'], 'WC_Product')) {
        return $thumbnail;
    }

    $product = $cart_item['data'];
    $product_id = $product->get_id();

    if ($product->is_type('variation')) {
        $product_id = $product->get_parent_id();
        $product = wc_get_product($product_id);
    }

    if (!$product) {
        return $thumbnail;
    }

    $gallery_ids = $product->get_gallery_image_ids();

    if (empty($gallery_ids)) {
        return $thumbnail;
    }

    $first_gallery_id = (int) $gallery_ids[0];
    $gallery_image = wp_get_attachment_image(
        $first_gallery_id,
        'woocommerce_single',
        false,
        [
            'class' => 'attachment-woocommerce_single size-woocommerce_single cr-cart-gallery-thumb',
            'alt'   => $product->get_name(),
            'style' => 'max-width:120px;height:auto;',
        ]
    );

    return $gallery_image ?: $thumbnail;
}
add_filter('woocommerce_cart_item_thumbnail', 'craftrootsmp_cart_item_gallery_thumbnail', 20, 3);
