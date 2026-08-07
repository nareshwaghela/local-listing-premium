<?php
namespace LocaList\Frontend;

defined( 'ABSPATH' ) || exit;

class LocaList_Dashboard {

    public function __construct() {
        add_action( 'init', [ $this, 'register_dashboard_pages' ] );
        add_filter( 'template_include', [ $this, 'load_dashboard_templates' ] );
        add_action( 'wp_ajax_localist_submit_listing', [ $this, 'handle_listing_submission' ] );
        add_action( 'wp_ajax_nopriv_localist_submit_listing', [ $this, 'require_login' ] );
    }

    public function register_dashboard_pages(): void {
        // Create dashboard page programmatically on theme activation
        if ( ! get_page_by_path( 'dashboard' ) ) {
            wp_insert_post([
                'post_title'   => __( 'Dashboard', 'localist' ),
                'post_name'    => 'dashboard',
                'post_status'  => 'publish',
                'post_type'    => 'page',
                'post_content' => '',
            ]);
        }
    }

    public function load_dashboard_templates( string $template ): string {
        if ( is_page( 'dashboard' ) ) {
            $subpage = sanitize_key( $_GET['tab'] ?? 'listings' );
            $custom  = LOCALIST_DIR . "/templates/dashboard/{$subpage}.php";
            if ( file_exists( $custom ) ) {
                return $custom;
            }
            return LOCALIST_DIR . '/templates/dashboard/listings.php';
        }
        return $template;
    }

    public function require_login(): void {
        wp_send_json_error([ 'message' => __( 'Authentication required.', 'localist' ) ], 401 );
    }

    public function handle_listing_submission(): void {
        check_ajax_referer( 'localist_submit_listing', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error([ 'message' => __( 'Please log in.', 'localist' ) ], 401 );
        }

        // Capability check: users can only submit if allowed
        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error([ 'message' => __( 'You do not have permission to submit listings.', 'localist' ) ], 403 );
        }

        $title       = sanitize_text_field( $_POST['title'] ?? '' );
        $content     = wp_kses_post( $_POST['description'] ?? '' );
        $category    = absint( $_POST['category'] ?? 0 );
        $location    = absint( $_POST['location'] ?? 0 );
        $address     = sanitize_text_field( $_POST['address'] ?? '' );
        $phone       = sanitize_text_field( $_POST['phone'] ?? '' );
        $website     = esc_url_raw( $_POST['website'] ?? '' );
        $lat         = floatval( $_POST['lat'] ?? 0 );
        $lng         = floatval( $_POST['lng'] ?? 0 );

        if ( empty( $title ) || empty( $content ) ) {
            wp_send_json_error([ 'message' => __( 'Title and description are required.', 'localist' ) ], 400 );
        }

        // Check listing limit for non-admins
        if ( ! current_user_can( 'manage_options' ) ) {
            $limit = (int) get_option( 'localist_user_listing_limit', 5 );
            $count = count_user_posts( get_current_user_id(), 'listing' );
            if ( $count >= $limit ) {
                wp_send_json_error([ 
                    'message' => sprintf( __( 'You have reached your limit of %d listings.', 'localist' ), $limit ) 
                ], 403 );
            }
        }

        // Auto-draft or pending based on admin setting
        $auto_approve = (bool) get_option( 'localist_auto_approve_listings', false );
        $status       = $auto_approve ? 'publish' : 'pending';

        $post_id = wp_insert_post([
            'post_title'   => $title,
            'post_content' => $content,
            'post_type'    => 'listing',
            'post_status'  => $status,
            'post_author'  => get_current_user_id(),
        ], true );

        if ( is_wp_error( $post_id ) ) {
            wp_send_json_error([ 'message' => $post_id->get_error_message() ], 500 );
        }

        // Save meta
        update_post_meta( $post_id, '_localist_address', $address );
        update_post_meta( $post_id, '_localist_phone', $phone );
        update_post_meta( $post_id, '_localist_website', $website );
        update_post_meta( $post_id, '_localist_lat', $lat );
        update_post_meta( $post_id, '_localist_lng', $lng );

        // Set taxonomies
        if ( $category ) wp_set_object_terms( $post_id, $category, 'listing_category' );
        if ( $location ) wp_set_object_terms( $post_id, $location, 'listing_location' );

        // Handle featured image upload
        if ( ! empty( $_FILES['featured_image']['tmp_name'] ) ) {
            require_once ABSPATH . 'wp-admin/includes/image.php';
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/media.php';

            $attachment_id = media_handle_upload( 'featured_image', $post_id );
            if ( ! is_wp_error( $attachment_id ) ) {
                set_post_thumbnail( $post_id, $attachment_id );
            }
        }

        wp_send_json_success([
            'message'  => $auto_approve 
                ? __( 'Listing published successfully!', 'localist' ) 
                : __( 'Listing submitted for review.', 'localist' ),
            'post_id'  => $post_id,
            'redirect' => get_permalink( $post_id ),
        ]);
    }
}
