<?php
/**
 * Email order details table
 *
 * @package WooCommerce\Templates\Emails
 * @version 9.0.0
 */

defined('ABSPATH') || exit;

$text_align  = is_rtl() ? 'right' : 'left';
$margin_side = is_rtl() ? 'left' : 'right';

$order_details_heading = apply_filters(
    'woocommerce_email_order_details_heading',
    '',
    $sent_to_admin,
    $order
);
?>

<?php if ($order_details_heading) : ?>
    <h2 class="order-details-title"><?php echo esc_html($order_details_heading); ?></h2>
<?php endif; ?>

<div style="margin-bottom: 24px;">
    <table class="td" cellspacing="0" cellpadding="6" style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;" border="0">
        <thead>
            <tr>
                <th class="td" scope="col" style="text-align:<?php echo esc_attr($text_align); ?>;">Producto</th>
                <th class="td" scope="col" style="text-align:center;">Cantidad</th>
                <th class="td" scope="col" style="text-align:right;">Precio</th>
            </tr>
        </thead>
        <tbody>
            <?php
            echo wc_get_email_order_items(
                $order,
                [
                    'show_sku'      => false,
                    'show_image'    => true,
                    'image_size'    => [64, 64],
                    'plain_text'    => $plain_text,
                    'sent_to_admin' => $sent_to_admin,
                ]
            );
            ?>
        </tbody>
        <tfoot>
            <?php
            $item_totals = $order->get_order_item_totals();

            if ($item_totals) {
                $i = 0;
                $item_totals_count = count($item_totals);

                foreach ($item_totals as $total) {
                    $i++;
                    $last_class = ($i === $item_totals_count) ? ' order-totals-last' : '';
                    ?>
                    <tr class="order-totals<?php echo esc_attr($last_class); ?>">
                        <th class="td" scope="row" colspan="2" style="text-align:<?php echo esc_attr($text_align); ?>;"><?php echo wp_kses_post($total['label']); ?></th>
                        <td class="td" style="text-align:right;"><?php echo wp_kses_post($total['value']); ?></td>
                    </tr>
                    <?php
                }
            }

            if ($order->get_customer_note()) {
                ?>
                <tr>
                    <th class="td" scope="row" colspan="2" style="text-align:<?php echo esc_attr($text_align); ?>;"><?php esc_html_e('Note:', 'woocommerce'); ?></th>
                    <td class="td" style="text-align:right;"><?php echo wp_kses(nl2br(wc_wptexturize_order_note($order->get_customer_note())), []); ?></td>
                </tr>
                <?php
            }
            ?>
        </tfoot>
    </table>
</div>
