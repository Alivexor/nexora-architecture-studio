<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function nexora_core_admin_lang() {
    return str_starts_with( get_user_locale(), 'fa' ) ? 'fa' : 'en';
}

function nexora_core_strlen( $value ) {
    $value = (string) $value;
    return function_exists( 'mb_strlen' ) ? mb_strlen( $value, 'UTF-8' ) : strlen( $value );
}

function nexora_core_substr( $value, $start, $length = null ) {
    $value = (string) $value;
    if ( function_exists( 'mb_substr' ) ) {
        return null === $length ? mb_substr( $value, $start, null, 'UTF-8' ) : mb_substr( $value, $start, $length, 'UTF-8' );
    }
    return null === $length ? substr( $value, $start ) : substr( $value, $start, $length );
}

function nexora_core_strtolower( $value ) {
    $value = (string) $value;
    return function_exists( 'mb_strtolower' ) ? mb_strtolower( $value, 'UTF-8' ) : strtolower( $value );
}

function nexora_core_front_lang() {
    if ( function_exists( 'pll_current_language' ) ) {
        $language = pll_current_language( 'slug' );
        if ( $language ) {
            return 'fa' === $language ? 'fa' : 'en';
        }
    }

    if ( isset( $_GET['lang'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only language fallback.
        $language = sanitize_key( wp_unslash( $_GET['lang'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if ( in_array( $language, [ 'fa', 'en' ], true ) ) {
            return $language;
        }
    }

    return str_starts_with( get_locale(), 'fa' ) ? 'fa' : 'en';
}

function nexora_core_strings() {
    return [
        'dashboard'           => [ 'fa' => 'داشبورد نکسورا', 'en' => 'Nexora Dashboard' ],
        'setup'               => [ 'fa' => 'راه‌اندازی دمو', 'en' => 'Demo Setup' ],
        'settings'            => [ 'fa' => 'تنظیمات برند', 'en' => 'Brand Settings' ],
        'projects'            => [ 'fa' => 'پروژه‌ها', 'en' => 'Projects' ],
        'project'             => [ 'fa' => 'پروژه', 'en' => 'Project' ],
        'services'            => [ 'fa' => 'خدمات', 'en' => 'Services' ],
        'service'             => [ 'fa' => 'خدمت', 'en' => 'Service' ],
        'team'                => [ 'fa' => 'تیم', 'en' => 'Team' ],
        'team_member'         => [ 'fa' => 'عضو تیم', 'en' => 'Team Member' ],
        'testimonials'        => [ 'fa' => 'نظر مشتریان', 'en' => 'Testimonials' ],
        'testimonial'         => [ 'fa' => 'نظر مشتری', 'en' => 'Testimonial' ],
        'inquiries'           => [ 'fa' => 'درخواست‌ها', 'en' => 'Inquiries' ],
        'inquiry'             => [ 'fa' => 'درخواست', 'en' => 'Inquiry' ],
        'journal'             => [ 'fa' => 'ژورنال', 'en' => 'Journal' ],
        'project_details'     => [ 'fa' => 'جزئیات حرفه‌ای پروژه', 'en' => 'Professional Project Details' ],
        'gallery'             => [ 'fa' => 'گالری پروژه', 'en' => 'Project Gallery' ],
        'media'               => [ 'fa' => 'رسانه‌های پروژه', 'en' => 'Project Media' ],
        'save'                => [ 'fa' => 'ذخیره تغییرات', 'en' => 'Save changes' ],
        'install_demo'        => [ 'fa' => 'نصب / ترمیم دموی نکسورا', 'en' => 'Install / repair Nexora demo' ],
        'reset_demo'          => [ 'fa' => 'حذف دموی نکسورا و بازیابی تنظیمات قبلی', 'en' => 'Reset Nexora demo & restore previous settings' ],
        'installed'           => [ 'fa' => 'دموی نکسورا با موفقیت نصب یا ترمیم شد.', 'en' => 'Nexora demo was installed or repaired successfully.' ],
        'reset_done'          => [ 'fa' => 'محتوای دمو حذف و تنظیمات قبلی سایت بازیابی شد.', 'en' => 'Demo content removed and previous site settings restored.' ],
        'polylang_missing'    => [ 'fa' => 'برای دموی دو‌زبانه کامل، Polylang باید فعال باشد و زبان‌های fa و en ساخته شده باشند.', 'en' => 'For the full bilingual demo, Polylang must be active with fa and en languages configured.' ],
        'need_languages'      => [ 'fa' => 'در Polylang دو زبان با slug های fa و en بسازید و دوباره نصب دمو را اجرا کنید.', 'en' => 'Create Persian and English languages in Polylang with the fa and en slugs, then run demo setup again.' ],
        'confirm_reset'       => [ 'fa' => 'فقط محتوای دموی نکسورا حذف شود و تنظیمات قبل از دمو بازیابی شوند؟', 'en' => 'Delete only Nexora demo content and restore the settings that existed before the demo?' ],
        'forbidden'           => [ 'fa' => 'شما اجازه انجام این عملیات را ندارید.', 'en' => 'You are not allowed to perform this action.' ],
        'date'                => [ 'fa' => 'تاریخ', 'en' => 'Date' ],
        'privacy_consent'     => [ 'fa' => 'با ثبت این فرم موافقم اطلاعات واردشده برای پیگیری درخواست ذخیره شود.', 'en' => 'I agree that the information entered here may be stored to follow up this inquiry.' ],
        'privacy_required'    => [ 'fa' => 'برای ارسال درخواست، موافقت با نگهداری اطلاعات ضروری است.', 'en' => 'Consent to store the inquiry details is required before submitting.' ],
        'select_project_type' => [ 'fa' => 'نوع پروژه را انتخاب کنید', 'en' => 'Select project type' ],
        'retention_days'      => [ 'fa' => 'نگهداری درخواست‌های بایگانی‌شده (روز)', 'en' => 'Archived inquiry retention (days)' ],
        'business_schema'     => [ 'fa' => 'انتشار اطلاعات شرکت در Schema', 'en' => 'Publish business details in Schema' ],
        'business_schema_help'=> [ 'fa' => 'فقط وقتی اطلاعات تماس و شبکه‌های اجتماعی واقعی هستند فعال کنید.', 'en' => 'Enable only when the contact and social details represent a real organization.' ],
        'security_failed'     => [ 'fa' => 'بررسی امنیتی ناموفق بود. صفحه را تازه کنید و دوباره تلاش کنید.', 'en' => 'Security verification failed. Refresh the page and try again.' ],
        'add_project'         => [ 'fa' => 'افزودن پروژه', 'en' => 'Add Project' ],
        'edit_project'        => [ 'fa' => 'ویرایش پروژه', 'en' => 'Edit Project' ],
        'add_service'         => [ 'fa' => 'افزودن خدمت', 'en' => 'Add Service' ],
        'edit_service'        => [ 'fa' => 'ویرایش خدمت', 'en' => 'Edit Service' ],
        'project_types'       => [ 'fa' => 'انواع پروژه', 'en' => 'Project Types' ],
        'project_type'        => [ 'fa' => 'نوع پروژه', 'en' => 'Project Type' ],
        'contact'             => [ 'fa' => 'مخاطب', 'en' => 'Contact' ],
        'mail'                => [ 'fa' => 'ایمیل', 'en' => 'Mail' ],
        'status'              => [ 'fa' => 'وضعیت', 'en' => 'Status' ],
        'notes'               => [ 'fa' => 'یادداشت داخلی', 'en' => 'Internal notes' ],
        'assigned_to'         => [ 'fa' => 'مسئول پیگیری', 'en' => 'Assigned to' ],
        'export_csv'          => [ 'fa' => 'خروجی CSV درخواست‌ها', 'en' => 'Export inquiries CSV' ],
        'quick_actions'       => [ 'fa' => 'دسترسی سریع', 'en' => 'Quick actions' ],
        'recent_inquiries'    => [ 'fa' => 'درخواست‌های اخیر', 'en' => 'Recent inquiries' ],
        'site_health'         => [ 'fa' => 'سلامت راه‌اندازی', 'en' => 'Setup health' ],
        'translation_health'  => [ 'fa' => 'کامل‌بودن ترجمه', 'en' => 'Translation completeness' ],
        'missing_images'      => [ 'fa' => 'پروژه بدون تصویر شاخص', 'en' => 'Projects missing featured images' ],
        'demo_state'          => [ 'fa' => 'وضعیت دمو', 'en' => 'Demo state' ],
        'ready'               => [ 'fa' => 'آماده', 'en' => 'Ready' ],
        'attention'           => [ 'fa' => 'نیاز به بررسی', 'en' => 'Needs attention' ],
        'new'                 => [ 'fa' => 'جدید', 'en' => 'New' ],
        'reviewed'            => [ 'fa' => 'بررسی‌شده', 'en' => 'Reviewed' ],
        'contacted'           => [ 'fa' => 'تماس گرفته شد', 'en' => 'Contacted' ],
        'qualified'           => [ 'fa' => 'واجد شرایط', 'en' => 'Qualified' ],
        'archived'            => [ 'fa' => 'بایگانی', 'en' => 'Archived' ],
        'select_image'        => [ 'fa' => 'انتخاب تصویر', 'en' => 'Select image' ],
        'remove_image'        => [ 'fa' => 'حذف انتخاب', 'en' => 'Remove selection' ],
        'add_reorder_images'  => [ 'fa' => 'افزودن / مرتب‌سازی تصاویر', 'en' => 'Add / reorder images' ],
        'gallery_help'        => [ 'fa' => 'تصاویر را از کتابخانه رسانه انتخاب کنید و برای تغییر ترتیب بکشید.', 'en' => 'Choose images from the media library, then drag thumbnails to reorder.' ],
        'captcha'             => [ 'fa' => 'بررسی انسانی داخلی', 'en' => 'Built-in human verification' ],
        'captcha_help'        => [ 'fa' => 'برای کاهش اسپم، سوال ریاضی ساده نمایش داده شود.', 'en' => 'Show a lightweight arithmetic challenge to reduce spam.' ],
        'studio_name'         => [ 'fa' => 'نام استودیو', 'en' => 'Studio name' ],
        'founded'             => [ 'fa' => 'سال تأسیس', 'en' => 'Founded' ],
        'phone'               => [ 'fa' => 'تلفن', 'en' => 'Phone' ],
        'email'               => [ 'fa' => 'ایمیل', 'en' => 'Email' ],
        'address_fa'          => [ 'fa' => 'آدرس فارسی', 'en' => 'Persian address' ],
        'address_en'          => [ 'fa' => 'آدرس انگلیسی', 'en' => 'English address' ],
        'hours_fa'            => [ 'fa' => 'ساعات کاری فارسی', 'en' => 'Persian studio hours' ],
        'hours_en'            => [ 'fa' => 'ساعات کاری انگلیسی', 'en' => 'English studio hours' ],
        'instagram'           => [ 'fa' => 'اینستاگرام', 'en' => 'Instagram' ],
        'linkedin'            => [ 'fa' => 'لینکدین', 'en' => 'LinkedIn' ],
        'directions_url'      => [ 'fa' => 'لینک مسیر واقعی (اختیاری)', 'en' => 'Real directions URL (optional)' ],
        'accent'              => [ 'fa' => 'رنگ تأکیدی', 'en' => 'Accent color' ],
        'hero_title_fa'       => [ 'fa' => 'عنوان Hero فارسی', 'en' => 'Persian hero title' ],
        'hero_title_en'       => [ 'fa' => 'عنوان Hero انگلیسی', 'en' => 'English hero title' ],
        'hero_text_fa'        => [ 'fa' => 'متن Hero فارسی', 'en' => 'Persian hero text' ],
        'hero_text_en'        => [ 'fa' => 'متن Hero انگلیسی', 'en' => 'English hero text' ],
        'approach_fa'         => [ 'fa' => 'رویکرد فارسی', 'en' => 'Persian approach' ],
        'approach_en'         => [ 'fa' => 'رویکرد انگلیسی', 'en' => 'English approach' ],
        'stat_projects'       => [ 'fa' => 'آمار پروژه‌ها', 'en' => 'Projects statistic' ],
        'stat_years'          => [ 'fa' => 'آمار سال‌های فعالیت', 'en' => 'Years statistic' ],
        'stat_cities'         => [ 'fa' => 'آمار شهرها', 'en' => 'Cities statistic' ],
        'stat_awards'         => [ 'fa' => 'آمار انتخاب‌ها / افتخارات', 'en' => 'Recognition statistic' ],
        'footer_fa'           => [ 'fa' => 'متن Footer فارسی', 'en' => 'Persian footer text' ],
        'footer_en'           => [ 'fa' => 'متن Footer انگلیسی', 'en' => 'English footer text' ],
        'selected_title_fa'   => [ 'fa' => 'عنوان پروژه‌های منتخب فارسی', 'en' => 'Persian selected-work title' ],
        'selected_title_en'   => [ 'fa' => 'عنوان پروژه‌های منتخب انگلیسی', 'en' => 'English selected-work title' ],
        'services_title_fa'   => [ 'fa' => 'عنوان خدمات فارسی', 'en' => 'Persian services title' ],
        'services_title_en'   => [ 'fa' => 'عنوان خدمات انگلیسی', 'en' => 'English services title' ],
        'clients_title_fa'    => [ 'fa' => 'عنوان مشتریان فارسی', 'en' => 'Persian clients title' ],
        'clients_title_en'    => [ 'fa' => 'عنوان مشتریان انگلیسی', 'en' => 'English clients title' ],
        'cta_title_fa'        => [ 'fa' => 'عنوان CTA فارسی', 'en' => 'Persian CTA title' ],
        'cta_title_en'        => [ 'fa' => 'عنوان CTA انگلیسی', 'en' => 'English CTA title' ],
        'clients_list'        => [ 'fa' => 'نام مشتریان، جداشده با ویرگول', 'en' => 'Client names, comma separated' ],
    ];
}

function nexora_core_t( $key, $context = 'admin' ) {
    $strings = nexora_core_strings();
    $lang    = 'front' === $context ? nexora_core_front_lang() : nexora_core_admin_lang();
    return $strings[ $key ][ $lang ] ?? $key;
}

function nexora_core_front_t( $fa, $en ) {
    return 'fa' === nexora_core_front_lang() ? $fa : $en;
}

function nexora_core_lang_t( $lang, $fa, $en ) {
    return 'fa' === $lang ? $fa : $en;
}

function nexora_core_brand() {
    $defaults = [
        'studio_name'       => 'NEXORA',
        'founded'           => '2013',
        'phone'             => '+98 21 0000 0000',
        'email'             => 'hello@nexora.local',
        'address_fa'        => 'تهران، منطقه ۳ — موقعیت نمایشی نمونه‌کار',
        'address_en'        => 'Tehran, District 3 — portfolio demo location',
        'hours_fa'          => 'شنبه تا چهارشنبه، ۹ تا ۱۸',
        'hours_en'          => 'Saturday–Wednesday, 09:00–18:00',
        'instagram'         => '',
        'linkedin'          => '',
        'directions_url'    => '',
        'accent'            => '#d7b57a',
        'hero_title_fa'     => 'فضاهایی برای زندگیِ آرام‌تر.',
        'hero_title_en'     => 'Spaces for a quieter life.',
        'hero_text_fa'      => 'از سال ۱۳۹۲، نکسورا معماری، طراحی داخلی و بازآفرینی فضا را با تمرکز بر نور، تناسب، مصالح ماندگار و تجربه واقعی زندگی پیش می‌برد.',
        'hero_text_en'      => 'Since 2013, Nexora has shaped architecture, interiors and adaptive spaces around light, proportion, durable materials and everyday life.',
        'approach_fa'       => 'ما معماری را از فرم شروع نمی‌کنیم؛ از آدم‌ها، نور، اقلیم، بودجه و عاداتی شروع می‌کنیم که قرار است سال‌ها در یک فضا جریان داشته باشند.',
        'approach_en'       => 'We do not begin with form. We begin with people, light, climate, budget and the routines a space must support for years.',
        'stat_projects'     => '46',
        'stat_years'        => '13',
        'stat_cities'       => '9',
        'stat_awards'       => '12',
        'footer_fa'         => 'استودیوی مستقل معماری و طراحی فضا؛ تهران، ایران.',
        'footer_en'         => 'Independent architecture and spatial design studio based in Tehran, Iran.',
        'selected_title_fa' => 'فضاهایی که با زمان بهتر می‌شوند.',
        'selected_title_en' => 'Spaces designed to become better with time.',
        'services_title_fa' => 'از اولین سؤال تا آخرین جزئیات ساخته‌شده.',
        'services_title_en' => 'From the first question to the final built detail.',
        'clients_title_fa'  => 'اعتماد، بخش جدانشدنی فرایند طراحی است.',
        'clients_title_en'  => 'Trust is part of the design process.',
        'cta_title_fa'      => 'فضایی دارید که ارزش دوباره فکرکردن دارد؟',
        'cta_title_en'      => 'Have a space worth rethinking?',
        'clients_list'      => 'SEPEHR DATA,MONO ARTS,RAVAQ HOUSE,TERRACE LAB,N01 CREATIVE,ATLAS DEVELOPMENT',
        'captcha_enabled'       => '1',
        'inquiry_retention_days' => '180',
        'business_schema_enabled' => '0',
    ];

    $saved = get_option( 'nexora_brand', [] );
    if ( empty( $saved ) ) {
        foreach ( [ 'nexora_brand_v3', 'nexora_brand_v2' ] as $legacy_key ) {
            $legacy = get_option( $legacy_key, [] );
            if ( is_array( $legacy ) && $legacy ) {
                $saved = $legacy;
                break;
            }
        }
    }

    return wp_parse_args( $saved, $defaults );
}

function nexora_core_meta( $post_id, $key, $default = '' ) {
    $value = get_post_meta( $post_id, $key, true );
    return '' === $value ? $default : $value;
}

function nexora_core_mark_demo( $post_id, $key ) {
    update_post_meta( $post_id, '_nexora_demo', '1' );
    update_post_meta( $post_id, '_nexora_demo_key', $key );
}

function nexora_core_find_demo( $type, $key ) {
    $posts = get_posts(
        [
            'post_type'        => $type,
            'post_status'      => 'any',
            'posts_per_page'   => 1,
            'meta_key'         => '_nexora_demo_key',
            'meta_value'       => $key,
            'fields'           => 'ids',
            'suppress_filters' => true,
        ]
    );

    return $posts ? (int) $posts[0] : 0;
}

function nexora_core_project_types() {
    return [
        'architecture' => [ 'fa' => 'معماری', 'en' => 'Architecture' ],
        'interior' => [ 'fa' => 'طراحی داخلی', 'en' => 'Interior Design' ],
        'renovation' => [ 'fa' => 'بازسازی', 'en' => 'Renovation' ],
        'strategy' => [ 'fa' => 'استراتژی طراحی', 'en' => 'Design Strategy' ],
        'landscape' => [ 'fa' => 'طراحی منظر', 'en' => 'Landscape Design' ],
        'furniture' => [ 'fa' => 'طراحی مبلمان سفارشی', 'en' => 'Bespoke Furniture' ],
    ];
}

function nexora_core_project_type_label( $slug, $lang = '' ) {
    $types = nexora_core_project_types();
    $lang  = in_array( $lang, [ 'fa', 'en' ], true ) ? $lang : nexora_core_front_lang();
    return isset( $types[ $slug ][ $lang ] ) ? $types[ $slug ][ $lang ] : (string) $slug;
}

function nexora_core_inquiry_statuses() {
    return [
        'new'       => nexora_core_t( 'new' ),
        'reviewed'  => nexora_core_t( 'reviewed' ),
        'contacted' => nexora_core_t( 'contacted' ),
        'qualified' => nexora_core_t( 'qualified' ),
        'archived'  => nexora_core_t( 'archived' ),
    ];
}

function nexora_core_is_polylang_ready() {
    if ( ! function_exists( 'pll_languages_list' ) ) {
        return false;
    }

    $languages = pll_languages_list( [ 'fields' => 'slug' ] );
    return in_array( 'fa', $languages, true ) && in_array( 'en', $languages, true );
}

function nexora_core_run_migrations() {
    $current = (string) get_option( 'nexora_core_db_version', '0' );
    if ( NEXORA_CORE_DB_VERSION === $current ) {
        return;
    }

    if ( version_compare( $current, '4', '<' ) ) {
        if ( ! get_option( 'nexora_brand' ) ) {
            foreach ( [ 'nexora_brand_v3', 'nexora_brand_v2' ] as $legacy_key ) {
                $legacy = get_option( $legacy_key, [] );
                if ( is_array( $legacy ) && $legacy ) {
                    update_option( 'nexora_brand', $legacy, false );
                    break;
                }
            }
        }
        if ( ! get_option( 'nexora_demo_pages' ) ) {
            foreach ( [ 'nexora_demo_pages_v3', 'nexora_demo_pages_v2' ] as $legacy_key ) {
                $legacy = get_option( $legacy_key, [] );
                if ( is_array( $legacy ) && $legacy ) {
                    update_option( 'nexora_demo_pages', $legacy, false );
                    break;
                }
            }
        }
        nexora_core_install_capabilities();
        nexora_core_schedule_cleanup();
    }

    update_option( 'nexora_core_db_version', NEXORA_CORE_DB_VERSION, false );
}
