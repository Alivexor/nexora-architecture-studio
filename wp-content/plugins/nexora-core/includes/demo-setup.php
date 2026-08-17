<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

function nexora_core_demo_page() {
    if ( ! current_user_can( 'manage_options' ) ) { return; }
    $lang = nexora_core_admin_lang();
    ?>
    <div class="wrap">
        <h1><?php echo esc_html( nexora_core_t( 'setup' ) ); ?></h1>
        <p><?php echo esc_html( nexora_core_is_polylang_ready() ? ( $lang === 'fa' ? 'این ابزار، محتوای کامل فارسی و انگلیسی NEXORA را نصب می‌کند و در اجرای دوباره فقط موارد گمشده را تعمیر می‌کند؛ ویرایش‌های شما بازنویسی نمی‌شوند.' : 'This tool installs the complete Persian and English NEXORA demo. Running it again repairs only missing demo items and never overwrites your edits.' ) : nexora_core_t( 'polylang_missing' ) ); ?></p>
        <?php if ( isset( $_GET['need_polylang'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
            <div class="notice notice-error"><p><?php echo esc_html( nexora_core_t( 'polylang_missing' ) ); ?></p></div>
        <?php endif; ?>
        <?php if ( isset( $_GET['need_languages'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
            <div class="notice notice-error"><p><?php echo esc_html( nexora_core_t( 'need_languages' ) ); ?></p></div>
        <?php endif; ?>
        <?php if ( isset( $_GET['installed'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
            <div class="notice notice-success"><p><?php echo esc_html( nexora_core_t( 'installed' ) ); ?></p></div>
        <?php endif; ?>
        <?php if ( isset( $_GET['reset'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
            <div class="notice notice-warning"><p><?php echo esc_html( nexora_core_t( 'reset_done' ) ); ?></p></div>
        <?php endif; ?>
        <?php if ( isset( $_GET['error'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
            <div class="notice notice-error"><p><?php echo esc_html( nexora_core_admin_lang() === 'fa' ? 'نصب دمو کامل نشد؛ تغییرات جدید این اجرا بازگردانده شد. گزارش خطا در transient مدیریتی ذخیره شده است.' : 'Demo installation did not complete; new changes from this run were rolled back. The error summary is stored temporarily for administrators.' ); ?></p></div>
        <?php endif; ?>

        <div style="display:flex;gap:12px;flex-wrap:wrap">
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                <input type="hidden" name="action" value="nexora_install_demo">
                <?php wp_nonce_field( 'nexora_install_demo' ); ?>
                <?php submit_button( nexora_core_t( 'install_demo' ), 'primary', 'submit', false ); ?>
            </form>
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" data-nexora-confirm="<?php echo esc_attr( nexora_core_t( 'confirm_reset' ) ); ?>">
                <input type="hidden" name="action" value="nexora_reset_demo">
                <?php wp_nonce_field( 'nexora_reset_demo' ); ?>
                <?php submit_button( nexora_core_t( 'reset_demo' ), 'delete', 'submit', false ); ?>
            </form>
        </div>
        <p class="description"><?php echo esc_html( $lang === 'fa' ? 'اجرای دوباره، ویرایش‌های موجود را دست‌نخورده نگه می‌دارد و فقط موارد گمشده را دوباره ایجاد می‌کند.' : 'Running setup again preserves existing edits and recreates only missing demo items.' ); ?></p>
    </div>
    <?php
}

function nexora_core_demo_error( $message ) {
    if ( ! isset( $GLOBALS['nexora_demo_errors'] ) || ! is_array( $GLOBALS['nexora_demo_errors'] ) ) {
        $GLOBALS['nexora_demo_errors'] = [];
    }
    $GLOBALS['nexora_demo_errors'][] = sanitize_text_field( $message );
}

function nexora_core_install_image( $relative, $key, $title, $alt = '' ) {
    $existing = get_posts( [
        'post_type' => 'attachment', 'post_status' => 'inherit', 'posts_per_page' => 1,
        'meta_key' => '_nexora_asset_key', 'meta_value' => $key, 'fields' => 'ids', 'suppress_filters' => true,
    ] );
    if ( $existing ) {
        $existing_id   = (int) $existing[0];
        $existing_file = get_attached_file( $existing_id );
        if ( wp_attachment_is_image( $existing_id ) && $existing_file && is_readable( $existing_file ) ) {
            return $existing_id;
        }
        // A partial/failed previous import must not poison future repair runs.
        wp_delete_attachment( $existing_id, true );
    }

    $src = get_template_directory() . '/assets/images/' . ltrim( $relative, '/' );
    if ( ! is_readable( $src ) ) {
        nexora_core_demo_error( 'Missing demo image: ' . $relative );
        return 0;
    }
    $contents = file_get_contents( $src ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local bundled file.
    if ( false === $contents ) {
        nexora_core_demo_error( 'Could not read demo image: ' . $relative );
        return 0;
    }
    $upload = wp_upload_bits( basename( $src ), null, $contents );
    if ( ! empty( $upload['error'] ) ) {
        nexora_core_demo_error( 'Could not upload demo image: ' . $relative );
        return 0;
    }

    $type = wp_check_filetype( $upload['file'] );
    $id = wp_insert_attachment( [
        'post_mime_type' => $type['type'] ?: 'image/webp',
        'post_title'     => sanitize_text_field( $title ),
        'post_status'    => 'inherit',
    ], $upload['file'], 0, true );
    if ( is_wp_error( $id ) ) {
        @unlink( $upload['file'] ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions.unlink_unlink
        nexora_core_demo_error( 'Could not create attachment: ' . $relative );
        return 0;
    }

    require_once ABSPATH . 'wp-admin/includes/image.php';
    $metadata = wp_generate_attachment_metadata( $id, $upload['file'] );
    if ( is_array( $metadata ) ) {
        wp_update_attachment_metadata( $id, $metadata );
    }
    update_post_meta( $id, '_nexora_demo_asset', '1' );
    update_post_meta( $id, '_nexora_asset_key', $key );
    update_post_meta( $id, '_wp_attachment_image_alt', sanitize_text_field( $alt ?: $title ) );
    return (int) $id;
}

function nexora_core_create_demo_post( $type, $key, $lang, $data, $featured = 0, $meta = [] ) {
    $unique = $key . '-' . $lang;
    $id = nexora_core_find_demo( $type, $unique );
    if ( $id ) {
        // Repair only missing/corrupt demo media and language metadata. Never overwrite administrator text edits.
        $thumb_id = (int) get_post_thumbnail_id( $id );
        if ( $featured && wp_attachment_is_image( $featured ) && ( ! $thumb_id || ! wp_attachment_is_image( $thumb_id ) ) ) {
            set_post_thumbnail( $id, $featured );
        }

        foreach ( [ '_nexora_plan_id', '_nexora_before_id', '_nexora_after_id' ] as $media_key ) {
            if ( empty( $meta[ $media_key ] ) || ! wp_attachment_is_image( (int) $meta[ $media_key ] ) ) {
                continue;
            }
            $existing_media = (int) get_post_meta( $id, $media_key, true );
            if ( ! $existing_media || ! wp_attachment_is_image( $existing_media ) ) {
                update_post_meta( $id, $media_key, (int) $meta[ $media_key ] );
            }
        }

        if ( isset( $meta['_nexora_gallery'] ) ) {
            $existing_gallery = array_values( array_filter( array_map( 'intval', explode( ',', (string) get_post_meta( $id, '_nexora_gallery', true ) ) ) ) );
            $gallery_invalid  = empty( $existing_gallery );
            foreach ( $existing_gallery as $attachment_id ) {
                if ( ! wp_attachment_is_image( $attachment_id ) ) {
                    $gallery_invalid = true;
                    break;
                }
            }
            if ( $gallery_invalid ) {
                $repair_gallery = array_values( array_filter( array_map( 'intval', explode( ',', (string) $meta['_nexora_gallery'] ) ), 'wp_attachment_is_image' ) );
                if ( $repair_gallery ) {
                    update_post_meta( $id, '_nexora_gallery', implode( ',', $repair_gallery ) );
                }
            }
        }

        if ( function_exists( 'pll_get_post_language' ) && function_exists( 'pll_set_post_language' ) ) {
            $current_lang = pll_get_post_language( $id, 'slug' );
            if ( $current_lang !== $lang ) {
                pll_set_post_language( $id, $lang );
            }
        }
        return $id;
    }

    $postarr = [
        'post_type'    => $type,
        'post_status'  => 'publish',
        'post_title'   => $data['title'] ?? '',
        'post_name'    => $data['slug'] ?? sanitize_title( $data['title'] ?? $unique ),
        'post_content' => $data['content'] ?? '',
        'post_excerpt' => $data['excerpt'] ?? '',
        'menu_order'   => (int) ( $data['order'] ?? 0 ),
    ];
    $result = wp_insert_post( $postarr, true );
    if ( is_wp_error( $result ) ) {
        nexora_core_demo_error( 'Could not create demo post: ' . $unique );
        return 0;
    }
    $id = (int) $result;
    nexora_core_mark_demo( $id, $unique );
    if ( $featured && wp_attachment_is_image( $featured ) ) {
        set_post_thumbnail( $id, $featured );
    }
    foreach ( $meta as $meta_key => $value ) {
        update_post_meta( $id, $meta_key, $value );
    }
    if ( function_exists( 'pll_set_post_language' ) ) {
        pll_set_post_language( $id, $lang );
    }
    return $id;
}

function nexora_core_link_translations( $fa, $en ) {
    if ( $fa && $en && function_exists( 'pll_save_post_translations' ) ) {
        pll_save_post_translations( [ 'fa' => $fa, 'en' => $en ] );
    }
}

function nexora_core_extended_project_meta( $project, $lang, $index ) {
    $x     = $project[ $lang ];
    $is_fa = 'fa' === $lang;

    $details = [
        1 => [
            'fa' => [
                'context' => 'زمین در لبه بافت ویلایی لواسان قرار دارد؛ جایی که دید کوه ارزشمند است اما همجواری نزدیک، حریم را به مسئله اصلی تبدیل می‌کند. اختلاف تراز طبیعی سایت حفظ شد تا خانه به‌جای نشستن روی یک سکوی مصنوعی، با باغ حرکت کند.',
                'brief' => 'خانواده چهار نفره فضایی می‌خواست که مهمانی‌های کوچک و زندگی روزمره را بدون تفکیک رسمی پشتیبانی کند. اتاق‌ها باید آرام و مستقل باشند، اما نشیمن، آشپزخانه و حیاط در طول روز پیوسته احساس شوند.',
                'environment' => 'حیاط‌های میانی به تهویه متقاطع کمک می‌کنند و سایبان‌های عمیق، تابش تند غرب را کنترل می‌کنند. آب باران برای آبیاری باغچه‌های کم‌آب‌بر جمع‌آوری می‌شود.',
                'lighting' => 'نور مخفی خطی فقط در نقاط حرکتی استفاده شده و بیشتر فضا با چراغ‌های کم‌ارتفاع و نور بازتابی روشن می‌شود تا بافت سنگ و چوب در شب غالب بماند.',
            ],
            'en' => [
                'context' => 'The site sits at the edge of Lavasan’s villa fabric, where mountain views are valuable but close neighbors make privacy the primary constraint. Natural level changes are retained so the house moves with the garden rather than sitting on an artificial platform.',
                'brief' => 'A family of four wanted a home that could support small gatherings and everyday life without formal separation. Bedrooms needed calm independence while living, kitchen and courtyard should feel continuous through the day.',
                'environment' => 'Intermediate courtyards support cross-ventilation and deep overhangs temper harsh western sun. Rainwater is collected for low-water planting.',
                'lighting' => 'Linear concealed light is limited to circulation. Most rooms rely on low-level and reflected light so stone and timber remain visually dominant at night.',
            ],
        ],
        2 => [
            'fa' => [
                'context' => 'سایت روی شیبی مرطوب در کلاردشت قرار دارد و مه، باران و دید دوردست بخشی از تجربه روزانه آن است. مسیر دسترسی از بالا وارد می‌شود و بنا باید بدون قطع خط طبیعی زمین به منظره متصل بماند.',
                'brief' => 'کارفرما ویلایی چهارفصل برای اقامت‌های کوتاه می‌خواست که نگهداری پیچیده نداشته باشد و در زمستان نیز بخش اصلی آن با مصرف انرژی کنترل‌شده قابل استفاده باشد.',
                'environment' => 'پوسته شمالی بسته‌تر و جبهه جنوبی کنترل‌شده‌تر باز شده است. بام شیبدار پنهان، آب را به مسیر جمع‌آوری هدایت می‌کند و مصالح بیرونی برای رطوبت مداوم انتخاب شده‌اند.',
                'lighting' => 'چراغ‌های خطی در لبه سقف و نور گرم در فرورفتگی‌ها، مسیرها را بدون روشن‌کردن بیش از حد محیط طبیعی مشخص می‌کنند.',
            ],
            'en' => [
                'context' => 'The site occupies a wet slope in Kelardasht where fog, rain and long views are part of daily experience. Access arrives from above, so the building had to connect to the landscape without cutting through the natural contour.',
                'brief' => 'The client wanted a low-maintenance four-season retreat for short stays, with a compact core that could remain comfortable in winter without conditioning the entire house.',
                'environment' => 'The northern envelope is more closed while controlled southern openings collect winter sun. A concealed pitched roof drains to a collection route and exterior materials are selected for persistent moisture.',
                'lighting' => 'Ceiling-edge lines and warm recessed light define movement without over-lighting the surrounding landscape.',
            ],
        ],
        3 => [
            'fa' => [
                'context' => 'فضای موجود یک آپارتمان شهری با پلان قطعه‌قطعه و نور محدود بود. ارزش پروژه در اضافه‌کردن متراژ نبود، بلکه در آزادکردن نور و ایجاد عمق دید در همان پوسته موجود تعریف شد.',
                'brief' => 'کارفرما فضای داخلی آرام و بدون نمایش متریال‌های متعدد می‌خواست؛ خانه‌ای که برای کار از خانه، مهمانی دو نفره و نگهداری مجموعه کتاب مناسب باشد.',
                'environment' => 'با حذف تیغه‌های غیرسازه‌ای، نور دو جبهه در عمق پلان پخش می‌شود. پرده‌های داخلی و سطوح مات به کنترل خیرگی کمک می‌کنند.',
                'lighting' => 'نور کاری در کتابخانه و آشپزخانه دقیق است، اما نور عمومی از سطوح سقف و دیوار بازتاب می‌شود تا کنتراست تند ایجاد نشود.',
            ],
            'en' => [
                'context' => 'The existing city apartment was fragmented and short on daylight. The project’s value came not from adding area, but from releasing light and creating visual depth within the same envelope.',
                'brief' => 'The client asked for a calm interior without a display of many materials; a home suitable for remote work, intimate dinners and a substantial book collection.',
                'environment' => 'Removing non-structural partitions allows daylight from two sides to travel deeper into the plan. Internal screens and matte surfaces control glare.',
                'lighting' => 'Task light is precise at the library and kitchen, while ambient light is reflected from ceilings and walls to avoid harsh contrast.',
            ],
        ],
        4 => [
            'fa' => [
                'context' => 'دفتر در طبقه‌ای عمیق با هسته مرکزی قرار دارد. نبود نور در بخش میانی و تفاوت نیازهای تیم‌های تمرکز و همکاری، سازمان‌دهی پلان را تعیین کرد.',
                'brief' => 'شرکت در حال رشد به محیطی نیاز داشت که ۷۰ نفر را بدون حس سالن باز بی‌هویت در خود جا دهد و امکان تغییر تیم‌ها در آینده را حفظ کند.',
                'environment' => 'گیاهان سایه‌دوست و حیاط داخلی، رطوبت و کیفیت بصری مرکز پلان را بهتر می‌کنند. سنسور حضور و تقسیم مدارهای روشنایی، مصرف ساعات کم‌تردد را کاهش می‌دهد.',
                'lighting' => 'دمای رنگ یکنواخت برای میزها و نور گرم‌تر در فضاهای مکث انتخاب شده تا ریتم کاری و اجتماعی از هم قابل تشخیص باشد.',
            ],
            'en' => [
                'context' => 'The office occupies a deep floor plate with a central core. Limited daylight in the middle and different needs for focus and collaboration drove the plan.',
                'brief' => 'A growing company needed room for 70 people without the anonymity of a single open office, while preserving the ability to reorganize teams later.',
                'environment' => 'Shade-tolerant planting and the internal courtyard improve the visual and environmental quality of the center. Occupancy sensing and zoned lighting reduce off-peak consumption.',
                'lighting' => 'Consistent task-light temperature is used at desks, with warmer light in pause spaces so work and social zones read differently.',
            ],
        ],
        5 => [
            'fa' => [
                'context' => 'خانه تاریخی جلفا طی چند دهه با الحاقات پراکنده تغییر کرده بود. پیش از طراحی، هر لایه ثبت شد تا مشخص شود چه چیزی باید حفظ، تقویت یا بدون آسیب حذف شود.',
                'brief' => 'خانواده می‌خواست بنا دوباره قابل سکونت شود اما نشانه‌های عمر ساختمان پاک نشوند. آشپزخانه و تاسیسات باید معاصر باشند، بدون آنکه فضای تاریخی به صحنه تزئینی تبدیل شود.',
                'environment' => 'حیاط دوباره به منبع اصلی تهویه و تعدیل دما تبدیل شد. اندود آهکی و مصالح تنفس‌پذیر برای مدیریت رطوبت دیوارهای قدیمی به‌کار رفتند.',
                'lighting' => 'نور جدید از کف و قاب‌های مستقل فاصله می‌گیرد و مستقیماً به بافت تاریخی متصل نمی‌شود؛ این کار مداخله را خوانا و قابل بازگشت نگه می‌دارد.',
            ],
            'en' => [
                'context' => 'The historic Jolfa house had accumulated scattered alterations over decades. Every layer was recorded before design so the team could decide what to retain, reinforce or remove without damage.',
                'brief' => 'The family wanted the building to become livable again without erasing signs of age. Kitchen and services had to be contemporary without turning the historic rooms into a decorative stage.',
                'environment' => 'The courtyard returns as the main source of ventilation and temperature moderation. Lime plaster and breathable materials help manage moisture in old walls.',
                'lighting' => 'New lighting is carried by floors and independent frames rather than fixed directly to historic surfaces, keeping interventions legible and reversible.',
            ],
        ],
        6 => [
            'fa' => [
                'context' => 'فروشگاه در یک واحد باریک تجاری شکل گرفت که ویترین طولانی اما عمق کم داشت. طراحی باید از خیابان خوانا می‌بود و در عین حال محصولات کوچک را با دقت نمایش می‌داد.',
                'brief' => 'برند خواهان فضایی بود که هم برای فروش روزمره و هم رونمایی محدود محصولات قابل تغییر باشد؛ بدون ساخت دکورهای فصلی پرهزینه.',
                'environment' => 'تجهیزات ماژولار و سطوح قابل تعویض عمر دکور را افزایش می‌دهند. نور روز نزدیک ویترین با سنسور روشنایی تکمیل می‌شود تا مصرف غیرضروری کاهش یابد.',
                'lighting' => 'ریل‌های قابل تنظیم، نور عمودی ویترین و نور مخفی قفسه‌ها سه لایه مستقل ایجاد می‌کنند و برای چیدمان‌های بعدی قابل تنظیم‌اند.',
            ],
            'en' => [
                'context' => 'The shop occupies a narrow retail unit with a long storefront but limited depth. It had to read clearly from the street while presenting small products with precision.',
                'brief' => 'The brand wanted a space that could shift between daily retail and small launches without expensive seasonal rebuilds.',
                'environment' => 'Modular fittings and replaceable surfaces extend the life of the interior. Daylight near the storefront is supplemented by sensing to reduce unnecessary artificial light.',
                'lighting' => 'Adjustable tracks, vertical storefront light and concealed shelf lighting form three independent layers that can be retuned for future displays.',
            ],
        ],
        7 => [
            'fa' => [
                'context' => 'زمین دماوند بادخیز و کم‌آب است و کیفیت اصلی آن از توالی باغ خشک، دیوارهای سنگی و دید باز به کوه می‌آید. معماری و منظر از ابتدا به‌عنوان یک پروژه واحد طراحی شدند.',
                'brief' => 'کارفرما اقامتگاهی کم‌نگهداری می‌خواست که بخش زیادی از تجربه آن بیرون از ساختمان اتفاق بیفتد و باغ در فصل‌های مختلف بدون مصرف زیاد آب کیفیت خود را حفظ کند.',
                'environment' => 'گیاهان بومی، خاک‌پوش نفوذپذیر، حوضچه جمع‌آوری باران و سایه‌اندازهای پیوسته مصرف آب و گرمای تابستان را کاهش می‌دهند.',
                'lighting' => 'نور مسیرها کم‌ارتفاع و جهت‌دار است تا آسمان شب حفظ شود. تنها درختان شاخص و ورودی اصلی نور تأکیدی دریافت می‌کنند.',
            ],
            'en' => [
                'context' => 'The Damavand site is windy and water-scarce, defined by a sequence of dry garden, stone walls and open mountain views. Architecture and landscape were designed as one project from the start.',
                'brief' => 'The client wanted a low-maintenance retreat where much of the experience happens outdoors and the garden can remain convincing across seasons without heavy irrigation.',
                'environment' => 'Native planting, permeable ground cover, rainwater collection and continuous shade reduce irrigation demand and summer heat.',
                'lighting' => 'Path lighting stays low and directional to protect the night sky. Only key trees and the main entrance receive accent light.',
            ],
        ],
        8 => [
            'fa' => [
                'context' => 'ساختمان موجود ظرفیت سازه‌ای مناسبی داشت اما نمای تجاری و فضای ورودی آن فاقد هویت بود. پروژه با کمترین تخریب، لایه جدیدی برای کاربری معاصر تعریف می‌کند.',
                'brief' => 'کارفرما می‌خواست فضا در ساعات مختلف برای جلسه، کار گروهی و رویداد کوچک تغییر کند و تجهیزات ثابت کمترین مزاحمت را ایجاد کنند.',
                'environment' => 'بازشوهای قابل کنترل و پرده‌های داخلی، بار سرمایش و خیرگی را کاهش می‌دهند. مبلمان ماژولار امکان تغییر کاربری بدون ساخت مجدد را فراهم می‌کند.',
                'lighting' => 'شبکه روشنایی با سناریوهای مستقل برای کار، ارائه و رویداد برنامه‌ریزی شده و همه منابع اصلی قابل دیمر هستند.',
            ],
            'en' => [
                'context' => 'The existing building had a sound structure but an anonymous commercial frontage and entrance. The project adds a contemporary layer with minimal demolition.',
                'brief' => 'The client needed the space to shift through the day between meetings, team work and small events, with as little fixed equipment as possible.',
                'environment' => 'Controllable openings and internal screens reduce cooling load and glare. Modular furniture allows new uses without repeated construction.',
                'lighting' => 'The lighting grid includes independent scenes for work, presentations and events, with dimming on all primary sources.',
            ],
        ],
        9 => [
            'fa' => [
                'context' => 'پروژه در ساختمانی شکل گرفت که اسکلت و پوسته آن ارزش نگهداری داشت اما تقسیمات داخلی با شیوه استفاده جدید هماهنگ نبود. بازسازی بر استفاده دوباره از بیشترین بخش ممکن تمرکز کرد.',
                'brief' => 'کارفرما خانه‌ای منعطف برای زندگی و کار می‌خواست و تأکید داشت مواد سالم موجود دور ریخته نشوند. بودجه باید بیشتر صرف تاسیسات و کیفیت فضا شود تا پوشش‌های تزئینی.',
                'environment' => 'پنجره‌ها تعمیر و درزبندی شدند، سطوح موجود تا حد ممکن حفظ شدند و تهویه طبیعی با بازکردن محورهای داخلی بهتر شد.',
                'lighting' => 'چراغ‌های جدید عمدتاً روکار و قابل دسترسی‌اند تا تعمیر آینده ساده باشد و سقف موجود کمتر تخریب شود.',
            ],
            'en' => [
                'context' => 'The project occupies a building whose structure and envelope were worth retaining, while the interior layout no longer matched contemporary use. Reuse of existing fabric became the central renovation strategy.',
                'brief' => 'The client wanted a flexible home for living and work, with a clear preference not to discard sound existing materials. Investment was directed to services and spatial quality rather than decorative finishes.',
                'environment' => 'Windows were repaired and sealed, existing finishes retained where viable, and natural ventilation improved by reopening internal sight and air paths.',
                'lighting' => 'Most new fixtures are surface-mounted and accessible, simplifying future maintenance and limiting demolition of the existing ceiling.',
            ],
        ],
        10 => [
            'fa' => [
                'context' => 'فضای کوچک شهری باید چند نقش را هم‌زمان بپذیرد و امکان پنهان‌کردن عملکردهای روزمره در زمان مهمانی را فراهم کند. محدودیت اصلی، کمبود سطح آزاد و انبار بود.',
                'brief' => 'کارفرما خواهان داخلی ساده اما گرم بود؛ با فضای کار واقعی، آشپزخانه کامل و امکان میزبانی بدون آنکه خانه همیشه شلوغ دیده شود.',
                'environment' => 'سطوح روشن و عمق کم کابینت‌های سفارشی نور را در پلان پخش می‌کنند. تجهیزات کم‌مصرف و کنترل مستقل فضاهای کوچک بار انرژی را پایین نگه می‌دارند.',
                'lighting' => 'نور خطی درون مبلمان با چراغ‌های سقفی محدود ترکیب شده تا سقف شلوغ نشود و هر عملکرد نور مخصوص خود را داشته باشد.',
            ],
            'en' => [
                'context' => 'The compact city interior had to support several roles at once and hide everyday functions when guests arrive. The main constraints were limited free floor area and storage.',
                'brief' => 'The client wanted a simple but warm interior with a real workspace, full kitchen and the ability to host without the apartment always appearing busy.',
                'environment' => 'Light surfaces and shallow custom storage spread daylight through the plan. Efficient equipment and independent controls keep energy demand modest.',
                'lighting' => 'Linear light integrated into furniture is combined with a limited number of ceiling fixtures so the ceiling remains quiet and each function has its own light.',
            ],
        ],
        11 => [
            'fa' => [
                'context' => 'باغ قدیمی بخشی از ساختار خود را از دست داده بود و مسیرهای خودرو، آبیاری پراکنده و گونه‌های ناسازگار تجربه آن را تکه‌تکه می‌کردند. پروژه از بازتعریف زمین آغاز شد، نه از افزودن ساختمان.',
                'brief' => 'کارفرما خواهان باغی قابل استفاده برای نسل‌های مختلف خانواده بود که نگهداری آن منطقی باشد و در تابستان و زمستان کیفیت فضایی متفاوت اما قابل‌قبولی ارائه دهد.',
                'environment' => 'گونه‌های بومی و مقاوم جایگزین بخش زیادی از کاشت پرمصرف شدند. آب باران و رواناب سطحی به حوضچه نفوذ و مخزن آبیاری هدایت می‌شود.',
                'lighting' => 'نور تنها مسیرهای اصلی، نشیمن بیرونی و دیوارهای سنگی را مشخص می‌کند. مناطق کاشت تا حد ممکن تاریک می‌مانند تا اکوسیستم شبانه مختل نشود.',
            ],
            'en' => [
                'context' => 'The old garden had lost much of its structure as vehicle routes, fragmented irrigation and unsuitable species divided the experience. The project began by redefining the ground rather than adding a building.',
                'brief' => 'The client wanted a garden usable by several generations, realistic to maintain and spatially convincing in both summer and winter.',
                'environment' => 'Native resilient species replace much of the water-intensive planting. Rain and surface runoff are directed to infiltration basins and an irrigation store.',
                'lighting' => 'Light is limited to primary paths, outdoor seating and selected stone walls. Planting areas remain dark wherever possible to protect the night ecology.',
            ],
        ],
    ];

    $copy = $details[ $index ][ $lang ] ?? [
        'context' => $is_fa ? 'پروژه از شرایط واقعی سایت و الگوی استفاده روزمره شکل گرفته است.' : 'The project grows from the site conditions and everyday patterns of use.',
        'brief' => $is_fa ? 'دامنه کار پس از شناخت نیازهای واقعی کارفرما و محدودیت‌های ساخت تعریف شد.' : 'The scope was defined after clarifying the client’s real needs and construction constraints.',
        'environment' => $is_fa ? 'راهبردهای اقلیمی هم‌زمان با فرم و پلان توسعه یافتند.' : 'Environmental strategies were developed alongside form and plan.',
        'lighting' => $is_fa ? 'نور مصنوعی با هدف حفظ آرامش معماری و کاهش خیرگی طراحی شد.' : 'Artificial lighting was designed to preserve spatial calm and minimize glare.',
    ];

    $credits = $is_fa
        ? 'طراحی: NEXORA Studio • مدیر پروژه: ' . $x['architect'] . ' • تصاویر و محتوای پروژه: اختصاصی و ساختگی برای دموی پورتفولیو'
        : 'Design: NEXORA Studio • Project lead: ' . $x['architect'] . ' • Project visuals and content: original fictional material created for this portfolio demo';

    return [
        '_nexora_context'     => $copy['context'],
        '_nexora_brief'       => $copy['brief'],
        '_nexora_environment' => $copy['environment'],
        '_nexora_lighting'    => $copy['lighting'],
        '_nexora_credits'     => $credits,
    ];
}

function nexora_core_backup_site_state() {
    if ( get_option( 'nexora_demo_backup' ) ) {
        return;
    }
    update_option( 'nexora_demo_backup', [
        'show_on_front'      => get_option( 'show_on_front' ),
        'page_on_front'      => (int) get_option( 'page_on_front' ),
        'page_for_posts'     => (int) get_option( 'page_for_posts' ),
        'nav_menu_locations' => get_theme_mod( 'nav_menu_locations', [] ),
    ], false );
}

function nexora_core_demo_snapshot() {
    $terms = get_terms( [
        'taxonomy' => 'nexora_project_type',
        'hide_empty' => false,
        'meta_key' => '_nexora_demo_term',
        'meta_value' => '1',
    ] );
    $term_ids = is_wp_error( $terms ) ? [] : array_map( 'intval', wp_list_pluck( $terms, 'term_id' ) );

    return [
        'posts' => array_map( 'intval', get_posts( [
            'post_type' => [ 'page', 'post', 'nexora_project', 'nexora_service', 'nexora_team', 'nexora_testimonial' ],
            'post_status' => 'any', 'posts_per_page' => -1, 'fields' => 'ids', 'meta_key' => '_nexora_demo', 'meta_value' => '1', 'suppress_filters' => true,
        ] ) ),
        'attachments' => array_map( 'intval', get_posts( [
            'post_type' => 'attachment', 'post_status' => 'inherit', 'posts_per_page' => -1, 'fields' => 'ids', 'meta_key' => '_nexora_demo_asset', 'meta_value' => '1', 'suppress_filters' => true,
        ] ) ),
        'terms' => $term_ids,
    ];
}

function nexora_core_demo_rollback_new( $before, $delete_new_attachments = true ) {
    $after = nexora_core_demo_snapshot();
    foreach ( array_diff( $after['posts'], $before['posts'] ) as $id ) {
        wp_delete_post( (int) $id, true );
    }
    if ( $delete_new_attachments ) {
        foreach ( array_diff( $after['attachments'], $before['attachments'] ) as $id ) {
            wp_delete_attachment( (int) $id, true );
        }
    }
    foreach ( array_diff( $after['terms'], $before['terms'] ) as $term_id ) {
        $term = get_term( (int) $term_id, 'nexora_project_type' );
        if ( $term && ! is_wp_error( $term ) && 0 === (int) $term->count ) {
            wp_delete_term( (int) $term_id, 'nexora_project_type' );
        }
    }
}

function nexora_core_ensure_project_type_term( $slug ) {
    $exists = term_exists( $slug, 'nexora_project_type' );
    if ( $exists ) {
        return (int) ( is_array( $exists ) ? $exists['term_id'] : $exists );
    }
    $label  = nexora_core_project_type_label( $slug, 'en' );
    $result = wp_insert_term( $label, 'nexora_project_type', [ 'slug' => $slug ] );
    if ( is_wp_error( $result ) ) {
        nexora_core_demo_error( 'Could not create project type: ' . $slug );
        return 0;
    }
    $term_id = (int) $result['term_id'];
    update_term_meta( $term_id, '_nexora_demo_term', '1' );
    return $term_id;
}

function nexora_core_install_demo() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html( nexora_core_t( 'forbidden' ) ), '', [ 'response' => 403 ] );
    }
    check_admin_referer( 'nexora_install_demo' );
    if ( ! function_exists( 'pll_set_post_language' ) ) {
        wp_safe_redirect( admin_url( 'admin.php?page=nexora-setup&need_polylang=1' ) );
        exit;
    }
    if ( ! nexora_core_is_polylang_ready() ) {
        wp_safe_redirect( admin_url( 'admin.php?page=nexora-setup&need_languages=1' ) );
        exit;
    }

    $GLOBALS['nexora_demo_errors'] = [];
    $before        = nexora_core_demo_snapshot();
    $first_install = ! get_option( 'nexora_demo_installed' );
    $had_backup    = (bool) get_option( 'nexora_demo_backup' );
    if ( $first_install ) {
        nexora_core_backup_site_state();
    }
    $data = nexora_core_demo_data();
    $pages = [];
    $page_defs = [
        'home' => [ 'fa' => [ 'title' => 'خانه', 'slug' => 'خانه', 'content' => '' ], 'en' => [ 'title' => 'Home', 'slug' => 'home', 'content' => '' ] ],
        'about' => [ 'fa' => [ 'title' => 'استودیو', 'slug' => 'استودیو', 'content' => '' ], 'en' => [ 'title' => 'Studio', 'slug' => 'studio', 'content' => '' ] ],
        'contact' => [ 'fa' => [ 'title' => 'تماس', 'slug' => 'تماس', 'content' => '' ], 'en' => [ 'title' => 'Contact', 'slug' => 'contact', 'content' => '' ] ],
        'journal' => [ 'fa' => [ 'title' => 'ژورنال', 'slug' => 'ژورنال', 'content' => '' ], 'en' => [ 'title' => 'Journal', 'slug' => 'journal', 'content' => '' ] ],
    ];
    foreach ( $page_defs as $key => $pair ) {
        $ids = [];
        foreach ( [ 'fa', 'en' ] as $lang ) {
            $ids[ $lang ] = nexora_core_create_demo_post( 'page', 'page-' . $key, $lang, $pair[ $lang ] );
        }
        nexora_core_link_translations( $ids['fa'], $ids['en'] );
        $pages[ $key ] = $ids;
    }

    $areas = [ 1 => '620 m²', 2 => '740 m²', 3 => '380 m²', 4 => '920 m²', 5 => '520 m²', 6 => '280 m²', 7 => '860 m²', 8 => '510 m²', 9 => '680 m²', 10 => '260 m²', 11 => '430 m²' ];
    $years = [ 1 => '2025', 2 => '2024', 3 => '2026', 4 => '2025', 5 => '2024', 6 => '2023', 7 => '2025', 8 => '2024', 9 => '2025', 10 => '2026', 11 => '2026' ];

    foreach ( $data['projects'] as $i => $project ) {
        $n = $i + 1;
        $hero = nexora_core_install_image( sprintf( 'projects/project-%02d-hero.webp', $n ), 'project-' . $n . '-hero', $project['en']['title'], $project['en']['title'] . ' — architectural view' );
        $gallery = [];
        for ( $g = 1; $g <= 8; ++$g ) {
            $gallery[] = nexora_core_install_image( sprintf( 'projects/project-%02d-gallery-%02d.webp', $n, $g ), 'project-' . $n . '-g' . $g, $project['en']['title'] . ' gallery ' . $g, $project['en']['title'] . ' — view ' . $g );
        }
        $plan = nexora_core_install_image( sprintf( 'projects/project-%02d-plan.webp', $n ), 'project-' . $n . '-plan', $project['en']['title'] . ' plan', $project['en']['title'] . ' conceptual plan' );
        $before_image = 0;
        $after_image  = 0;
        if ( in_array( $n, [ 5, 11 ], true ) ) {
            $before_image = nexora_core_install_image( sprintf( 'projects/project-%02d-before.webp', $n ), 'project-' . $n . '-before', $project['en']['title'] . ' before', $project['en']['title'] . ' before renovation' );
            $after_image  = nexora_core_install_image( sprintf( 'projects/project-%02d-after.webp', $n ), 'project-' . $n . '-after', $project['en']['title'] . ' after', $project['en']['title'] . ' after renovation' );
        }
        $term_id = nexora_core_ensure_project_type_term( $project['type'] );
        $ids = [];
        foreach ( [ 'fa', 'en' ] as $lang ) {
            $x = $project[ $lang ];
            $x['slug'] = 'fa' === $lang ? sanitize_title( $x['title'] ) : $project['slug'];
            $meta = [
                '_nexora_location' => $x['location'], '_nexora_area' => $areas[ $n ], '_nexora_year' => $years[ $n ], '_nexora_client' => $x['client'],
                '_nexora_architect' => $x['architect'], '_nexora_status' => $x['status'], '_nexora_budget' => $x['budget'], '_nexora_duration' => $x['duration'],
                '_nexora_awards' => $x['awards'], '_nexora_materials' => $x['materials'], '_nexora_concept' => $x['concept'], '_nexora_challenge' => $x['challenge'],
                '_nexora_solution' => $x['solution'], '_nexora_timeline' => $x['timeline'], '_nexora_gallery' => implode( ',', array_filter( $gallery ) ),
                '_nexora_plan_id' => $plan, '_nexora_before_id' => $before_image, '_nexora_after_id' => $after_image,
            ];
            $meta = array_merge( $meta, nexora_core_extended_project_meta( $project, $lang, $n ) );
            $ids[ $lang ] = nexora_core_create_demo_post( 'nexora_project', 'project-' . $project['key'], $lang, $x, $hero, $meta );
            if ( $ids[ $lang ] && $term_id ) {
                wp_set_object_terms( $ids[ $lang ], [ $term_id ], 'nexora_project_type', false );
            }
        }
        nexora_core_link_translations( $ids['fa'], $ids['en'] );
    }

    foreach ( $data['services'] as $i => $service ) {
        $image = nexora_core_install_image( sprintf( 'services/service-%02d.webp', $service['image'] ), 'service-' . $service['image'], $service['en']['title'], $service['en']['title'] . ' — original Nexora illustration' );
        $ids = [];
        foreach ( [ 'fa', 'en' ] as $lang ) {
            $x = $service[ $lang ];
            $x['slug'] = 'fa' === $lang ? sanitize_title( $x['title'] ) : $service['slug'];
            $x['order'] = $i + 1;
            $ids[ $lang ] = nexora_core_create_demo_post( 'nexora_service', 'service-' . $service['slug'], $lang, $x, $image );
        }
        nexora_core_link_translations( $ids['fa'], $ids['en'] );
    }

    foreach ( $data['journal'] as $journal ) {
        $image = nexora_core_install_image( sprintf( 'journal/journal-%02d.webp', $journal['image'] ), 'journal-' . $journal['image'], $journal['en']['title'], $journal['en']['title'] );
        $ids = [];
        foreach ( [ 'fa', 'en' ] as $lang ) {
            $x = $journal[ $lang ];
            $x['slug'] = 'fa' === $lang ? sanitize_title( $x['title'] ) : $journal['slug'];
            $ids[ $lang ] = nexora_core_create_demo_post( 'post', 'journal-' . $journal['slug'], $lang, $x, $image );
        }
        nexora_core_link_translations( $ids['fa'], $ids['en'] );
    }

    $team = [
        [ 'آرمان فرهمند', 'Arman Farahmand', 'هم‌بنیان‌گذار / معمار ارشد', 'Co-founder / Principal Architect' ],
        [ 'سارا نیازی', 'Sara Niazi', 'مدیر طراحی داخلی', 'Interior Design Director' ],
        [ 'نیما دادفر', 'Nima Dadfar', 'معمار پروژه', 'Project Architect' ],
        [ 'لیلا فروزان', 'Leila Forouzan', 'معمار منظر', 'Landscape Architect' ],
    ];
    foreach ( $team as $i => $member ) {
        $portrait = nexora_core_install_image( sprintf( 'team/team-%02d.webp', $i + 1 ), 'team-' . ( $i + 1 ), $member[1], $member[1] . ' — fictional team portrait illustration' );
        $fa = nexora_core_create_demo_post( 'nexora_team', 'team-' . $i, 'fa', [ 'title' => $member[0], 'content' => $member[2], 'order' => $i ], $portrait );
        $en = nexora_core_create_demo_post( 'nexora_team', 'team-' . $i, 'en', [ 'title' => $member[1], 'content' => $member[3], 'order' => $i ], $portrait );
        nexora_core_link_translations( $fa, $en );
    }

    $testimonials = [
        [ 'مریم نادری', 'Maryam Naderi', 'کارفرما — خانه حیاط خاموش، لواسان', 'Client — Silent Courtyard House, Lavasan', 'روند طراحی دقیق بود و هیچ تصمیمی بدون توضیح رها نشد. نتیجه نهایی از چیزی که در جلسات اولیه تصور می‌کردیم آرام‌تر و کاربردی‌تر شد.', 'The process was precise and no decision was left unexplained. The finished space is calmer and more useful than we imagined in the first meetings.' ],
        [ 'امیر کیانی', 'Amir Kiani', 'کارفرما — خانه قاب، کردان', 'Client — Frame House, Kordan', 'نکسورا محدودیت بودجه را پنهان نکرد؛ از همان ابتدا اولویت‌ها را روشن کرد و همین باعث شد کیفیت قسمت‌های مهم پروژه حفظ شود.', 'Nexora never hid the budget constraints. Priorities were clear from the start, which protected quality where it mattered most.' ],
        [ 'رضا محمودی', 'Reza Mahmoudi', 'مدیر پروژه — دفتر حیاط مرکزی، تهران', 'Project director — Courtyard Office, Tehran', 'تیم طراحی در اجرا هم حضور واقعی داشت و جزئیات روی کاغذ رها نشدند.', 'The design team stayed genuinely involved during construction, so details did not remain only on drawings.' ],
    ];
    foreach ( $testimonials as $i => $testimonial ) {
        $fa = nexora_core_create_demo_post( 'nexora_testimonial', 'test-' . $i, 'fa', [ 'title' => $testimonial[0], 'content' => $testimonial[4], 'order' => $i ], 0, [ '_testimonial_role' => $testimonial[2] ] );
        $en = nexora_core_create_demo_post( 'nexora_testimonial', 'test-' . $i, 'en', [ 'title' => $testimonial[1], 'content' => $testimonial[5], 'order' => $i ], 0, [ '_testimonial_role' => $testimonial[3] ] );
        nexora_core_link_translations( $fa, $en );
    }

    if ( ! empty( $GLOBALS['nexora_demo_errors'] ) ) {
        nexora_core_demo_rollback_new( $before, $first_install );
        if ( $first_install && ! $had_backup ) {
            delete_option( 'nexora_demo_backup' );
        }
        set_transient( 'nexora_demo_last_errors', array_slice( $GLOBALS['nexora_demo_errors'], 0, 20 ), 10 * MINUTE_IN_SECONDS );
        wp_safe_redirect( admin_url( 'admin.php?page=nexora-setup&error=1' ) );
        exit;
    }

    update_option( 'nexora_demo_pages', $pages, false );
    if ( $first_install ) {
        update_option( 'show_on_front', 'page' );
        update_option( 'page_on_front', (int) $pages['home']['fa'] );
        update_option( 'page_for_posts', (int) $pages['journal']['fa'] );
    }
    update_option( 'nexora_demo_installed', gmdate( 'c' ), false );
    flush_rewrite_rules();
    wp_safe_redirect( admin_url( 'admin.php?page=nexora-setup&installed=1' ) );
    exit;
}
add_action( 'admin_post_nexora_install_demo', 'nexora_core_install_demo' );

function nexora_core_reset_demo() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html( nexora_core_t( 'forbidden' ) ), '', [ 'response' => 403 ] );
    }
    check_admin_referer( 'nexora_reset_demo' );
    $posts = get_posts( [
        'post_type' => [ 'page', 'post', 'nexora_project', 'nexora_service', 'nexora_team', 'nexora_testimonial' ],
        'post_status' => 'any', 'posts_per_page' => -1, 'meta_key' => '_nexora_demo', 'meta_value' => '1', 'fields' => 'ids', 'suppress_filters' => true,
    ] );
    foreach ( $posts as $id ) {
        wp_delete_post( (int) $id, true );
    }
    $attachments = get_posts( [ 'post_type' => 'attachment', 'post_status' => 'inherit', 'posts_per_page' => -1, 'meta_key' => '_nexora_demo_asset', 'meta_value' => '1', 'fields' => 'ids', 'suppress_filters' => true ] );
    foreach ( $attachments as $id ) {
        wp_delete_attachment( (int) $id, true );
    }
    $terms = get_terms( [ 'taxonomy' => 'nexora_project_type', 'hide_empty' => false, 'meta_key' => '_nexora_demo_term', 'meta_value' => '1' ] );
    if ( ! is_wp_error( $terms ) ) {
        foreach ( $terms as $term ) {
            if ( 0 === (int) $term->count ) {
                wp_delete_term( (int) $term->term_id, 'nexora_project_type' );
            }
        }
    }

    $backup = get_option( 'nexora_demo_backup', [] );
    if ( is_array( $backup ) && $backup ) {
        if ( array_key_exists( 'show_on_front', $backup ) ) {
            update_option( 'show_on_front', $backup['show_on_front'] );
        }
        if ( array_key_exists( 'page_on_front', $backup ) ) {
            update_option( 'page_on_front', (int) $backup['page_on_front'] );
        }
        if ( array_key_exists( 'page_for_posts', $backup ) ) {
            update_option( 'page_for_posts', (int) $backup['page_for_posts'] );
        }
        if ( array_key_exists( 'nav_menu_locations', $backup ) ) {
            set_theme_mod( 'nav_menu_locations', (array) $backup['nav_menu_locations'] );
        }
    }
    delete_option( 'nexora_demo_backup' );
    delete_option( 'nexora_demo_pages' );
    delete_option( 'nexora_demo_installed' );
    flush_rewrite_rules();
    wp_safe_redirect( admin_url( 'admin.php?page=nexora-setup&reset=1' ) );
    exit;
}
add_action( 'admin_post_nexora_reset_demo', 'nexora_core_reset_demo' );
