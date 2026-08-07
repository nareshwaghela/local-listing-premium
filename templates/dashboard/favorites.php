<?php get_header(); ?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col md:flex-row gap-8">
        <?php include LOCALIST_DIR . '/templates/dashboard/partials/sidebar-nav.php'; ?>

        <div class="flex-grow">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6"><?php esc_html_e( 'My Favorites', 'localist' ); ?></h1>

            <?php
            $favorites = get_user_meta( get_current_user_id(), '_localist_favorites', true );
            $favorites = is_array( $favorites ) ? $favorites : [];

            if ( ! empty( $favorites ) ) :
                $fav_query = new WP_Query([
                    'post_type'      => 'listing',
                    'post__in'       => $favorites,
                    'posts_per_page' => 20,
                    'orderby'        => 'post__in',
                ]);
                ?>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <?php while ( $fav_query->have_posts() ) : $fav_query->the_post(); ?>
                        <article class="card group relative">
                            <div class="aspect-video overflow-hidden bg-gray-200 dark:bg-slate-700">
                                <?php if ( has_post_thumbnail() ) : ?>
                                    <?php the_post_thumbnail( 'medium_large', [ 'class' => 'w-full h-full object-cover group-hover:scale-105 transition-transform duration-300', 'loading' => 'lazy' ] ); ?>
                                <?php endif; ?>
                            </div>
                            <div class="p-5">
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2 line-clamp-1">
                                    <a href="<?php the_permalink(); ?>" class="hover:text-primary-600"><?php the_title(); ?></a>
                                </h3>
                                <p class="text-gray-600 dark:text-gray-300 text-sm line-clamp-2"><?php echo wp_trim_words( get_the_excerpt(), 15 ); ?></p>
                            </div>
                            <button x-data="favoritesComponent()" x-init="init()" @click="toggle()" data-post-id="<?php echo get_the_ID(); ?>"
                                    class="absolute top-3 right-3 p-2 bg-white/90 dark:bg-slate-800/90 rounded-full shadow-sm hover:bg-red-50 dark:hover:bg-red-900/30">
                                <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 24 24"><path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                            </button>
                        </article>
                    <?php endwhile; wp_reset_postdata(); ?>
                </div>
            <?php else : ?>
                <div class="text-center py-12 bg-gray-50 dark:bg-slate-800 rounded-xl">
                    <p class="text-gray-600 dark:text-gray-400 mb-4"><?php esc_html_e( 'No saved listings yet.', 'localist' ); ?></p>
                    <a href="<?php echo esc_url( get_post_type_archive_link( 'listing' ) ); ?>" class="btn-primary">
                        <?php esc_html_e( 'Explore Listings', 'localist' ); ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php get_footer(); ?>
