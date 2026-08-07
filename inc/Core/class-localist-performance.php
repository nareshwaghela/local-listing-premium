<?php
namespace LocaList\Core;

defined( 'ABSPATH' ) || exit;

class LocaList_Performance {

    public function __construct() {
        add_filter( 'wp_lazy_loading_enabled', '__return_true' );
        add_filter( 'lazy_load_images', '__return_true' );
        add_action( 'wp_head', [ $this, 'preload_critical_assets' ], 1 );
        add_filter( 'style_loader_tag', [ $this, 'add_font_display_swap' ], 10, 2 );
        add_filter( 'script_loader_tag', [ $this, 'defer_non_critical_scripts' ], 10, 2 );
        add_action( 'init', [ $this, 'enable_object_caching_hints' ] );
        add_filter( 'big_image_size_threshold', [ $this, 'limit_upload_dimensions' ] );
    }

    public function preload_critical_assets(): void {
        // Preload LCP image on singular listing pages
        if ( is_singular( 'listing' ) && has_post_thumbnail() ) {
            $lcp_image = get_the_post_thumbnail_url( get_the_ID(), 'large' );
            if ( $lcp_image ) {
                echo '<link rel="preload" as="image" href="' . esc_url( $lcp_image ) . '" fetchpriority="high">' . "\n";
            }
        }

        // Preload fonts
        echo '<link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>' . "\n";
        echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
    }

    public function add_font_display_swap( string $html, string $handle ): string {
        if ( strpos( $html, 'fonts.googleapis.com' ) !== false ) {
            return str_replace( "rel='stylesheet'", "rel='stylesheet' media='print' onload=\"this.media='all'\"", $html );
        }
        return $html;
    }

    public function defer_non_critical_scripts( string $tag, string $handle ): string {
        // Defer all scripts except jQuery and critical Alpine
        $critical_handles = [ 'jquery', 'localist-alpine', 'localist-app' ];
        if ( ! in_array( $handle, $critical_handles, true ) && strpos( $tag, ' defer' ) === false ) {
            return str_replace( ' src', ' defer src', $tag );
        }
        return $tag;
    }

    public function enable_object_caching_hints(): void {
        // Warm cache for frequently accessed options
        wp_cache_add_non_persistent_groups( 'localist' );
    }

    public function limit_upload_dimensions( int $threshold ): int {
        return 1920; // Max width/height for uploaded images
    }

    /**
     * Generate critical CSS inline (simplified version)
     */
    public static function get_critical_css(): string {
        return 'body{margin:0;font-family:Inter,system-ui,sans-serif}.btn-primary{display:inline-flex;align-items:center;padding:.75rem 1.5rem;background:#2563eb;color:#fff;border-radius:.5rem}.card{background:#fff;border-radius:.75rem;box-shadow:0 1px 3px rgba(0,0,0,.1)}';
    }
}
