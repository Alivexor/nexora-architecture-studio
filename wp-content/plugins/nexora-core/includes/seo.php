<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function nexora_core_has_seo_plugin() {
    return defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || defined( 'SEOPRESS_VERSION' );
}

/**
 * Nexora prints its own canonical tag when no dedicated SEO plugin is active.
 * WordPress core also prints rel_canonical() on singular requests, so remove it
 * to guarantee a single canonical tag.
 */
function nexora_core_disable_core_canonical() {
    if ( ! nexora_core_has_seo_plugin() ) {
        remove_action( 'wp_head', 'rel_canonical' );
    }
}
add_action( 'wp', 'nexora_core_disable_core_canonical', 1 );

/** Add one x-default entry to Polylang's own hreflang set instead of printing duplicates. */
function nexora_core_polylang_hreflang( $hreflangs ) {
    if ( is_array( $hreflangs ) && ! isset( $hreflangs['x-default'] ) ) {
        if ( isset( $hreflangs['fa'] ) ) {
            $hreflangs['x-default'] = $hreflangs['fa'];
        } elseif ( $hreflangs ) {
            $hreflangs['x-default'] = reset( $hreflangs );
        }
    }
    return $hreflangs;
}
add_filter( 'pll_rel_hreflang_attributes', 'nexora_core_polylang_hreflang' );

function nexora_core_meta_description() {
    $brand = nexora_core_brand();
    if ( is_front_page() ) {
        return 'fa' === nexora_core_front_lang() ? $brand['hero_text_fa'] : $brand['hero_text_en'];
    }
    if ( is_singular() ) {
        $description = get_the_excerpt();
        if ( ! $description ) {
            $description = wp_trim_words( wp_strip_all_tags( get_post_field( 'post_content', get_queried_object_id() ) ), 28 );
        }
        if ( $description ) {
            return trim( $description );
        }
        if ( is_page() ) {
            return nexora_core_front_t(
                'NEXORA؛ استودیوی فرضی معماری و طراحی فضا برای نمایش یک نمونه‌کار وردپرسی دو‌زبانه.',
                'NEXORA is a fictional architecture and spatial-design studio used to demonstrate a bilingual WordPress portfolio.'
            );
        }
    }
    if ( is_post_type_archive( 'nexora_project' ) ) {
        return nexora_core_front_t( 'مجموعه پروژه‌های معماری، داخلی، بازسازی و منظر NEXORA.', 'Architecture, interior, renovation and landscape case studies from NEXORA.' );
    }
    if ( is_post_type_archive( 'nexora_service' ) ) {
        return nexora_core_front_t( 'خدمات معماری و طراحی فضایی NEXORA.', 'Architecture and spatial-design services from NEXORA.' );
    }
    if ( is_search() ) {
        return nexora_core_front_t( 'نتایج جستجوی نکسورا برای «' . get_search_query() . '».', 'Nexora search results for “' . get_search_query() . '”.' );
    }
    $description = trim( (string) get_bloginfo( 'description' ) );
    return $description ?: ( $brand['studio_name'] . ' — Architecture & Spatial Design' );
}

function nexora_core_current_url() {
    $paged = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
    if ( $paged > 1 && ! is_singular() ) {
        return get_pagenum_link( $paged );
    }
    if ( is_singular() ) {
        return get_permalink();
    }
    if ( is_post_type_archive() ) {
        return get_post_type_archive_link( get_query_var( 'post_type' ) );
    }
    if ( is_category() ) {
        return get_category_link( get_queried_object_id() );
    }
    if ( is_home() ) {
        $page_for_posts = (int) get_option( 'page_for_posts' );
        return $page_for_posts ? get_permalink( $page_for_posts ) : home_url( '/' );
    }
    if ( is_search() ) {
        return add_query_arg( 's', get_search_query( false ), nexora_core_language_home_url( nexora_core_front_lang() ) );
    }
    $request = isset( $GLOBALS['wp']->request ) ? ltrim( (string) $GLOBALS['wp']->request, '/' ) : '';
    return home_url( '/' . $request );
}

function nexora_core_social_image() {
    $image_id = 0;
    $image    = '';
    if ( is_singular() ) {
        $image_id = get_post_thumbnail_id( get_queried_object_id() );
        $image    = $image_id ? wp_get_attachment_image_url( $image_id, 'full' ) : '';
    }
    if ( ! $image && function_exists( 'get_template_directory_uri' ) ) {
        $image = get_template_directory_uri() . '/assets/images/brand/home-hero.webp';
    }
    return [ $image, $image_id ];
}

function nexora_core_language_links() {
    if ( ! function_exists( 'pll_the_languages' ) ) {
        return [];
    }
    $links = [];
    foreach ( pll_the_languages( [ 'raw' => 1, 'hide_current' => 0 ] ) ?: [] as $language ) {
        if ( ! empty( $language['slug'] ) && ! empty( $language['url'] ) ) {
            $links[ $language['slug'] ] = $language['url'];
        }
    }
    return $links;
}

function nexora_core_seo_head() {
    if ( is_admin() || nexora_core_has_seo_plugin() ) {
        return;
    }
    $title          = wp_get_document_title();
    $description    = nexora_core_meta_description();
    $url            = nexora_core_current_url();
    [ $image, $image_id ] = nexora_core_social_image();
    $locale         = 'fa' === nexora_core_front_lang() ? 'fa_IR' : 'en_US';
    $language_links = nexora_core_language_links();
    $image_alt      = is_singular() ? get_the_title( get_queried_object_id() ) : nexora_core_brand()['studio_name'];

    echo '<meta name="description" content="' . esc_attr( $description ) . '">' . "\n";
    if ( ! is_404() && $url ) {
        echo '<link rel="canonical" href="' . esc_url( $url ) . '">' . "\n";
    }
    echo '<meta property="og:type" content="' . esc_attr( is_singular( 'post' ) ? 'article' : 'website' ) . '">' . "\n";
    echo '<meta property="og:title" content="' . esc_attr( $title ) . '">' . "\n";
    echo '<meta property="og:description" content="' . esc_attr( $description ) . '">' . "\n";
    if ( $url ) {
        echo '<meta property="og:url" content="' . esc_url( $url ) . '">' . "\n";
    }
    echo '<meta property="og:locale" content="' . esc_attr( $locale ) . '">' . "\n";
    echo '<meta property="og:locale:alternate" content="' . esc_attr( 'fa_IR' === $locale ? 'en_US' : 'fa_IR' ) . '">' . "\n";
    if ( $image ) {
        echo '<meta property="og:image" content="' . esc_url( $image ) . '">' . "\n";
        echo '<meta property="og:image:alt" content="' . esc_attr( $image_alt ) . '">' . "\n";
        $meta = $image_id ? wp_get_attachment_metadata( $image_id ) : [];
        if ( ! empty( $meta['width'] ) ) {
            echo '<meta property="og:image:width" content="' . esc_attr( (string) $meta['width'] ) . '">' . "\n";
        }
        if ( ! empty( $meta['height'] ) ) {
            echo '<meta property="og:image:height" content="' . esc_attr( (string) $meta['height'] ) . '">' . "\n";
        }
    }
    echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
    echo '<meta name="twitter:title" content="' . esc_attr( $title ) . '">' . "\n";
    echo '<meta name="twitter:description" content="' . esc_attr( $description ) . '">' . "\n";
    if ( $image ) {
        echo '<meta name="twitter:image" content="' . esc_url( $image ) . '">' . "\n";
        echo '<meta name="twitter:image:alt" content="' . esc_attr( $image_alt ) . '">' . "\n";
    }

    // Polylang prints its own hreflang links. Only provide a fallback when Polylang is unavailable.
    if ( ! function_exists( 'pll_the_languages' ) ) {
        foreach ( $language_links as $slug => $language_url ) {
            echo '<link rel="alternate" hreflang="' . esc_attr( $slug ) . '" href="' . esc_url( $language_url ) . '">' . "\n";
        }
    }
}
add_action( 'wp_head', 'nexora_core_seo_head', 4 );

function nexora_core_schema() {
    if ( is_admin() || nexora_core_has_seo_plugin() ) {
        return;
    }
    $brand            = nexora_core_brand();
    $schemas          = [];
    $lang             = nexora_core_front_lang();
    $publish_business = '1' === (string) ( $brand['business_schema_enabled'] ?? '0' );

    if ( $publish_business ) {
        $organization = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $brand['studio_name'],
            'foundingDate' => $brand['founded'],
            'url' => home_url( '/' ),
            'email' => $brand['email'],
            'telephone' => $brand['phone'],
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => 'fa' === $lang ? $brand['address_fa'] : $brand['address_en'],
                'addressLocality' => 'fa' === $lang ? 'تهران' : 'Tehran',
                'addressCountry' => 'IR',
            ],
        ];
        $same_as = array_values( array_filter( [ $brand['instagram'], $brand['linkedin'] ] ) );
        if ( $same_as ) {
            $organization['sameAs'] = $same_as;
        }
        $schemas[] = $organization;
    }

    if ( is_singular( 'nexora_project' ) ) {
        $id      = get_the_ID();
        $project = [
            '@context' => 'https://schema.org',
            '@type' => 'CreativeWork',
            'name' => get_the_title(),
            'description' => nexora_core_meta_description(),
            'dateCreated' => get_post_meta( $id, '_nexora_year', true ),
            'locationCreated' => get_post_meta( $id, '_nexora_location', true ),
            'image' => array_values( array_filter( [ get_the_post_thumbnail_url( $id, 'full' ) ] ) ),
        ];
        if ( $publish_business ) {
            $project['creator'] = [ '@type' => 'Organization', 'name' => $brand['studio_name'] ];
        }
        $schemas[] = $project;
    } elseif ( is_singular( 'post' ) ) {
        $article = [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => get_the_title(),
            'datePublished' => get_the_date( 'c' ),
            'dateModified' => get_the_modified_date( 'c' ),
        ];
        $article_image = get_the_post_thumbnail_url( get_the_ID(), 'full' );
        if ( $article_image ) {
            $article['image'] = $article_image;
        }
        if ( $publish_business ) {
            $article['author']    = [ '@type' => 'Organization', 'name' => $brand['studio_name'] ];
            $article['publisher'] = [ '@type' => 'Organization', 'name' => $brand['studio_name'] ];
        }
        $schemas[] = $article;
    }

    if ( is_singular() ) {
        $schemas[] = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                [ '@type' => 'ListItem', 'position' => 1, 'name' => 'fa' === $lang ? 'خانه' : 'Home', 'item' => nexora_core_language_home_url( $lang ) ],
                [ '@type' => 'ListItem', 'position' => 2, 'name' => get_the_title(), 'item' => get_permalink() ],
            ],
        ];
    }

    if ( is_front_page() ) {
        $home      = nexora_core_language_home_url( $lang );
        $schemas[] = [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => $brand['studio_name'],
            'url' => $home,
            'inLanguage' => 'fa' === $lang ? 'fa-IR' : 'en-US',
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => add_query_arg( 's', '{search_term_string}', $home ),
                'query-input' => 'required name=search_term_string',
            ],
        ];
    }

    foreach ( $schemas as $schema ) {
        echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
    }
}
add_action( 'wp_head', 'nexora_core_schema', 30 );

function nexora_core_robots( $robots ) {
    if ( nexora_core_has_seo_plugin() ) {
        return $robots;
    }
    if ( is_search() || is_404() ) {
        $robots['noindex'] = true;
        $robots['follow']  = true;
    }
    return $robots;
}
add_filter( 'wp_robots', 'nexora_core_robots' );
