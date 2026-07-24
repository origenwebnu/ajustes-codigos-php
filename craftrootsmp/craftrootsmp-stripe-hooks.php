<?php
/**
 * CraftRootsMP - Mover Stripe Express Checkout al resumen del pedido
 *
 * Pegar este código en el functions.php del child theme
 * o incluirlo con: require get_stylesheet_directory() . '/craftrootsmp/craftrootsmp-stripe-hooks.php';
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Reposiciona los hooks de Stripe Express Checkout (carrito + checkout).
 */
function craftrootsmp_move_stripe_express_checkout_hooks() {
    if (!class_exists('WC_Stripe_Express_Checkout_Element')) {
        return;
    }

    $stripe = WC_Stripe_Express_Checkout_Element::instance();

    remove_action('woocommerce_checkout_before_customer_details', [$stripe, 'display_express_checkout_button_html'], 1);
    remove_action('woocommerce_proceed_to_checkout', [$stripe, 'display_express_checkout_button_html'], 6);

    if (method_exists($stripe, 'display_express_checkout_button_separator_html')) {
        remove_action('woocommerce_checkout_before_customer_details', [$stripe, 'display_express_checkout_button_separator_html'], 2);
        remove_action('woocommerce_proceed_to_checkout', [$stripe, 'display_express_checkout_button_separator_html'], 7);
    }

    // Carrito: debajo de "Proceed to checkout" (botón WooCommerce usa prioridad 20)
    add_action('woocommerce_proceed_to_checkout', [$stripe, 'display_express_checkout_button_html'], 30);

    // Checkout: dentro del resumen del pedido, antes de métodos de pago
    add_action('woocommerce_review_order_before_payment', [$stripe, 'display_express_checkout_button_html'], 5);
}
add_action('wp_loaded', 'craftrootsmp_move_stripe_express_checkout_hooks', 20);

/**
 * Encola CSS y JS personalizados en cart/checkout.
 */
function craftrootsmp_enqueue_stripe_express_assets() {
    if (!is_cart() && !is_checkout()) {
        return;
    }

    $base_uri = get_stylesheet_directory_uri() . '/craftrootsmp';
    $base_dir = get_stylesheet_directory() . '/craftrootsmp';
    $version  = file_exists($base_dir . '/stripe-express-checkout.css')
        ? filemtime($base_dir . '/stripe-express-checkout.css')
        : '1.0.0';

    wp_enqueue_style(
        'craftrootsmp-stripe-express',
        $base_uri . '/stripe-express-checkout.css',
        ['wc-stripe-express-checkout-style'],
        $version
    );

    wp_enqueue_script(
        'craftrootsmp-stripe-express',
        $base_uri . '/stripe-express-checkout.js',
        ['wc-stripe-express-checkout'],
        $version,
        true
    );
}
add_action('wp_enqueue_scripts', 'craftrootsmp_enqueue_stripe_express_assets', 30);
