<?php get_header(); ?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <?php if ( have_posts() ) : ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php while ( have_posts() ) : the_post(); ?>
                <article <?php post_class( 'card group' ); ?>>
                    <?php if ( has_post_thumbnail() ) : ?>
                        <div class="aspect-video overflow-hidden">
                            <?php the_post_thumbnail( 'medium_large', [
                                'class' => 'w-full h-full object-cover group-hover:scale-105 transition-transform duration-300',
                                'loading' => 'lazy',
                            ] ); ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="p-5">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-2">
                            <a href="<?php the_permalink(); ?>" class="hover:text-primary-600 transition-colors">
                                <?php the_title(); ?>
                            </a>
                        </h2>
                        
                        <div class="flex items-center text-sm text-gray-500 dark:text-gray-400 mb-3">
                            <?php 
                            $categories = get_the_terms( get_the_ID(), 'listing_category' );
                            if ( $categories && ! is_wp_error( $categories ) ) :
                                echo '<span class="mr-2">' . esc_html( $categories[0]->name ) . '</span>';
                            endif;
                            ?>
                            <span>•</span>
                            <span class="ml-2"><?php echo esc_html( get_the_date() ); ?></span>
                        </div>
                        
                        <p class="text-gray-600 dark:text-gray-300 text-sm line-clamp-3">
                            <?php echo wp_trim_words( get_the_excerpt(), 20 ); ?>
                        </p>
                    </div>
                </article>
            <?php endwhile; ?>
        </div>
        
        <div class="mt-12">
            <?php the_posts_pagination([
                'mid_size'  => 2,
                'prev_text' => __( '&larr; Previous', 'localist' ),
                'next_text' => __( 'Next &rarr;', 'localist' ),
            ]); ?>
        </div>
    <?php else : ?>
        <div class="text-center py-20">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">
                <?php esc_html_e( 'No listings found', 'localist' ); ?>
            </h2>
            <p class="text-gray-600 dark:text-gray-400">
                <?php esc_html_e( 'Be the first to add a listing!', 'localist' ); ?>
            </p>
        </div>
    <?php endif; ?>
</div>

<?php get_footer(); ?>
