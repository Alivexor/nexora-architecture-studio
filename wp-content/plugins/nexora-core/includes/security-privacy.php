<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Capabilities used by the private inquiry post type.
 * These intentionally do not inherit the normal post/editor capabilities.
 */
function nexora_core_inquiry_capabilities() {
    return [
        'manage_nexora_inquiries',
        'edit_nexora_inquiry',
        'read_nexora_inquiry',
        'delete_nexora_inquiry',
        'edit_nexora_inquiries',
        'edit_others_nexora_inquiries',
        'delete_nexora_inquiries',
        'delete_others_nexora_inquiries',
        'read_private_nexora_inquiries',
        'publish_nexora_inquiries',
    ];
}

function nexora_core_install_capabilities() {
    $role = get_role( 'administrator' );
    if ( ! $role ) {
        return;
    }
    foreach ( nexora_core_inquiry_capabilities() as $capability ) {
        $role->add_cap( $capability );
    }
}

function nexora_core_schedule_cleanup() {
    if ( ! wp_next_scheduled( 'nexora_core_daily_cleanup' ) ) {
        wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'nexora_core_daily_cleanup' );
    }
}

function nexora_core_unschedule_cleanup() {
    $timestamp = wp_next_scheduled( 'nexora_core_daily_cleanup' );
    while ( $timestamp ) {
        wp_unschedule_event( $timestamp, 'nexora_core_daily_cleanup' );
        $timestamp = wp_next_scheduled( 'nexora_core_daily_cleanup' );
    }
}

function nexora_core_inquiry_retention_days() {
    $brand = function_exists( 'nexora_core_brand' ) ? nexora_core_brand() : [];
    $days  = isset( $brand['inquiry_retention_days'] ) ? absint( $brand['inquiry_retention_days'] ) : 180;
    return max( 30, min( 1095, $days ) );
}

function nexora_core_cleanup_old_inquiries() {
    $days = nexora_core_inquiry_retention_days();
    $ids  = get_posts(
        [
            'post_type'        => 'nexora_inquiry',
            'post_status'      => [ 'private', 'publish', 'draft', 'trash' ],
            'posts_per_page'   => 100,
            'fields'           => 'ids',
            'date_query'       => [ [ 'before' => $days . ' days ago', 'inclusive' => true ] ],
            'meta_query'       => [ [ 'key' => '_nexora_inquiry_status', 'value' => 'archived' ] ],
            'suppress_filters' => true,
            'no_found_rows'    => true,
        ]
    );
    foreach ( $ids as $id ) {
        wp_delete_post( (int) $id, true );
    }
}
add_action( 'nexora_core_daily_cleanup', 'nexora_core_cleanup_old_inquiries' );

function nexora_core_privacy_policy_content() {
    if ( ! function_exists( 'wp_add_privacy_policy_content' ) ) {
        return;
    }
    $text = nexora_core_admin_lang() === 'fa'
        ? 'فرم تماس نکسورا نام، ایمیل، اطلاعات پروژه و پیام را برای پیگیری درخواست ذخیره می‌کند. درخواست‌های بایگانی‌شده بر اساس دوره نگهداری تنظیم‌شده پاک می‌شوند. مدیر سایت می‌تواند داده‌های مرتبط با یک ایمیل را با ابزارهای حریم خصوصی وردپرس صادر یا پاک کند.'
        : 'The Nexora inquiry form stores the visitor name, email, project details and message so the inquiry can be followed up. Archived inquiries are removed according to the configured retention period. Site administrators can export or erase records associated with an email address through WordPress privacy tools.';
    wp_add_privacy_policy_content( 'Nexora Core', wpautop( esc_html( $text ) ) );
}
add_action( 'admin_init', 'nexora_core_privacy_policy_content' );

function nexora_core_privacy_exporters( $exporters ) {
    $exporters['nexora-inquiries'] = [
        'exporter_friendly_name' => nexora_core_admin_lang() === 'fa' ? 'درخواست‌های نکسورا' : 'Nexora inquiries',
        'callback'               => 'nexora_core_privacy_exporter',
    ];
    return $exporters;
}
add_filter( 'wp_privacy_personal_data_exporters', 'nexora_core_privacy_exporters' );

function nexora_core_privacy_exporter( $email_address, $page = 1 ) {
    $ids = get_posts(
        [
            'post_type'        => 'nexora_inquiry',
            'post_status'      => [ 'private', 'publish', 'draft', 'trash' ],
            'posts_per_page'   => 50,
            'paged'            => max( 1, absint( $page ) ),
            'fields'           => 'ids',
            'meta_key'         => '_email',
            'meta_value'       => sanitize_email( $email_address ),
            'suppress_filters' => true,
        ]
    );
    $data = [];
    foreach ( $ids as $id ) {
        $fields = [
            'name' => '_name', 'email' => '_email', 'phone' => '_phone', 'project_type' => '_project_type',
            'project_location' => '_project_location', 'project_area' => '_project_area', 'budget_range' => '_budget_range',
            'timeline' => '_timeline', 'message' => '_message', 'status' => '_nexora_inquiry_status',
        ];
        $item = [];
        foreach ( $fields as $name => $meta_key ) {
            $item[] = [ 'name' => $name, 'value' => (string) get_post_meta( $id, $meta_key, true ) ];
        }
        $data[] = [
            'group_id'    => 'nexora-inquiries',
            'group_label' => nexora_core_admin_lang() === 'fa' ? 'درخواست‌های نکسورا' : 'Nexora inquiries',
            'item_id'     => 'nexora-inquiry-' . (int) $id,
            'data'        => $item,
        ];
    }
    return [ 'data' => $data, 'done' => count( $ids ) < 50 ];
}

function nexora_core_privacy_erasers( $erasers ) {
    $erasers['nexora-inquiries'] = [
        'eraser_friendly_name' => nexora_core_admin_lang() === 'fa' ? 'درخواست‌های نکسورا' : 'Nexora inquiries',
        'callback'             => 'nexora_core_privacy_eraser',
    ];
    return $erasers;
}
add_filter( 'wp_privacy_personal_data_erasers', 'nexora_core_privacy_erasers' );

function nexora_core_privacy_eraser( $email_address, $page = 1 ) {
    $ids = get_posts(
        [
            'post_type'        => 'nexora_inquiry',
            'post_status'      => [ 'private', 'publish', 'draft', 'trash' ],
            'posts_per_page'   => 50,
            // Always erase the first remaining batch. Paging a shrinking result set can skip records.
            'paged'            => 1,
            'fields'           => 'ids',
            'meta_key'         => '_email',
            'meta_value'       => sanitize_email( $email_address ),
            'suppress_filters' => true,
        ]
    );
    $removed = false;
    foreach ( $ids as $id ) {
        $removed = (bool) wp_delete_post( (int) $id, true ) || $removed;
    }
    return [
        'items_removed'  => $removed,
        'items_retained' => false,
        'messages'       => [],
        'done'           => count( $ids ) < 50,
    ];
}
