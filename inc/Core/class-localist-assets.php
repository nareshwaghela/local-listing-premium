<?php
namespace LocaList\Core;

defined( 'ABSPATH' ) || exit;

class LocaList_Assets {

    public function __construct() {
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_frontend_assets' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );
    }

    public function enqueue_frontend_assets(): void {
        $version = defined( 'WP_DEBUG' ) && WP_DEBUG ? time() : LOCALIST_VERSION;

        // Compiled Tailwind CSS
        wp_enqueue_style(
            'localist-main',
            LOCALIST_URI . '/assets/dist/css/main.css',
            [],
            $version
        );

        // Alpine.js (CDN fallback-free, self-hosted for ThemeForest compliance)
        wp_enqueue_script(
            'localist-alpine',
            LOCALIST_URI . '/assets/dist/js/alpine.min.js',
            [],
            '3.14.1',
            true
        );

        // Theme JS
        wp_enqueue_script(
            'localist-app',
            LOCALIST_URI . '/assets/dist/js/app.js',
            [ 'localist-alpine' ],
            $version,
            true
        );

        // Localize data for AJAX / REST
        wp_localize_script( 'localist-app', 'localistData', [
            'restUrl'  => esc_url_raw( rest_url( 'localist/v1/' ) ),
            'nonce'    => wp_create_nonce( 'wp_rest' ),
            'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
            'themeUrl' => LOCALIST_URI,
            'i18n'     => [
                'loading'     => __( 'Loading...', 'localist' ),
                'noResults'   => __( 'No listings found.', 'localist' ),
                'error'       => __( 'Something went wrong.', 'localist' ),
            ],
        ]);

        // Leaflet CSS/JS (only on pages with maps)
        if ( is_singular( 'listing' ) || is_post_type_archive( 'listing' ) || is_front_page() ) {
            wp_enqueue_style( 'leaflet-css', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css', [], '1.9.4' );
            wp_enqueue_script( 'leaflet-js', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js', [], '1.9.4', true );
        }
    }

    public function enqueue_admin_assets( string $hook ): void {
        // Only load on listing edit screens
        $screen = get_current_screen();
        if ( ! $screen || 'listing' !== $screen->post_type ) {
            return;
        }

        wp_enqueue_style(
            'localist-admin',
            LOCALIST_URI . '/assets/dist/css/admin.css',
            [],
            LOCALIST_VERSION
        );
    }
}
