<?php
get_header();
$b = nexora_brand();
?>
<section class="hero shell-wide">
    <div class="hero-grid">
        <div class="hero-copy reveal" data-reveal="up">
            <span class="eyebrow"><?php echo esc_html( nexora_t( 'eyebrow' ) ); ?></span>
            <h1><?php echo esc_html( nexora_b( $b['hero_title_fa'], $b['hero_title_en'] ) ); ?></h1>
            <p><?php echo esc_html( nexora_b( $b['hero_text_fa'], $b['hero_text_en'] ) ); ?></p>
            <div class="hero-actions">
                <a class="button button-primary magnetic" href="<?php echo esc_url( nexora_archive_url( 'nexora_project' ) ); ?>"><?php echo esc_html( nexora_t( 'explore' ) ); ?> <span aria-hidden="true">↗</span></a>
                <a class="text-link" href="<?php echo esc_url( nexora_page_url( 'about' ) ); ?>"><?php echo esc_html( nexora_t( 'meet' ) ); ?> <span aria-hidden="true">↗</span></a>
            </div>
        </div>
        <figure class="hero-image reveal" data-reveal="image">
            <img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/brand/studio-01.webp' ); ?>" alt="<?php echo esc_attr( nexora_b( 'نمای مفهومی معماری نکسورا', 'Nexora architectural concept view' ) ); ?>" fetchpriority="high">
            <span class="hero-shade" aria-hidden="true"></span>
            <figcaption class="hero-caption">
                <span><?php echo esc_html( nexora_b( 'تهران / استودیوی مستقل', 'TEHRAN / INDEPENDENT STUDIO' ) ); ?></span>
                <span><?php echo esc_html( nexora_b( 'معماری · داخلی · منظر', 'ARCHITECTURE · INTERIOR · LANDSCAPE' ) ); ?></span>
            </figcaption>
        </figure>
    </div>
    <div class="hero-stats reveal" data-reveal="up">
        <div><strong><?php echo esc_html( $b['stat_projects'] ); ?></strong><span><?php echo esc_html( nexora_b( 'پروژه تکمیل‌شده', 'completed projects' ) ); ?></span></div>
        <div><strong><?php echo esc_html( $b['stat_years'] ); ?></strong><span><?php echo esc_html( nexora_b( 'سال فعالیت', 'years in practice' ) ); ?></span></div>
        <div><strong><?php echo esc_html( $b['stat_cities'] ); ?></strong><span><?php echo esc_html( nexora_b( 'شهر', 'cities' ) ); ?></span></div>
        <div><strong><?php echo esc_html( $b['stat_awards'] ); ?></strong><span><?php echo esc_html( nexora_b( 'انتخاب فرضی دمو', 'fictional demo selections' ) ); ?></span></div>
    </div>
</section>

<section class="section shell">
    <div class="section-heading reveal" data-reveal="up">
        <div><span class="eyebrow">02 / <?php echo esc_html( nexora_t( 'selected' ) ); ?></span><h2><?php echo esc_html( nexora_b( $b['selected_title_fa'], $b['selected_title_en'] ) ); ?></h2></div>
        <a class="text-link" href="<?php echo esc_url( nexora_archive_url( 'nexora_project' ) ); ?>"><?php echo esc_html( nexora_t( 'all_projects' ) ); ?> ↗</a>
    </div>
    <div class="projects-grid featured-grid"><?php
        $q = new WP_Query( [ 'post_type' => 'nexora_project', 'posts_per_page' => 6, 'post_status' => 'publish', 'suppress_filters' => false ] );
        while ( $q->have_posts() ) : $q->the_post(); get_template_part( 'template-parts/project-card' ); endwhile;
        wp_reset_postdata();
    ?></div>
</section>

<section class="statement section">
    <div class="shell statement-grid reveal" data-reveal="up">
        <span class="eyebrow">03 / <?php echo esc_html( nexora_t( 'approach' ) ); ?></span>
        <p><?php echo esc_html( nexora_b( $b['approach_fa'], $b['approach_en'] ) ); ?></p>
    </div>
</section>

<section class="section shell">
    <div class="section-heading reveal" data-reveal="up">
        <div><span class="eyebrow">04 / <?php echo esc_html( nexora_t( 'services' ) ); ?></span><h2><?php echo esc_html( nexora_b( $b['services_title_fa'], $b['services_title_en'] ) ); ?></h2></div>
        <a class="text-link" href="<?php echo esc_url( nexora_archive_url( 'nexora_service' ) ); ?>"><?php echo esc_html( nexora_t( 'all_services' ) ); ?> ↗</a>
    </div>
    <div class="service-grid"><?php
        $s = new WP_Query( [ 'post_type' => 'nexora_service', 'posts_per_page' => 6, 'orderby' => 'menu_order', 'order' => 'ASC', 'suppress_filters' => false ] );
        $i = 1;
        while ( $s->have_posts() ) : $s->the_post(); ?>
            <article class="service-card reveal" data-reveal="up">
                <a href="<?php the_permalink(); ?>" class="service-media"><?php if ( has_post_thumbnail() ) { the_post_thumbnail( 'large', [ 'loading' => 'lazy', 'alt' => nexora_image_alt( 'hero' ) ] ); } ?></a>
                <div class="service-copy"><span><?php echo esc_html( str_pad( (string) $i, 2, '0', STR_PAD_LEFT ) ); ?></span><h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3><p><?php echo esc_html( get_the_excerpt() ); ?></p></div>
            </article>
        <?php $i++; endwhile; wp_reset_postdata(); ?>
    </div>
</section>

<section class="section shell client-section">
    <div class="section-heading reveal" data-reveal="up"><div><span class="eyebrow">05 / <?php echo esc_html( nexora_t( 'clients' ) ); ?></span><h2><?php echo esc_html( nexora_b( $b['clients_title_fa'], $b['clients_title_en'] ) ); ?></h2></div></div>
    <div class="client-logo-grid reveal" data-reveal="up" aria-label="<?php echo esc_attr( nexora_t( 'clients' ) ); ?>"><?php
        $clients = array_values( array_filter( array_map( 'trim', explode( ',', $b['clients_list'] ) ) ) );
        foreach ( $clients as $i => $client ) :
            $logo = get_template_directory_uri() . '/assets/images/clients/client-' . str_pad( (string) ( ( $i % 6 ) + 1 ), 2, '0', STR_PAD_LEFT ) . '.svg'; ?>
            <div class="client-logo"><img src="<?php echo esc_url( $logo ); ?>" alt="<?php echo esc_attr( $client ); ?>"><span><?php echo esc_html( $client ); ?></span></div>
        <?php endforeach; ?>
    </div>
</section>

<section class="section shell testimonials">
    <div class="section-heading reveal" data-reveal="up"><div><span class="eyebrow">06 / <?php echo esc_html( nexora_t( 'testimonials' ) ); ?></span><h2><?php echo esc_html( nexora_b( 'همکاری خوب، با گفت‌وگوی دقیق شروع می‌شود.', 'Good work begins with a precise conversation.' ) ); ?></h2></div></div>
    <div class="testimonial-grid"><?php
        $t = new WP_Query( [ 'post_type' => 'nexora_testimonial', 'posts_per_page' => 3, 'orderby' => 'menu_order', 'order' => 'ASC', 'suppress_filters' => false ] );
        while ( $t->have_posts() ) : $t->the_post(); ?>
            <blockquote class="testimonial-card reveal" data-reveal="up"><p>“<?php echo esc_html( wp_strip_all_tags( get_the_content() ) ); ?>”</p><cite><?php the_title(); ?></cite><small><?php echo esc_html( get_post_meta( get_the_ID(), '_testimonial_role', true ) ); ?></small></blockquote>
        <?php endwhile; wp_reset_postdata(); ?>
    </div>
</section>

<section class="cta-section section shell-wide reveal" data-reveal="image">
    <div class="cta-card">
        <img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/brand/studio-02.webp' ); ?>" alt="<?php echo esc_attr( nexora_b( 'فضای داخلی استودیوی نکسورا', 'Nexora studio interior' ) ); ?>" loading="lazy">
        <div class="cta-overlay" aria-hidden="true"></div>
        <div class="cta-copy"><span class="eyebrow">07 / <?php echo esc_html( nexora_t( 'new_project' ) ); ?></span><h2><?php echo esc_html( nexora_b( $b['cta_title_fa'], $b['cta_title_en'] ) ); ?></h2><a class="button button-light magnetic" href="<?php echo esc_url( nexora_page_url( 'contact' ) ); ?>"><?php echo esc_html( nexora_t( 'cta' ) ); ?> ↗</a></div>
    </div>
</section>
<?php get_footer(); ?>
