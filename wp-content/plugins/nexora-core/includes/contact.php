<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function nexora_core_form_messages( $lang = '' ) {
    $lang = in_array( $lang, [ 'fa', 'en' ], true ) ? $lang : nexora_core_front_lang();
    $t = static fn( $fa, $en ) => nexora_core_lang_t( $lang, $fa, $en );
    return [
        'saved_mailed'       => $t( 'درخواست شما ثبت شد و برای ارسال ایمیل به سیستم تحویل داده شد. به‌زودی با شما تماس می‌گیریم.', 'Your inquiry was saved and handed to the mail system. We will get back to you soon.' ),
        'saved_mail_failed'  => $t( 'درخواست شما ذخیره شد، اما ارسال ایمیل ناموفق بود. تیم سایت همچنان آن را در پنل مدیریت می‌بیند.', 'Your inquiry was saved, but email sending failed. The site team can still see it in the dashboard.' ),
        'save_failed_mailed' => $t( 'ایمیل درخواست برای تیم تحویل داده شد، اما ذخیره آن در پنل ناموفق بود. در صورت نیاز دوباره با ما تماس بگیرید.', 'The inquiry was handed to the mail system, but saving it to the dashboard failed. Please contact us again if needed.' ),
        'both_failed'        => $t( 'درخواست ارسال نشد. لطفاً چند دقیقه بعد دوباره تلاش کنید یا از راه ایمیل با ما تماس بگیرید.', 'The inquiry could not be submitted. Please try again in a few minutes or contact us by email.' ),
        'rate'               => $t( 'تعداد درخواست‌های اخیر زیاد است. حدود ۱۵ دقیقه بعد دوباره تلاش کنید.', 'There have been several recent submissions. Please try again in about 15 minutes.' ),
        'spam'               => $t( 'درخواست توسط فیلتر ضداسپم رد شد.', 'The request was rejected by the anti-spam filter.' ),
        'captcha'            => $t( 'پاسخ بررسی انسانی درست نیست. دوباره تلاش کنید.', 'The human-verification answer is incorrect. Please try again.' ),
        'form_error'         => $t( 'لطفاً خطاهای مشخص‌شده در فرم را اصلاح کنید.', 'Please correct the highlighted form fields.' ),
        'security'           => $t( 'نشست فرم منقضی یا تکراری است. صفحه را تازه کنید و دوباره تلاش کنید.', 'The form session expired or was already used. Refresh the page and try again.' ),
    ];
}

function nexora_core_contact_state() {
    $token = isset( $_GET['nexora_form'] ) ? sanitize_key( wp_unslash( $_GET['nexora_form'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- opaque transient token only.
    if ( ! $token || ! preg_match( '/^[a-f0-9]{24}$/', $token ) ) {
        return [ 'data' => [], 'errors' => [] ];
    }
    $state = get_transient( 'nexora_form_' . $token );
    delete_transient( 'nexora_form_' . $token );
    return is_array( $state ) ? wp_parse_args( $state, [ 'data' => [], 'errors' => [] ] ) : [ 'data' => [], 'errors' => [] ];
}

function nexora_core_contact_value( $data, $key ) {
    return isset( $data[ $key ] ) ? (string) $data[ $key ] : '';
}

function nexora_core_contact_error( $errors, $key ) {
    if ( empty( $errors[ $key ] ) ) {
        return;
    }
    printf( '<span id="nexora-error-%1$s" class="field-error" role="alert">%2$s</span>', esc_attr( $key ), esc_html( $errors[ $key ] ) );
}

function nexora_core_contact_aria( $errors, $key ) {
    if ( empty( $errors[ $key ] ) ) {
        return '';
    }
    return ' aria-invalid="true" aria-describedby="nexora-error-' . esc_attr( $key ) . '"';
}

function nexora_core_contact_url( $lang = '' ) {
    $lang  = in_array( $lang, [ 'fa', 'en' ], true ) ? $lang : nexora_core_front_lang();
    $pages = get_option( 'nexora_demo_pages', [] );
    if ( isset( $pages['contact'][ $lang ] ) && get_post_status( (int) $pages['contact'][ $lang ] ) ) {
        return get_permalink( (int) $pages['contact'][ $lang ] );
    }
    return home_url( '/' );
}

function nexora_core_contact_shortcode() {
    $lang     = nexora_core_front_lang();
    $status   = isset( $_GET['nexora_contact'] ) ? sanitize_key( wp_unslash( $_GET['nexora_contact'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- display state only.
    $state    = nexora_core_contact_state();
    $data     = $state['data'];
    $errors   = $state['errors'];
    $brand    = nexora_core_brand();
    $messages = nexora_core_form_messages( $lang );

    $form_instance = substr( hash( 'sha256', wp_generate_uuid4() . microtime( true ) ), 0, 32 );
    set_transient( 'nexora_form_instance_' . $form_instance, '1', 30 * MINUTE_IN_SECONDS );

    $human_token = '';
    $a = 0;
    $b = 0;
    if ( '1' === $brand['captcha_enabled'] ) {
        $a = random_int( 2, 8 );
        $b = random_int( 1, 7 );
        $human_token = substr( hash( 'sha256', wp_generate_uuid4() . microtime( true ) . random_int( 1, PHP_INT_MAX ) ), 0, 32 );
        set_transient( 'nexora_human_' . $human_token, [ 'answer' => $a + $b, 'lang' => $lang ], 30 * MINUTE_IN_SECONDS );
    }

    ob_start();
    if ( isset( $messages[ $status ] ) ) {
        $success = in_array( $status, [ 'saved_mailed', 'saved_mail_failed', 'save_failed_mailed' ], true );
        printf( '<div class="nexora-form-status %1$s" role="status">%2$s</div>', esc_attr( $success ? 'success' : 'error' ), esc_html( $messages[ $status ] ) );
    }
    ?>
    <form class="nexora-contact-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" data-contact-form>
        <input type="hidden" name="action" value="nexora_contact_submit">
        <input type="hidden" name="form_lang" value="<?php echo esc_attr( $lang ); ?>">
        <input type="hidden" name="form_instance" value="<?php echo esc_attr( $form_instance ); ?>">
        <?php if ( $human_token ) : ?><input type="hidden" name="human_token" value="<?php echo esc_attr( $human_token ); ?>"><?php endif; ?>
        <?php wp_nonce_field( 'nexora_contact_submit', 'nexora_contact_nonce' ); ?>

        <div class="nexora-hp" aria-hidden="true">
            <label><?php echo esc_html( nexora_core_lang_t( $lang, 'وب‌سایت شرکت', 'Company website' ) ); ?><input type="text" tabindex="-1" autocomplete="off" name="company_website" value=""></label>
        </div>

        <label for="nexora-name">
            <?php echo esc_html( nexora_core_lang_t( $lang, 'نام و نام خانوادگی', 'Full name' ) ); ?>
            <input id="nexora-name" type="text" name="name" required minlength="2" maxlength="120" autocomplete="name" value="<?php echo esc_attr( nexora_core_contact_value( $data, 'name' ) ); ?>"<?php echo nexora_core_contact_aria( $errors, 'name' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> >
            <?php nexora_core_contact_error( $errors, 'name' ); ?>
        </label>

        <label for="nexora-email">
            <?php echo esc_html( nexora_core_lang_t( $lang, 'ایمیل', 'Email' ) ); ?>
            <input id="nexora-email" type="email" name="email" required maxlength="190" autocomplete="email" value="<?php echo esc_attr( nexora_core_contact_value( $data, 'email' ) ); ?>"<?php echo nexora_core_contact_aria( $errors, 'email' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> >
            <?php nexora_core_contact_error( $errors, 'email' ); ?>
        </label>

        <label for="nexora-phone">
            <?php echo esc_html( nexora_core_lang_t( $lang, 'شماره تماس', 'Phone' ) ); ?>
            <input id="nexora-phone" type="tel" name="phone" maxlength="40" autocomplete="tel" value="<?php echo esc_attr( nexora_core_contact_value( $data, 'phone' ) ); ?>">
        </label>

        <label for="nexora-project-type">
            <?php echo esc_html( nexora_core_lang_t( $lang, 'نوع پروژه', 'Project type' ) ); ?>
            <?php $project_type = nexora_core_contact_value( $data, 'project_type' ); ?>
            <select id="nexora-project-type" name="project_type" required<?php echo nexora_core_contact_aria( $errors, 'project_type' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> >
                <option value=""><?php echo esc_html( nexora_core_t( 'select_project_type', 'front' ) ); ?></option>
                <?php foreach ( nexora_core_project_types() as $slug => $labels ) : ?>
                    <option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $project_type, $slug ); ?>><?php echo esc_html( $labels[ $lang ] ); ?></option>
                <?php endforeach; ?>
            </select>
            <?php nexora_core_contact_error( $errors, 'project_type' ); ?>
        </label>

        <label for="nexora-project-location">
            <?php echo esc_html( nexora_core_lang_t( $lang, 'موقعیت پروژه', 'Project location' ) ); ?>
            <input id="nexora-project-location" type="text" name="project_location" maxlength="180" value="<?php echo esc_attr( nexora_core_contact_value( $data, 'project_location' ) ); ?>">
        </label>

        <label for="nexora-project-area">
            <?php echo esc_html( nexora_core_lang_t( $lang, 'متراژ تقریبی', 'Approx. area' ) ); ?>
            <input id="nexora-project-area" type="text" name="project_area" maxlength="80" placeholder="<?php echo esc_attr( nexora_core_lang_t( $lang, 'مثلاً ۴۲۰ مترمربع', 'e.g. 420 m²' ) ); ?>" value="<?php echo esc_attr( nexora_core_contact_value( $data, 'project_area' ) ); ?>">
        </label>

        <label for="nexora-budget">
            <?php echo esc_html( nexora_core_lang_t( $lang, 'محدوده بودجه', 'Budget range' ) ); ?>
            <input id="nexora-budget" type="text" name="budget_range" maxlength="120" value="<?php echo esc_attr( nexora_core_contact_value( $data, 'budget_range' ) ); ?>">
        </label>

        <label for="nexora-timeline">
            <?php echo esc_html( nexora_core_lang_t( $lang, 'زمان تقریبی شروع', 'Approx. start' ) ); ?>
            <input id="nexora-timeline" type="text" name="timeline" maxlength="120" value="<?php echo esc_attr( nexora_core_contact_value( $data, 'timeline' ) ); ?>">
        </label>

        <label class="full" for="nexora-message">
            <?php echo esc_html( nexora_core_lang_t( $lang, 'درباره پروژه بگویید', 'Tell us about the project' ) ); ?>
            <textarea id="nexora-message" name="message" rows="7" minlength="20" maxlength="5000" required<?php echo nexora_core_contact_aria( $errors, 'message' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_textarea( nexora_core_contact_value( $data, 'message' ) ); ?></textarea>
            <?php nexora_core_contact_error( $errors, 'message' ); ?>
        </label>

        <?php if ( $human_token ) : ?>
            <label class="full compact" for="nexora-human-answer">
                <?php printf( esc_html( nexora_core_lang_t( $lang, 'بررسی انسانی: حاصل %1$d + %2$d ؟', 'Human check: %1$d + %2$d ?' ) ), (int) $a, (int) $b ); ?>
                <input id="nexora-human-answer" type="number" name="human_answer" required inputmode="numeric" min="0" max="99"<?php echo nexora_core_contact_aria( $errors, 'human_answer' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> >
                <?php nexora_core_contact_error( $errors, 'human_answer' ); ?>
            </label>
        <?php endif; ?>

        <label class="full compact nexora-consent" for="nexora-privacy-consent">
            <input id="nexora-privacy-consent" type="checkbox" name="privacy_consent" value="1" required <?php checked( nexora_core_contact_value( $data, 'privacy_consent' ), '1' ); ?><?php echo nexora_core_contact_aria( $errors, 'privacy_consent' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> >
            <span><?php echo esc_html( nexora_core_t( 'privacy_consent', 'front' ) ); ?></span>
            <?php nexora_core_contact_error( $errors, 'privacy_consent' ); ?>
        </label>

        <button class="button button-primary magnetic" type="submit" data-submit-label="<?php echo esc_attr( nexora_core_lang_t( $lang, 'ارسال درخواست ↗', 'Send inquiry ↗' ) ); ?>" data-loading-label="<?php echo esc_attr( nexora_core_lang_t( $lang, 'در حال ارسال…', 'Sending…' ) ); ?>"><?php echo esc_html( nexora_core_lang_t( $lang, 'ارسال درخواست ↗', 'Send inquiry ↗' ) ); ?></button>
    </form>
    <?php
    return ob_get_clean();
}
add_shortcode( 'nexora_contact_form', 'nexora_core_contact_shortcode' );

function nexora_core_contact_redirect_with_state( $redirect, $status, $data = [], $errors = [] ) {
    $args = [ 'nexora_contact' => sanitize_key( $status ) ];
    if ( $data || $errors ) {
        $token = substr( hash( 'sha256', wp_generate_uuid4() . microtime( true ) ), 0, 24 );
        set_transient( 'nexora_form_' . $token, [ 'data' => $data, 'errors' => $errors ], 10 * MINUTE_IN_SECONDS );
        $args['nexora_form'] = $token;
    }
    wp_safe_redirect( add_query_arg( $args, remove_query_arg( [ 'nexora_contact', 'nexora_form' ], $redirect ) ) );
    exit;
}

function nexora_core_contact_submit() {
    $form_lang = isset( $_POST['form_lang'] ) ? sanitize_key( wp_unslash( $_POST['form_lang'] ) ) : 'fa';
    $form_lang = in_array( $form_lang, [ 'fa', 'en' ], true ) ? $form_lang : 'fa';
    $fallback  = nexora_core_contact_url( $form_lang );
    $redirect  = wp_validate_redirect( wp_get_referer() ?: '', $fallback );

    $nonce = isset( $_POST['nexora_contact_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nexora_contact_nonce'] ) ) : '';
    if ( ! $nonce || ! wp_verify_nonce( $nonce, 'nexora_contact_submit' ) ) {
        nexora_core_contact_redirect_with_state( $redirect, 'security' );
    }

    $instance = isset( $_POST['form_instance'] ) ? sanitize_key( wp_unslash( $_POST['form_instance'] ) ) : '';
    if ( ! $instance || '1' !== get_transient( 'nexora_form_instance_' . $instance ) ) {
        nexora_core_contact_redirect_with_state( $redirect, 'security' );
    }
    delete_transient( 'nexora_form_instance_' . $instance );

    $honeypot = isset( $_POST['company_website'] ) ? trim( (string) wp_unslash( $_POST['company_website'] ) ) : '';
    if ( '' !== $honeypot ) {
        nexora_core_contact_redirect_with_state( $redirect, 'spam' );
    }

    $data = [
        'name'             => sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) ),
        'email'            => sanitize_email( wp_unslash( $_POST['email'] ?? '' ) ),
        'phone'            => sanitize_text_field( wp_unslash( $_POST['phone'] ?? '' ) ),
        'project_type'     => sanitize_key( wp_unslash( $_POST['project_type'] ?? '' ) ),
        'project_location' => sanitize_text_field( wp_unslash( $_POST['project_location'] ?? '' ) ),
        'project_area'     => sanitize_text_field( wp_unslash( $_POST['project_area'] ?? '' ) ),
        'budget_range'     => sanitize_text_field( wp_unslash( $_POST['budget_range'] ?? '' ) ),
        'timeline'         => sanitize_text_field( wp_unslash( $_POST['timeline'] ?? '' ) ),
        'message'          => sanitize_textarea_field( wp_unslash( $_POST['message'] ?? '' ) ),
        'privacy_consent'  => isset( $_POST['privacy_consent'] ) && '1' === (string) wp_unslash( $_POST['privacy_consent'] ) ? '1' : '',
        'form_lang'        => $form_lang,
    ];

    $errors = [];
    if ( nexora_core_strlen( $data['name'] ) < 2 || nexora_core_strlen( $data['name'] ) > 120 ) {
        $errors['name'] = nexora_core_lang_t( $form_lang, 'نام را کامل وارد کنید.', 'Enter your full name.' );
    }
    if ( ! is_email( $data['email'] ) || strlen( $data['email'] ) > 190 ) {
        $errors['email'] = nexora_core_lang_t( $form_lang, 'یک ایمیل معتبر وارد کنید.', 'Enter a valid email address.' );
    }
    if ( ! array_key_exists( $data['project_type'], nexora_core_project_types() ) ) {
        $errors['project_type'] = nexora_core_lang_t( $form_lang, 'نوع پروژه را انتخاب کنید.', 'Select a project type.' );
    }
    if ( nexora_core_strlen( $data['message'] ) < 20 || nexora_core_strlen( $data['message'] ) > 5000 ) {
        $errors['message'] = nexora_core_lang_t( $form_lang, 'لطفاً بین ۲۰ تا ۵۰۰۰ نویسه درباره پروژه بنویسید.', 'Please write between 20 and 5000 characters about the project.' );
    }
    if ( '1' !== $data['privacy_consent'] ) {
        $errors['privacy_consent'] = nexora_core_lang_t( $form_lang, 'موافقت با نگهداری اطلاعات برای پیگیری درخواست ضروری است.', 'Consent to store the inquiry details is required.' );
    }

    $brand = nexora_core_brand();
    if ( '1' === $brand['captcha_enabled'] ) {
        $human_token = isset( $_POST['human_token'] ) ? sanitize_key( wp_unslash( $_POST['human_token'] ) ) : '';
        $challenge   = $human_token ? get_transient( 'nexora_human_' . $human_token ) : false;
        if ( $human_token ) {
            delete_transient( 'nexora_human_' . $human_token );
        }
        $answer = isset( $_POST['human_answer'] ) ? absint( $_POST['human_answer'] ) : -1;
        if ( ! is_array( $challenge ) || ! isset( $challenge['answer'] ) || $answer !== (int) $challenge['answer'] ) {
            $errors['human_answer'] = nexora_core_lang_t( $form_lang, 'پاسخ درست نیست یا بررسی انسانی منقضی شده است.', 'The answer is incorrect or the human check expired.' );
        }
    }

    if ( $errors ) {
        nexora_core_contact_redirect_with_state( $redirect, isset( $errors['human_answer'] ) && 1 === count( $errors ) ? 'captcha' : 'form_error', $data, $errors );
    }

    $ip       = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'unknown';
    $ip_hash  = hash_hmac( 'sha256', $ip, wp_salt( 'auth' ) );
    $mail_key = hash_hmac( 'sha256', strtolower( $data['email'] ), wp_salt( 'auth' ) );
    $rate_key = 'nexora_rate_' . substr( hash( 'sha256', $ip_hash . ':' . $mail_key ), 0, 24 );
    $count    = (int) get_transient( $rate_key );
    $limit    = max( 1, (int) apply_filters( 'nexora_contact_rate_limit', 4 ) );
    if ( $count >= $limit ) {
        nexora_core_contact_redirect_with_state( $redirect, 'rate', $data );
    }
    set_transient( $rate_key, $count + 1, 15 * MINUTE_IN_SECONDS );

    $post_id = wp_insert_post(
        [
            'post_type'   => 'nexora_inquiry',
            'post_status' => 'private',
            'post_title'  => $data['name'] . ' — ' . wp_date( 'Y-m-d H:i' ),
        ],
        true
    );
    $saved = ! is_wp_error( $post_id );
    if ( $saved ) {
        $meta = [
            '_name' => $data['name'], '_email' => $data['email'], '_phone' => $data['phone'], '_project_type' => $data['project_type'],
            '_project_location' => $data['project_location'], '_project_area' => $data['project_area'], '_budget_range' => $data['budget_range'],
            '_timeline' => $data['timeline'], '_message' => $data['message'], '_ip_hash' => $ip_hash, '_nexora_inquiry_status' => 'new',
            '_form_language' => $data['form_lang'], '_privacy_consent' => '1', '_privacy_consent_at' => gmdate( 'c' ), '_submitted_at' => gmdate( 'c' ),
        ];
        foreach ( $meta as $key => $value ) {
            update_post_meta( $post_id, $key, $value );
        }
    }

    $admin_email = sanitize_email( $brand['email'] ?? '' );
    if ( ! $admin_email ) {
        $admin_email = sanitize_email( get_option( 'admin_email' ) );
    }
    $type_label  = nexora_core_project_type_label( $data['project_type'], $form_lang );
    $subject     = sprintf(
        '[Nexora] %s — %s',
        nexora_core_lang_t( $form_lang, 'درخواست جدید', 'New inquiry' ),
        $data['name']
    );
    $body        = implode( "\n", [
        'Name / نام: ' . $data['name'], 'Email / ایمیل: ' . $data['email'], 'Phone / تلفن: ' . $data['phone'],
        'Project / پروژه: ' . $type_label, 'Location / موقعیت: ' . $data['project_location'], 'Area / متراژ: ' . $data['project_area'],
        'Budget / بودجه: ' . $data['budget_range'], 'Timeline / زمان: ' . $data['timeline'], 'Language / زبان: ' . strtoupper( $data['form_lang'] ), '', $data['message'],
    ] );
    $headers = [ 'Reply-To: ' . $data['name'] . ' <' . $data['email'] . '>' ];
    $mailed  = $admin_email ? (bool) wp_mail( $admin_email, $subject, $body, $headers ) : false;
    if ( $saved ) {
        update_post_meta( $post_id, '_mail_status', $mailed ? 'accepted-by-mailer' : 'failed' );
    }

    if ( $saved && $mailed ) {
        $status = 'saved_mailed';
    } elseif ( $saved ) {
        $status = 'saved_mail_failed';
    } elseif ( $mailed ) {
        $status = 'save_failed_mailed';
    } else {
        $status = 'both_failed';
    }
    nexora_core_contact_redirect_with_state( $redirect, $status );
}
add_action( 'admin_post_nopriv_nexora_contact_submit', 'nexora_core_contact_submit' );
add_action( 'admin_post_nexora_contact_submit', 'nexora_core_contact_submit' );
