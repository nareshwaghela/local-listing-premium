<?php
/**
 * LocaList Premium - Functions Bootstrap
 *
 * @package LocaList
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Define theme constants.
define( 'LOCALIST_VERSION', '1.0.0' );
define( 'LOCALIST_DIR', get_template_directory() );
define( 'LOCALIST_URI', get_template_directory_uri() );
define( 'LOCALIST_INC', LOCALIST_DIR . '/inc/' );

// Minimum PHP version check.
if ( version_compare( PHP_VERSION, '8.2', '<' ) ) {
    add_action( 'admin_notices', function () {
        echo '<div class="error"><p>' . esc_html__( 'LocaList Premium requires PHP 8.2+. Please upgrade your server.', 'localist' ) . '</p></div>';
    });
    return;
}

// Autoload classes.
spl_autoload_register( function ( $class ) {
    $prefix = 'LocaList\\';
    if ( strpos( $class, $prefix ) !== 0 ) {
        return;
    }

    $relative = str_replace( $prefix, '', $class );
    $file     = LOCALIST_INC . str_replace( '\\', '/', $relative ) . '.php';

    // Convert PascalCase to kebab-case filename.
    $file = preg_replace_callback( '/([a-z])([A-Z])/', function ( $m ) {
        return $m[1] . '-' . strtolower( $m[2] );
    }, $file );

    $file = strtolower( $file );

    if ( file_exists( $file ) ) {
        require_once $file;
    }
});

// Initialize theme.
add_action( 'after_setup_theme', function () {
    if ( class_exists( 'LocaList\\Core\\LocaList_Theme' ) ) {
        \LocaList\Core\LocaList_Theme::instance();
    }
}, 10 );
