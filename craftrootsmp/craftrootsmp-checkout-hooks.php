<?php
/**
 * CraftRootsMP - Assets y personalización del checkout WooCommerce
 *
 * En functions.php del child theme:
 * require get_stylesheet_directory() . '/craftrootsmp/craftrootsmp-checkout-hooks.php';
 */

if (!defined('ABSPATH')) {
    exit;
}

define('CRAFTROOTSMP_ID_FIELD_KEY', 'billing_identification');
define('CRAFTROOTSMP_ID_META_KEY', '_billing_identification');

/**
 * Claves de campos de identificación que pueden existir por otros plugins.
 */
function craftrootsmp_get_identification_field_keys() {
    return [
        'billing_document',
        'billing_cedula',
        'billing_identification',
        'billing_id_number',
        'billing_nit',
    ];
}

/**
 * Agrega el campo "Número de identificación" al checkout.
 */
function craftrootsmp_add_identification_checkout_field($fields) {
    if (empty($fields['billing'])) {
        return $fields;
    }

    foreach (craftrootsmp_get_identification_field_keys() as $key) {
        if (!empty($fields['billing'][$key])) {
            return $fields;
        }
    }

    $fields['billing'][CRAFTROOTSMP_ID_FIELD_KEY] = [
        'type'         => 'text',
        'label'        => 'Número de Identificación',
        'required'     => true,
        'class'        => ['form-row-first'],
        'priority'     => 30,
        'autocomplete' => 'off',
    ];

    return $fields;
}
add_filter('woocommerce_checkout_fields', 'craftrootsmp_add_identification_checkout_field', 10);

/**
 * Guarda el número de identificación en el pedido.
 */
function craftrootsmp_save_identification_checkout_field($order, $data) {
    $field_key = CRAFTROOTSMP_ID_FIELD_KEY;

    if (empty($data[$field_key])) {
        return;
    }

    $order->update_meta_data(
        CRAFTROOTSMP_ID_META_KEY,
        sanitize_text_field($data[$field_key])
    );
}
add_action('woocommerce_checkout_create_order', 'craftrootsmp_save_identification_checkout_field', 10, 2);

/**
 * Obtiene el valor guardado del número de identificación.
 */
function craftrootsmp_get_order_identification($order) {
    if (is_numeric($order)) {
        $order = wc_get_order($order);
    }

    if (!$order) {
        return '';
    }

    return (string) $order->get_meta(CRAFTROOTSMP_ID_META_KEY);
}

/**
 * Muestra el número de identificación en el panel de administración.
 */
function craftrootsmp_display_identification_admin_order($order) {
    $value = craftrootsmp_get_order_identification($order);

    if ($value === '') {
        return;
    }

    echo '<p><strong>' . esc_html__('Número de Identificación', 'craftrootsmp') . ':</strong> ' . esc_html($value) . '</p>';
}
add_action('woocommerce_admin_order_data_after_billing_address', 'craftrootsmp_display_identification_admin_order', 10, 1);

/**
 * Muestra el número de identificación en la página de pedido recibido.
 */
function craftrootsmp_display_identification_order_received($order) {
    $value = craftrootsmp_get_order_identification($order);

    if ($value === '') {
        return;
    }

    echo '<p><strong>' . esc_html__('Número de Identificación', 'craftrootsmp') . ':</strong> ' . esc_html($value) . '</p>';
}
add_action('woocommerce_order_details_after_customer_details', 'craftrootsmp_display_identification_order_received', 10, 1);

/**
 * Incluye el número de identificación en los correos del pedido.
 */
function craftrootsmp_identification_email_order_meta($fields, $sent_to_admin, $order) {
    $value = craftrootsmp_get_order_identification($order);

    if ($value === '') {
        return $fields;
    }

    $fields[CRAFTROOTSMP_ID_FIELD_KEY] = [
        'label' => __('Número de Identificación', 'craftrootsmp'),
        'value' => $value,
    ];

    return $fields;
}
add_filter('woocommerce_email_order_meta_fields', 'craftrootsmp_identification_email_order_meta', 10, 3);

function craftrootsmp_enqueue_checkout_assets() {
    if (!function_exists('is_checkout') || !is_checkout()) {
        return;
    }

    $base_uri = get_stylesheet_directory_uri() . '/craftrootsmp';
    $base_dir = get_stylesheet_directory() . '/craftrootsmp';

    $css_version = file_exists($base_dir . '/checkout.css')
        ? filemtime($base_dir . '/checkout.css')
        : '1.0.0';

    $js_version = file_exists($base_dir . '/checkout.js')
        ? filemtime($base_dir . '/checkout.js')
        : '1.0.0';

    wp_enqueue_style(
        'craftrootsmp-checkout',
        $base_uri . '/checkout.css',
        [],
        $css_version
    );

    wp_enqueue_script(
        'craftrootsmp-checkout',
        $base_uri . '/checkout.js',
        ['jquery'],
        $js_version,
        true
    );
}
add_action('wp_enqueue_scripts', 'craftrootsmp_enqueue_checkout_assets', 40);

/**
 * Ajusta etiquetas y disposición de campos del checkout.
 */
function craftrootsmp_checkout_fields_layout($fields) {
    if (empty($fields['billing'])) {
        return $fields;
    }

    $billing = &$fields['billing'];

    if (isset($billing['billing_first_name'])) {
        $billing['billing_first_name']['label'] = 'Nombres';
    }

    if (isset($billing['billing_last_name'])) {
        $billing['billing_last_name']['label'] = 'Apellidos';
    }

    $id_field_keys = craftrootsmp_get_identification_field_keys();

    $id_field_key = null;
    foreach ($id_field_keys as $key) {
        if (isset($billing[$key])) {
            $id_field_key = $key;
            break;
        }
    }

    if ($id_field_key) {
        $billing[$id_field_key]['label'] = 'Número de Identificación';
        $billing[$id_field_key]['class'] = ['form-row-first'];
        $billing[$id_field_key]['priority'] = 30;
    }

    if (isset($billing['billing_country'])) {
        $billing['billing_country']['class'] = $id_field_key
            ? ['form-row-last', 'address-field', 'update_totals_on_change']
            : ['form-row-first', 'address-field', 'update_totals_on_change'];
        $billing['billing_country']['priority'] = 35;
    }

    if (isset($billing['billing_address_1'])) {
        $billing['billing_address_1']['label'] = 'Calle, Número.';
        $billing['billing_address_1']['placeholder'] = '';
    }

    if (isset($billing['billing_address_2'])) {
        $billing['billing_address_2']['label'] = 'Casa, apartamento, etc. (Opcional)';
        $billing['billing_address_2']['label_class'] = [];
        $billing['billing_address_2']['placeholder'] = '';
    }

    if (isset($billing['billing_city'])) {
        $billing['billing_city']['label'] = 'Ciudad';
        $billing['billing_city']['class'] = ['form-row-first', 'address-field'];
    }

    if (isset($billing['billing_state'])) {
        $billing['billing_state']['label'] = 'Departamento';
        $billing['billing_state']['class'] = ['form-row-last', 'address-field'];
    }

    if (isset($billing['billing_postcode'])) {
        $billing['billing_postcode']['label'] = 'Código postal / Zip Code (opcional)';
        $billing['billing_postcode']['class'] = ['form-row-first', 'address-field'];
        $billing['billing_postcode']['priority'] = 95;
    }

    if (isset($billing['billing_phone'])) {
        $billing['billing_phone']['label'] = 'Teléfono';
        $billing['billing_phone']['class'] = ['form-row-last'];
        $billing['billing_phone']['priority'] = 100;
    }

    if (isset($billing['billing_email'])) {
        $billing['billing_email']['label'] = 'Correo electrónico';
        $billing['billing_email']['class'] = ['form-row-wide'];
        $billing['billing_email']['priority'] = 110;
    }

    if (!empty($fields['order']['order_comments'])) {
        $fields['order']['order_comments']['label'] = 'Notas adicionales (opcional)';
        $fields['order']['order_comments']['class'] = ['form-row-wide', 'notes'];
        $fields['order']['order_comments']['placeholder'] = 'Notas sobre tu pedido, por ejemplo alguna indicación para la entrega.';
    }

    return $fields;
}
add_filter('woocommerce_checkout_fields', 'craftrootsmp_checkout_fields_layout', 999);

/**
 * Fuerza columnas en campos de dirección (el locale de CO los pone full-width).
 */
function craftrootsmp_checkout_default_address_fields($fields) {
    if (is_admin() && !wp_doing_ajax()) {
        return $fields;
    }

    if (isset($fields['city'])) {
        $fields['city']['class'] = ['form-row-first', 'address-field'];
    }

    if (isset($fields['state'])) {
        $fields['state']['class'] = ['form-row-last', 'address-field'];
    }

    if (isset($fields['postcode'])) {
        $fields['postcode']['class'] = ['form-row-first', 'address-field'];
    }

    if (isset($fields['country'])) {
        $fields['country']['class'] = ['form-row-last', 'address-field', 'update_totals_on_change'];
    }

    return $fields;
}
add_filter('woocommerce_default_address_fields', 'craftrootsmp_checkout_default_address_fields', 999);

/**
 * Locale Colombia: dos columnas en ciudad/departamento/código postal.
 */
function craftrootsmp_checkout_country_locale($locale) {
    if (!isset($locale['CO'])) {
        return $locale;
    }

    if (isset($locale['CO']['city'])) {
        $locale['CO']['city']['class'] = ['form-row-first', 'address-field'];
    }

    if (isset($locale['CO']['state'])) {
        $locale['CO']['state']['class'] = ['form-row-last', 'address-field'];
    }

    if (isset($locale['CO']['postcode'])) {
        $locale['CO']['postcode']['class'] = ['form-row-first', 'address-field'];
    }

    if (isset($locale['CO']['country'])) {
        $locale['CO']['country']['class'] = ['form-row-last', 'address-field', 'update_totals_on_change'];
    }

    return $locale;
}
add_filter('woocommerce_get_country_locale', 'craftrootsmp_checkout_country_locale', 999);

/**
 * Muestra "Gratis" en la fila de envío del checkout.
 */
function craftrootsmp_checkout_shipping_totals_html($html) {
    if (!is_checkout()) {
        return $html;
    }

    $is_free = false;

    if (WC()->cart && WC()->cart->get_shipping_total() <= 0) {
        $is_free = true;
    }

    if (
        stripos($html, 'gratuito') !== false
        || stripos($html, 'gratis') !== false
        || stripos($html, 'free') !== false
    ) {
        $is_free = true;
    }

    if ($is_free) {
        return '<span class="cr-shipping-free">Gratis</span>';
    }

    return $html;
}
add_filter('woocommerce_cart_totals_shipping_html', 'craftrootsmp_checkout_shipping_totals_html', 999);

/**
 * Obtiene miniatura del producto usando la primera imagen de galería.
 */
function craftrootsmp_get_gallery_product_image($product, $size = 'woocommerce_thumbnail') {
    if (!$product || !is_a($product, 'WC_Product')) {
        return '';
    }

    $product_id = $product->get_id();

    if ($product->is_type('variation')) {
        $product_id = $product->get_parent_id();
        $product = wc_get_product($product_id);
    }

    if (!$product) {
        return '';
    }

    $gallery_ids = $product->get_gallery_image_ids();

    if (!empty($gallery_ids)) {
        $image = wp_get_attachment_image(
            (int) $gallery_ids[0],
            $size,
            false,
            [
                'class' => 'attachment-woocommerce_thumbnail size-woocommerce_thumbnail cr-checkout-gallery-thumb',
                'alt'   => $product->get_name(),
            ]
        );

        if ($image) {
            return $image;
        }
    }

    return $product->get_image($size, [
        'class' => 'attachment-woocommerce_thumbnail size-woocommerce_thumbnail',
        'alt'   => $product->get_name(),
    ]);
}

/**
 * Muestra producto en resumen con miniatura y badge de cantidad.
 */
function craftrootsmp_checkout_cart_item_name($name, $cart_item, $cart_item_key) {
    if (!is_checkout() || empty($cart_item['data']) || !is_a($cart_item['data'], 'WC_Product')) {
        return $name;
    }

    $product = $cart_item['data'];
    $quantity = isset($cart_item['quantity']) ? (int) $cart_item['quantity'] : 1;
    $thumbnail = craftrootsmp_get_gallery_product_image($product);

    if (!$thumbnail) {
        return $name;
    }

    $product_name = wp_strip_all_tags($name);

    return sprintf(
        '<div class="cr-checkout-product"><div class="cr-checkout-product__thumb">%1$s<span class="cr-checkout-product__qty">%2$d</span></div><div class="cr-checkout-product__name">%3$s</div></div>',
        $thumbnail,
        $quantity,
        esc_html($product_name)
    );
}
add_filter('woocommerce_cart_item_name', 'craftrootsmp_checkout_cart_item_name', 25, 3);

/**
 * Oculta la cantidad inline (ya va en el badge).
 */
function craftrootsmp_checkout_hide_item_quantity($quantity_html, $cart_item, $cart_item_key) {
    if (is_checkout()) {
        return '';
    }

    return $quantity_html;
}
add_filter('woocommerce_checkout_cart_item_quantity', 'craftrootsmp_checkout_hide_item_quantity', 25, 3);

/**
 * Texto de confirmación en pedido recibido.
 */
function craftrootsmp_thankyou_order_received_text($text, $order) {
    return 'Gracias. Tu pedido ha sido recibido. Tu orden se está procesando.';
}
add_filter('woocommerce_thankyou_order_received_text', 'craftrootsmp_thankyou_order_received_text', 99999, 2);

/**
 * Ajusta totales del resumen en la página de pedido recibido.
 */
function craftrootsmp_order_received_item_totals($total_rows, $order, $tax_display) {
    if (!function_exists('is_order_received_page') || !is_order_received_page() || !$order) {
        return $total_rows;
    }

    $count = $order->get_item_count();

    if (isset($total_rows['cart_subtotal'])) {
        $label = $count === 1 ? 'producto' : 'productos';
        $total_rows['cart_subtotal']['label'] = 'Subtotal (' . $count . ' ' . $label . '):';
    }

    if (isset($total_rows['shipping']) && (float) $order->get_shipping_total() <= 0) {
        $total_rows['shipping']['value'] = 'Gratis';
    }

    if (isset($total_rows['payment_method'])) {
        $total_rows['payment_method']['label'] = 'Método de pago:';
    }

    return $total_rows;
}
add_filter('woocommerce_get_order_item_totals', 'craftrootsmp_order_received_item_totals', 10, 3);

/**
 * Miniatura del producto en la tabla de pedido recibido.
 */
function craftrootsmp_order_received_item_name($item_name, $item, $is_visible) {
    if (
        !function_exists('is_order_received_page')
        || !is_order_received_page()
        || !is_a($item, 'WC_Order_Item_Product')
    ) {
        return $item_name;
    }

    $product = $item->get_product();
    if (!$product) {
        return $item_name;
    }

    $thumbnail = craftrootsmp_get_gallery_product_image($product);
    $plain_name = wp_strip_all_tags($item_name);
    $plain_name = preg_replace('/\s*×\s*\d+\s*$/', '', $plain_name);

    $quantity = (int) $item->get_quantity();

    if (!$thumbnail) {
        return '<span class="cr-order-item-qty" data-qty="' . esc_attr($quantity) . '"></span>' . esc_html($plain_name);
    }

    return sprintf(
        '<span class="cr-order-item-qty" data-qty="%1$d"></span><div class="cr-checkout-product"><div class="cr-checkout-product__thumb">%2$s</div><div class="cr-checkout-product__name">%3$s</div></div>',
        $quantity,
        $thumbnail,
        esc_html($plain_name)
    );
}
add_filter('woocommerce_order_item_name', 'craftrootsmp_order_received_item_name', 25, 3);
