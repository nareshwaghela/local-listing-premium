<?php get_header(); ?>

<?php while ( have_posts() ) : the_post(); 
    $lat = get_post_meta( get_the_ID(), '_localist_lat', true );
    $lng = get_post_meta( get_the_ID(), '_localist_lng', true );
    $phone = get_post_meta( get_the_ID(), '_localist_phone', true );
    $website = get_post_meta( get_the_ID(), '_localist_website', true );
    $address = get_post_meta( get_the_ID(), '_localist_address', true );
    $avg_rating = (float) get_post_meta( get_the_ID(), '_localist_avg_rating', true );
    $review_count = (int) get_post_meta( get_the_ID(), '_localist_review_count', true );
?>

<!-- JSON-LD Schema -->
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "LocalBusiness",
    "name": "<?php echo esc_js( get_the_title() ); ?>",
    "description": "<?php echo esc_js( wp_strip_all_tags( get_the_excerpt() ) ); ?>",
    "url": "<?php echo esc_url( get_permalink() ); ?>",
    "telephone": "<?php echo esc_js( $phone ); ?>",
    "address": {
        "@type": "PostalAddress",
        "streetAddress": "<?php echo esc_js( $address ); ?>"
    },
    "geo": {
        "@type": "GeoCoordinates",
        "latitude": <?php echo floatval( $lat ); ?>,
        "longitude": <?php echo floatval( $lng ); ?>
    },
    "aggregateRating": {
        "@type": "AggregateRating",
        "ratingValue": <?php echo floatval( $avg_rating ); ?>,
        "reviewCount": <?php echo intval( $review_count ); ?>
    }
}
</script>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Header -->
            <div>
                <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-2">
                    <?php 
                    $cats = get_the_terms( get_the_ID(), 'listing_category' );
                    if ( $cats && ! is_wp_error( $cats ) ) {
                        echo '<a href="' . esc_url( get_term_link( $cats[0] ) ) . '" class="hover:text-primary-600">' . esc_html( $cats[0]->name ) . '</a>';
                    }
                    ?>
                </div>
                <h1 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4"><?php the_title(); ?></h1>
                
                <div class="flex flex-wrap items-center gap-4 mb-6">
                    <div class="flex items-center gap-1">
                        <?php for ( $i = 1; $i <= 5; $i++ ) : ?>
                            <svg class="w-5 h-5 <?php echo $i <= round( $avg_rating ) ? 'text-yellow-400' : 'text-gray-300 dark:text-slate-600'; ?>" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        <?php endfor; ?>
                        <span class="text-gray-600 dark:text-gray-400 ml-1">(<?php echo esc_html( $review_count ); ?> reviews)</span>
                    </div>
                </div>
            </div>

            <!-- Gallery -->
            <?php if ( has_post_thumbnail() ) : ?>
                <div class="rounded-xl overflow-hidden aspect-video bg-gray-200 dark:bg-slate-700">
                    <?php the_post_thumbnail( 'large', [ 'class' => 'w-full h-full object-cover', 'loading' => 'eager' ] ); ?>
                </div>
            <?php endif; ?>

            <!-- Description -->
            <div class="prose dark:prose-invert max-w-none">
                <?php the_content(); ?>
            </div>

            <!-- Reviews Section -->
            <div id="reviews" class="border-t border-gray-200 dark:border-slate-700 pt-8">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">
                    <?php printf( esc_html__( 'Reviews (%d)', 'localist' ), $review_count ); ?>
                </h2>
                
                <?php comments_template( '/templates/parts/reviews.php' ); ?>
            </div>
        </div>

        <!-- Sidebar -->
        <aside class="space-y-6">
            <!-- Info Card -->
            <div class="card p-6 sticky top-24">
                <h3 class="font-bold text-lg text-gray-900 dark:text-white mb-4"><?php esc_html_e( 'Contact Info', 'localist' ); ?></h3>
                
                <?php if ( $address ) : ?>
                    <div class="flex items-start gap-3 mb-4">
                        <svg class="w-5 h-5 text-gray-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span class="text-gray-700 dark:text-gray-300"><?php echo esc_html( $address ); ?></span>
                    </div>
                <?php endif; ?>

                <?php if ( $phone ) : ?>
                    <div class="flex items-center gap-3 mb-4">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        <a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9]/', '', $phone ) ); ?>" class="text-primary-600 hover:underline"><?php echo esc_html( $phone ); ?></a>
                    </div>
                <?php endif; ?>

                <?php if ( $website ) : ?>
                    <div class="flex items-center gap-3 mb-6">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                        <a href="<?php echo esc_url( $website ); ?>" target="_blank" rel="noopener noreferrer" class="text-primary-600 hover:underline truncate"><?php echo esc_html( parse_url( $website, PHP_URL_HOST ) ); ?></a>
                    </div>
                <?php endif; ?>

                <!-- Map -->
                <?php if ( $lat && $lng ) : ?>
                    <div id="listing-map" class="w-full h-48 rounded-lg bg-gray-200 dark:bg-slate-700 mb-4"
                         data-lat="<?php echo esc_attr( $lat ); ?>" 
                         data-lng="<?php echo esc_attr( $lng ); ?>"
                         data-title="<?php echo esc_attr( get_the_title() ); ?>">
                    </div>
                <?php endif; ?>

                <a href="#reviews" class="btn-primary w-full text-center block">
                    <?php esc_html_e( 'Write a Review', 'localist' ); ?>
                </a>
            </div>
        </aside>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const mapEl = document.getElementById('listing-map');
    if (mapEl && typeof L !== 'undefined') {
        const lat = parseFloat(mapEl.dataset.lat);
        const lng = parseFloat(mapEl.dataset.lng);
        const title = mapEl.dataset.title;
        
        const map = L.map('listing-map').setView([lat, lng], 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors',
            maxZoom: 19
        }).addTo(map);
        L.marker([lat, lng]).addTo(map).bindPopup(title);
    }
});
</script>

<?php endwhile; ?>
<?php get_footer(); ?>
