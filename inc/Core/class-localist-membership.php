<?php
namespace LocaList\Core;

defined( 'ABSPATH' ) || exit;

class LocaList_Membership {

    public function __construct() {
        add_action( 'init', [ $this, 'register_custom_roles' ] );
        add_filter( 'localist_search_query_args', [ $this, 'filter_by_membership_status' ] );
    }

    public function register_custom_roles(): void {
        // Add a "Pro Member" role with higher limits
        if ( ! get_role( 'localist_pro_member' ) ) {
            add_role( 'localist_pro_member', __( 'Pro Member', 'localist' ), [
                'read'         => true,
                'edit_posts'   => true, // Can submit listings
                'upload_files' => true,
            ]);
        }
    }

    /**
     * Get listing limit based on user role
     */
    public static function get_user_listing_limit( int $user_id ): int {
        $user = get_userdata( $user_id );
        if ( ! $user ) return 0;

        if ( in_array( 'administrator', $user->roles ) || in_array( 'editor', $user->roles ) ) {
            return PHP_INT_MAX; // Unlimited
        }

        if ( in_array( 'localist_pro_member', $user->roles ) ) {
            return (int) get_option( 'localist_pro_listing_limit', 50 );
        }

        // Default standard user
        return (int) get_option( 'localist_user_listing_limit', 5 );
    }

    /**
     * Check if a listing belongs to a featured/pro member
     */
    public static function is_featured_member_listing( int $post_id ): bool {
        $author_id = get_post_field( 'post_author', $post_id );
        $user = get_userdata( $author_id );
        return $user && in_array( 'localist_pro_member', $user->roles );
    }
}
