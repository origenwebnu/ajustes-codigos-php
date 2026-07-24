<?php
/**
 * Customer processing order email
 *
 * @package WooCommerce\Templates\Emails
 * @version 9.0.0
 */

defined('ABSPATH') || exit;

$email_improvements_enabled = class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')
    && \Automattic\WooCommerce\Utilities\FeaturesUtil::feature_is_enabled('email_improvements');

/*
 * @hooked WC_Emails::email_header() Output the email header
 */
do_action('woocommerce_email_header', $email_heading, $email);

wc_get_template(
    'emails/craftrootsmp-email-intro.php',
    [
        'order'      => $order,
        'intro_type' => 'processing',
    ],
    'craftrootsmp/woocommerce/',
    get_stylesheet_directory() . '/craftrootsmp/woocommerce/'
);

/*
 * @hooked WC_Emails::order_details() Shows the order details table.
 * @hooked WC_Emails::order_meta() Shows order meta data.
 * @hooked WC_Emails::customer_details() Shows customer details
 */
do_action('woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email);
do_action('woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email);
do_action('woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email);

if ($additional_content) {
    echo $email_improvements_enabled ? '<table border="0" cellpadding="0" cellspacing="0" width="100%"><tr><td class="email-additional-content">' : '';
    echo wp_kses_post(wpautop(wptexturize($additional_content)));
    echo $email_improvements_enabled ? '</td></tr></table>' : '';
}

/*
 * @hooked WC_Emails::email_footer() Output the email footer
 */
do_action('woocommerce_email_footer', $email);
