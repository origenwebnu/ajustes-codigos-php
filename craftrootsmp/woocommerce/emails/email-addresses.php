<?php
/**
 * Email addresses
 *
 * @package WooCommerce\Templates\Emails
 * @version 9.0.0
 */

defined('ABSPATH') || exit;

$billing  = $order->get_formatted_billing_address();
$shipping = $order->get_formatted_shipping_address();
?>

<table class="cr-email-addresses" cellspacing="0" cellpadding="0" style="width:100%;" border="0">
    <tr>
        <td class="td" valign="top" width="50%" style="padding-right:16px;">
            <h3>Dirección de facturación:</h3>
            <div class="address">
                <?php
                if ($billing) {
                    echo wp_kses_post($billing);
                } else {
                    esc_html_e('N/A', 'woocommerce');
                }
                ?>
                <?php if ($order->get_billing_phone()) : ?>
                    <p><?php echo esc_html($order->get_billing_phone()); ?></p>
                <?php endif; ?>
            </div>
        </td>
        <?php if ($order->needs_shipping_address() && $shipping) : ?>
            <td class="td" valign="top" width="50%" style="padding-left:16px;">
                <h3>Dirección de entrega:</h3>
                <div class="address">
                    <?php echo wp_kses_post($shipping); ?>
                    <?php if ($order->get_shipping_phone()) : ?>
                        <p><?php echo esc_html($order->get_shipping_phone()); ?></p>
                    <?php endif; ?>
                </div>
            </td>
        <?php endif; ?>
    </tr>
</table>
