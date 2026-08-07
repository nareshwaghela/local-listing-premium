<?php
namespace LocaList\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Custom Post Type & Taxonomy Registration
 * Uses native WP APIs - no third-party CPT plugins needed
 */
class LocaList_CPT {

    public function __construct() {
        add_action( 'init', [ $this, 'register_listing_cpt' ] );
        add_action( 'init', [ $this, 'register_taxonomies' ] );
        add_filter( 'enter_title_here', [ $this, 'change_title_placeholder' ] );
    }

    public function register_listing_cpt(): void {
        $labels = [
            'name'               => _x( 'Listings', 'Post type general name', 'localist' ),
            'singular_name'      => _x( 'Listing', 'Post type singular name', 'localist' ),
            'menu_name'          => _x( 'Listings', 'Admin Menu text', 'localist' ),
            'add_new'            => __( 'Add New Listing', 'localist' ),
            'add_new_item'       => __( 'Add New Listing', 'localist' ),
            'edit_item'          => __( 'Edit Listing', 'localist' ),
            'new_item'           => __( 'New Listing', 'localist' ),
            'view_item'          => __( 'View Listing', 'localist' ),
            'search_items'       => __( 'Search Listings', 'localist' ),
            'not_found'          => __( 'No listings found', 'localist' ),
            'all_items'          => __( 'All Listings', 'localist' ),
        ];

        $args = [
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'show_in_rest'       => true, // Gutenberg + REST API support
            'query_var'          => true,
            'rewrite'            => [ 'slug' => 'listing', 'with_front' => false ],
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => 5,
            'menu_icon'          => 'dashicons-location-alt',
            'supports'           => [ 'title', 'editor', 'thumbnail', 'excerpt', 'comments', 'author', 'custom-fields' ],
            'taxonomies'         => [ 'listing_category', 'listing_location', 'listing_tag' ],
        ];

        register_post_type( 'listing', apply_filters( 'localist_listing_cpt_args', $args ) );
    }

    public function register_taxonomies(): void {
        // Categories
        register_taxonomy( 'listing_category', 'listing', [
            'labels'            => [ 'name' => __( 'Categories', 'localist' ) ],
            'hierarchical'      => true,
            'show_in_rest'      => true,
            'show_admin_column' => true,
            'rewrite'           => [ 'slug' => 'category', 'with_front' => false ],
        ]);

        // Locations
        register_taxonomy( 'listing_location', 'listing', [
            'labels'            => [ 'name' => __( 'Locations', 'localist' ) ],
            'hierarchical'      => true,
            'show_in_rest'      => true,
            'show_admin_column' => true,
            'rewrite'           => [ 'slug' => 'location', 'with_front' => false ],
        ]);

        // Tags
        register_taxonomy( 'listing_tag', 'listing', [
            'labels'            => [ 'name' => __( 'Tags', 'localist' ) ],
            'hierarchical'      => false,
            'show_in_rest'      => true,
            'show_admin_column' => true,
            'rewrite'           => [ 'slug' => 'tag', 'with_front' => false ],
        ]);
    }

    public function change_title_placeholder( string $title ): string {
        $screen = get_current_screen();
        if ( $screen && 'listing' === $screen->post_type ) {
            return __( 'Enter listing title...', 'localist' );
        }
        return $title;
    }
}
