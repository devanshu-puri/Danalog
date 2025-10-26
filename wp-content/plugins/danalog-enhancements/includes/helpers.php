<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

if (!function_exists('danalog_get_whatsapp_number')) {
    function danalog_get_whatsapp_number(): string
    {
        $number = get_theme_mod('danalog_whatsapp_number', '');

        /**
         * Filters the raw WhatsApp number stored in the Customizer before sanitisation for display.
         *
         * @param string $number Raw number value.
         */
        return (string) apply_filters('danalog_whatsapp_number_raw', $number);
    }
}

if (!function_exists('danalog_clean_whatsapp_number')) {
    function danalog_clean_whatsapp_number(): string
    {
        $number = danalog_get_whatsapp_number();
        $number = trim($number);

        if (str_starts_with($number, '00')) {
            $number = '+' . substr($number, 2);
        }

        $number = preg_replace('/[^0-9+]/', '', $number ?? '');

        if (null === $number) {
            return '';
        }

        $number = ltrim($number, '+');

        return $number;
    }
}

if (!function_exists('danalog_get_whatsapp_urls')) {
    function danalog_get_whatsapp_urls(string $message): array
    {
        $number = danalog_clean_whatsapp_number();

        if ('' === $number) {
            return [
                'mobile'   => '',
                'desktop'  => '',
                'universal' => '',
            ];
        }

        $message = trim($message);
        $encoded = rawurlencode($message);

        return [
            'mobile'   => sprintf('https://wa.me/%s?text=%s', $number, $encoded),
            'desktop'  => sprintf('https://web.whatsapp.com/send?phone=%s&text=%s', $number, $encoded),
            'universal' => sprintf('https://wa.me/%s?text=%s', $number, $encoded),
        ];
    }
}

if (!function_exists('danalog_get_brand_name')) {
    function danalog_get_brand_name(): string
    {
        return get_bloginfo('name') ?: 'Danalog';
    }
}
