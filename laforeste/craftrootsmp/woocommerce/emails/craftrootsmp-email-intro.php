<?php
/**
 * Introducción personalizada para correos al cliente.
 *
 * @var WC_Order $order
 * @var string   $intro_type processing|completed
 */

defined('ABSPATH') || exit;

if (empty($order) || !is_a($order, 'WC_Order')) {
    return;
}

$intro_type = isset($intro_type) ? $intro_type : 'processing';
$order_number = $order->get_order_number();
$first_name = $order->get_billing_first_name();

if ($intro_type === 'completed') {
    $lead = sprintf(
        'Tu pedido n. %s ha sido completado.',
        $order_number
    );
} else {
    $lead = sprintf(
        'Tu pedido n. %s ha sido recibido y será procesado.',
        $order_number
    );
}
?>
<div class="cr-email-intro">
    <p>
        <strong><?php echo esc_html(sprintf('Hola %s,', $first_name)); ?></strong>
        <?php echo esc_html($lead); ?>
    </p>
    <?php if ($intro_type !== 'completed') : ?>
        <p>Nuestro tiempo estimado de entrega a ciudades principales es entre 5 - 8 días hábiles posteriores a tu compra.</p>
        <p>Cuando tu pedido sea despachado, recibirás un correo con el número de guía de envío para hacer seguimiento.</p>
    <?php endif; ?>
</div>
