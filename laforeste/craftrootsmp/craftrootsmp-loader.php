<?php
/**
 * CraftRootsMP - Loader de assets WooCommerce
 *
 * En functions.php del child theme:
 * require get_stylesheet_directory() . '/craftrootsmp/craftrootsmp-loader.php';
 */

if (!defined('ABSPATH')) {
    exit;
}

$craftrootsmp_dir = get_stylesheet_directory() . '/craftrootsmp';

if (file_exists($craftrootsmp_dir . '/craftrootsmp-cart-hooks.php')) {
    require $craftrootsmp_dir . '/craftrootsmp-cart-hooks.php';
}

if (file_exists($craftrootsmp_dir . '/craftrootsmp-checkout-hooks.php')) {
    require $craftrootsmp_dir . '/craftrootsmp-checkout-hooks.php';
}

if (file_exists($craftrootsmp_dir . '/craftrootsmp-stripe-hooks.php')) {
    require $craftrootsmp_dir . '/craftrootsmp-stripe-hooks.php';
}

if (file_exists($craftrootsmp_dir . '/craftrootsmp-email-hooks.php')) {
    require $craftrootsmp_dir . '/craftrootsmp-email-hooks.php';
}
