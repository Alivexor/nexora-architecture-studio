<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="icon" href="<?php echo esc_url( get_template_directory_uri() . '/assets/images/brand/favicon.svg' ); ?>" type="image/svg+xml">
<script>document.documentElement.classList.add('nexora-js');</script>
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#main"><?php echo esc_html( nexora_t( 'skip' ) ); ?></a>
<div class="noise" aria-hidden="true"></div><div class="cursor-dot" aria-hidden="true"></div>
<header class="site-header" id="site-header"><div class="shell nav-shell">
<?php nexora_brand_markup(); ?>
<button class="menu-toggle" type="button" aria-controls="main-navigation" aria-expanded="false"><span class="screen-reader-text"><?php echo esc_html( nexora_t( 'menu' ) ); ?></span><span></span><span></span></button>
<nav class="main-nav" id="main-navigation" aria-label="<?php echo esc_attr( nexora_t( 'primary_nav' ) ); ?>" data-mobile-nav>
<ul class="nav-links">
<?php if ( has_nav_menu( 'primary' ) ) { wp_nav_menu( [ 'theme_location' => 'primary', 'container' => false, 'items_wrap' => '%3$s', 'fallback_cb' => false, 'depth' => 1 ] ); } else { nexora_primary_fallback(); } ?>
</ul>
<div class="nav-actions"><?php nexora_language_switcher(); ?><a class="nav-cta magnetic" href="<?php echo esc_url( nexora_page_url( 'contact' ) ); ?>"><?php echo esc_html( nexora_t( 'nav_contact' ) ); ?> ↗</a></div>
</nav></div><div class="scroll-progress" aria-hidden="true"><i></i></div></header><main id="main">
