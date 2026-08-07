<?php get_header(); ?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col md:flex-row gap-8">
        <!-- Sidebar Nav -->
        <aside class="md:w-64 flex-shrink-0">
            <nav class="space-y-1">
                <?php 
                $tabs = [
                    'listings' => __( 'My Listings', 'localist' ),
                    'submit'   => __( 'Add Listing', 'localist' ),
                    'reviews'  => __( 'Reviews', 'localist' ),
                    'favorites'=> __( 'Favorites', 'localist' ),
                    'profile'  => __( 'Profile', 'localist' ),
                ];
                $current = sanitize_key( $_GET['tab'] ?? 'listings' );
                foreach ( $tabs as $slug => $label ) : 
                    $url = add_query_arg( 'tab', $slug, home_url( '/dashboard/' ) );
                ?>
                    <a href="<?php echo esc_url( $url ); ?>" 
                       class="block px-4 py-3 rounded-lg text-sm font-medium <?php echo $current === $slug ? 'bg-primary-600 text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-800'; ?>">
                        <?php echo esc_html( $label ); ?>
                    </a>
                <?php endforeach; ?>
            </nav>
        </aside>

        <!-- Content -->
        <div class="flex-grow">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6"><?php esc_html_e( 'My Listings', 'localist' ); ?></h1>

            <?php 
            $user_listings = new WP_Query([
                'post_type'      => 'listing',
                'author'         => get_current_user_id(),
                'posts_per_page' => 20,
                'post_status'    => [ 'publish', 'pending', 'draft' ],
            ]);
            ?>

            <?php if ( $user_listings->have_posts() ) : ?>
                <div class="space-y-4">
                    <?php while ( $user_listings->have_posts() ) : $user_listings->the_post(); ?>
                        <div class="card p-4 flex items-center gap-4">
                            <div class="w-20 h-20 rounded-lg overflow-hidden bg-gray-200 dark:bg-slate-700 flex-shrink-0">
                                <?php if ( has_post_thumbnail() ) : ?>
                                    <?php the_post_thumbnail( 'thumbnail', [ 'class' => 'w-full h-full object-cover' ] ); ?>
                                <?php endif; ?>
                            </div>
                            <div class="flex-grow min-w-0">
                                <h3 class="font-semibold text-gray-900 dark:text-white truncate">
                                    <a href="<?php the_permalink(); ?>" class="hover:text-primary-600"><?php the_title(); ?></a>
                                </h3>
                                <span class="inline-block mt-1 px-2 py-0.5 text-xs rounded-full 
                                    <?php 
                                    $status = get_post_status();
                                    echo match($status) {
                                        'publish' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                        'pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                                        default   => 'bg-gray-100 text-gray-800 dark:bg-slate-700 dark:text-gray-300',
                                    };
                                    ?>">
                                    <?php echo esc_html( ucfirst( $status ) ); ?>
                                </span>
                            </div>
                            <a href="<?php echo esc_url( add_query_arg( 'edit', get_the_ID(), home_url( '/dashboard/?tab=submit' ) ) ); ?>" 
                               class="text-sm text-primary-600 hover:underline flex-shrink-0">
                                <?php esc_html_e( 'Edit', 'localist' ); ?>
                            </a>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else : ?>
                <div class="text-center py-12 bg-gray-50 dark:bg-slate-800 rounded-xl">
                    <p class="text-gray-600 dark:text-gray-400 mb-4"><?php esc_html_e( 'No listings yet.', 'localist' ); ?></p>
                    <a href="<?php echo esc_url( add_query_arg( 'tab', 'submit', home_url( '/dashboard/' ) ) ); ?>" class="btn-primary">
                        <?php esc_html_e( 'Add Your First Listing', 'localist' ); ?>
                    </a>
                </div>
            <?php endif; wp_reset_postdata(); ?>
        </div>
    </div>
</div>

<?php get_footer(); ?>
