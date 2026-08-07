<?php
namespace LocaList\Frontend;

defined( 'ABSPATH' ) || exit;

class LocaList_Search {

    public function __construct() {
        add_action( 'rest_api_init', [ $this, 'register_routes' ] );
        add_filter( 'localist_listing_cpt_args', [ $this, 'enable_search_columns' ] );
    }

    public function register_routes(): void {
        register_rest_route( 'localist/v1', '/search', [
            'methods'             => 'GET',
            'callback'            => [ $this, 'handle_search' ],
            'permission_callback' => '__return_true',
            'args'                => [
                'q'         => [ 'sanitize_callback' => 'sanitize_text_field' ],
                'category'  => [ 'sanitize_callback' => 'absint' ],
                'location'  => [ 'sanitize_callback' => 'absint' ],
                'lat'       => [ 'sanitize_callback' => 'floatval' ],
                'lng'       => [ 'sanitize_callback' => 'floatval' ],
                'radius'    => [ 'sanitize_callback' => 'absint', 'default' => 10 ],
                'page'      => [ 'sanitize_callback' => 'absint', 'default' => 1 ],
                'per_page'  => [ 'sanitize_callback' => 'absint', 'default' => 12 ],
                'sort'      => [ 
                    'sanitize_callback' => 'sanitize_key',
                    'default'           => 'date',
                    'enum'              => [ 'date', 'title', 'rating', 'featured' ],
                ],
            ],
        ]);
    }

    public function handle_search( \WP_REST_Request $request ): \WP_REST_Response {
        $args = [
            'post_type'      => 'listing',
            'post_status'    => 'publish',
            'posts_per_page' => $request->get_param( 'per_page' ),
            'paged'          => $request->get_param( 'page' ),
            's'              => $request->get_param( 'q' ),
        ];

        // Taxonomy filters
        $tax_query = [];
        if ( $cat = $request->get_param( 'category' ) ) {
            $tax_query[] = [
                'taxonomy' => 'listing_category',
                'field'    => 'term_id',
                'terms'    => $cat,
            ];
        }
        if ( $loc = $request->get_param( 'location' ) ) {
            $tax_query[] = [
                'taxonomy' => 'listing_location',
                'field'    => 'term_id',
                'terms'    => $loc,
            ];
        }
        if ( ! empty( $tax_query ) ) {
            $args['tax_query'] = $tax_query;
        }

        // Radius search via meta query (Haversine approximation stored as meta)
        $lat = $request->get_param( 'lat' );
        $lng = $request->get_param( 'lng' );
        $radius = $request->get_param( 'radius' );

        if ( $lat && $lng && $radius > 0 ) {
            global $wpdb;
            $distance_formula = "( 3959 * acos( cos( radians(%f) ) * cos( radians( ll.lat ) ) * cos( radians( ll.lng ) - radians(%f) ) + sin( radians(%f) ) * sin( radians( ll.lat ) ) ) )";
            
            $args['meta_query'] = [
                'relation' => 'AND',
                'has_coords' => [
                    'key'     => '_localist_lat',
                    'compare' => 'EXISTS',
                ],
            ];

            // Custom WHERE clause for radius
            add_filter( 'posts_where', function( $where ) use ( $wpdb, $lat, $lng, $radius, $distance_formula ) {
                $where .= $wpdb->prepare(
                    " AND {$wpdb->posts}.ID IN ( SELECT post_id FROM {$wpdb->postmeta} pm_lat 
                      JOIN {$wpdb->postmeta} pm_lng ON pm_lat.post_id = pm_lng.post_id 
                      WHERE pm_lat.meta_key = '_localist_lat' AND pm_lng.meta_key = '_localist_lng' 
                      AND ({$distance_formula}) <= %d )",
                    $lat, $lng, $lat, $radius
                );
                return $where;
            });

            // Add distance to results
            add_filter( 'posts_fields', function( $fields ) use ( $wpdb, $lat, $lng, $distance_formula ) {
                $fields .= $wpdb->prepare(
                    ", (SELECT MIN({$distance_formula}) FROM {$wpdb->postmeta} pm_lat 
                       JOIN {$wpdb->postmeta} pm_lng ON pm_lat.post_id = pm_lng.post_id 
                       WHERE pm_lat.post_id = {$wpdb->posts}.ID 
                       AND pm_lat.meta_key = '_localist_lat' AND pm_lng.meta_key = '_localist_lng') AS distance",
                    $lat, $lng, $lat
                );
                return $fields;
            });
        }

        // Sorting
        switch ( $request->get_param( 'sort' ) ) {
            case 'title':
                $args['orderby'] = 'title';
                $args['order']   = 'ASC';
                break;
            case 'rating':
                $args['meta_key'] = '_localist_avg_rating';
                $args['orderby']  = 'meta_value_num';
                $args['order']    = 'DESC';
                break;
            case 'featured':
                $args['meta_key'] = '_localist_featured';
                $args['orderby']  = 'meta_value_num';
                $args['order']    = 'DESC';
                break;
            default:
                $args['orderby'] = 'date';
                $args['order']   = 'DESC';
        }

        $query = new \WP_Query( apply_filters( 'localist_search_query_args', $args ) );

        $listings = array_map( function( $post ) {
            return [
                'id'          => $post->ID,
                'title'       => get_the_title( $post ),
                'excerpt'     => wp_trim_words( $post->post_excerpt ?: $post->post_content, 20 ),
                'permalink'   => get_permalink( $post ),
                'thumbnail'   => get_the_post_thumbnail_url( $post, 'medium_large' ) ?: '',
                'category'    => wp_get_object_terms( $post->ID, 'listing_category', [ 'fields' => 'names' ] )[0] ?? '',
                'rating'      => (float) get_post_meta( $post->ID, '_localist_avg_rating', true ) ?: 0,
                'review_count'=> (int) get_post_meta( $post->ID, '_localist_review_count', true ) ?: 0,
                'is_featured' => (bool) get_post_meta( $post->ID, '_localist_featured', true ),
                'distance'    => isset( $post->distance ) ? round( (float) $post->distance, 1 ) : null,
                'lat'         => (float) get_post_meta( $post->ID, '_localist_lat', true ) ?: null,
                'lng'         => (float) get_post_meta( $post->ID, '_localist_lng', true ) ?: null,
            ];
        }, $query->posts );

        return rest_ensure_response([
            'listings'   => $listings,
            'total'      => $query->found_posts,
            'pages'      => $query->max_num_pages,
            'current_page'=> $query->query_vars['paged'],
        ]);
    }

    public function enable_search_columns( array $args ): array {
        // Ensure excerpt is searchable
        return $args;
    }
}
