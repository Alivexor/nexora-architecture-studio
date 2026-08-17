<?php
/**
 * Plugin Name: Nexora Core
 * Description: Content models, bilingual demo data, inquiries, media tools, SEO, brand settings and setup tools for Nexora.
 * Version: 11.0.0
 * Author: Ali.D
 * Text Domain: nexora-core
 * Requires at least: 6.4
 * Requires PHP: 8.1
 * License: GPL-2.0-or-later
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'NEXORA_CORE_VERSION', '11.0.0' );
define( 'NEXORA_CORE_DB_VERSION', '11' );
define( 'NEXORA_CORE_DIR', plugin_dir_path( __FILE__ ) );
define( 'NEXORA_CORE_URL', plugin_dir_url( __FILE__ ) );

$includes = [
    'helpers',
    'security-privacy',
    'post-types',
    'localized-routes',
    'meta-boxes',
    'brand-settings',
    'contact',
    'search',
    'seo',
    'demo-data',
    'demo-setup',
    'admin-dashboard',
    'requirements',
];

foreach ( $includes as $file ) {
    require_once NEXORA_CORE_DIR . 'includes/' . $file . '.php';
}

function nexora_core_activate() {
    nexora_core_install_capabilities();
    nexora_core_schedule_cleanup();
    nexora_core_register_content();
    nexora_core_localized_rewrite_rules();
    nexora_core_run_migrations();
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'nexora_core_activate' );

function nexora_core_deactivate() {
    nexora_core_unschedule_cleanup();
    flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'nexora_core_deactivate' );

add_action( 'plugins_loaded', 'nexora_core_run_migrations', 5 );
