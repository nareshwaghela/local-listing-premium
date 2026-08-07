<?php
namespace LocaList\Frontend;

defined( 'ABSPATH' ) || exit;

class LocaList_Favorites {

    public function __construct() {
        add_action( 'wp_ajax_localist_toggle_favorite', [ $this, 'toggle_favorite' ] );
        add_action( 'wp_ajax_nopriv_localist_toggle_favorite', [ $this, 'require_login_for_favorites' ] );
        add_action( 'rest_api_init', [ $this, 'register_rest_routes' ] );
        add_action( 'wp_login', [ $this, 'sync_guest_favorites_on_login' ], 10, 2 );
    }

    public function require_login_for_favorites(): void {
        wp_send_json_error([ 'message' => __( 'Please log in to save favorites.', 'localist' ), 'login_required' => true ], 401 );
    }

    public function toggle_favorite(): void {
        check_ajax_referer( 'localist_favorites_nonce', 'nonce' );

        $post_id = absint( $_POST['post_id'] ?? 0 );
        if ( ! $post_id || 'listing' !== get_post_type( $post_id ) ) {
            wp_send_json_error([ 'message' => __( 'Invalid listing.', 'localist' ) ], 400 );
        }

        $user_id = get_current_user_id();
        $favorites = get_user_meta( $user_id, '_localist_favorites', true );
        $favorites = is_array( $favorites ) ? $favorites : [];

        $is_favorite = in_array( $post_id, $favorites, true );

        if ( $is_favorite ) {
            $favorites = array_diff( $favorites, [ $post_id ] );
        } else {
            $favorites[] = $post_id;
        }

        update_user_meta( $user_id, '_localist_favorites', array_values( $favorites ) );

        wp_send_json_success([
            'is_favorite' => ! $is_favorite,
            'count'       => count( $favorites ),
            'message'     => ! $is_favorite ? __( 'Added to favorites!', 'localist' ) : __( 'Removed from favorites.', 'localist' ),
        ]);
    }

    public function register_rest_routes(): void {
        register_rest_route( 'localist/v1', '/favorites', [
            'methods'             => 'GET',
            'callback'            => [ $this, 'get_favorites_rest' ],
            'permission_callback' => function () { return is_user_logged_in(); },
        ]);
    }

    public function get_favorites_rest( \WP_REST_Request $request ): \WP_REST_Response {
        $user_id = get_current_user_id();
        $favorites = get_user_meta( $user_id, '_localist_favorites', true );
        $favorites = is_array( $favorites ) ? $favorites : [];

        $listings = array_map( function( $id ) {
            $post = get_post( $id );
            if ( ! $post || 'publish' !== $post->post_status ) return null;
            return [
                'id'        => $id,
                'title'     => get_the_title( $post ),
                'permalink' => get_permalink( $post ),
                'thumbnail' => get_the_post_thumbnail_url( $post, 'medium_large' ) ?: '',
            ];
        }, $favorites );

        return rest_ensure_response( array_filter( $listings ) );
    }

    /**
     * Sync localStorage favorites to DB on login
     */
    public function sync_guest_favorites_on_login( string $user_login, \WP_User $user ): void {
        if ( empty( $_COOKIE['localist_guest_favorites'] ) ) return;

        $guest_favs = json_decode( stripslashes( $_COOKIE['localist_guest_favorites'] ), true );
        if ( ! is_array( $guest_favs ) ) return;

        $existing = get_user_meta( $user->ID, '_localist_favorites', true );
        $existing = is_array( $existing ) ? $existing : [];

        $merged = array_unique( array_merge( $existing, array_map( 'absint', $guest_favs ) ) );
        update_user_meta( $user->ID, '_localist_favorites', array_values( $merged ) );

        // Clear cookie after sync
        setcookie( 'localist_guest_favorites', '', time() - 3600, COOKIEPATH, COOKIE_DOMAIN );
    }

    /**
     * Helper: Check if current user favorited a post
     */
    public static function is_favorite( int $post_id ): bool {
        if ( ! is_user_logged_in() ) return false;
        $favorites = get_user_meta( get_current_user_id(), '_localist_favorites', true );
        return is_array( $favorites ) && in_array( $post_id, $favorites, true );
    }
}
