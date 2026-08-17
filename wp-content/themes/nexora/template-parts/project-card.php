<?php
$terms = get_the_terms( get_the_ID(), 'nexora_project_type' );
$slugs = [];
if ( $terms && ! is_wp_error( $terms ) ) {
    $slugs = wp_list_pluck( $terms, 'slug' );
}
$year = nexora_meta( '_nexora_year' );
?>
<article class="project-card reveal" data-type="<?php echo esc_attr( implode( ' ', $slugs ) ); ?>" data-year="<?php echo esc_attr( $year ); ?>">
<a class="project-media" href="<?php the_permalink(); ?>"><?php if ( has_post_thumbnail() ) { the_post_thumbnail( 'large', [ 'loading' => 'lazy', 'alt' => nexora_image_alt( 'hero' ) ] ); } ?><span class="project-overlay"><span><?php echo esc_html( nexora_t( 'view_project' ) ); ?> ↗</span></span></a>
<div class="project-info"><div><h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3><p><?php echo esc_html( nexora_meta( '_nexora_location' ) ); ?><?php if ( $slugs ) : ?> · <?php echo esc_html( implode( ' / ', array_map( 'nexora_project_type_label', $slugs ) ) ); ?><?php endif; ?></p></div><span><?php echo esc_html( $year ); ?></span></div>
</article>
