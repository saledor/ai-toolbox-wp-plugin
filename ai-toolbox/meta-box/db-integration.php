<?php
// db-integration.php:
if (!defined('AI_TOOLBOX_INIT')) {
    exit;
}

// Insert request data into DB and set status to 'in_progress'
function insert_request_data($table_name, $args, $api_version)
{
    global $wpdb;
    $request_data = [
        'item_type' => 'Content',
        'request' => json_encode($args),
        'request_version' => $api_version,
        'request_time_utc' => current_time('mysql', 1),
        'response_status' => 'in_progress' // Set the default status to 'in_progress'
    ];
    $wpdb->insert($table_name, $request_data);
    return $wpdb->insert_id;
}

// Update response data in DB
function update_response_data($table_name, $response, $task_id)
{
    

    global $wpdb;
    $response_code = wp_remote_retrieve_response_code($response);
    $is_success = ($response_code >= 200 && $response_code < 300) && !is_wp_error($response);

    $response_data = [
        'response' => $is_success ? wp_remote_retrieve_body($response) : (is_wp_error($response) ? $response->get_error_message() : wp_remote_retrieve_body($response)),
        'response_status' => $is_success ? 'success' : 'error',
        'response_code' => $response_code,
        'response_time_utc' => current_time('mysql', 1)
    ];
    $wpdb->update($table_name, $response_data, ['id' => $task_id]);
}