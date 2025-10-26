<?php

if (! defined('ABSPATH')) {
    exit;
}

if (! function_exists('danalog_setup')) {
    function danalog_setup(): void
    {
        add_theme_support('woocommerce');
        add_theme_support('wp-block-styles');
        add_theme_support('editor-styles');
        add_theme_support('responsive-embeds');
        add_theme_support('align-wide');
        add_theme_support('post-thumbnails');

        add_editor_style(['assets/css/base.css', 'assets/fonts/fonts.css']);

        register_nav_menus([
            'primary'   => __('Primary Navigation', 'danalog'),
            'secondary' => __('Secondary Navigation', 'danalog'),
            'footer'    => __('Footer Navigation', 'danalog'),
        ]);

        add_image_size('danalog-hero', 1920, 1080, true);
        add_image_size('danalog-square', 960, 960, true);
    }
}
add_action('after_setup_theme', 'danalog_setup');

function danalog_theme_version(): string
{
    $theme = wp_get_theme('danalog');

    if ($theme instanceof WP_Theme && $theme->exists()) {
        return (string) $theme->get('Version');
    }

    return '1.0.0';
}

if (! function_exists('danalog_enqueue_assets')) {
    function danalog_enqueue_assets(): void
    {
        $version = danalog_theme_version();

        wp_enqueue_style(
            'danalog-fonts',
            get_theme_file_uri('/assets/fonts/fonts.css'),
            [],
            $version
        );

        wp_enqueue_style(
            'danalog-base',
            get_theme_file_uri('/assets/css/base.css'),
            ['danalog-fonts'],
            $version
        );
    }
}
add_action('wp_enqueue_scripts', 'danalog_enqueue_assets');

if (! function_exists('danalog_enqueue_block_editor_assets')) {
    function danalog_enqueue_block_editor_assets(): void
    {
        $version = danalog_theme_version();

        wp_enqueue_style(
            'danalog-editor-fonts',
            get_theme_file_uri('/assets/fonts/fonts.css'),
            [],
            $version
        );

        wp_enqueue_style(
            'danalog-editor-base',
            get_theme_file_uri('/assets/css/base.css'),
            ['danalog-editor-fonts'],
            $version
        );
    }
}
add_action('enqueue_block_editor_assets', 'danalog_enqueue_block_editor_assets');

if (! function_exists('danalog_preload_fonts')) {
    function danalog_preload_fonts(array $hints, string $relation_type): array
    {
        if ('preload' !== $relation_type) {
            return $hints;
        }

        $fonts = [
            get_theme_file_uri('/assets/fonts/Manrope-Variable.woff2'),
            get_theme_file_uri('/assets/fonts/SeigneurSerifDisplay-Regular.woff2'),
        ];

        foreach ($fonts as $font_url) {
            $hints[] = [
                'href'        => $font_url,
                'as'          => 'font',
                'type'        => 'font/woff2',
                'crossorigin' => 'anonymous',
            ];
        }

        return $hints;
    }
}
add_filter('wp_resource_hints', 'danalog_preload_fonts', 10, 2);
