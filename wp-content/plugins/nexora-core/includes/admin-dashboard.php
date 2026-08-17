<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function nexora_core_admin_menu() {
    add_menu_page( nexora_core_t( 'dashboard' ), 'Nexora', 'manage_options', 'nexora-studio', 'nexora_core_dashboard', 'dashicons-admin-multisite', 3 );
    add_submenu_page( 'nexora-studio', nexora_core_t( 'dashboard' ), nexora_core_t( 'dashboard' ), 'manage_options', 'nexora-studio', 'nexora_core_dashboard' );
    add_submenu_page( 'nexora-studio', nexora_core_t( 'setup' ), nexora_core_t( 'setup' ), 'manage_options', 'nexora-setup', 'nexora_core_demo_page' );
    add_submenu_page( 'nexora-studio', nexora_core_t( 'settings' ), nexora_core_t( 'settings' ), 'manage_options', 'nexora-settings', 'nexora_core_brand_page' );
}
add_action( 'admin_menu', 'nexora_core_admin_menu', 5 );

function nexora_core_translation_health() {
    if ( ! function_exists( 'pll_get_post_translations' ) ) {
        return [ 0, 0 ];
    }
    $ids = get_posts( [
        'post_type' => [ 'nexora_project', 'nexora_service', 'post', 'page' ], 'post_status' => 'publish', 'posts_per_page' => -1,
        'fields' => 'ids', 'suppress_filters' => true, 'no_found_rows' => true,
    ] );
    $total = 0;
    $complete = 0;
    foreach ( $ids as $id ) {
        $lang = function_exists( 'pll_get_post_language' ) ? pll_get_post_language( $id, 'slug' ) : '';
        if ( 'fa' !== $lang ) {
            continue;
        }
        ++$total;
        $translations = pll_get_post_translations( $id );
        if ( ! empty( $translations['fa'] ) && ! empty( $translations['en'] ) ) {
            ++$complete;
        }
    }
    return [ $complete, $total ];
}

function nexora_core_dashboard() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    $inquiry_counts = wp_count_posts( 'nexora_inquiry' );
    $counts = [
        nexora_core_t( 'projects' )  => (int) ( wp_count_posts( 'nexora_project' )->publish ?? 0 ),
        nexora_core_t( 'services' )  => (int) ( wp_count_posts( 'nexora_service' )->publish ?? 0 ),
        nexora_core_t( 'inquiries' ) => (int) ( ( $inquiry_counts->private ?? 0 ) + ( $inquiry_counts->publish ?? 0 ) ),
        nexora_core_t( 'journal' )   => (int) ( wp_count_posts( 'post' )->publish ?? 0 ),
    ];
    [ $translated, $translatable ] = nexora_core_translation_health();
    $missing_images = new WP_Query( [
        'post_type' => 'nexora_project', 'post_status' => 'publish', 'posts_per_page' => 1, 'fields' => 'ids',
        'meta_query' => [ [ 'key' => '_thumbnail_id', 'compare' => 'NOT EXISTS' ] ], 'no_found_rows' => false,
    ] );
    $recent = current_user_can( 'manage_nexora_inquiries' ) ? get_posts( [
        'post_type' => 'nexora_inquiry', 'post_status' => [ 'private', 'publish' ], 'posts_per_page' => 5, 'orderby' => 'date', 'order' => 'DESC',
    ] ) : [];
    $demo = (string) get_option( 'nexora_demo_installed', '' );
    ?>
    <div class="wrap">
        <h1>NEXORA</h1>
        <p><?php echo esc_html( nexora_core_admin_lang() === 'fa' ? 'مرکز کنترل محتوا، درخواست‌ها و سلامت نکسورا.' : 'Control center for Nexora content, inquiries and setup health.' ); ?></p>
        <div class="nexora-dashboard-grid">
            <?php foreach ( $counts as $label => $value ) : ?><div class="nexora-dashboard-card"><strong class="big"><?php echo esc_html( (string) $value ); ?></strong><span><?php echo esc_html( $label ); ?></span></div><?php endforeach; ?>
        </div>
        <div class="nexora-dashboard-grid">
            <section class="nexora-dashboard-card">
                <h2><?php echo esc_html( nexora_core_t( 'site_health' ) ); ?></h2>
                <ul class="nexora-health-list">
                    <li><span>Polylang</span><span class="nexora-status-pill <?php echo nexora_core_is_polylang_ready() ? 'ok' : 'warn'; ?>"><?php echo esc_html( nexora_core_is_polylang_ready() ? nexora_core_t( 'ready' ) : nexora_core_t( 'attention' ) ); ?></span></li>
                    <li><span><?php echo esc_html( nexora_core_t( 'demo_state' ) ); ?></span><span class="nexora-status-pill <?php echo $demo ? 'ok' : 'warn'; ?>"><?php echo esc_html( $demo ? nexora_core_t( 'ready' ) : nexora_core_t( 'attention' ) ); ?></span></li>
                    <li><span><?php echo esc_html( nexora_core_t( 'translation_health' ) ); ?></span><strong><?php echo esc_html( $translatable ? round( ( $translated / $translatable ) * 100 ) . '%' : '—' ); ?></strong></li>
                    <li><span><?php echo esc_html( nexora_core_t( 'missing_images' ) ); ?></span><strong><?php echo esc_html( (string) $missing_images->found_posts ); ?></strong></li>
                </ul>
            </section>
            <section class="nexora-dashboard-card">
                <h2><?php echo esc_html( nexora_core_t( 'recent_inquiries' ) ); ?></h2>
                <?php if ( $recent ) : ?>
                    <ul class="nexora-health-list">
                        <?php foreach ( $recent as $inquiry ) : ?>
                            <li><a href="<?php echo esc_url( get_edit_post_link( $inquiry->ID ) ); ?>"><?php echo esc_html( get_the_title( $inquiry ) ); ?></a><span><?php $st = get_post_meta( $inquiry->ID, '_nexora_inquiry_status', true ) ?: 'new'; $labels = nexora_core_inquiry_statuses(); echo esc_html( $labels[ $st ] ?? $st ); ?></span></li>
                        <?php endforeach; ?>
                    </ul>
                <?php else : ?><p><?php echo esc_html( nexora_core_admin_lang() === 'fa' ? 'هنوز درخواستی ثبت نشده است.' : 'No inquiries yet.' ); ?></p><?php endif; ?>
            </section>
            <section class="nexora-dashboard-card">
                <h2><?php echo esc_html( nexora_core_t( 'quick_actions' ) ); ?></h2>
                <p><a class="button button-primary" href="<?php echo esc_url( admin_url( 'post-new.php?post_type=nexora_project' ) ); ?>"><?php echo esc_html( nexora_core_t( 'add_project' ) ); ?></a></p>
                <?php if ( current_user_can( 'manage_nexora_inquiries' ) ) : ?><p><a class="button" href="<?php echo esc_url( admin_url( 'edit.php?post_type=nexora_inquiry' ) ); ?>"><?php echo esc_html( nexora_core_t( 'inquiries' ) ); ?></a></p><?php endif; ?>
                <p><a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=nexora-setup' ) ); ?>"><?php echo esc_html( nexora_core_t( 'setup' ) ); ?></a></p>
                <?php if ( current_user_can( 'manage_nexora_inquiries' ) ) : ?><p><a class="button" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=nexora_export_inquiries' ), 'nexora_export_inquiries' ) ); ?>"><?php echo esc_html( nexora_core_t( 'export_csv' ) ); ?></a></p><?php endif; ?>
            </section>
        </div>
    </div>
    <?php
}

function nexora_core_csv_cell( $value ) {
    $value = (string) $value;
    if ( preg_match( '/^\s*[=+\-@]/u', $value ) ) {
        return "'" . $value;
    }
    return $value;
}

function nexora_core_export_inquiries() {
    if ( ! current_user_can( 'manage_nexora_inquiries' ) ) {
        wp_die( esc_html( nexora_core_t( 'forbidden' ) ), '', [ 'response' => 403 ] );
    }
    check_admin_referer( 'nexora_export_inquiries' );
    $rows = get_posts( [ 'post_type' => 'nexora_inquiry', 'post_status' => [ 'private', 'publish' ], 'posts_per_page' => -1, 'orderby' => 'date', 'order' => 'DESC', 'suppress_filters' => true ] );
    nocache_headers();
    header( 'Content-Type: text/csv; charset=utf-8' );
    header( 'Content-Disposition: attachment; filename="nexora-inquiries-' . gmdate( 'Y-m-d' ) . '.csv"' );
    $out = fopen( 'php://output', 'w' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen
    if ( false === $out ) {
        wp_die( esc_html( nexora_core_admin_lang() === 'fa' ? 'ساخت فایل خروجی ناموفق بود.' : 'Could not create the export stream.' ) );
    }
    $lang = nexora_core_admin_lang();
    // UTF-8 BOM keeps Persian text readable in spreadsheet apps that do not auto-detect UTF-8 CSV.
    fwrite( $out, "\xEF\xBB\xBF" ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite
    $headers = 'fa' === $lang
        ? [ 'تاریخ', 'نام', 'ایمیل', 'تلفن', 'نوع پروژه', 'موقعیت', 'متراژ', 'بودجه', 'زمان‌بندی', 'وضعیت', 'ایمیل ارسالی', 'پیام' ]
        : [ 'Date', 'Name', 'Email', 'Phone', 'Project type', 'Location', 'Area', 'Budget', 'Timeline', 'Status', 'Mail', 'Message' ];
    fputcsv( $out, $headers, ',', '"', '' );
    $status_labels = nexora_core_inquiry_statuses();
    foreach ( $rows as $row ) {
        $status = get_post_meta( $row->ID, '_nexora_inquiry_status', true );
        $values = [
            get_the_date( 'c', $row ), get_post_meta( $row->ID, '_name', true ), get_post_meta( $row->ID, '_email', true ),
            get_post_meta( $row->ID, '_phone', true ), nexora_core_project_type_label( get_post_meta( $row->ID, '_project_type', true ), $lang ),
            get_post_meta( $row->ID, '_project_location', true ), get_post_meta( $row->ID, '_project_area', true ), get_post_meta( $row->ID, '_budget_range', true ),
            get_post_meta( $row->ID, '_timeline', true ), $status_labels[ $status ] ?? $status, get_post_meta( $row->ID, '_mail_status', true ), get_post_meta( $row->ID, '_message', true ),
        ];
        fputcsv( $out, array_map( 'nexora_core_csv_cell', $values ), ',', '"', '' );
    }
    fclose( $out ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
    exit;
}
add_action( 'admin_post_nexora_export_inquiries', 'nexora_core_export_inquiries' );
