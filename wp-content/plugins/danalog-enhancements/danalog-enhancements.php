<?php
/**
 * Plugin Name:       Danalog Enhancements
 * Plugin URI:        https://danalog.example
 * Description:       Adds Buy Now checkout shortcuts and WhatsApp concierge entry points for the Danalog store.
 * Version:           1.0.0
 * Author:            Danalog
 * Requires Plugins:  woocommerce
 * License:           GPL-2.0-or-later
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

if (!class_exists('Danalog_Enhancements')) {
    final class Danalog_Enhancements
    {
        private static ?self $instance = null;

        public static function instance(): self
        {
            if (null === self::$instance) {
                self::$instance = new self();
            }

            return self::$instance;
        }

        private function __construct()
        {
            add_action('plugins_loaded', [$this, 'boot']);
        }

        public function boot(): void
        {
            if (!class_exists('WooCommerce')) {
                add_action('admin_notices', [$this, 'woocommerceMissingNotice']);
                return;
            }

            $this->defineConstants();
            $this->loadIncludes();
        }

        private function defineConstants(): void
        {
            if (!defined('DANALOG_ENHANCEMENTS_VERSION')) {
                define('DANALOG_ENHANCEMENTS_VERSION', '1.0.0');
            }

            if (!defined('DANALOG_ENHANCEMENTS_PLUGIN_FILE')) {
                define('DANALOG_ENHANCEMENTS_PLUGIN_FILE', __FILE__);
            }

            if (!defined('DANALOG_ENHANCEMENTS_PLUGIN_DIR')) {
                define('DANALOG_ENHANCEMENTS_PLUGIN_DIR', plugin_dir_path(__FILE__));
            }

            if (!defined('DANALOG_ENHANCEMENTS_PLUGIN_URL')) {
                define('DANALOG_ENHANCEMENTS_PLUGIN_URL', plugin_dir_url(__FILE__));
            }
        }

        private function loadIncludes(): void
        {
            require_once DANALOG_ENHANCEMENTS_PLUGIN_DIR . 'includes/helpers.php';
            require_once DANALOG_ENHANCEMENTS_PLUGIN_DIR . 'includes/class-danalog-buy-now.php';
            require_once DANALOG_ENHANCEMENTS_PLUGIN_DIR . 'includes/class-danalog-whatsapp.php';

            Danalog_Buy_Now::init();
            Danalog_WhatsApp::init();
        }

        public function woocommerceMissingNotice(): void
        {
            echo '<div class="notice notice-error"><p>' . esc_html__('Danalog Enhancements requires WooCommerce to be installed and active.', 'danalog') . '</p></div>';
        }
    }

    Danalog_Enhancements::instance();
}
