<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function nexora_core_route_base( $post_type, $lang ) {
    $routes = [
        'nexora_project' => [ 'fa' => 'پروژه‌ها', 'en' => 'projects' ],
        'nexora_service' => [ 'fa' => 'خدمات', 'en' => 'services' ],
    ];
    return $routes[ $post_type ][ $lang ] ?? '';
}

function nexora_core_language_home_url( $lang ) {
    if ( function_exists( 'pll_home_url' ) ) {
        $url = pll_home_url( $lang );
        if ( $url ) {
            return trailingslashit( $url );
        }
    }
    return trailingslashit( home_url( '/' ) );
}

function nexora_core_language_rewrite_prefix( $lang ) {
    $language_home = wp_parse_url( nexora_core_language_home_url( $lang ), PHP_URL_PATH );
    $site_home     = wp_parse_url( home_url( '/' ), PHP_URL_PATH );
    $language_home = trim( (string) $language_home, '/' );
    $site_home     = trim( (string) $site_home, '/' );
    if ( $site_home && str_starts_with( $language_home, $site_home ) ) {
        $language_home = trim( substr( $language_home, strlen( $site_home ) ), '/' );
    }
    return $language_home ? preg_quote( $language_home, '#' ) . '/' : '';
}

function nexora_core_filter_archive_link( $link, $post_type ) {
    if ( ! in_array( $post_type, [ 'nexora_project', 'nexora_service' ], true ) ) {
        return $link;
    }
    $lang = nexora_core_front_lang();
    if ( ! get_option( 'permalink_structure' ) ) {
        return add_query_arg( [ 'post_type' => $post_type, 'lang' => $lang ], home_url( '/' ) );
    }
    $base = nexora_core_route_base( $post_type, $lang );
    return $base ? user_trailingslashit( nexora_core_language_home_url( $lang ) . $base ) : $link;
}
add_filter( 'post_type_archive_link', 'nexora_core_filter_archive_link', 20, 2 );

function nexora_core_filter_post_type_link( $link, $post ) {
    if ( ! $post instanceof WP_Post || ! in_array( $post->post_type, [ 'nexora_project', 'nexora_service' ], true ) ) {
        return $link;
    }
    $lang = function_exists( 'pll_get_post_language' ) ? pll_get_post_language( $post->ID, 'slug' ) : nexora_core_front_lang();
    $lang = 'fa' === $lang ? 'fa' : 'en';
    if ( ! get_option( 'permalink_structure' ) ) {
        return add_query_arg( [ $post->post_type => $post->post_name, 'lang' => $lang ], home_url( '/' ) );
    }
    $base = nexora_core_route_base( $post->post_type, $lang );
    return $base ? user_trailingslashit( nexora_core_language_home_url( $lang ) . $base . '/' . $post->post_name ) : $link;
}
add_filter( 'post_type_link', 'nexora_core_filter_post_type_link', 20, 2 );

function nexora_core_localized_rewrite_rules() {
    foreach ( [ 'fa', 'en' ] as $lang ) {
        $prefix = nexora_core_language_rewrite_prefix( $lang );
        foreach ( [ 'nexora_project', 'nexora_service' ] as $post_type ) {
            $base = nexora_core_route_base( $post_type, $lang );
            if ( ! $base ) {
                continue;
            }
            $base_regex = preg_quote( $base, '#' );
            $query_var = 'nexora_project' === $post_type ? 'nexora_project' : 'nexora_service';
            add_rewrite_rule( '^' . $prefix . $base_regex . '/?$', 'index.php?post_type=' . $post_type . '&lang=' . $lang, 'top' );
            add_rewrite_rule( '^' . $prefix . $base_regex . '/([^/]+)/?$', 'index.php?' . $query_var . '=$matches[1]&lang=' . $lang, 'top' );
        }
    }
}
add_action( 'init', 'nexora_core_localized_rewrite_rules', 30 );
