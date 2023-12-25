<?php
if ( ! defined( 'ABSPATH' ) ) exit;
// meta-box-actions.php

// Ensure the script is not accessed directly
if (!defined('AI_TOOLBOX_INIT')) {
    exit;
}

/**
 * Enqueue the AI Toolbox script for the meta box.
 */
function ai_toolbox_enqueue_meta_box_script()
{
    wp_enqueue_script(
        'ai_toolbox_meta_box_script',
        plugin_dir_url(__FILE__) . 'meta-box.js',
        array('jquery'),
        '1.0.0',
        true
    );

    // Localize the script with data for AJAX calls.
    $translation_array = array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'ai_toolbox_meta_box_nonce' => wp_create_nonce('ai_toolbox_call_meta_box_data')
    );
    wp_localize_script('ai_toolbox_meta_box_script', 'ai_toolbox', $translation_array);
}
add_action('admin_enqueue_scripts', 'ai_toolbox_enqueue_meta_box_script');

/**
 * Register routes for the AI Toolbox REST API.
 */
function ai_toolbox_register_routes() {
    register_rest_route('ai-toolbox/v1', '/status/(?P<task_id>\d+)', array(
        'methods' => 'GET',
        'callback' => 'ai_toolbox_handle_get_task_status_ajax',
        'permission_callback' => '__return_true', // Allows public access
    ));
}

add_action('rest_api_init', 'ai_toolbox_register_routes');
