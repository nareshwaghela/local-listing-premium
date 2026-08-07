<?php get_header(); 

$edit_id = absint( $_GET['edit'] ?? 0 );
$is_edit = $edit_id && get_post( $edit_id ) && get_post_field( 'post_author', $edit_id ) == get_current_user_id();
$post    = $is_edit ? get_post( $edit_id ) : null;
?>

<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">
        <?php echo $is_edit ? esc_html__( 'Edit Listing', 'localist' ) : esc_html__( 'Add New Listing', 'localist' ); ?>
    </h1>

    <form id="listing-form" class="space-y-6" enctype="multipart/form-data"
          x-data="{ submitting: false, previewUrl: '<?php echo $is_edit && has_post_thumbnail( $edit_id ) ? get_the_post_thumbnail_url( $edit_id, 'medium' ) : ''; ?>' }"
          @submit.prevent="submitListing">

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"><?php esc_html_e( 'Title *', 'localist' ); ?></label>
            <input type="text" name="title" required value="<?php echo esc_attr( $post?->post_title ); ?>"
                   class="w-full rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:ring-primary-500">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"><?php esc_html_e( 'Description *', 'localist' ); ?></label>
            <textarea name="description" rows="6" required 
                      class="w-full rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:ring-primary-500"><?php echo esc_textarea( $post?->post_content ); ?></textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"><?php esc_html_e( 'Category', 'localist' ); ?></label>
                <?php 
                wp_dropdown_categories([
                    'taxonomy'        => 'listing_category',
                    'name'            => 'category',
                    'show_option_all' => __( 'Select Category', 'localist' ),
                    'selected'        => $is_edit ? wp_get_object_terms( $edit_id, 'listing_category', [ 'fields' => 'ids' ] )[0] ?? 0 : 0,
                    'class'           => 'w-full rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white',
                ]); 
                ?>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"><?php esc_html_e( 'Location', 'localist' ); ?></label>
                <?php 
                wp_dropdown_categories([
                    'taxonomy'        => 'listing_location',
                    'name'            => 'location',
                    'show_option_all' => __( 'Select Location', 'localist' ),
                    'selected'        => $is_edit ? wp_get_object_terms( $edit_id, 'listing_location', [ 'fields' => 'ids' ] )[0] ?? 0 : 0,
                    'class'           => 'w-full rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white',
                ]); 
                ?>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"><?php esc_html_e( 'Address', 'localist' ); ?></label>
            <input type="text" name="address" value="<?php echo esc_attr( $is_edit ? get_post_meta( $edit_id, '_localist_address', true ) : '' ); ?>"
                   class="w-full rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:ring-primary-500"
                   placeholder="<?php esc_attr_e( 'Start typing to auto-fill coordinates...', 'localist' ); ?>"
                   x-ref="addressInput">
            <input type="hidden" name="lat" value="<?php echo esc_attr( $is_edit ? get_post_meta( $edit_id, '_localist_lat', true ) : '' ); ?>" x-ref="latInput">
            <input type="hidden" name="lng" value="<?php echo esc_attr( $is_edit ? get_post_meta( $edit_id, '_localist_lng', true ) : '' ); ?>" x-ref="lngInput">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"><?php esc_html_e( 'Phone', 'localist' ); ?></label>
                <input type="tel" name="phone" value="<?php echo esc_attr( $is_edit ? get_post_meta( $edit_id, '_localist_phone', true ) : '' ); ?>"
                       class="w-full rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:ring-primary-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"><?php esc_html_e( 'Website', 'localist' ); ?></label>
                <input type="url" name="website" value="<?php echo esc_attr( $is_edit ? get_post_meta( $edit_id, '_localist_website', true ) : '' ); ?>"
                       class="w-full rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:ring-primary-500">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"><?php esc_html_e( 'Featured Image', 'localist' ); ?></label>
            <input type="file" name="featured_image" accept="image/*" 
                   @change="previewUrl = URL.createObjectURL($event.target.files[0])"
                   class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 dark:file:bg-slate-700 dark:file:text-gray-300">
            <img x-show="previewUrl" :src="previewUrl" class="mt-3 h-32 w-auto rounded-lg object-cover" alt="Preview">
        </div>

        <input type="hidden" name="action" value="localist_submit_listing">
        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce( 'localist_submit_listing' ); ?>">
        <?php if ( $is_edit ) : ?>
            <input type="hidden" name="post_id" value="<?php echo esc_attr( $edit_id ); ?>">
        <?php endif; ?>

        <div class="flex items-center gap-4 pt-4">
            <button type="submit" :disabled="submitting" class="btn-primary disabled:opacity-50">
                <span x-text="submitting ? '<?php esc_attr_e( 'Saving...', 'localist' ); ?>' : '<?php echo $is_edit ? esc_attr__( 'Update Listing', 'localist' ) : esc_attr__( 'Submit Listing', 'localist' ); ?>'"></span>
            </button>
            <a href="<?php echo esc_url( home_url( '/dashboard/' ) ); ?>" class="text-gray-600 dark:text-gray-400 hover:underline">
                <?php esc_html_e( 'Cancel', 'localist' ); ?>
            </a>
        </div>

        <div x-show="message" x-text="message" 
             :class="success ? 'text-green-600' : 'text-red-600'" 
             class="text-sm font-medium"></div>
    </form>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('listingForm', () => ({
        submitting: false,
        message: '',
        success: false,
        
        async submitListing() {
            this.submitting = true;
            this.message = '';
            
            const formData = new FormData(document.getElementById('listing-form'));
            
            try {
                const response = await fetch(window.localistData.ajaxUrl, {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                });
                
                const data = await response.json();
                this.success = data.success;
                this.message = data.data.message;
                
                if (data.success && data.data.redirect) {
                    setTimeout(() => window.location.href = data.data.redirect, 1000);
                }
            } catch (err) {
                this.success = false;
                this.message = '<?php esc_attr_e( 'An error occurred. Please try again.', 'localist' ); ?>';
            } finally {
                this.submitting = false;
            }
        }
    }));
});
</script>

<?php get_footer(); ?>
