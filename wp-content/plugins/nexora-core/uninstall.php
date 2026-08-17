<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

$role = get_role( 'administrator' );
if ( $role ) {
    foreach ( [
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
    ] as $capability ) {
        $role->remove_cap( $capability );
    }
}

$timestamp = wp_next_scheduled( 'nexora_core_daily_cleanup' );
while ( $timestamp ) {
    wp_unschedule_event( $timestamp, 'nexora_core_daily_cleanup' );
    $timestamp = wp_next_scheduled( 'nexora_core_daily_cleanup' );
}

// Content and settings are deliberately preserved on uninstall to avoid destructive data loss.
