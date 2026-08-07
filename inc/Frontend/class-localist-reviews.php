<?php
namespace LocaList\Frontend;

defined( 'ABSPATH' ) || exit;

class LocaList_Reviews {

    public function __construct() {
        add_action( 'wp_ajax_localist_submit_review', [ $this, 'handle_review_submission' ] );
        add_action( 'comment_post', [ $this, 'save_review_rating' ], 10, 2 );
        add_filter( 'preprocess_comment', [ $this, 'validate_review_data' ] );
        add_action( 'transition_comment_status', [ $this, 'recalculate_ratings_on_status_change' ], 10, 3 );
    }

    public function validate_review_data( array $commentdata ): array {
        if ( 'listing' !== get_post_type( $commentdata['comment_post_ID'] ) ) {
            return $commentdata;
        }

        // Require rating for listing reviews
        if ( empty( $_POST['rating'] ) || ! is_numeric( $_POST['rating'] ) ) {
            wp_die( esc_html__( 'Please select a star rating.', 'localist' ) );
        }

        $rating = absint( $_POST['rating'] );
        if ( $rating < 1 || $rating > 5 ) {
            wp_die( esc_html__( 'Rating must be between 1 and 5 stars.', 'localist' ) );
        }

        // Prevent duplicate reviews from same user on same listing
        if ( is_user_logged_in() ) {
            $existing = get_comments([
                'post_id' => $commentdata['comment_post_ID'],
                'user_id' => get_current_user_id(),
                'status'  => 'approve',
                'count'   => true,
            ]);
            if ( $existing > 0 ) {
                wp_die( esc_html__( 'You have already reviewed this listing.', 'localist' ) );
            }
        }

        $commentdata['comment_meta']['_localist_rating'] = $rating;
        return $commentdata;
    }

    public function save_review_rating( int $comment_id, int $comment_approved ): void {
        if ( 1 !== $comment_approved ) return;

        $rating = $_POST['rating'] ?? null;
        if ( $rating ) {
            update_comment_meta( $comment_id, '_localist_rating', absint( $rating ) );
            $this->recalculate_listing_rating( get_comment( $comment_id )->comment_post_ID );
        }
    }

    public function handle_review_submission(): void {
        check_ajax_referer( 'localist_submit_review', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error([ 'message' => __( 'Authentication required.', 'localist' ) ], 401 );
        }

        $post_id = absint( $_POST['post_id'] ?? 0 );
        $rating  = absint( $_POST['rating'] ?? 0 );
        $content = sanitize_textarea_field( $_POST['comment'] ?? '' );

        if ( ! $post_id || 'listing' !== get_post_type( $post_id ) ) {
            wp_send_json_error([ 'message' => __( 'Invalid listing.', 'localist' ) ], 400 );
        }

        if ( $rating < 1 || $rating > 5 ) {
            wp_send_json_error([ 'message' => __( 'Please select a valid rating.', 'localist' ) ], 400 );
        }

        if ( strlen( $content ) < 10 ) {
            wp_send_json_error([ 'message' => __( 'Review must be at least 10 characters long.', 'localist' ) ], 400 );
        }

        // Check duplicate
        $existing = get_comments([
            'post_id' => $post_id,
            'user_id' => get_current_user_id(),
            'status'  => 'approve',
            'count'   => true,
        ]);

        if ( $existing > 0 ) {
            wp_send_json_error([ 'message' => __( 'You have already reviewed this listing.', 'localist' ) ], 409 );
        }

        $comment_id = wp_insert_comment([
            'comment_post_ID' => $post_id,
            'comment_author'  => wp_get_current_user()->display_name,
            'comment_author_email' => wp_get_current_user()->user_email,
            'comment_content' => $content,
            'user_id'         => get_current_user_id(),
            'comment_type'    => 'review',
            'comment_approved'=> 1, // Auto-approve for logged-in users (can be controlled via admin setting)
        ]);

        if ( ! $comment_id ) {
            wp_send_json_error([ 'message' => __( 'Failed to submit review.', 'localist' ) ], 500 );
        }

        update_comment_meta( $comment_id, '_localist_rating', $rating );
        $this->recalculate_listing_rating( $post_id );

        wp_send_json_success([
            'message' => __( 'Thank you! Your review has been published.', 'localist' ),
            'rating'  => $rating,
        ]);
    }

    public function recalculate_listing_rating( int $post_id ): void {
        global $wpdb;

        $results = $wpdb->get_results( $wpdb->prepare(
            "SELECT AVG(cm.meta_value) as avg_rating, COUNT(*) as total 
             FROM {$wpdb->comments} c 
             JOIN {$wpdb->commentmeta} cm ON c.comment_ID = cm.comment_id 
             WHERE c.comment_post_ID = %d 
             AND c.comment_approved = '1' 
             AND cm.meta_key = '_localist_rating'",
            $post_id
        ) );

        $avg   = $results[0]->avg_rating ? round( (float) $results[0]->avg_rating, 1 ) : 0;
        $total = (int) $results[0]->total;

        update_post_meta( $post_id, '_localist_avg_rating', $avg );
        update_post_meta( $post_id, '_localist_review_count', $total );
    }

    public function recalculate_ratings_on_status_change( string $new_status, string $old_status, \WP_Comment $comment ): void {
        if ( 'listing' !== get_post_type( $comment->comment_post_ID ) ) return;
        if ( $new_status === $old_status ) return;

        $this->recalculate_listing_rating( $comment->comment_post_ID );
    }
}
