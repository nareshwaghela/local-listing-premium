<?php
namespace LocaList\Core;

defined( 'ABSPATH' ) || exit;

class LocaList_SEO {

    public function __construct() {
        add_action( 'wp_head', [ $this, 'render_meta_tags' ], 1 );
        add_action( 'wp_head', [ $this, 'render_json_ld' ], 20 );
        add_filter( 'document_title_parts', [ $this, 'customize_title' ] );
        add_action( 'pre_get_posts', [ $this, 'optimize_main_query' ] );
    }

    public function customize_title( array $parts ): array {
        if ( is_singular( 'listing' ) ) {
            $parts['title'] = get_the_title() . ' - ' . get_bloginfo( 'name' );
            unset( $parts['tagline'] );
        } elseif ( is_post_type_archive( 'listing' ) ) {
            $parts['title'] = __( 'Explore Local Listings', 'localist' ) . ' - ' . get_bloginfo( 'name' );
        }
        return $parts;
    }

    public function render_meta_tags(): void {
        if ( is_singular( 'listing' ) ) {
            global $post;
            $description = wp_trim_words( wp_strip_all_tags( $post->post_excerpt ?: $post->post_content ), 30 );
            $image       = get_the_post_thumbnail_url( $post, 'large' );

            echo '<meta name="description" content="' . esc_attr( $description ) . '">' . "\n";
            echo '<meta property="og:title" content="' . esc_attr( get_the_title() ) . '">' . "\n";
            echo '<meta property="og:description" content="' . esc_attr( $description ) . '">' . "\n";
            echo '<meta property="og:type" content="business.business">' . "\n";
            echo '<meta property="og:url" content="' . esc_url( get_permalink() ) . '">' . "\n";
            if ( $image ) {
                echo '<meta property="og:image" content="' . esc_url( $image ) . '">' . "\n";
            }
            echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
        }
    }

    public function render_json_ld(): void {
        $schema = [];

        // Site-wide Organization schema
        $schema[] = [
            '@context' => 'https://schema.org',
            '@type'    => 'Organization',
            'name'     => get_bloginfo( 'name' ),
            'url'      => home_url( '/' ),
            'logo'     => get_custom_logo() ? wp_get_attachment_image_url( get_theme_mod( 'custom_logo' ), 'full' ) : '',
        ];

        // Breadcrumbs
        if ( ! is_front_page() ) {
            $breadcrumbs = $this->get_breadcrumbs();
            if ( ! empty( $breadcrumbs ) ) {
                $schema[] = [
                    '@context'        => 'https://schema.org',
                    '@type'           => 'BreadcrumbList',
                    'itemListElement' => $breadcrumbs,
                ];
            }
        }

        // Single Listing LocalBusiness schema (enhanced version)
        if ( is_singular( 'listing' ) ) {
            global $post;
            $lat      = get_post_meta( $post->ID, '_localist_lat', true );
            $lng      = get_post_meta( $post->ID, '_localist_lng', true );
            $phone    = get_post_meta( $post->ID, '_localist_phone', true );
            $website  = get_post_meta( $post->ID, '_localist_website', true );
            $address  = get_post_meta( $post->ID, '_localist_address', true );
            $avg      = (float) get_post_meta( $post->ID, '_localist_avg_rating', true );
            $count    = (int) get_post_meta( $post->ID, '_localist_review_count', true );
            $cats     = wp_get_object_terms( $post->ID, 'listing_category', [ 'fields' => 'names' ] );

            $business = [
                '@context'    => 'https://schema.org',
                '@type'       => 'LocalBusiness',
                'name'        => get_the_title(),
                'description' => wp_trim_words( wp_strip_all_tags( $post->post_excerpt ?: $post->post_content ), 50 ),
                'url'         => get_permalink(),
                'image'       => get_the_post_thumbnail_url( $post, 'large' ) ?: '',
                'telephone'   => $phone,
                'priceRange'  => '$$',
            ];

            if ( $address ) {
                $business['address'] = [
                    '@type'           => 'PostalAddress',
                    'streetAddress'   => $address,
                ];
            }

            if ( $lat && $lng ) {
                $business['geo'] = [
                    '@type'     => 'GeoCoordinates',
                    'latitude'  => floatval( $lat ),
                    'longitude' => floatval( $lng ),
                ];
            }

            if ( $website ) {
                $business['sameAs'] = [ $website ];
            }

            if ( $avg > 0 ) {
                $business['aggregateRating'] = [
                    '@type'       => 'AggregateRating',
                    'ratingValue' => $avg,
                    'reviewCount' => $count,
                ];
            }

            if ( ! empty( $cats ) ) {
                $business['category'] = $cats[0];
            }

            $schema[] = $business;
        }

        // Category Archive ItemList
        if ( is_tax( 'listing_category' ) || is_post_type_archive( 'listing' ) ) {
            global $wp_query;
            $items = [];
            foreach ( $wp_query->posts as $index => $post ) {
                $items[] = [
                    '@type'    => 'ListItem',
                    'position' => $index + 1,
                    'url'      => get_permalink( $post ),
                    'name'     => get_the_title( $post ),
                ];
            }
            if ( ! empty( $items ) ) {
                $schema[] = [
                    '@context'        => 'https://schema.org',
                    '@type'           => 'ItemList',
                    'itemListElement' => $items,
                ];
            }
        }

        if ( ! empty( $schema ) ) {
            echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
        }
    }

    private function get_breadcrumbs(): array {
        $crumbs   = [];
        $position = 1;

        $crumbs[] = [
            '@type'    => 'ListItem',
            'position' => $position++,
            'name'     => get_bloginfo( 'name' ),
            'item'     => home_url( '/' ),
        ];

        if ( is_singular( 'listing' ) ) {
            $cats = wp_get_object_terms( get_the_ID(), 'listing_category' );
            if ( ! empty( $cats ) ) {
                $crumbs[] = [
                    '@type'    => 'ListItem',
                    'position' => $position++,
                    'name'     => $cats[0]->name,
                    'item'     => get_term_link( $cats[0] ),
                ];
            }
            $crumbs[] = [
                '@type'    => 'ListItem',
                'position' => $position,
                'name'     => get_the_title(),
            ];
        } elseif ( is_post_type_archive( 'listing' ) ) {
            $crumbs[] = [
                '@type'    => 'ListItem',
                'position' => $position,
                'name'     => __( 'Listings', 'localist' ),
            ];
        }

        return $crumbs;
    }

    /**
     * Optimize main query for performance
     */
    public function optimize_main_query( \WP_Query $query ): void {
        if ( ! is_admin() && $query->is_main_query() ) {
            if ( $query->is_post_type_archive( 'listing' ) || $query->is_tax( 'listing_category' ) ) {
                $query->set( 'posts_per_page', 12 );
                $query->set( 'no_found_rows', false ); // Keep pagination accurate
                $query->set( 'update_post_meta_cache', true );
                $query->set( 'update_post_term_cache', true );
            }
        }
    }
}
