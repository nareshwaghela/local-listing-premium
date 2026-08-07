<?php defined( 'ABSPATH' ) || exit; ?>

<div x-data="{ showForm: false }">
    <!-- Review Form Toggle -->
    <?php if ( is_user_logged_in() ) : ?>
        <button @click="showForm = !showForm" class="mb-6 text-primary-600 font-medium hover:underline">
            <span x-text="showForm ? '<?php esc_attr_e( 'Cancel', 'localist' ); ?>' : '<?php esc_attr_e( 'Write a Review', 'localist' ); ?>'"></span>
        </button>

        <form x-show="showForm" x-transition class="bg-gray-50 dark:bg-slate-800 rounded-lg p-6 mb-8" 
              @submit.prevent="submitReview">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"><?php esc_html_e( 'Rating', 'localist' ); ?></label>
                <div class="flex gap-1" x-data="{ rating: 0 }">
                    <template x-for="i in 5">
                        <button type="button" @click="rating = i" 
                                class="text-2xl focus:outline-none"
                                :class="i <= rating ? 'text-yellow-400' : 'text-gray-300 dark:text-slate-600'">★</button>
                    </template>
                    <input type="hidden" name="rating" x-model="rating" required>
                </div>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"><?php esc_html_e( 'Review', 'localist' ); ?></label>
                <textarea name="comment" rows="4" required 
                          class="w-full rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:ring-primary-500"></textarea>
            </div>
            <input type="hidden" name="post_id" value="<?php echo get_the_ID(); ?>">
            <input type="hidden" name="nonce" value="<?php echo wp_create_nonce( 'localist_submit_review' ); ?>">
            <button type="submit" class="btn-primary"><?php esc_html_e( 'Submit Review', 'localist' ); ?></button>
        </form>
    <?php else : ?>
        <p class="mb-6 text-gray-600 dark:text-gray-400">
            <?php printf( 
                __( '<a href="%s" class="text-primary-600 hover:underline">Log in</a> to write a review.', 'localist' ),
                wp_login_url( get_permalink() )
            ); ?>
        </p>
    <?php endif; ?>

    <!-- Existing Reviews -->
    <?php if ( have_comments() ) : ?>
        <div class="space-y-6">
            <?php wp_list_comments([
                'style'       => 'div',
                'short_ping'  => true,
                'avatar_size' => 48,
                'callback'    => function( $comment, $args, $depth ) {
                    $rating = get_comment_meta( $comment->comment_ID, '_localist_rating', true );
                    ?>
                    <div class="flex gap-4 pb-6 border-b border-gray-100 dark:border-slate-700 last:border-0">
                        <div class="flex-shrink-0"><?php echo get_avatar( $comment, 48, '', '', [ 'class' => 'rounded-full' ] ); ?></div>
                        <div class="flex-grow">
                            <div class="flex items-center justify-between mb-1">
                                <h4 class="font-semibold text-gray-900 dark:text-white"><?php comment_author(); ?></h4>
                                <time class="text-sm text-gray-500"><?php comment_date(); ?></time>
                            </div>
                            <?php if ( $rating ) : ?>
                                <div class="flex gap-0.5 mb-2">
                                    <?php for ( $i = 1; $i <= 5; $i++ ) : ?>
                                        <span class="<?php echo $i <= $rating ? 'text-yellow-400' : 'text-gray-300'; ?>">★</span>
                                    <?php endfor; ?>
                                </div>
                            <?php endif; ?>
                            <div class="text-gray-700 dark:text-gray-300"><?php comment_text(); ?></div>
                        </div>
                    </div>
                    <?php
                }
            ]); ?>
        </div>
        <?php the_comments_navigation(); ?>
    <?php endif; ?>
</div>
