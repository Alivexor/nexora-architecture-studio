<?php
if(!defined('ABSPATH')) exit;
function nexora_core_requirements_notice(){
    if(!current_user_can('manage_options')||nexora_core_is_polylang_ready()) return;
    echo '<div class="notice notice-warning"><p><strong>Nexora:</strong> '.esc_html(nexora_core_t('polylang_missing')).' <a href="'.esc_url(admin_url('plugin-install.php?s=polylang&tab=search&type=term')).'">Polylang</a></p></div>';
}
add_action('admin_notices','nexora_core_requirements_notice');
add_filter('pll_get_post_types',function($types,$hide){foreach(['nexora_project','nexora_service','nexora_team','nexora_testimonial'] as $type){if($hide) unset($types[$type]); else $types[$type]=$type;}return $types;},10,2);
