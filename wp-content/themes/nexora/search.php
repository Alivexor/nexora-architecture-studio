<?php
get_header();
$term = get_search_query();
$groups = [
    'nexora_project' => [ 'label' => nexora_t( 'project_results' ), 'items' => [] ],
    'nexora_service' => [ 'label' => nexora_t( 'service_results' ), 'items' => [] ],
    'post'           => [ 'label' => nexora_t( 'journal_results' ), 'items' => [] ],
];
if ( have_posts() ) {
    while ( have_posts() ) {
        the_post();
        $type = get_post_type();
        if ( isset( $groups[ $type ] ) ) {
            $groups[ $type ]['items'][] = get_post();
        }
    }
}
?>
<section class="page-hero shell"><span class="eyebrow">01 / <?php echo esc_html( nexora_t( 'search' ) ); ?></span><h1><?php echo esc_html( nexora_t( 'search_results' ) ); ?> “<?php echo esc_html( $term ); ?>”</h1>
<form class="search-form-large" role="search" method="get" action="<?php echo esc_url( nexora_home_url() ); ?>">
<label class="screen-reader-text" for="nexora-search"><?php echo esc_html( nexora_t( 'search' ) ); ?></label>
<input id="nexora-search" type="search" name="s" autocomplete="off" data-search-suggest value="<?php echo esc_attr( $term ); ?>" aria-controls="nexora-search-suggestions" aria-autocomplete="list" aria-expanded="false">
<div id="nexora-search-suggestions" class="search-suggestions" data-search-suggestions role="listbox" hidden></div>
<button type="submit" aria-label="<?php echo esc_attr( nexora_t( 'search' ) ); ?>">↗</button>
</form></section>
<section class="section shell search-sections">
<?php $any = false; foreach ( $groups as $group ) : if ( ! $group['items'] ) { continue; } $any = true; ?>
<div class="search-group"><h2><?php echo esc_html( $group['label'] ); ?></h2><div class="search-list">
<?php foreach ( $group['items'] as $item ) : setup_postdata( $item ); ?><a href="<?php the_permalink(); ?>"><span><?php the_title(); ?></span><small><?php echo esc_html( get_the_excerpt() ); ?></small><b aria-hidden="true">↗</b></a><?php endforeach; wp_reset_postdata(); ?>
</div></div>
<?php endforeach; if ( ! $any ) : ?><p><?php echo esc_html( nexora_t( 'nothing' ) ); ?></p><?php endif; ?>
<div class="pagination"><?php the_posts_pagination( [ 'mid_size' => 1 ] ); ?></div>
</section>
<?php get_footer(); ?>
