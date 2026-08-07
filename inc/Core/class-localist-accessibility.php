<?php
namespace LocaList\Core;

defined( 'ABSPATH' ) || exit;

class LocaList_Accessibility {

    public function __construct() {
        add_action( 'wp_body_open', [ $this, 'render_skip_link' ] );
        add_filter( 'nav_menu_link_attributes', [ $this, 'add_aria_current' ], 10, 2 );
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_a11y_scripts' ] );
        add_filter( 'the_content', [ $this, 'ensure_image_alt_text' ] );
    }

    public function render_skip_link(): void {
        echo '<a href="#content" class="skip-link screen-reader-text">' . esc_html__( 'Skip to content', 'localist' ) . '</a>';
    }

    public function add_aria_current( array $atts, object $item ): array {
        if ( isset( $item->current ) && $item->current ) {
            $atts['aria-current'] = 'page';
        }
        return $atts;
    }

    public function enqueue_a11y_scripts(): void {
        // Keyboard trap prevention for modals/dropdowns
        wp_add_inline_script( 'localist-app', "
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    document.querySelectorAll('[x-show]').forEach(el => {
                        if (el.style.display !== 'none') el.dispatchEvent(new Event('close'));
                    });
                }
            });
        " );
    }

    public function ensure_image_alt_text( string $content ): string {
        // Add empty alt to decorative images missing alt attribute
        return preg_replace( '/<img((?!alt=)[^>]*)>/i', '<img alt=""$1>', $content );
    }
}
