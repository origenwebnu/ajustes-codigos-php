<?php
/**
 * Email Header
 *
 * @package WooCommerce\Templates\Emails
 * @version 9.0.0
 */

defined('ABSPATH') || exit;

$header_lime = '#DFFF70';
$header_text = '#332B2B';
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=<?php bloginfo('charset'); ?>" />
        <meta content="width=device-width, initial-scale=1.0" name="viewport">
        <title><?php echo esc_html(get_bloginfo('name', 'display')); ?></title>
    </head>
    <body <?php echo is_rtl() ? 'rightmargin' : 'leftmargin'; ?>="0" marginwidth="0" topmargin="0" marginheight="0" offset="0">
        <div id="wrapper" dir="<?php echo is_rtl() ? 'rtl' : 'ltr'; ?>">
            <table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%">
                <tr>
                    <td align="center" valign="top">
                        <table border="0" cellpadding="0" cellspacing="0" width="600" id="template_container">
                            <tr>
                                <td align="center" valign="top">
                                    <table border="0" cellpadding="0" cellspacing="0" width="100%" id="template_header" style="background-color:<?php echo esc_attr($header_lime); ?>;">
                                        <tr>
                                            <td id="header_wrapper" style="padding:36px 32px 28px;">
                                                <?php
                                                $img = apply_filters('woocommerce_email_header_image', get_option('woocommerce_email_header_image'));

                                                if ($img) {
                                                    echo '<p style="margin:0 0 12px; text-align:center;"><img src="' . esc_url($img) . '" alt="' . esc_attr(get_bloginfo('name', 'display')) . '" style="display:block; margin:0 auto; max-width:120px; width:120px; height:auto;" /></p>';
                                                }

                                                if ($email_heading) {
                                                    echo '<h1 style="color:' . esc_attr($header_text) . '; font-family:Helvetica, Arial, sans-serif; font-size:22px; font-weight:600; line-height:1.3; margin:0; text-align:center;">' . esc_html($email_heading) . '</h1>';
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td align="center" valign="top">
                                    <table border="0" cellpadding="0" cellspacing="0" width="100%" id="template_body">
                                        <tr>
                                            <td valign="top" id="body_content">
                                                <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                                    <tr>
                                                        <td valign="top" id="body_content_inner">
