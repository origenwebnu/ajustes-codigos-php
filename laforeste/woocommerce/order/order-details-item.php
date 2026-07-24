<?php
/**
 * Order details item row (3 columnas: Producto | Cantidad | Total)
 *
 * @package WooCommerce\Templates
 * @version 9.0.0
 */

defined('ABSPATH') || exit;

if (!apply_filters('woocommerce_order_item_visible', true, $item)) {
    return;
}

$product           = isset($product) ? $product : $item->get_product();
$is_visible        = $product && $product->is_visible();
$product_permalink = apply_filters('woocommerce_order_item_permalink', $is_visible ? $product->get_permalink($item) : '', $item, $order);
$quantity          = (int) $item->get_quantity();
?>
<tr class="<?php echo esc_attr(apply_filters('woocommerce_order_item_class', 'woocommerce-table__line-item order_item cr-order-item-row', $item, $order)); ?>" data-cr-qty="<?php echo esc_attr($quantity); ?>">
    <td class="woocommerce-table__product-name product-name" data-title="<?php esc_attr_e('Product', 'woocommerce'); ?>">
        <?php
        echo wp_kses_post(
            apply_filters(
                'woocommerce_order_item_name',
                $product_permalink
                    ? sprintf('<a href="%s">%s</a>', esc_url($product_permalink), $item->get_name())
                    : $item->get_name(),
                $item,
                $is_visible
            )
        );

        do_action('woocommerce_order_item_meta_start', $item_id, $item, $order, false);
        wc_display_item_meta($item);
        do_action('woocommerce_order_item_meta_end', $item_id, $item, $order, false);
        ?>
    </td>
    <td class="woocommerce-table__product-quantity product-quantity" data-title="<?php esc_attr_e('Quantity', 'woocommerce'); ?>" data-cr-qty="<?php echo esc_attr($quantity); ?>">
        <?php echo esc_html($quantity); ?>
    </td>
    <td class="woocommerce-table__product-total product-total" data-title="<?php esc_attr_e('Total', 'woocommerce'); ?>">
        <?php echo wp_kses_post($order->get_formatted_line_subtotal($item)); ?>
    </td>
</tr>
