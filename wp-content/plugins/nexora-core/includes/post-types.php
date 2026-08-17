<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function nexora_core_register_content() {
    register_post_type(
        'nexora_project',
        [
            'labels' => [
                'name'          => nexora_core_t( 'projects' ),
                'singular_name' => nexora_core_t( 'project' ),
                'add_new_item'  => nexora_core_t( 'add_project' ),
                'edit_item'     => nexora_core_t( 'edit_project' ),
            ],
            'public'       => true,
            'show_in_rest' => true,
            'menu_icon'    => 'dashicons-building',
            'has_archive'  => true,
            'rewrite'      => false,
            'supports'     => [ 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'page-attributes' ],
            'taxonomies'   => [ 'nexora_project_type' ],
        ]
    );

    register_taxonomy(
        'nexora_project_type',
        [ 'nexora_project' ],
        [
            'labels' => [
                'name'          => nexora_core_t( 'project_types' ),
                'singular_name' => nexora_core_t( 'project_type' ),
            ],
            'public'       => false,
            'show_ui'      => true,
            'show_in_rest' => true,
            'hierarchical' => true,
            'rewrite'      => false,
        ]
    );

    register_post_type(
        'nexora_service',
        [
            'labels' => [
                'name'          => nexora_core_t( 'services' ),
                'singular_name' => nexora_core_t( 'service' ),
                'add_new_item'  => nexora_core_t( 'add_service' ),
                'edit_item'     => nexora_core_t( 'edit_service' ),
            ],
            'public'       => true,
            'show_in_rest' => true,
            'menu_icon'    => 'dashicons-art',
            'has_archive'  => true,
            'rewrite'      => false,
            'supports'     => [ 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes', 'revisions' ],
        ]
    );

    foreach (
        [
            'nexora_team' => [ nexora_core_t( 'team' ), nexora_core_t( 'team_member' ), [ 'title', 'editor', 'thumbnail', 'page-attributes' ] ],
            'nexora_testimonial' => [ nexora_core_t( 'testimonials' ), nexora_core_t( 'testimonial' ), [ 'title', 'editor', 'page-attributes' ] ],
        ] as $post_type => $definition
    ) {
        register_post_type(
            $post_type,
            [
                'labels' => [ 'name' => $definition[0], 'singular_name' => $definition[1] ],
                'public'       => false,
                'show_ui'      => true,
                'show_in_menu' => 'nexora-studio',
                'show_in_rest' => false,
                'supports'     => $definition[2],
            ]
        );
    }

    register_post_type(
        'nexora_inquiry',
        [
            'labels' => [
                'name'          => nexora_core_t( 'inquiries' ),
                'singular_name' => nexora_core_t( 'inquiry' ),
            ],
            'public'              => false,
            'publicly_queryable'  => false,
            'exclude_from_search' => true,
            'show_ui'             => true,
            'show_in_menu'        => 'nexora-studio',
            'show_in_rest'        => false,
            'supports'            => [ 'title' ],
            'capability_type'     => [ 'nexora_inquiry', 'nexora_inquiries' ],
            'map_meta_cap'        => true,
            'capabilities'        => [
                'edit_post'              => 'edit_nexora_inquiry',
                'read_post'              => 'read_nexora_inquiry',
                'delete_post'            => 'delete_nexora_inquiry',
                'edit_posts'             => 'edit_nexora_inquiries',
                'edit_others_posts'      => 'edit_others_nexora_inquiries',
                'publish_posts'          => 'publish_nexora_inquiries',
                'read_private_posts'     => 'read_private_nexora_inquiries',
                'delete_posts'           => 'delete_nexora_inquiries',
                'delete_private_posts'   => 'delete_nexora_inquiries',
                'delete_published_posts' => 'delete_nexora_inquiries',
                'delete_others_posts'    => 'delete_others_nexora_inquiries',
                'edit_private_posts'     => 'edit_nexora_inquiries',
                'edit_published_posts'   => 'edit_nexora_inquiries',
                'create_posts'           => 'do_not_allow',
            ],
        ]
    );
}
add_action( 'init', 'nexora_core_register_content' );

function nexora_core_inquiry_columns( $columns ) {
    return [
        'cb'      => $columns['cb'] ?? '<input type="checkbox">',
        'title'   => nexora_core_t( 'contact' ),
        'project' => nexora_core_t( 'project_type' ),
        'status'  => nexora_core_t( 'status' ),
        'mail'    => nexora_core_t( 'mail' ),
        'date'    => $columns['date'] ?? nexora_core_t( 'date' ),
    ];
}
add_filter( 'manage_nexora_inquiry_posts_columns', 'nexora_core_inquiry_columns' );

function nexora_core_inquiry_column_value( $column, $post_id ) {
    if ( 'project' === $column ) {
        echo esc_html( nexora_core_project_type_label( get_post_meta( $post_id, '_project_type', true ), nexora_core_admin_lang() ) );
    } elseif ( 'mail' === $column ) {
        echo esc_html( get_post_meta( $post_id, '_mail_status', true ) );
    } elseif ( 'status' === $column ) {
        $status   = get_post_meta( $post_id, '_nexora_inquiry_status', true ) ?: 'new';
        $statuses = nexora_core_inquiry_statuses();
        echo esc_html( $statuses[ $status ] ?? $status );
    }
}
add_action( 'manage_nexora_inquiry_posts_custom_column', 'nexora_core_inquiry_column_value', 10, 2 );

function nexora_core_inquiry_row_actions( $actions, $post ) {
    if ( 'nexora_inquiry' !== $post->post_type ) {
        return $actions;
    }
    unset( $actions['view'], $actions['inline hide-if-no-js'] );
    return $actions;
}
add_filter( 'post_row_actions', 'nexora_core_inquiry_row_actions', 10, 2 );
