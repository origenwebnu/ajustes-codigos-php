<?php
/**
 * CraftRootsMP - Personalización de correos WooCommerce (cliente)
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * URL del logo para todos los correos WooCommerce.
 */
function craftrootsmp_get_email_logo_url() {
    return get_stylesheet_directory_uri() . '/craftrootsmp/logo-email.svg';
}

/**
 * IDs de correos al cliente que usan el diseño La Foreste.
 */
function craftrootsmp_customer_email_ids() {
    return [
        'customer_processing_order',
        'customer_on_hold_order',
        'customer_completed_order',
    ];
}

/**
 * @param WC_Email|null $email
 */
function craftrootsmp_is_laforeste_customer_email($email) {
    return is_object($email)
        && !empty($email->id)
        && in_array($email->id, craftrootsmp_customer_email_ids(), true);
}

/**
 * Carga plantillas de correo desde craftrootsmp/woocommerce/.
 */
function craftrootsmp_locate_email_templates($template, $template_name, $template_path) {
    if (strpos($template_name, 'emails/') !== 0) {
        return $template;
    }

    $custom = get_stylesheet_directory() . '/craftrootsmp/woocommerce/' . $template_name;

    if (!file_exists($custom)) {
        return $template;
    }

    $customer_only_templates = [
        'emails/customer-processing-order.php',
        'emails/customer-on-hold-order.php',
        'emails/customer-completed-order.php',
        'emails/craftrootsmp-email-intro.php',
    ];

    $customer_context_templates = [
        'emails/email-header.php',
        'emails/email-order-details.php',
        'emails/email-order-items.php',
        'emails/email-addresses.php',
    ];

    if (in_array($template_name, $customer_only_templates, true)) {
        return $custom;
    }

    if (
        in_array($template_name, $customer_context_templates, true)
        && !empty($GLOBALS['craftrootsmp_rendering_customer_email'])
    ) {
        return $custom;
    }

    return $template;
}
add_filter('woocommerce_locate_template', 'craftrootsmp_locate_email_templates', 999, 3);

/**
 * Marca el contexto de renderizado para filtros compartidos.
 */
function craftrootsmp_email_header_flag($email_heading, $email) {
    if (craftrootsmp_is_laforeste_customer_email($email)) {
        $GLOBALS['craftrootsmp_rendering_customer_email'] = true;
        $GLOBALS['craftrootsmp_current_customer_email'] = $email;
    }
}
add_action('woocommerce_email_header', 'craftrootsmp_email_header_flag', 1, 2);

/**
 * Activa el contexto al reenviar correos desde el administrador.
 */
function craftrootsmp_email_before_resend($order, $email_id = '') {
    if ($email_id && in_array($email_id, craftrootsmp_customer_email_ids(), true)) {
        $GLOBALS['craftrootsmp_rendering_customer_email'] = true;
    }
}
add_action('woocommerce_before_resend_order_emails', 'craftrootsmp_email_before_resend', 1, 2);

function craftrootsmp_email_footer_unflag() {
    unset($GLOBALS['craftrootsmp_rendering_customer_email'], $GLOBALS['craftrootsmp_current_customer_email']);
}
add_action('woocommerce_email_footer', 'craftrootsmp_email_footer_unflag', 999);

/**
 * Logo del sitio en el encabezado de todos los correos.
 */
function craftrootsmp_email_header_image($image) {
    return craftrootsmp_get_email_logo_url();
}
add_filter('woocommerce_email_header_image', 'craftrootsmp_email_header_image');

/**
 * Título principal del correo.
 */
function craftrootsmp_email_heading($heading, $email) {
    if (!craftrootsmp_is_laforeste_customer_email($email)) {
        return $heading;
    }

    return '¡Gracias por tu compra!';
}
add_filter('woocommerce_email_heading', 'craftrootsmp_email_heading', 20, 2);

/**
 * Encabezado de la tabla de pedido.
 */
function craftrootsmp_email_order_details_heading($heading, $sent_to_admin, $order) {
    if ($sent_to_admin || empty($GLOBALS['craftrootsmp_rendering_customer_email'])) {
        return $heading;
    }

    return 'Resumen del pedido:';
}
add_filter('woocommerce_email_order_details_heading', 'craftrootsmp_email_order_details_heading', 20, 3);

/**
 * Miniatura de galería en filas del correo.
 */
function craftrootsmp_email_order_item_thumbnail($image, $item) {
    if (
        empty($GLOBALS['craftrootsmp_rendering_customer_email'])
        || !is_a($item, 'WC_Order_Item_Product')
        || !function_exists('craftrootsmp_get_gallery_product_image')
    ) {
        return $image;
    }

    $product = $item->get_product();
    if (!$product) {
        return $image;
    }

    $thumbnail = craftrootsmp_get_gallery_product_image($product, [64, 64]);

    if (!$thumbnail) {
        return $image;
    }

    return str_replace(
        'cr-checkout-gallery-thumb',
        'cr-email-product-thumb',
        $thumbnail
    );
}
add_filter('woocommerce_order_item_thumbnail', 'craftrootsmp_email_order_item_thumbnail', 20, 2);

/**
 * Nombre del producto con variación en el correo.
 */
function craftrootsmp_email_order_item_name($item_name, $item, $is_visible) {
    if (
        empty($GLOBALS['craftrootsmp_rendering_customer_email'])
        || !is_a($item, 'WC_Order_Item_Product')
    ) {
        return $item_name;
    }

    $product = $item->get_product();
    if (!$product) {
        return $item_name;
    }

    $name = wp_strip_all_tags($item_name);
    $name = preg_replace('/\s*×\s*\d+\s*$/', '', $name);

    $meta_lines = wc_display_item_meta(
        $item,
        [
            'before'    => '',
            'after'     => '',
            'separator' => ' / ',
            'echo'      => false,
            'autop'     => false,
        ]
    );

    if ($meta_lines) {
        $meta_plain = wp_strip_all_tags($meta_lines);
        $meta_plain = preg_replace('/\s+/', ' ', $meta_plain);
        $name .= ' / ' . trim($meta_plain);
    }

    return esc_html($name);
}
add_filter('woocommerce_order_item_name', 'craftrootsmp_email_order_item_name', 30, 3);

/**
 * Etiquetas y valores de totales en español.
 */
function craftrootsmp_email_order_item_totals($total_rows, $order, $tax_display) {
    if (empty($GLOBALS['craftrootsmp_rendering_customer_email']) || !$order) {
        return $total_rows;
    }

    if (isset($total_rows['cart_subtotal'])) {
        $total_rows['cart_subtotal']['label'] = 'Subtotal:';
    }

    if (isset($total_rows['shipping']) && (float) $order->get_shipping_total() <= 0) {
        $total_rows['shipping']['label'] = 'Envío:';
        $total_rows['shipping']['value'] = 'Gratis';
    } elseif (isset($total_rows['shipping'])) {
        $total_rows['shipping']['label'] = 'Envío:';
    }

    if (isset($total_rows['order_total'])) {
        $total_rows['order_total']['label'] = 'Total:';
    }

    if (isset($total_rows['payment_method'])) {
        $total_rows['payment_method']['label'] = 'Método de pago:';
    }

    return $total_rows;
}
add_filter('woocommerce_get_order_item_totals', 'craftrootsmp_email_order_item_totals', 25, 3);

/**
 * Identificación en dirección de facturación del correo.
 */
function craftrootsmp_email_billing_address($address, $raw_address, $order) {
    if (empty($GLOBALS['craftrootsmp_rendering_customer_email']) || !$order || $address === '') {
        return $address;
    }

    if (!function_exists('craftrootsmp_get_order_identification')) {
        return $address;
    }

    $value = craftrootsmp_get_order_identification($order);
    if ($value === '') {
        return $address;
    }

    return $address . '<br>' . esc_html($value);
}
add_filter('woocommerce_order_get_formatted_billing_address', 'craftrootsmp_email_billing_address', 25, 3);

/**
 * Evita duplicar la identificación en el bloque de meta del correo.
 */
function craftrootsmp_email_remove_identification_meta($fields, $sent_to_admin, $order) {
    if (empty($GLOBALS['craftrootsmp_rendering_customer_email'])) {
        return $fields;
    }

    if (defined('CRAFTROOTSMP_ID_FIELD_KEY')) {
        unset($fields[CRAFTROOTSMP_ID_FIELD_KEY]);
    }

    return $fields;
}
add_filter('woocommerce_email_order_meta_fields', 'craftrootsmp_email_remove_identification_meta', 999, 3);

/**
 * Estilos del correo alineados con la maqueta La Foreste.
 *
 * @param WC_Email $email
 */
function craftrootsmp_email_styles($css, $email) {
    if (!craftrootsmp_is_laforeste_customer_email($email)) {
        return $css;
    }

    $lime = '#DFFF70';
    $text = '#332B2B';
    $border = '#E0E0E0';

    $css .= '
#wrapper {
    background-color: #ffffff;
    padding: 0;
}

#template_container {
    box-shadow: none !important;
    border-radius: 0 !important;
}

#template_header {
    background-color: ' . $lime . ' !important;
    border-radius: 0 !important;
    padding: 36px 32px 28px !important;
}

#header_wrapper {
    padding: 0 !important;
}

#template_header h1 {
    color: ' . $text . ' !important;
    font-size: 22px !important;
    font-weight: 600 !important;
    line-height: 1.3 !important;
    margin: 18px 0 0 !important;
    text-align: center !important;
}

#template_header img {
    display: block !important;
    margin: 0 auto !important;
    max-width: 120px !important;
    width: 120px !important;
    height: auto !important;
}

#body_content {
    background-color: #ffffff !important;
}

#body_content_inner {
    color: ' . $text . ' !important;
    font-family: Helvetica, Arial, sans-serif !important;
    font-size: 15px !important;
    line-height: 1.6 !important;
    padding: 28px 32px 8px !important;
}

#body_content_inner p {
    color: ' . $text . ' !important;
    margin: 0 0 14px !important;
}

.cr-email-intro {
    margin-bottom: 24px !important;
}

.cr-email-intro p {
    margin: 0 0 12px !important;
}

#body_content_inner h2,
#body_content_inner h3 {
    color: ' . $text . ' !important;
    font-size: 16px !important;
    font-weight: 700 !important;
    margin: 0 0 16px !important;
}

.order-details-title {
    margin-top: 8px !important;
}

.td {
    color: ' . $text . ' !important;
    border-color: ' . $border . ' !important;
    padding: 12px 8px !important;
    vertical-align: middle !important;
}

th.td,
th.td:nth-child(2),
th.td:nth-child(3) {
    font-weight: 600 !important;
    border-bottom: 1px solid ' . $border . ' !important;
}

.order_item .td {
    border-bottom: 1px solid ' . $border . ' !important;
}

.order_item .td:first-child {
    width: 58% !important;
}

.order_item .td:nth-child(2) {
    text-align: center !important;
    width: 14% !important;
}

.order_item .td:nth-child(3) {
    text-align: right !important;
    width: 28% !important;
}

.cr-email-product-thumb {
    border-radius: 6px !important;
    display: block !important;
    height: 64px !important;
    margin-right: 12px !important;
    object-fit: cover !important;
    width: 64px !important;
}

.order_item .td:first-child .cr-email-product-cell {
    display: table !important;
    width: 100% !important;
}

.order_item .td:first-child .cr-email-product-cell > span {
    display: table-cell !important;
    vertical-align: middle !important;
}

.order-totals td {
    border-top: 1px solid ' . $border . ' !important;
    padding-top: 10px !important;
    padding-bottom: 10px !important;
}

.order-totals tr:first-child td {
    border-top: 1px solid ' . $border . ' !important;
}

.order-totals .td:first-child {
    text-align: left !important;
    width: 70% !important;
}

.order-totals .td:last-child {
    text-align: right !important;
    width: 30% !important;
}

.order-totals-last td {
    font-weight: 700 !important;
}

#body_content_inner .address {
    background: transparent !important;
    border: 0 !important;
    color: ' . $text . ' !important;
    padding: 0 !important;
}

.cr-email-addresses {
    border-top: 1px solid ' . $border . ' !important;
    margin-top: 24px !important;
    padding-top: 24px !important;
}

.cr-email-addresses h3 {
    font-size: 15px !important;
    margin: 0 0 10px !important;
}

.cr-email-addresses p {
    margin: 0 0 6px !important;
}

#template_footer {
    background-color: #ffffff !important;
    border-top: 0 !important;
}

#template_footer #credit {
    border-top: 0 !important;
    color: #888888 !important;
    font-size: 12px !important;
    padding: 16px 32px 24px !important;
}
';

    return $css;
}
add_filter('woocommerce_email_styles', 'craftrootsmp_email_styles', 999, 2);