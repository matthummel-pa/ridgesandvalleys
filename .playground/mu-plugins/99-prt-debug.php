<?php
/** Dev-only inspector: /?prt_debug=1 (requires login as admin). */
add_action('template_redirect', function () {
    if (! isset($_GET['prt_debug']) || ! current_user_can('manage_options')) {
        return;
    }
    header('Content-Type: application/json');
    $out = ['pages' => [], 'patterns' => [], 'options' => []];
    foreach (['resources', 'contact', 'blog', 'pricing', 'home'] as $slug) {
        $p = get_page_by_path($slug);
        $out['pages'][$slug] = $p ? [
            'id' => $p->ID,
            'template' => get_post_meta($p->ID, '_wp_page_template', true),
            'content_len' => strlen($p->post_content),
            'content_head' => substr($p->post_content, 0, 120),
        ] : null;
    }
    foreach (\WP_Block_Patterns_Registry::get_instance()->get_all_registered() as $pat) {
        if (str_starts_with($pat['name'], 'pressroot/')) {
            $out['patterns'][] = $pat['name'] . ':' . strlen($pat['content'] ?? '');
        }
    }
    foreach (['prt_preview_seed_v1', 'prt_preview_seed_v4', 'show_on_front', 'page_on_front'] as $o) {
        $out['options'][$o] = get_option($o);
    }
    echo json_encode($out, JSON_PRETTY_PRINT);
    exit;
});
