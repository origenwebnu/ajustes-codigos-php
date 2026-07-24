<?php
/**
 * Email order items rows
 *
 * @package WooCommerce\Templates\Emails
 * @version 9.0.0
 */

defined('ABSPATH') || exit;

foreach ($items as $item_id => $item) {
    if (!apply_filters('woocommerce_order_item_visible', true, $item)) {
        continue;
    }

    $product = $item->get_product();
    $sku     = '';

    if ($show_sku && is_object($product) && $product->get_sku()) {
        $sku = ' (#' . $product->get_sku() . ')';
    }

    $qty_display = esc_html($item->get_quantity());
    ?>
    <tr class="order_item">
        <td class="td" style="text-align:<?php echo esc_attr($text_align); ?>; vertical-align:middle;">
            <span class="cr-email-product-cell">
                <?php if ($show_image) : ?>
                    <span style="padding-right:12px; vertical-align:middle;">
                        <?php
                        echo apply_filters(
                            'woocommerce_order_item_thumbnail',
                            '',
                            $item
                        );
                        ?>
                    </span>
                <?php endif; ?>
                <span style="vertical-align:middle;">
                    <?php
                    echo wp_kses_post(
                        apply_filters('woocommerce_order_item_name', $item->get_name(), $item, false)
                    ) . esc_html($sku);
                    ?>
                </span>
            </span>
        </td>
        <td class="td" style="text-align:center; vertical-align:middle;"><?php echo esc_html($qty_display); ?></td>
        <td class="td" style="text-align:right; vertical-align:middle;"><?php echo wp_kses_post($order->get_formatted_line_subtotal($item)); ?></td>
    </tr>
    <?php
}
