<?php
if ( ! defined( 'ABSPATH' ) ) exit;
// db-integration.php

// Ensure the script is not accessed directly
if (!defined('AI_TOOLBOX_INIT')) {
    exit;
}

/**
 * Insert request data into the database and set the status to 'in_progress'.
 *
 * @param string $table_name The name of the database table.
 * @param array $args The arguments to be stored.
 * @param string $api_version The API version being used.
 * @return int The inserted row's ID.
 */
function ai_toolbox_insert_request_data($table_name, $args, $api_version)
{
    global $wpdb;
    $request_data = [
        'item_type' => 'Content',
        'request' => json_encode($args),
        'request_version' => $api_version,
        'request_time_utc' => current_time('mysql', 1),
        'response_status' => 'in_progress'
    ];
    $wpdb->insert($table_name, $request_data);
    return $wpdb->insert_id;
}

/**
 * Update the response data in the database.
 *
 * @param string $table_name The name of the database table.
 * @param WP_HTTP_Response|array $response The response to be stored.
 * @param int $task_id The ID of the task being updated.
 */
function ai_toolbox_update_response_data($table_name, $response, $task_id)
{
    global $wpdb;
    $response_code = wp_remote_retrieve_response_code($response);
    $is_success = ($response_code >= 200 && $response_code < 300) && !is_wp_error($response);

    $response_data = [
        'response' => $is_success ? wp_remote_retrieve_body($response) : (is_wp_error($response) && $response instanceof WP_Error ? $response->get_error_message() : wp_remote_retrieve_body($response)),
        'response_status' => $is_success ? 'success' : 'error',
        'response_code' => $response_code,
        'response_time_utc' => current_time('mysql', 1)
    ];
    $wpdb->update($table_name, $response_data, ['id' => $task_id]);
}
