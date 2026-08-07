<!DOCTYPE html>
<html <?php language_attributes(); ?> x-data="{ darkMode: localStorage.getItem('darkMode') === 'true', mobileMenu: false }" 
      :class="{ 'dark': darkMode }" 
      x-init="$watch('darkMode', val => localStorage.setItem('darkMode', val))">
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <?php wp_head(); ?>
</head>
<body <?php body_class( 'antialiased min-h-screen flex flex-col' ); ?>>
<?php wp_body_open(); ?>

<header class="sticky top-0 z-50 bg-white/80 dark:bg-slate-900/80 backdrop-blur-md border-b border-gray-200 dark:border-slate-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <!-- Logo -->
            <div class="flex-shrink-0 flex items-center">
                <?php if ( has_custom_logo() ) : ?>
                    <?php the_custom_logo(); ?>
                <?php else : ?>
                    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="text-xl font-bold text-primary-600">
                        <?php bloginfo( 'name' ); ?>
                    </a>
                <?php endif; ?>
            </div>

            <!-- Desktop Nav -->
            <nav class="hidden md:flex space-x-8">
                <a href="<?php echo esc_url( get_post_type_archive_link( 'listing' ) ); ?>" class="text-gray-700 dark:text-gray-300 hover:text-primary-600 font-medium">
                    <?php esc_html_e( 'Explore', 'localist' ); ?>
                </a>
                <a href="#" class="text-gray-700 dark:text-gray-300 hover:text-primary-600 font-medium">
                    <?php esc_html_e( 'Categories', 'localist' ); ?>
                </a>
            </nav>

            <!-- Actions -->
            <div class="flex items-center space-x-4">
                <!-- Dark Mode Toggle -->
                <button @click="darkMode = !darkMode" class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-slate-800">
                    <svg x-show="!darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                    <svg x-show="darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </button>

                <!-- Add Listing CTA -->
                <a href="<?php echo esc_url( home_url( '/submit-listing' ) ); ?>" class="btn-primary text-sm">
                    <?php esc_html_e( 'Add Listing', 'localist' ); ?>
                </a>
            </div>
        </div>
    </div>
</header>

<main id="content" class="flex-grow">
