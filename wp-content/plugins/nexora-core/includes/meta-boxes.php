<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function nexora_core_add_meta_boxes() {
    add_meta_box(
        'nexora_project_details',
        nexora_core_t( 'project_details' ),
        'nexora_core_project_details_cb',
        'nexora_project',
        'normal',
        'high'
    );

    add_meta_box(
        'nexora_project_gallery',
        nexora_core_t( 'gallery' ),
        'nexora_core_gallery_cb',
        'nexora_project',
        'normal',
        'high'
    );

    add_meta_box(
        'nexora_project_media',
        nexora_core_t( 'media' ),
        'nexora_core_project_media_cb',
        'nexora_project',
        'side',
        'default'
    );

    add_meta_box(
        'nexora_inquiry_details',
        nexora_core_t( 'inquiry' ),
        'nexora_core_inquiry_cb',
        'nexora_inquiry',
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes', 'nexora_core_add_meta_boxes' );

function nexora_core_project_fields() {
    $fa = nexora_core_admin_lang() === 'fa';
    return [
        '_nexora_location'    => [ 'label' => $fa ? 'موقعیت' : 'Location', 'type' => 'text' ],
        '_nexora_area'        => [ 'label' => $fa ? 'متراژ' : 'Area', 'type' => 'text' ],
        '_nexora_year'        => [ 'label' => $fa ? 'سال' : 'Year', 'type' => 'text' ],
        '_nexora_client'      => [ 'label' => $fa ? 'کارفرما' : 'Client', 'type' => 'text' ],
        '_nexora_architect'   => [ 'label' => $fa ? 'معمار مسئول' : 'Lead architect', 'type' => 'text' ],
        '_nexora_status'      => [ 'label' => $fa ? 'وضعیت پروژه' : 'Project status', 'type' => 'text' ],
        '_nexora_budget'      => [ 'label' => $fa ? 'سطح بودجه' : 'Budget range', 'type' => 'text' ],
        '_nexora_duration'    => [ 'label' => $fa ? 'مدت پروژه' : 'Duration', 'type' => 'text' ],
        '_nexora_awards'      => [ 'label' => $fa ? 'انتخاب‌ها / افتخارات دمو' : 'Demo recognition', 'type' => 'textarea' ],
        '_nexora_materials'   => [ 'label' => $fa ? 'مصالح' : 'Materials', 'type' => 'textarea' ],
        '_nexora_context'     => [ 'label' => $fa ? 'زمینه پروژه' : 'Context', 'type' => 'textarea' ],
        '_nexora_brief'       => [ 'label' => $fa ? 'خواسته کارفرما' : 'Client brief', 'type' => 'textarea' ],
        '_nexora_concept'     => [ 'label' => $fa ? 'ایده' : 'Concept', 'type' => 'textarea' ],
        '_nexora_challenge'   => [ 'label' => $fa ? 'چالش' : 'Challenge', 'type' => 'textarea' ],
        '_nexora_solution'    => [ 'label' => $fa ? 'راهکار' : 'Solution', 'type' => 'textarea' ],
        '_nexora_environment' => [ 'label' => $fa ? 'راهبرد اقلیمی' : 'Environmental strategy', 'type' => 'textarea' ],
        '_nexora_lighting'    => [ 'label' => $fa ? 'راهبرد نور' : 'Lighting strategy', 'type' => 'textarea' ],
        '_nexora_timeline'    => [ 'label' => $fa ? 'روند اجرا' : 'Timeline', 'type' => 'textarea' ],
        '_nexora_credits'     => [ 'label' => $fa ? 'عوامل پروژه' : 'Credits', 'type' => 'textarea' ],
    ];
}

function nexora_core_project_details_cb( $post ) {
    wp_nonce_field( 'nexora_project_save', 'nexora_project_nonce' );
    echo '<div class="nexora-admin-grid">';

    foreach ( nexora_core_project_fields() as $key => $field ) {
        $value   = get_post_meta( $post->ID, $key, true );
        $is_long = 'textarea' === $field['type'];
        printf(
            '<label class="%1$s"><strong>%2$s</strong>',
            esc_attr( $is_long ? 'wide' : '' ),
            esc_html( $field['label'] )
        );

        if ( $is_long ) {
            printf(
                '<textarea name="%1$s" rows="4">%2$s</textarea>',
                esc_attr( $key ),
                esc_textarea( $value )
            );
        } else {
            printf(
                '<input type="text" name="%1$s" value="%2$s">',
                esc_attr( $key ),
                esc_attr( $value )
            );
        }

        echo '</label>';
    }

    echo '</div>';
}

function nexora_core_gallery_cb( $post ) {
    $ids = array_values(
        array_filter(
            array_map(
                'absint',
                explode( ',', (string) get_post_meta( $post->ID, '_nexora_gallery', true ) )
            )
        )
    );

    wp_nonce_field( 'nexora_gallery_save', 'nexora_gallery_nonce' );
    printf(
        '<input type="hidden" id="nexora_gallery_ids" name="_nexora_gallery" value="%s">',
        esc_attr( implode( ',', $ids ) )
    );

    printf(
        '<button type="button" class="button button-primary" id="nexora-gallery-add">%s</button>',
        esc_html( nexora_core_t( 'add_reorder_images' ) )
    );
    printf( '<p class="description">%s</p>', esc_html( nexora_core_t( 'gallery_help' ) ) );
    echo '<ul id="nexora-gallery-list" class="nexora-gallery-admin">';

    foreach ( $ids as $id ) {
        $src = wp_get_attachment_image_url( $id, 'thumbnail' );
        if ( ! $src ) {
            continue;
        }

        printf(
            '<li data-id="%1$d"><img src="%2$s" alt=""><span class="nexora-gallery-id">#%1$d</span><button type="button" class="button-link-delete" aria-label="%3$s">×</button></li>',
            (int) $id,
            esc_url( $src ),
            esc_attr( nexora_core_t( 'remove_image' ) )
        );
    }

    echo '</ul>';
}

function nexora_core_media_picker( $post_id, $key, $label ) {
    $id      = absint( get_post_meta( $post_id, $key, true ) );
    $preview = $id ? wp_get_attachment_image_url( $id, 'medium' ) : '';
    ?>
    <div class="nexora-media-field" data-media-field>
        <strong><?php echo esc_html( $label ); ?></strong>
        <input type="hidden" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $id ); ?>" data-media-input>
        <div class="nexora-media-preview" data-media-preview>
            <?php if ( $preview ) : ?>
                <img src="<?php echo esc_url( $preview ); ?>" alt="">
            <?php endif; ?>
        </div>
        <p>
            <button type="button" class="button" data-media-select><?php echo esc_html( nexora_core_t( 'select_image' ) ); ?></button>
            <button type="button" class="button-link-delete" data-media-remove <?php echo $id ? '' : 'hidden'; ?>><?php echo esc_html( nexora_core_t( 'remove_image' ) ); ?></button>
        </p>
    </div>
    <?php
}

function nexora_core_project_media_cb( $post ) {
    nexora_core_media_picker( $post->ID, '_nexora_plan_id', nexora_core_admin_lang()==='fa'?'پلان':'Plan' );
    nexora_core_media_picker( $post->ID, '_nexora_before_id', nexora_core_admin_lang()==='fa'?'تصویر قبل':'Before image' );
    nexora_core_media_picker( $post->ID, '_nexora_after_id', nexora_core_admin_lang()==='fa'?'تصویر بعد':'After image' );
}

function nexora_core_inquiry_cb( $post ) {
    if ( ! current_user_can( 'edit_post', $post->ID ) ) {
        return;
    }
    wp_nonce_field( 'nexora_inquiry_save', 'nexora_inquiry_nonce' );

    $details = [
        '_name'             => [ 'fa' => 'نام', 'en' => 'Name' ],
        '_email'            => [ 'fa' => 'ایمیل', 'en' => 'Email' ],
        '_phone'            => [ 'fa' => 'تلفن', 'en' => 'Phone' ],
        '_project_type'     => [ 'fa' => 'نوع پروژه', 'en' => 'Project type' ],
        '_project_location' => [ 'fa' => 'موقعیت پروژه', 'en' => 'Project location' ],
        '_project_area'     => [ 'fa' => 'متراژ تقریبی', 'en' => 'Approx. area' ],
        '_budget_range'     => [ 'fa' => 'محدوده بودجه', 'en' => 'Budget range' ],
        '_timeline'         => [ 'fa' => 'زمان شروع', 'en' => 'Timeline' ],
        '_message'          => [ 'fa' => 'پیام', 'en' => 'Message' ],
        '_mail_status'      => [ 'fa' => 'وضعیت ایمیل', 'en' => 'Mail status' ],
        '_submitted_at'     => [ 'fa' => 'زمان ثبت', 'en' => 'Submitted at' ],
    ];

    $lang = nexora_core_admin_lang();
    echo '<div class="nexora-inquiry-details">';
    foreach ( $details as $key => $label ) {
        $value = (string) get_post_meta( $post->ID, $key, true );
        if ( '_project_type' === $key ) {
            $value = nexora_core_project_type_label( $value, $lang );
        }
        printf( '<p><strong>%1$s:</strong><br>%2$s</p>', esc_html( $label[ $lang ] ), nl2br( esc_html( $value ) ) );
    }
    echo '</div>';

    $status      = get_post_meta( $post->ID, '_nexora_inquiry_status', true ) ?: 'new';
    $assigned_id = absint( get_post_meta( $post->ID, '_nexora_assigned_user_id', true ) );
    $notes       = get_post_meta( $post->ID, '_nexora_internal_notes', true );
    $history     = get_post_meta( $post->ID, '_nexora_status_history', true );
    $history     = is_array( $history ) ? $history : [];
    $users       = get_users( [ 'role__in' => [ 'administrator' ], 'orderby' => 'display_name', 'order' => 'ASC' ] );
    ?>
    <hr>
    <p>
        <label for="nexora_inquiry_status"><strong><?php echo esc_html( nexora_core_t( 'status' ) ); ?></strong></label><br>
        <select id="nexora_inquiry_status" name="_nexora_inquiry_status">
            <?php foreach ( nexora_core_inquiry_statuses() as $key => $label ) : ?>
                <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $status, $key ); ?>><?php echo esc_html( $label ); ?></option>
            <?php endforeach; ?>
        </select>
    </p>
    <p>
        <label for="nexora_assigned_user_id"><strong><?php echo esc_html( nexora_core_t( 'assigned_to' ) ); ?></strong></label><br>
        <select id="nexora_assigned_user_id" name="_nexora_assigned_user_id">
            <option value="0">—</option>
            <?php foreach ( $users as $user ) : ?>
                <option value="<?php echo esc_attr( (string) $user->ID ); ?>" <?php selected( $assigned_id, $user->ID ); ?>><?php echo esc_html( $user->display_name ); ?></option>
            <?php endforeach; ?>
        </select>
    </p>
    <p>
        <label for="nexora_internal_notes"><strong><?php echo esc_html( nexora_core_t( 'notes' ) ); ?></strong></label><br>
        <textarea class="large-text" rows="6" id="nexora_internal_notes" name="_nexora_internal_notes"><?php echo esc_textarea( $notes ); ?></textarea>
    </p>
    <?php if ( $history ) : ?>
        <details>
            <summary><strong><?php echo esc_html( $lang === 'fa' ? 'تاریخچه وضعیت' : 'Status history' ); ?></strong></summary>
            <ol>
                <?php foreach ( array_reverse( $history ) as $event ) : ?>
                    <li><?php echo esc_html( sprintf( '%s: %s → %s (%s)', $event['at'] ?? '', $event['from'] ?? '', $event['to'] ?? '', $event['by_name'] ?? '' ) ); ?></li>
                <?php endforeach; ?>
            </ol>
        </details>
    <?php endif; ?>
    <?php
}

function nexora_core_valid_image_attachment( $id ) {
    $id = absint( $id );
    return $id && 'attachment' === get_post_type( $id ) && wp_attachment_is_image( $id ) ? $id : 0;
}

function nexora_core_save_project_meta( $post_id ) {
    if ( ! isset( $_POST['nexora_project_nonce'] ) ) {
        return;
    }
    $nonce = sanitize_text_field( wp_unslash( $_POST['nexora_project_nonce'] ) );
    if ( ! wp_verify_nonce( $nonce, 'nexora_project_save' ) || ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    foreach ( nexora_core_project_fields() as $key => $field ) {
        if ( ! isset( $_POST[ $key ] ) ) {
            continue;
        }
        $raw   = wp_unslash( $_POST[ $key ] );
        $value = 'textarea' === $field['type'] ? sanitize_textarea_field( $raw ) : sanitize_text_field( $raw );
        if ( '_nexora_year' === $key && $value ) {
            $year = absint( $value );
            $value = $year >= 1900 && $year <= 2200 ? (string) $year : '';
        }
        update_post_meta( $post_id, $key, $value );
    }

    foreach ( [ '_nexora_plan_id', '_nexora_before_id', '_nexora_after_id' ] as $key ) {
        if ( isset( $_POST[ $key ] ) ) {
            $id = nexora_core_valid_image_attachment( $_POST[ $key ] );
            if ( $id ) {
                update_post_meta( $post_id, $key, $id );
            } else {
                delete_post_meta( $post_id, $key );
            }
        }
    }

    if ( isset( $_POST['_nexora_gallery'] ) ) {
        $candidate_ids = array_unique( array_filter( array_map( 'absint', explode( ',', sanitize_text_field( wp_unslash( $_POST['_nexora_gallery'] ) ) ) ) ) );
        $ids = [];
        foreach ( $candidate_ids as $id ) {
            $valid = nexora_core_valid_image_attachment( $id );
            if ( $valid ) {
                $ids[] = $valid;
            }
        }
        update_post_meta( $post_id, '_nexora_gallery', implode( ',', $ids ) );
    }
}
add_action( 'save_post_nexora_project', 'nexora_core_save_project_meta' );

function nexora_core_save_inquiry_meta( $post_id ) {
    if ( ! isset( $_POST['nexora_inquiry_nonce'] ) ) {
        return;
    }
    $nonce = sanitize_text_field( wp_unslash( $_POST['nexora_inquiry_nonce'] ) );
    if ( ! wp_verify_nonce( $nonce, 'nexora_inquiry_save' ) || ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    $old_status = get_post_meta( $post_id, '_nexora_inquiry_status', true ) ?: 'new';
    $status = sanitize_key( wp_unslash( $_POST['_nexora_inquiry_status'] ?? 'new' ) );
    if ( ! array_key_exists( $status, nexora_core_inquiry_statuses() ) ) {
        $status = 'new';
    }

    $assigned_id = absint( $_POST['_nexora_assigned_user_id'] ?? 0 );
    if ( $assigned_id && ! get_user_by( 'id', $assigned_id ) ) {
        $assigned_id = 0;
    }

    update_post_meta( $post_id, '_nexora_inquiry_status', $status );
    update_post_meta( $post_id, '_nexora_assigned_user_id', $assigned_id );
    update_post_meta( $post_id, '_nexora_internal_notes', sanitize_textarea_field( wp_unslash( $_POST['_nexora_internal_notes'] ?? '' ) ) );

    if ( $status !== $old_status ) {
        $history = get_post_meta( $post_id, '_nexora_status_history', true );
        $history = is_array( $history ) ? $history : [];
        $user = wp_get_current_user();
        $history[] = [
            'at'      => gmdate( 'c' ),
            'from'    => $old_status,
            'to'      => $status,
            'by'      => (int) $user->ID,
            'by_name' => $user->display_name,
        ];
        update_post_meta( $post_id, '_nexora_status_history', array_slice( $history, -100 ) );
    }
}
add_action( 'save_post_nexora_inquiry', 'nexora_core_save_inquiry_meta' );

function nexora_core_admin_assets() {
    $screen = get_current_screen();
    if ( ! $screen ) {
        return;
    }

    $needs_assets = in_array( $screen->post_type, [ 'nexora_project', 'nexora_inquiry' ], true ) || str_contains( $screen->id, 'nexora' );
    if ( ! $needs_assets ) {
        return;
    }

    wp_enqueue_style( 'nexora-core-admin', NEXORA_CORE_URL . 'assets/admin.css', [], NEXORA_CORE_VERSION );
    wp_enqueue_script( 'nexora-core-admin', NEXORA_CORE_URL . 'assets/admin.js', [ 'jquery', 'jquery-ui-sortable' ], NEXORA_CORE_VERSION, true );

    if ( 'nexora_project' === $screen->post_type ) {
        wp_enqueue_media();
        wp_enqueue_script( 'jquery-ui-sortable' );
    }

    wp_localize_script(
        'nexora-core-admin',
        'NexoraAdmin',
        [
            'galleryTitle' => nexora_core_t( 'gallery' ),
            'mediaTitle'   => nexora_core_t( 'select_image' ),
            'removeLabel' => nexora_core_t( 'remove_image' ),
        ]
    );
}
add_action( 'admin_enqueue_scripts', 'nexora_core_admin_assets' );
