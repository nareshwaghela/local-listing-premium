<?php
namespace LocaList\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Main Theme Orchestrator
 */
class LocaList_Theme {

    private static ?self $instance = null;

    public static function instance(): self {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->setup_hooks();
        $this->load_modules();
    }

    private function setup_hooks(): void {
        add_action( 'init', [ $this, 'register_features' ] );
        add_filter( 'script_loader_tag', [ $this, 'add_module_type' ], 10, 3 );
    }

    /**
     * Enable modern WP features
     */
    public function register_features(): void {
        add_theme_support( 'title-tag' );
        add_theme_support( 'post-thumbnails' );
        add_theme_support( 'responsive-embeds' );
        add_theme_support( 'html5', [ 'search-form', 'comment-form', 'gallery', 'caption', 'style', 'script' ] );
        add_theme_support( 'custom-logo' );
        add_theme_support( 'align-wide' );
        add_theme_support( 'editor-styles' );
        
        // WooCommerce compatibility (optional activation)
        add_theme_support( 'woocommerce' );
        add_theme_support( 'wc-product-gallery-zoom' );
        add_theme_support( 'wc-product-gallery-lightbox' );
        add_theme_support( 'wc-product-gallery-slider' );
    }

    /**
     * Load all functional modules
     */
    private function load_modules(): void {
        new LocaList_CPT();
        new LocaList_Assets();
        // Future modules loaded here as they are built
    }

    /**
     * Add type="module" to Alpine.js script
     */
    public function add_module_type( string $tag, string $handle, string $src ): string {
        if ( 'localist-alpine' === $handle ) {
            return str_replace( ' src', ' defer src', $tag );
        }
        return $tag;
    }
}
