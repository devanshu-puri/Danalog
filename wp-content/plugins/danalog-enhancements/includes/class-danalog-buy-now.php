<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

if (!class_exists('Danalog_Buy_Now')) {
    final class Danalog_Buy_Now
    {
        private static bool $is_request = false;
        private static bool $add_to_cart_succeeded = false;

        public static function init(): void
        {
            add_action('init', [__CLASS__, 'bootstrapFlags']);
            add_action('wp_enqueue_scripts', [__CLASS__, 'enqueueAssets']);
            add_action('woocommerce_before_add_to_cart_button', [__CLASS__, 'renderHiddenField']);
            add_action('woocommerce_after_add_to_cart_button', [__CLASS__, 'renderProductButton'], 15);
            add_action('woocommerce_after_shop_loop_item', [__CLASS__, 'renderLoopButton'], 25);
            add_filter('woocommerce_add_to_cart_redirect', [__CLASS__, 'maybeRedirectToCheckout'], 10, 2);
            add_action('woocommerce_add_to_cart', [__CLASS__, 'handleSuccessfulAddToCart'], 10, 6);
            add_filter('woocommerce_add_to_cart_validation', [__CLASS__, 'enforcePurchasableRules'], 10, 6);
            add_action('template_redirect', [__CLASS__, 'maybeHandleDirectRequest']);
        }

        public static function enqueueAssets(): void
        {
            if (!wp_script_is('danalog-buy-now', 'registered')) {
                wp_register_script(
                    'danalog-buy-now',
                    DANALOG_ENHANCEMENTS_PLUGIN_URL . 'assets/js/buy-now.js',
                    [],
                    DANALOG_ENHANCEMENTS_VERSION,
                    true
                );
            }

            if (is_product() || is_shop() || is_product_category() || is_product_tag()) {
                wp_enqueue_script('danalog-buy-now');
            }
        }

        public static function bootstrapFlags(): void
        {
            if (isset($_REQUEST['danalog_buy_now'])) {
                $flag = sanitize_text_field(wp_unslash((string) $_REQUEST['danalog_buy_now']));
                self::$is_request = ('1' === $flag);
            }

            if (!self::$is_request) {
                $query_flag = filter_input(INPUT_GET, 'danalog_buy_now_add', FILTER_SANITIZE_NUMBER_INT);
                if (!empty($query_flag)) {
                    self::$is_request = true;
                }
            }
        }

        public static function renderHiddenField(): void
        {
            global $product;

            if (!$product instanceof \WC_Product) {
                return;
            }

            if (!$product->is_purchasable()) {
                return;
            }

            echo '<input type="hidden" name="danalog_buy_now" value="0" class="danalog-buy-now-flag" />';
        }

        public static function renderProductButton(): void
        {
            global $product;

            if (!$product instanceof \WC_Product) {
                return;
            }

            if (!$product->is_purchasable()) {
                return;
            }

            if (!$product->is_in_stock() && !$product->backorders_allowed()) {
                return;
            }

            $classes = apply_filters('danalog_buy_now_button_classes', 'button alt danalog-buy-now-button');
            $label   = apply_filters('danalog_buy_now_button_label', __('Buy Now', 'danalog'));

            echo sprintf(
                '<button type="button" class="%1$s" data-product-id="%2$d" data-danalog-buy-now="1">%3$s</button>',
                esc_attr($classes),
                (int) $product->get_id(),
                esc_html($label)
            );
        }

        public static function renderLoopButton(): void
        {
            global $product;

            if (!$product instanceof \WC_Product) {
                return;
            }

            if ('simple' !== $product->get_type()) {
                return;
            }

            if (!$product->is_purchasable()) {
                return;
            }

            if (!$product->is_in_stock() && !$product->backorders_allowed()) {
                return;
            }

            $product_id = $product->get_id();

            echo '<form class="cart danalog-loop-buy-now" method="post">';
            echo sprintf('<input type="hidden" name="danalog_buy_now" value="1" />');
            echo sprintf('<input type="hidden" name="add-to-cart" value="%d" />', (int) $product_id);
            echo '<input type="hidden" name="quantity" value="1" />';

            $classes = apply_filters('danalog_buy_now_loop_button_classes', 'button alt danalog-loop-buy-now__button');
            $label   = apply_filters('danalog_buy_now_button_label', __('Buy Now', 'danalog'));

            echo sprintf(
                '<button type="submit" class="%1$s" data-product-id="%2$d" data-danalog-buy-now="1">%3$s</button>',
                esc_attr($classes),
                (int) $product_id,
                esc_html($label)
            );
            echo '</form>';
        }

        public static function maybeRedirectToCheckout(string $url, \WC_Product $product): string
        {
            if (!self::$is_request) {
                return $url;
            }

            if (!self::$add_to_cart_succeeded) {
                return $url;
            }

            return wc_get_checkout_url();
        }

        public static function handleSuccessfulAddToCart(string $cart_item_key, int $product_id, int $quantity, int $variation_id, array $variation, array $cart_item_data): void
        {
            if (!self::$is_request) {
                return;
            }

            self::$add_to_cart_succeeded = true;

            if (1 !== $quantity && WC()->cart) {
                WC()->cart->set_quantity($cart_item_key, 1, false);
            }
        }

        public static function enforcePurchasableRules(bool $passed, int $product_id, int $quantity, $variation_id = 0, array $variations = [], array $cart_item_data = []): bool
        {
            if (!$passed || !self::$is_request) {
                return $passed;
            }

            $product = wc_get_product($product_id);

            if (!$product instanceof \WC_Product) {
                wc_add_notice(__('Unable to process Buy Now for this product.', 'danalog'), 'error');
                return false;
            }

            $target_product = $product;

            if (!empty($variation_id)) {
                $variation_product = wc_get_product((int) $variation_id);
                if ($variation_product instanceof \WC_Product) {
                    $target_product = $variation_product;
                }
            } elseif ($product instanceof \WC_Product_Variable) {
                wc_add_notice(__('Please choose product options before using Buy Now.', 'danalog'), 'error');
                return false;
            }

            if (!$target_product->is_purchasable()) {
                wc_add_notice(__('This product is currently unavailable for Buy Now.', 'danalog'), 'error');
                return false;
            }

            if (!$target_product->is_in_stock() && !$target_product->backorders_allowed()) {
                wc_add_notice(__('This product is out of stock.', 'danalog'), 'error');
                return false;
            }

            return $passed;
        }

        public static function maybeHandleDirectRequest(): void
        {
            $product_id = isset($_GET['danalog_buy_now_add']) ? absint($_GET['danalog_buy_now_add']) : 0;

            if (!$product_id) {
                return;
            }

            self::$is_request = true;

            if (!function_exists('wc_get_product')) {
                return;
            }

            $product = wc_get_product($product_id);
            if (!$product || !$product->is_purchasable()) {
                wc_add_notice(__('This product cannot be purchased right now.', 'danalog'), 'error');
                wp_safe_redirect(get_permalink($product_id));
                exit;
            }

            if (!$product->is_in_stock() && !$product->backorders_allowed()) {
                wc_add_notice(__('This product is out of stock.', 'danalog'), 'error');
                wp_safe_redirect(get_permalink($product_id));
                exit;
            }

            if (!WC()->cart) {
                if (function_exists('wc_load_cart')) {
                    wc_load_cart();
                }
            }

            if (!WC()->cart) {
                wc_add_notice(__('We could not access your cart. Please try again.', 'danalog'), 'error');
                wp_safe_redirect(get_permalink($product_id));
                exit;
            }

            $key = WC()->cart->add_to_cart($product_id, 1);

            if (!$key) {
                wc_add_notice(__('Unable to add the product to your cart. Please try again.', 'danalog'), 'error');
                wp_safe_redirect(get_permalink($product_id));
                exit;
            }

            self::$add_to_cart_succeeded = true;

            wp_safe_redirect(wc_get_checkout_url());
            exit;
        }
    }
}
