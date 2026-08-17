<?php
get_header();
$terms = get_terms( [ 'taxonomy' => 'nexora_project_type', 'hide_empty' => true, 'orderby' => 'name', 'order' => 'ASC' ] );
$terms = is_wp_error( $terms ) ? [] : $terms;
$years = [];
$qall = new WP_Query( [
    'post_type' => 'nexora_project', 'posts_per_page' => -1, 'post_status' => 'publish', 'fields' => 'ids',
    'suppress_filters' => false, 'no_found_rows' => true,
] );
foreach ( $qall->posts as $project_id ) {
    $year = nexora_meta( '_nexora_year', $project_id );
    if ( $year ) {
        $years[ $year ] = true;
    }
}
wp_reset_postdata();
krsort( $years );
global $wp_query;
$count = isset( $wp_query->found_posts ) ? (int) $wp_query->found_posts : 0;
$title = nexora_lang() === 'fa'
    ? sprintf( '%s پروژه، %s پاسخ متفاوت به زمینه.', number_format_i18n( $count ), number_format_i18n( $count ) )
    : sprintf( '%s projects, %s different responses to context.', number_format_i18n( $count ), number_format_i18n( $count ) );
?>
<section class="page-hero shell"><span class="eyebrow">01 / <?php echo esc_html( nexora_t( 'nav_projects' ) ); ?></span><h1><?php echo esc_html( $title ); ?></h1><p><?php echo esc_html( nexora_b( 'پروژه‌ها را بر اساس نوع یا سال فیلتر کنید. تمام تصاویر این مجموعه برای دموی NEXORA تولید شده‌اند.', 'Filter by type or year. Every image in this collection was created specifically for the NEXORA demo.' ) ); ?></p></section>
<section class="section shell project-archive">
<div class="project-filters" data-project-filters>
<button class="active" data-filter="all" type="button"><?php echo esc_html( nexora_t( 'filter_all' ) ); ?></button>
<?php foreach ( $terms as $term ) : ?><button type="button" data-filter="<?php echo esc_attr( $term->slug ); ?>"><?php echo esc_html( nexora_project_type_label( $term->slug ) ); ?></button><?php endforeach; ?>
<label class="screen-reader-text" for="nexora-year-filter"><?php echo esc_html( nexora_t( 'filter_year' ) ); ?></label>
<select id="nexora-year-filter" data-year-filter><option value="all"><?php echo esc_html( nexora_t( 'filter_year' ) ); ?> — <?php echo esc_html( nexora_t( 'filter_all' ) ); ?></option><?php foreach ( array_keys( $years ) as $year ) : ?><option value="<?php echo esc_attr( $year ); ?>"><?php echo esc_html( $year ); ?></option><?php endforeach; ?></select>
</div>
<div class="projects-grid filter-grid" data-project-grid><?php if ( have_posts() ) : while ( have_posts() ) : the_post(); get_template_part( 'template-parts/project-card' ); endwhile; endif; ?></div>
<p class="filter-empty" role="status" hidden><?php echo esc_html( nexora_t( 'no_projects' ) ); ?></p>
<div class="pagination"><?php the_posts_pagination( [ 'mid_size' => 1 ] ); ?></div>
</section>
<?php get_footer(); ?>
