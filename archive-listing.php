<?php get_header(); ?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8"
     x-data="searchComponent()"
     x-init="init()">

    <!-- Search Bar -->
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-100 dark:border-slate-700 p-6 mb-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <input type="text" x-model="query" 
                   placeholder="<?php esc_attr_e( 'What are you looking for?', 'localist' ); ?>"
                   class="w-full rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:ring-primary-500 focus:border-primary-500">
            
            <?php 
            wp_dropdown_categories([
                'taxonomy'        => 'listing_category',
                'name'            => 'category',
                'show_option_all' => __( 'All Categories', 'localist' ),
                'selected'        => isset( $_GET['category'] ) ? absint( $_GET['category'] ) : '',
                'class'           => 'w-full rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white',
                'echo'            => false,
            ]); 
            ?>

            <select x-model="sort" class="w-full rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                <option value="date"><?php esc_html_e( 'Newest First', 'localist' ); ?></option>
                <option value="rating"><?php esc_html_e( 'Highest Rated', 'localist' ); ?></option>
                <option value="title"><?php esc_html_e( 'Alphabetical', 'localist' ); ?></option>
                <option value="featured"><?php esc_html_e( 'Featured', 'localist' ); ?></option>
            </select>

            <button @click="setGeolocation()" class="btn-primary flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <?php esc_html_e( 'Near Me', 'localist' ); ?>
            </button>
        </div>
    </div>

    <!-- Results Count -->
    <div class="flex justify-between items-center mb-6">
        <p class="text-gray-600 dark:text-gray-400">
            <span x-text="total"></span> <?php esc_html_e( 'listings found', 'localist' ); ?>
        </p>
    </div>

    <!-- Loading State -->
    <div x-show="loading" class="py-20 text-center">
        <svg class="animate-spin h-8 w-8 text-primary-600 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
    </div>

    <!-- Listings Grid -->
    <div x-show="!loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <template x-for="listing in listings" :key="listing.id">
            <article class="card group relative">
                <div class="aspect-video overflow-hidden bg-gray-200 dark:bg-slate-700">
                    <img :src="listing.thumbnail || '<?php echo LOCALIST_URI; ?>/assets/dist/images/placeholder.svg'" 
                         :alt="listing.title"
                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                         loading="lazy">
                    <span x-show="listing.is_featured" class="absolute top-3 left-3 bg-yellow-500 text-white text-xs font-bold px-2 py-1 rounded">
                        <?php esc_html_e( 'Featured', 'localist' ); ?>
                    </span>
                </div>
                <div class="p-5">
                    <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400 mb-2">
                        <span x-text="listing.category"></span>
                        <span x-show="listing.distance">• <span x-text="listing.distance + ' mi'"></span></span>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2 line-clamp-1">
                        <a :href="listing.permalink" class="hover:text-primary-600 transition-colors" x-text="listing.title"></a>
                    </h3>
                    <div class="flex items-center gap-1 mb-3">
                        <template x-for="i in 5">
                            <svg class="w-4 h-4" :class="i <= Math.round(listing.rating) ? 'text-yellow-400' : 'text-gray-300 dark:text-slate-600'" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        </template>
                        <span class="text-xs text-gray-500 ml-1" x-text="'(' + listing.review_count + ')'"></span>
                    </div>
                    <p class="text-gray-600 dark:text-gray-300 text-sm line-clamp-2" x-text="listing.excerpt"></p>
                </div>
            </article>
        </template>
    </div>

    <!-- Empty State -->
    <div x-show="!loading && listings.length === 0" class="text-center py-20">
        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2"><?php esc_html_e( 'No listings found', 'localist' ); ?></h3>
        <p class="text-gray-600 dark:text-gray-400"><?php esc_html_e( 'Try adjusting your search criteria.', 'localist' ); ?></p>
    </div>

    <!-- Pagination -->
    <div x-show="pages > 1" class="mt-12 flex justify-center gap-2">
        <button @click="goToPage(page - 1)" :disabled="page <= 1" 
                class="px-4 py-2 rounded-lg border border-gray-300 dark:border-slate-600 disabled:opacity-50 hover:bg-gray-100 dark:hover:bg-slate-700">
            <?php esc_html_e( 'Previous', 'localist' ); ?>
        </button>
        <template x-for="p in pages">
            <button @click="goToPage(p)" 
                    :class="p === page ? 'bg-primary-600 text-white border-primary-600' : 'border-gray-300 dark:border-slate-600 hover:bg-gray-100 dark:hover:bg-slate-700'"
                    class="px-4 py-2 rounded-lg border" x-text="p"></button>
        </template>
        <button @click="goToPage(page + 1)" :disabled="page >= pages"
                class="px-4 py-2 rounded-lg border border-gray-300 dark:border-slate-600 disabled:opacity-50 hover:bg-gray-100 dark:hover:bg-slate-700">
            <?php esc_html_e( 'Next', 'localist' ); ?>
        </button>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('searchComponent', () => import('<?php echo LOCALIST_URI; ?>/assets/dist/js/search-component.js').then(m => m.default()));
});
</script>

<?php get_footer(); ?>
