</main><!-- #content -->

<footer class="bg-white dark:bg-slate-900 border-t border-gray-200 dark:border-slate-800 mt-auto">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <div class="col-span-1 md:col-span-2">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4"><?php bloginfo( 'name' ); ?></h3>
                <p class="text-gray-600 dark:text-gray-400 max-w-md">
                    <?php esc_html_e( 'Discover the best local businesses, services, and events in your area.', 'localist' ); ?>
                </p>
            </div>
            <div>
                <h4 class="font-semibold text-gray-900 dark:text-white mb-4"><?php esc_html_e( 'Quick Links', 'localist' ); ?></h4>
                <ul class="space-y-2 text-gray-600 dark:text-gray-400">
                    <li><a href="<?php echo esc_url( get_post_type_archive_link( 'listing' ) ); ?>" class="hover:text-primary-600"><?php esc_html_e( 'Browse Listings', 'localist' ); ?></a></li>
                    <li><a href="<?php echo esc_url( home_url( '/submit-listing' ) ); ?>" class="hover:text-primary-600"><?php esc_html_e( 'Add Listing', 'localist' ); ?></a></li>
                </ul>
            </div>
            <div>
                <h4 class="font-semibold text-gray-900 dark:text-white mb-4"><?php esc_html_e( 'Legal', 'localist' ); ?></h4>
                <ul class="space-y-2 text-gray-600 dark:text-gray-400">
                    <li><a href="#" class="hover:text-primary-600"><?php esc_html_e( 'Privacy Policy', 'localist' ); ?></a></li>
                    <li><a href="#" class="hover:text-primary-600"><?php esc_html_e( 'Terms of Service', 'localist' ); ?></a></li>
                </ul>
            </div>
        </div>
        <div class="mt-8 pt-8 border-t border-gray-200 dark:border-slate-800 text-center text-gray-500 text-sm">
            &copy; <?php echo date( 'Y' ); ?> <?php bloginfo( 'name' ); ?>. <?php esc_html_e( 'All rights reserved.', 'localist' ); ?>
        </div>
    </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
