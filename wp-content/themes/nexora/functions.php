<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

define( 'NEXORA_VERSION', '11.0.0' );

function nexora_setup() {
    load_theme_textdomain( 'nexora', get_template_directory() . '/languages' );
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'custom-logo', [ 'height'=>80, 'width'=>260, 'flex-height'=>true, 'flex-width'=>true ] );
    add_theme_support( 'html5', [ 'search-form','comment-form','gallery','caption','style','script' ] );
    add_theme_support( 'align-wide' );
    add_theme_support( 'responsive-embeds' );
    add_theme_support( 'automatic-feed-links' );
    register_nav_menus( [ 'primary'=>'Primary / اصلی', 'footer'=>'Footer / پاورقی' ] );
}
add_action( 'after_setup_theme', 'nexora_setup' );

function nexora_assets() {
    wp_enqueue_style( 'nexora-main', get_template_directory_uri().'/assets/css/main.css', [], NEXORA_VERSION );
    wp_enqueue_script( 'nexora-main', get_template_directory_uri().'/assets/js/main.js', [], NEXORA_VERSION, [ 'strategy'=>'defer', 'in_footer'=>true ] );
    $brand=nexora_brand();
    wp_add_inline_style( 'nexora-main', ':root{--accent:'.esc_attr($brand['accent']).';}' );
    wp_localize_script( 'nexora-main', 'NexoraFront', [
        'sending'=>nexora_t('sending'),
        'sent'=>nexora_t('send_inquiry'),
        'menu'=>nexora_t('menu'),
        'nameRequired'=>nexora_b('نام را کامل وارد کنید.','Enter your full name.'),
        'emailRequired'=>nexora_b('ایمیل را وارد کنید.','Enter your email address.'),
        'emailInvalid'=>nexora_b('یک ایمیل معتبر وارد کنید.','Enter a valid email address.'),
        'messageRequired'=>nexora_b('لطفاً حداقل چند جمله درباره پروژه بنویسید.','Please add at least a few sentences about the project.'),
        'humanRequired'=>nexora_b('پاسخ بررسی انسانی را وارد کنید.','Complete the human verification.'),
        'projectTypeRequired'=>nexora_b('نوع پروژه را انتخاب کنید.','Select a project type.'),
        'privacyRequired'=>nexora_b('موافقت با نگهداری اطلاعات برای پیگیری درخواست ضروری است.','Consent to store the inquiry details is required.'),
        'searchUrl'=>esc_url_raw(rest_url('nexora/v1/search-suggest')),
        'searchLang'=>nexora_lang(),
        'noSuggestions'=>nexora_b('پیشنهادی پیدا نشد.','No suggestions found.'),
        'searchTypes'=>[
            'nexora_project'=>nexora_t('project_results'),
            'nexora_service'=>nexora_t('service_results'),
            'post'=>nexora_t('journal_results'),
        ],
    ] );
}
add_action( 'wp_enqueue_scripts', 'nexora_assets' );

function nexora_lang() {
    if ( function_exists('pll_current_language') ) { $l=pll_current_language('slug'); if($l) return $l==='fa'?'fa':'en'; }
    if(isset($_GET['lang'])){ // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only fallback.
        $l=sanitize_key(wp_unslash($_GET['lang'])); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if(in_array($l,['fa','en'],true)) return $l;
    }
    return str_starts_with(get_locale(),'fa')?'fa':'en';
}

function nexora_strings(){return [
'nav_projects'=>['fa'=>'پروژه‌ها','en'=>'Projects'],'nav_studio'=>['fa'=>'استودیو','en'=>'Studio'],'nav_services'=>['fa'=>'خدمات','en'=>'Services'],'nav_journal'=>['fa'=>'ژورنال','en'=>'Journal'],'nav_contact'=>['fa'=>'شروع گفتگو','en'=>'Start a project'],'menu'=>['fa'=>'منو','en'=>'Menu'],'skip'=>['fa'=>'پرش به محتوا','en'=>'Skip to content'],'language'=>['fa'=>'زبان','en'=>'Language'],'primary_nav'=>['fa'=>'ناوبری اصلی','en'=>'Primary navigation'],'footer_nav'=>['fa'=>'ناوبری پاورقی','en'=>'Footer navigation'],
'eyebrow'=>['fa'=>'استودیو معماری و طراحی فضا / از ۱۳۹۲','en'=>'ARCHITECTURE & SPATIAL DESIGN / SINCE 2013'],'explore'=>['fa'=>'مشاهده پروژه‌ها','en'=>'Explore projects'],'meet'=>['fa'=>'آشنایی با استودیو','en'=>'Meet the studio'],'selected'=>['fa'=>'پروژه‌های منتخب','en'=>'Selected work'],'all_projects'=>['fa'=>'همه پروژه‌ها','en'=>'All projects'],'approach'=>['fa'=>'رویکرد ما','en'=>'Our approach'],'services'=>['fa'=>'حوزه‌های طراحی','en'=>'Design disciplines'],'all_services'=>['fa'=>'همه خدمات','en'=>'All services'],'clients'=>['fa'=>'همکاری با','en'=>'Selected clients'],'testimonials'=>['fa'=>'آنچه کارفرماها می‌گویند','en'=>'What clients say'],'new_project'=>['fa'=>'پروژه جدید','en'=>'New project'],'cta_title'=>['fa'=>'فضایی دارید که ارزش دوباره فکرکردن دارد؟','en'=>'Have a space worth rethinking?'],'cta'=>['fa'=>'درباره پروژه بگویید','en'=>'Tell us about it'],
'project_meta'=>['fa'=>'اطلاعات پروژه','en'=>'Project information'],'location'=>['fa'=>'موقعیت','en'=>'Location'],'area'=>['fa'=>'متراژ','en'=>'Area'],'year'=>['fa'=>'سال','en'=>'Year'],'client'=>['fa'=>'کارفرما','en'=>'Client'],'architect'=>['fa'=>'معمار مسئول','en'=>'Lead architect'],'status'=>['fa'=>'وضعیت','en'=>'Status'],'duration'=>['fa'=>'مدت','en'=>'Duration'],'budget'=>['fa'=>'سطح بودجه','en'=>'Budget range'],'context'=>['fa'=>'زمینه','en'=>'Context'],'brief'=>['fa'=>'خواسته کارفرما','en'=>'Client brief'],'concept'=>['fa'=>'ایده','en'=>'Concept'],'challenge'=>['fa'=>'چالش','en'=>'Challenge'],'solution'=>['fa'=>'راهکار','en'=>'Solution'],'environment'=>['fa'=>'راهبرد اقلیمی','en'=>'Environmental strategy'],'lighting'=>['fa'=>'راهبرد نور','en'=>'Lighting strategy'],'materials'=>['fa'=>'مصالح','en'=>'Materials'],'timeline'=>['fa'=>'روند پروژه','en'=>'Timeline'],'credits'=>['fa'=>'عوامل پروژه','en'=>'Credits'],'awards'=>['fa'=>'انتخاب‌ها و افتخارات دموی فرضی','en'=>'Fictional demo recognition'],'gallery'=>['fa'=>'گالری','en'=>'Gallery'],'plan'=>['fa'=>'پلان مفهومی','en'=>'Concept plan'],'next_project'=>['fa'=>'پروژه بعدی','en'=>'Next project'],'view_project'=>['fa'=>'مشاهده پروژه','en'=>'View project'],'before_after'=>['fa'=>'قبل و بعد','en'=>'Before / After'],'before'=>['fa'=>'قبل','en'=>'Before'],'after'=>['fa'=>'بعد','en'=>'After'],'compare'=>['fa'=>'مقایسه قبل و بعد','en'=>'Compare before and after'],
'filter_all'=>['fa'=>'همه','en'=>'All'],'filter_year'=>['fa'=>'سال','en'=>'Year'],'no_projects'=>['fa'=>'پروژه‌ای با این فیلتر پیدا نشد.','en'=>'No projects match this filter.'],'studio_title'=>['fa'=>'۱۳ سال طراحی با تمرکز بر چیزی که باقی می‌ماند.','en'=>'13 years designing for what lasts.'],'studio_story'=>['fa'=>'نکسورا در سال ۱۳۹۲ به‌عنوان یک استودیوی کوچک مستقل در تهران شکل گرفت. امروز تیم ما روی پروژه‌های مسکونی، محیط کار، بازسازی، منظر و طراحی سفارشی کار می‌کند؛ اما اندازه پروژه هرچه باشد، روش کار ثابت است: مسئله را دقیق بفهمیم، کمتر اما بهتر طراحی کنیم و تا اجرای آخرین جزئیات کنار پروژه بمانیم.','en'=>'Nexora began in 2013 as a small independent studio in Tehran. Today we work across homes, workplaces, adaptive reuse, landscape and bespoke objects. Whatever the scale, our method is consistent: understand the real problem, design less but better, and stay involved through the final built detail.'],'philosophy'=>['fa'=>'فلسفه','en'=>'Philosophy'],'process'=>['fa'=>'فرآیند','en'=>'Process'],'team'=>['fa'=>'تیم','en'=>'Team'],'recognition'=>['fa'=>'انتخاب‌ها و همکاری‌ها','en'=>'Recognition & collaborations'],
'contact_title'=>['fa'=>'بیایید پروژه را از یک گفت‌وگوی دقیق شروع کنیم.','en'=>'Let’s begin with a precise conversation.'],'contact_intro'=>['fa'=>'موقعیت، نوع پروژه، زمان تقریبی و چیزی که دوست دارید تغییر کند را برای ما بنویسید. لازم نیست در اولین پیام همه‌چیز قطعی باشد.','en'=>'Tell us the location, project type, rough timeline and what you hope to change. Nothing needs to be fully decided in the first message.'],'office'=>['fa'=>'دفتر','en'=>'Studio'],'hours'=>['fa'=>'ساعات پاسخ‌گویی','en'=>'Studio hours'],'social'=>['fa'=>'شبکه‌های اجتماعی','en'=>'Social'],'faq'=>['fa'=>'پرسش‌های کوتاه','en'=>'Quick questions'],'directions'=>['fa'=>'مسیر روی نقشه','en'=>'Open directions'],
'journal_title'=>['fa'=>'یادداشت‌هایی درباره فضا، نور، مصالح و فرایند ساخت.','en'=>'Notes on space, light, material and the process of making.'],'read'=>['fa'=>'مطالعه','en'=>'Read'],'min_read'=>['fa'=>'دقیقه مطالعه','en'=>'min read'],'related'=>['fa'=>'مطالب مرتبط','en'=>'Related notes'],'search'=>['fa'=>'جستجو','en'=>'Search'],'search_results'=>['fa'=>'نتایج جستجو برای','en'=>'Search results for'],'nothing'=>['fa'=>'چیزی پیدا نشد.','en'=>'Nothing found.'],'project_results'=>['fa'=>'پروژه‌ها','en'=>'Projects'],'journal_results'=>['fa'=>'ژورنال','en'=>'Journal'],'service_results'=>['fa'=>'خدمات','en'=>'Services'],'back_home'=>['fa'=>'بازگشت به خانه','en'=>'Back home'],'error_kicker'=>['fa'=>'نکسورا / فضای پیدا نشده','en'=>'NEXORA / LOST SPACE'],'error_title'=>['fa'=>'این فضا هنوز ساخته نشده.','en'=>'This space has not been built yet.'],'error_text'=>['fa'=>'آدرس ممکن است تغییر کرده باشد. می‌توانید یکی از پروژه‌های اخیر را ببینید یا به صفحه اصلی برگردید.','en'=>'The address may have changed. Explore a recent project or return to the home page.'],'page_kicker'=>['fa'=>'نکسورا / صفحه','en'=>'NEXORA / PAGE'],'category_kicker'=>['fa'=>'ژورنال / دسته‌بندی','en'=>'JOURNAL / CATEGORY'],'service_kicker'=>['fa'=>'نکسورا / حوزه طراحی','en'=>'NEXORA / DESIGN DISCIPLINE'],'share'=>['fa'=>'اشتراک','en'=>'Share'],'send_inquiry'=>['fa'=>'ارسال درخواست ↗','en'=>'Send inquiry ↗'],'sending'=>['fa'=>'در حال ارسال…','en'=>'Sending…'],
];}
function nexora_t($key){$s=nexora_strings();return $s[$key][nexora_lang()]??$key;}
function nexora_b($fa,$en){return nexora_lang()==='fa'?$fa:$en;}

function nexora_substr($value,$start,$length=null){$value=(string)$value;if(function_exists('mb_substr'))return null===$length?mb_substr($value,$start,null,'UTF-8'):mb_substr($value,$start,$length,'UTF-8');return null===$length?substr($value,$start):substr($value,$start,$length);}

function nexora_brand(){
    if(function_exists('nexora_core_brand')) return nexora_core_brand();
    return ['studio_name'=>'NEXORA','phone'=>'+98 21 0000 0000','email'=>'hello@nexora.local','address_fa'=>'تهران، منطقه ۳ — موقعیت نمایشی نمونه‌کار','address_en'=>'Tehran, District 3 — portfolio demo location','hours_fa'=>'شنبه تا چهارشنبه، ۹ تا ۱۸','hours_en'=>'Saturday–Wednesday, 09:00–18:00','instagram'=>'','linkedin'=>'','directions_url'=>'','accent'=>'#d7b57a','hero_title_fa'=>'فضاهایی برای زندگیِ آرام‌تر.','hero_title_en'=>'Spaces for a quieter life.','hero_text_fa'=>'معماری و طراحی داخلی دقیق، آرام و ماندگار.','hero_text_en'=>'Precise, calm and durable architecture and interiors.','approach_fa'=>'ما از آدم‌ها، نور و زمینه شروع می‌کنیم.','approach_en'=>'We begin with people, light and context.','stat_projects'=>'46','stat_years'=>'13','stat_cities'=>'9','stat_awards'=>'12','selected_title_fa'=>'فضاهایی که با زمان بهتر می‌شوند.','selected_title_en'=>'Spaces designed to become better with time.','services_title_fa'=>'از اولین سؤال تا آخرین جزئیات ساخته‌شده.','services_title_en'=>'From the first question to the final built detail.','clients_title_fa'=>'اعتماد، بخش جدانشدنی فرایند طراحی است.','clients_title_en'=>'Trust is part of the design process.','cta_title_fa'=>'فضایی دارید که ارزش دوباره فکرکردن دارد؟','cta_title_en'=>'Have a space worth rethinking?','clients_list'=>'SEPEHR DATA,MONO ARTS,RAVAQ HOUSE,TERRACE LAB,N01 CREATIVE,ATLAS DEVELOPMENT','footer_fa'=>'استودیوی مستقل معماری و طراحی فضا؛ تهران، ایران.','footer_en'=>'Independent architecture and spatial design studio based in Tehran, Iran.'];
}
function nexora_home_url(){return function_exists('pll_home_url')?pll_home_url(nexora_lang()):home_url('/');}
function nexora_page_url($key){$pages=get_option('nexora_demo_pages',get_option('nexora_demo_pages_v3',get_option('nexora_demo_pages_v2',[])));$id=(int)($pages[$key]['fa']??0);if($id&&function_exists('pll_get_post')){$translated=pll_get_post($id,nexora_lang());if($translated)$id=$translated;}return $id?get_permalink($id):home_url('/'.($key==='about'?'studio':$key).'/');}
function nexora_archive_url($type){$url=get_post_type_archive_link($type);return $url?:home_url('/');}
function nexora_language_switcher(){
    if(function_exists('pll_the_languages')){$langs=pll_the_languages(['raw'=>1,'hide_current'=>0]);if($langs){echo '<div class="language-switch" aria-label="'.esc_attr(nexora_t('language')).'">';foreach($langs as $l){$slug=$l['slug']==='fa'?'fa':'en';echo '<a class="'.esc_attr($l['current_lang']?'active':'').'" lang="'.esc_attr($slug).'" hreflang="'.esc_attr($slug).'" href="'.esc_url($l['url']).'">'.esc_html(strtoupper($slug)).'</a>';}echo '</div>';return;}}
    $base=remove_query_arg('lang');echo '<div class="language-switch" aria-label="'.esc_attr(nexora_t('language')).'"><a class="'.esc_attr(nexora_lang()==='fa'?'active':'').'" href="'.esc_url(add_query_arg('lang','fa',$base)).'">FA</a><a class="'.esc_attr(nexora_lang()==='en'?'active':'').'" href="'.esc_url(add_query_arg('lang','en',$base)).'">EN</a></div>';
}
function nexora_meta($key,$id=null){$id=$id?:get_the_ID();return get_post_meta($id,$key,true);}
function nexora_gallery_ids($id=null){return array_values(array_filter(array_map('absint',explode(',',(string)nexora_meta('_nexora_gallery',$id?:get_the_ID())))));}
function nexora_project_type_label($slug){$m=['residential'=>['fa'=>'مسکونی','en'=>'Residential'],'interior'=>['fa'=>'طراحی داخلی','en'=>'Interior'],'commercial'=>['fa'=>'تجاری / محیط کار','en'=>'Commercial / Workplace'],'renovation'=>['fa'=>'بازسازی','en'=>'Renovation'],'landscape'=>['fa'=>'منظر','en'=>'Landscape'],'architecture'=>['fa'=>'معماری','en'=>'Architecture'],'strategy'=>['fa'=>'استراتژی طراحی','en'=>'Design Strategy'],'furniture'=>['fa'=>'مبلمان سفارشی','en'=>'Bespoke Furniture']];if(isset($m[$slug]))return $m[$slug][nexora_lang()];$term=get_term_by('slug',$slug,'nexora_project_type');return $term&&!is_wp_error($term)?$term->name:ucwords(str_replace(['-','_'],' ',$slug));}
function nexora_reading_time($id=null){$content=wp_strip_all_tags(get_post_field('post_content',$id?:get_the_ID()));$words=nexora_lang()==='fa'?count(preg_split('/\s+/u',trim($content),-1,PREG_SPLIT_NO_EMPTY)):str_word_count($content);return max(1,(int)ceil($words/180));}
function nexora_body_class($classes){$classes[]='nexora-lang-'.nexora_lang();if(nexora_lang()==='fa')$classes[]='is-rtl';return $classes;}add_filter('body_class','nexora_body_class');
function nexora_next_project($id){
    $args=['post_type'=>'nexora_project','post_status'=>'publish','posts_per_page'=>-1,'orderby'=>['menu_order'=>'ASC','date'=>'ASC'],'order'=>'ASC','fields'=>'ids','suppress_filters'=>false];
    if(function_exists('pll_get_post_language')){$lang=pll_get_post_language($id,'slug');if($lang)$args['lang']=$lang;}
    $q=new WP_Query($args);$ids=array_map('absint',$q->posts);wp_reset_postdata();
    if(!$ids)return null;
    $index=array_search((int)$id,$ids,true);
    $next_id=$index===false?$ids[0]:$ids[($index+1)%count($ids)];
    return get_post($next_id)?:null;
}
function nexora_demo_page_template($template){if(!is_page())return $template;$pages=get_option('nexora_demo_pages',get_option('nexora_demo_pages_v3',get_option('nexora_demo_pages_v2',[])));$id=get_queried_object_id();foreach(['about'=>'page-about.php','contact'=>'page-contact.php'] as $key=>$file){$base=(int)($pages[$key]['fa']??0);$ids=[$base];if($base&&function_exists('pll_get_post')){$translated=(int)pll_get_post($base,nexora_lang());if($translated)$ids[]=$translated;}if(in_array($id,$ids,true)&&file_exists(get_template_directory().'/'.$file))return get_template_directory().'/'.$file;}return $template;}add_filter('template_include','nexora_demo_page_template',20);
function nexora_main_queries($q){if(is_admin()||!$q->is_main_query())return;if($q->is_post_type_archive('nexora_project'))$q->set('posts_per_page',24);if($q->is_post_type_archive('nexora_service')){$q->set('posts_per_page',12);$q->set('orderby','menu_order');$q->set('order','ASC');}if($q->is_search()){$q->set('post_type',['nexora_project','nexora_service','post']);$q->set('posts_per_page',12);}}add_action('pre_get_posts','nexora_main_queries');
function nexora_core_missing_notice(){if(!current_user_can('manage_options')||function_exists('nexora_core_brand'))return;echo '<div class="notice notice-warning"><p><strong>Nexora:</strong> '.esc_html(nexora_b('برای پروژه‌ها، دمو، فرم و پنل مدیریت افزونه Nexora Core را فعال کنید.','Activate Nexora Core for projects, demo content, forms and the management dashboard.')).'</p></div>';}add_action('admin_notices','nexora_core_missing_notice');
function nexora_image_alt($context='image',$index=0,$post_id=0){$post_id=$post_id?:get_the_ID();$title=get_the_title($post_id);$labels=['hero'=>['fa'=>'نمای اصلی','en'=>'main view'],'gallery'=>['fa'=>'نمای گالری','en'=>'gallery view'],'plan'=>['fa'=>'پلان مفهومی','en'=>'concept plan'],'before'=>['fa'=>'پیش از بازسازی','en'=>'before renovation'],'after'=>['fa'=>'پس از بازسازی','en'=>'after renovation']];$label=$labels[$context][nexora_lang()]??($context==='image'?nexora_b('تصویر','image'):$context);if($index)$label.=' '.(int)$index;return trim($title.' — '.$label);}
function nexora_share_links(){if(!is_singular())return;$url=rawurlencode(get_permalink());$title=rawurlencode(get_the_title());echo '<div class="share-links"><span>'.esc_html(nexora_t('share')).'</span><a target="_blank" rel="noopener noreferrer" href="https://t.me/share/url?url='.$url.'&text='.$title.'">Telegram ↗</a><a target="_blank" rel="noopener noreferrer" href="https://www.linkedin.com/sharing/share-offsite/?url='.$url.'">LinkedIn ↗</a></div>';}
function nexora_brand_markup(){
    $brand=nexora_brand();
    if(has_custom_logo()){$logo=get_custom_logo();echo str_replace('custom-logo-link','custom-logo-link brand',wp_kses_post($logo));return;}
    echo '<a class="brand" href="'.esc_url(nexora_home_url()).'" aria-label="'.esc_attr($brand['studio_name']).'"><span class="brand-mark">N</span><span class="brand-name">'.esc_html($brand['studio_name']).'</span></a>';
}
function nexora_primary_fallback(){echo '<li><a href="'.esc_url(nexora_archive_url('nexora_project')).'">'.esc_html(nexora_t('nav_projects')).'</a></li><li><a href="'.esc_url(nexora_page_url('about')).'">'.esc_html(nexora_t('nav_studio')).'</a></li><li><a href="'.esc_url(nexora_archive_url('nexora_service')).'">'.esc_html(nexora_t('nav_services')).'</a></li><li><a href="'.esc_url(nexora_page_url('journal')).'">'.esc_html(nexora_t('nav_journal')).'</a></li>';}
function nexora_footer_fallback(){nexora_primary_fallback();}
