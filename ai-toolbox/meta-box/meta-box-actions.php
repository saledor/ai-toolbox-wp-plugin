<?php
// meta-box-actions.php:
if (!defined('AI_TOOLBOX_INIT')) {
    exit;
}

function enqueue_ai_toolbox_script()
{
    wp_enqueue_script(
        'ai_toolbox_meta_box_script',
        plugin_dir_url(__FILE__) . 'meta-box.js',
        array('jquery'),
        '1.0.0',
        true
    );

    // Localize the script with your data.
    $translation_array = array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'ai_toolbox_meta_box_nonce' => wp_create_nonce('ai_toolbox_call_meta_box_data')
    );
    wp_localize_script('ai_toolbox_meta_box_script', 'ai_toolbox', $translation_array);
}
add_action('admin_enqueue_scripts', 'enqueue_ai_toolbox_script');

function register_ai_toolbox_routes() {
    register_rest_route('ai-toolbox/v1', '/status/(?P<task_id>\d+)', array(
        'methods' => 'GET',
        'callback' => 'handle_get_task_status_ajax',
        'permission_callback' => '__return_true', // This line is added for public access
    ));
}

add_action('rest_api_init', 'register_ai_toolbox_routes');
