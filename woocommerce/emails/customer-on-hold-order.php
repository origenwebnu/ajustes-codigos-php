<?php
/**
 * Wrapper: WooCommerce busca plantillas aquí por defecto.
 * La lógica vive en craftrootsmp/woocommerce/emails/.
 */

defined('ABSPATH') || exit;

$template = get_stylesheet_directory() . '/craftrootsmp/woocommerce/emails/customer-on-hold-order.php';

if (file_exists($template)) {
    include $template;
}
