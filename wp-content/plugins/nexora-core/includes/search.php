<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function nexora_core_normalize_search_term( $term ) {
    $term = sanitize_text_field( (string) $term );
    $term = strtr( $term, [ 'ي' => 'ی', 'ى' => 'ی', 'ك' => 'ک', 'ۀ' => 'هٔ' ] );
    return nexora_core_substr( trim( $term ), 0, 80 );
}

function nexora_core_register_search_api() {
    register_rest_route(
        'nexora/v1',
        '/search-suggest',
        [
            'methods'             => WP_REST_Server::READABLE,
            'permission_callback' => '__return_true',
            'args'                => [
                'term' => [ 'required' => true, 'sanitize_callback' => 'nexora_core_normalize_search_term' ],
                'lang' => [ 'sanitize_callback' => 'sanitize_key' ],
            ],
            'callback'            => 'nexora_core_search_suggest',
        ]
    );
}
add_action( 'rest_api_init', 'nexora_core_register_search_api' );

function nexora_core_search_rate_key() {
    $ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'unknown';
    return 'nexora_search_' . substr( hash_hmac( 'sha256', $ip, wp_salt( 'auth' ) ), 0, 24 );
}

function nexora_core_search_suggest( WP_REST_Request $request ) {
    $term = nexora_core_normalize_search_term( (string) $request->get_param( 'term' ) );
    if ( nexora_core_strlen( $term ) < 2 ) {
        return rest_ensure_response( [] );
    }

    $lang = (string) $request->get_param( 'lang' );
    $lang = in_array( $lang, [ 'fa', 'en' ], true ) ? $lang : nexora_core_front_lang();

    $rate_key = nexora_core_search_rate_key();
    $count = (int) get_transient( $rate_key );
    if ( $count >= 60 ) {
        return new WP_Error(
            'nexora_search_rate',
            nexora_core_lang_t( $lang, 'تعداد جستجوها بیش از حد مجاز است. کمی بعد دوباره تلاش کنید.', 'Too many search requests. Try again shortly.' ),
            [ 'status' => 429 ]
        );
    }
    set_transient( $rate_key, $count + 1, MINUTE_IN_SECONDS );
    $cache_key = 'nexora_suggest_' . md5( $lang . '|' . nexora_core_strtolower( $term ) );
    $cached = get_transient( $cache_key );
    if ( is_array( $cached ) ) {
        return rest_ensure_response( $cached );
    }

    $args = [
        'post_type'        => [ 'nexora_project', 'nexora_service', 'post' ],
        'post_status'      => 'publish',
        'posts_per_page'   => 8,
        's'                => $term,
        'suppress_filters' => false,
        'no_found_rows'    => true,
    ];
    if ( function_exists( 'pll_get_post_language' ) ) {
        $args['lang'] = $lang;
    }

    $query = new WP_Query( $args );
    $items = [];
    foreach ( $query->posts as $post ) {
        $items[] = [
            'title' => get_the_title( $post ),
            'url'   => get_permalink( $post ),
            'type'  => $post->post_type,
        ];
    }
    wp_reset_postdata();
    set_transient( $cache_key, $items, 2 * MINUTE_IN_SECONDS );
    return rest_ensure_response( $items );
}
