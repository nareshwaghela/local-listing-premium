<?php
namespace LocaList\Admin;

defined( 'ABSPATH' ) || exit;

class LocaList_Settings {

    public function __construct() {
        add_action( 'admin_menu', [ $this, 'add_admin_menu' ] );
        add_action( 'admin_init', [ $this, 'register_settings' ] );
    }

    public function add_admin_menu(): void {
        add_submenu_page(
            'edit.php?post_type=listing',
            __( 'Theme Settings', 'localist' ),
            __( 'Settings', 'localist' ),
            'manage_options',
            'localist-settings',
            [ $this, 'render_settings_page' ]
        );
    }

    public function register_settings(): void {
        register_setting( 'localist_options_group', 'localist_auto_approve_listings', [
            'sanitize_callback' => 'rest_sanitize_boolean',
            'default'           => false,
        ]);

        register_setting( 'localist_options_group', 'localist_user_listing_limit', [
            'sanitize_callback' => 'absint',
            'default'           => 5,
        ]);

        register_setting( 'localist_options_group', 'localist_primary_color', [
            'sanitize_callback' => 'sanitize_hex_color',
            'default'           => '#2563eb',
        ]);

        add_settings_section(
            'localist_general_section',
            __( 'General Settings', 'localist' ),
            null,
            'localist-settings'
        );

        add_settings_field(
            'localist_auto_approve',
            __( 'Auto Approve Listings', 'localist' ),
            [ $this, 'render_checkbox' ],
            'localist-settings',
            'localist_general_section',
            [ 'label_for' => 'localist_auto_approve_listings', 'description' => __( 'Automatically publish listings submitted by users.', 'localist' ) ]
        );

        add_settings_field(
            'localist_listing_limit',
            __( 'Listing Limit per User', 'localist' ),
            [ $this, 'render_number' ],
            'localist-settings',
            'localist_general_section',
            [ 'label_for' => 'localist_user_listing_limit', 'description' => __( 'Maximum number of listings a standard user can create.', 'localist' ) ]
        );

        add_settings_field(
            'localist_primary_color',
            __( 'Primary Brand Color', 'localist' ),
            [ $this, 'render_color' ],
            'localist-settings',
            'localist_general_section',
            [ 'label_for' => 'localist_primary_color' ]
        );
    }

    public function render_settings_page(): void {
        if ( ! current_user_can( 'manage_options' ) ) return;
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields( 'localist_options_group' );
                do_settings_sections( 'localist-settings' );
                submit_button( __( 'Save Settings', 'localist' ) );
                ?>
            </form>
        </div>
        <?php
    }

    public function render_checkbox( array $args ): void {
        $value = get_option( $args['label_for'], false );
        ?>
        <input type="checkbox" id="<?php echo esc_attr( $args['label_for'] ); ?>" name="<?php echo esc_attr( $args['label_for'] ); ?>" value="1" <?php checked( $value, true ); ?>>
        <p class="description"><?php echo esc_html( $args['description'] ); ?></p>
        <?php
    }

    public function render_number( array $args ): void {
        $value = get_option( $args['label_for'], 5 );
        ?>
        <input type="number" id="<?php echo esc_attr( $args['label_for'] ); ?>" name="<?php echo esc_attr( $args['label_for'] ); ?>" value="<?php echo esc_attr( $value ); ?>" min="0" class="small-text">
        <p class="description"><?php echo esc_html( $args['description'] ); ?></p>
        <?php
    }

    public function render_color( array $args ): void {
        $value = get_option( $args['label_for'], '#2563eb' );
        ?>
        <input type="color" id="<?php echo esc_attr( $args['label_for'] ); ?>" name="<?php echo esc_attr( $args['label_for'] ); ?>" value="<?php echo esc_attr( $value ); ?>">
        <?php
    }
}
