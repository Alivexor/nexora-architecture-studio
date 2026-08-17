<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function nexora_core_brand_fields() {
    return [
        'identity' => [
            'studio_name', 'founded', 'phone', 'email', 'address_fa', 'address_en', 'hours_fa', 'hours_en', 'instagram', 'linkedin', 'directions_url', 'accent', 'inquiry_retention_days',
        ],
        'hero' => [
            'hero_title_fa', 'hero_title_en', 'hero_text_fa', 'hero_text_en', 'approach_fa', 'approach_en',
        ],
        'metrics' => [
            'stat_projects', 'stat_years', 'stat_cities', 'stat_awards',
        ],
        'sections' => [
            'selected_title_fa', 'selected_title_en', 'services_title_fa', 'services_title_en', 'clients_title_fa', 'clients_title_en', 'cta_title_fa', 'cta_title_en', 'clients_list', 'footer_fa', 'footer_en',
        ],
    ];
}

function nexora_core_brand_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $brand = nexora_core_brand();
    $groups = nexora_core_brand_fields();
    $group_titles = nexora_core_admin_lang() === 'fa'
        ? [ 'identity' => 'هویت و تماس', 'hero' => 'صفحه اصلی و رویکرد', 'metrics' => 'آمار', 'sections' => 'بخش‌های محتوایی' ]
        : [ 'identity' => 'Identity & contact', 'hero' => 'Homepage & approach', 'metrics' => 'Statistics', 'sections' => 'Content sections' ];
    ?>
    <div class="wrap nexora-brand-sections">
        <h1><?php echo esc_html( nexora_core_t( 'settings' ) ); ?></h1>
        <?php if ( isset( $_GET['saved'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
            <div class="notice notice-success is-dismissible"><p><?php echo esc_html( nexora_core_admin_lang() === 'fa' ? 'تنظیمات ذخیره شد.' : 'Settings saved.' ); ?></p></div>
        <?php endif; ?>
        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
            <input type="hidden" name="action" value="nexora_brand_save">
            <?php wp_nonce_field( 'nexora_brand_save' ); ?>

            <?php foreach ( $groups as $group => $fields ) : ?>
                <section class="nexora-brand-section">
                    <h2><?php echo esc_html( $group_titles[ $group ] ); ?></h2>
                    <table class="form-table" role="presentation"><tbody>
                    <?php foreach ( $fields as $key ) : ?>
                        <?php
                        $is_textarea = str_contains( $key, 'text' ) || str_contains( $key, 'approach' ) || str_contains( $key, 'footer' );
                        $type = 'text';
                        if ( 'email' === $key ) {
                            $type = 'email';
                        } elseif ( in_array( $key, [ 'instagram', 'linkedin', 'directions_url' ], true ) ) {
                            $type = 'url';
                        } elseif ( 'accent' === $key ) {
                            $type = 'color';
                        } elseif ( 'inquiry_retention_days' === $key ) {
                            $type = 'number';
                        }
                        ?>
                        <tr>
                            <th scope="row"><label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( nexora_core_t( $key ) ); ?></label></th>
                            <td>
                                <?php if ( $is_textarea ) : ?>
                                    <textarea class="large-text" rows="4" id="<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $key ); ?>"><?php echo esc_textarea( $brand[ $key ] ); ?></textarea>
                                <?php else : ?>
                                    <input class="regular-text" type="<?php echo esc_attr( $type ); ?>" id="<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $brand[ $key ] ); ?>" <?php echo 'inquiry_retention_days' === $key ? 'min="30" max="1095" step="1"' : ''; ?>>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody></table>
                </section>
            <?php endforeach; ?>

            <section class="nexora-brand-section">
                <h2><?php echo esc_html( nexora_core_t( 'captcha' ) ); ?></h2>
                <p>
                    <label>
                        <input type="checkbox" name="captcha_enabled" value="1" <?php checked( $brand['captcha_enabled'], '1' ); ?>>
                        <?php echo esc_html( nexora_core_t( 'captcha_help' ) ); ?>
                    </label>
                </p>
            </section>


            <section class="nexora-brand-section">
                <h2><?php echo esc_html( nexora_core_t( 'business_schema' ) ); ?></h2>
                <p><label><input type="checkbox" name="business_schema_enabled" value="1" <?php checked( $brand['business_schema_enabled'], '1' ); ?>> <?php echo esc_html( nexora_core_t( 'business_schema_help' ) ); ?></label></p>
            </section>

            <?php submit_button( nexora_core_t( 'save' ) ); ?>
        </form>
    </div>
    <?php
}

function nexora_core_brand_save() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html( nexora_core_t( 'forbidden' ) ), '', [ 'response' => 403 ] );
    }

    check_admin_referer( 'nexora_brand_save' );

    $old = nexora_core_brand();
    $keys = array_merge( ...array_values( nexora_core_brand_fields() ) );
    $new = [];

    foreach ( $keys as $key ) {
        $raw = isset( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : $old[ $key ];
        $new[ $key ] = str_contains( $key, 'text' ) || str_contains( $key, 'approach' ) || str_contains( $key, 'footer' )
            ? sanitize_textarea_field( $raw )
            : sanitize_text_field( $raw );
    }

    $new['email']           = sanitize_email( $new['email'] );
    $new['instagram']       = esc_url_raw( $new['instagram'] );
    $new['linkedin']        = esc_url_raw( $new['linkedin'] );
    $new['directions_url']  = esc_url_raw( $new['directions_url'] );
    $new['accent']          = sanitize_hex_color( $new['accent'] ) ?: '#d7b57a';
    $new['captcha_enabled']       = isset( $_POST['captcha_enabled'] ) ? '1' : '0';
    $new['business_schema_enabled'] = isset( $_POST['business_schema_enabled'] ) ? '1' : '0';
    $new['inquiry_retention_days'] = (string) max( 30, min( 1095, absint( $new['inquiry_retention_days'] ) ) );

    update_option( 'nexora_brand', $new, false );
    wp_safe_redirect( admin_url( 'admin.php?page=nexora-settings&saved=1' ) );
    exit;
}
add_action( 'admin_post_nexora_brand_save', 'nexora_core_brand_save' );
