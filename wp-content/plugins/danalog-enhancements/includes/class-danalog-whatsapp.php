<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

if (!class_exists('Danalog_WhatsApp')) {
    final class Danalog_WhatsApp
    {
        public static function init(): void
        {
            add_action('customize_register', [__CLASS__, 'registerCustomizer']);
            add_action('wp_enqueue_scripts', [__CLASS__, 'enqueueAssets']);
            add_action('woocommerce_single_product_summary', [__CLASS__, 'renderInlineCta'], 35);
            add_action('wp_footer', [__CLASS__, 'renderFloatingButton'], 15);
        }

        public static function registerCustomizer($wp_customize): void
        {
            if (!$wp_customize instanceof \WP_Customize_Manager) {
                return;
            }

            $wp_customize->add_section(
                'danalog_whatsapp',
                [
                    'title'       => __('WhatsApp Concierge', 'danalog'),
                    'priority'    => 160,
                    'description' => __('Configure WhatsApp contact entry points for customers.', 'danalog'),
                ]
            );

            $wp_customize->add_setting(
                'danalog_whatsapp_number',
                [
                    'type'              => 'theme_mod',
                    'sanitize_callback' => [__CLASS__, 'sanitizeNumber'],
                    'transport'         => 'refresh',
                ]
            );

            $wp_customize->add_control(
                'danalog_whatsapp_number',
                [
                    'label'       => __('WhatsApp number', 'danalog'),
                    'section'     => 'danalog_whatsapp',
                    'settings'    => 'danalog_whatsapp_number',
                    'type'        => 'text',
                    'description' => __('Enter the WhatsApp number in international format, for example 919812345678.', 'danalog'),
                ]
            );
        }

        public static function sanitizeNumber(?string $value): string
        {
            if (null === $value) {
                return '';
            }

            $value = trim($value);

            if (str_starts_with($value, '00')) {
                $value = '+' . substr($value, 2);
            }

            $value = preg_replace('/[^0-9+]/', '', $value);

            if (null === $value) {
                return '';
            }

            return $value;
        }

        public static function enqueueAssets(): void
        {
            if ('' === danalog_clean_whatsapp_number()) {
                return;
            }

            if (!wp_style_is('danalog-whatsapp', 'registered')) {
                wp_register_style(
                    'danalog-whatsapp',
                    DANALOG_ENHANCEMENTS_PLUGIN_URL . 'assets/css/whatsapp.css',
                    [],
                    DANALOG_ENHANCEMENTS_VERSION
                );
            }

            if (!wp_script_is('danalog-whatsapp', 'registered')) {
                wp_register_script(
                    'danalog-whatsapp',
                    DANALOG_ENHANCEMENTS_PLUGIN_URL . 'assets/js/whatsapp.js',
                    [],
                    DANALOG_ENHANCEMENTS_VERSION,
                    true
                );
            }

            wp_enqueue_style('danalog-whatsapp');
            wp_enqueue_script('danalog-whatsapp');
        }

        public static function renderInlineCta(): void
        {
            if ('' === danalog_clean_whatsapp_number()) {
                return;
            }

            if (!function_exists('is_product') || !is_product()) {
                return;
            }

            global $product;

            if (!$product instanceof \WC_Product) {
                return;
            }

            $brand   = danalog_get_brand_name();
            $name    = $product->get_name();
            $message = sprintf(__('Hi %1$s, I have a question about %2$s.', 'danalog'), $brand, $name);
            $message = apply_filters('danalog_whatsapp_inline_message', $message, $product);
            $urls    = danalog_get_whatsapp_urls($message);

            if (empty($urls['mobile']) || empty($urls['desktop'])) {
                return;
            }

            $label = apply_filters('danalog_whatsapp_inline_label', __('Ask on WhatsApp', 'danalog'), $product);

            echo '<div class="danalog-whatsapp-inline-wrapper">';
            printf(
                '<a class="danalog-whatsapp-inline button alt" href="%1$s" data-danalog-whatsapp-link="1" data-mobile-url="%2$s" data-desktop-url="%3$s" target="_blank" rel="noopener">%4$s</a>',
                esc_url($urls['universal']),
                esc_url($urls['mobile']),
                esc_url($urls['desktop']),
                esc_html($label)
            );
            echo '</div>';
        }

        public static function renderFloatingButton(): void
        {
            if ('' === danalog_clean_whatsapp_number()) {
                return;
            }

            $message = self::getFloatingMessage();
            $message = apply_filters('danalog_whatsapp_floating_message', $message);
            $urls    = danalog_get_whatsapp_urls($message);

            if (empty($urls['mobile']) || empty($urls['desktop'])) {
                return;
            }

            $label = apply_filters('danalog_whatsapp_floating_label', __('Chat on WhatsApp', 'danalog'));

            echo '<div class="danalog-whatsapp-floating" aria-live="polite">';
            printf(
                '<a class="danalog-whatsapp-floating__link" href="%1$s" data-danalog-whatsapp-link="1" data-mobile-url="%2$s" data-desktop-url="%3$s" target="_blank" rel="noopener" aria-label="%4$s">%5$s</a>',
                esc_url($urls['universal']),
                esc_url($urls['mobile']),
                esc_url($urls['desktop']),
                esc_attr($label),
                esc_html($label)
            );
            echo '</div>';
        }

        private static function getFloatingMessage(): string
        {
            $brand = danalog_get_brand_name();

            if (function_exists('is_wc_endpoint_url') && is_wc_endpoint_url('order-received')) {
                $order_id = absint(get_query_var('order-received'));
                if ($order_id) {
                    $order = function_exists('wc_get_order') ? wc_get_order($order_id) : null;
                    if ($order instanceof \WC_Order) {
                        $order_number = $order->get_order_number();
                        return sprintf(__('Hi %1$s, I have a question about order %2$s.', 'danalog'), $brand, $order_number);
                    }

                    return sprintf(__('Hi %1$s, I have a question about order %2$d.', 'danalog'), $brand, $order_id);
                }
            }

            if (function_exists('is_cart') && is_cart()) {
                $cart_id = self::getCartIdentifier();
                if ('' !== $cart_id) {
                    return sprintf(__('Hi %1$s, I have a question about cart %2$s.', 'danalog'), $brand, $cart_id);
                }

                return sprintf(__('Hi %s, I have a question about my cart.', 'danalog'), $brand);
            }

            if (function_exists('is_checkout') && is_checkout()) {
                $cart_id = self::getCartIdentifier();
                if ('' !== $cart_id) {
                    return sprintf(__('Hi %1$s, I have a question about checkout cart %2$s.', 'danalog'), $brand, $cart_id);
                }

                return sprintf(__('Hi %s, I have a question about my checkout.', 'danalog'), $brand);
            }

            if (function_exists('is_product') && is_product()) {
                global $product;
                if ($product instanceof \WC_Product) {
                    return sprintf(__('Hi %1$s, I have a question about %2$s.', 'danalog'), $brand, $product->get_name());
                }
            }

            return sprintf(__('Hi %s, I have a question about my order.', 'danalog'), $brand);
        }

        private static function getCartIdentifier(): string
        {
            if (!function_exists('WC')) {
                return '';
            }

            $cart = WC()->cart ?? null;

            if (!$cart) {
                return '';
            }

            if (method_exists($cart, 'get_cart_hash')) {
                $hash = (string) $cart->get_cart_hash();
                if ('' !== $hash) {
                    return substr($hash, 0, 12);
                }
            }

            if (method_exists($cart, 'get_cart_contents_count')) {
                $count = (int) $cart->get_cart_contents_count();
                if ($count > 0) {
                    return sprintf(__('with %d items', 'danalog'), $count);
                }
            }

            return '';
        }
    }
}
