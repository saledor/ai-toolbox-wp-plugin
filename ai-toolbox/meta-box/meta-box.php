<?php
if ( ! defined( 'ABSPATH' ) ) exit;
// meta-box.php:
if (!defined('AI_TOOLBOX_INIT')) {
    exit;
}
require_once 'api-integration.php';
require_once 'meta-box-actions.php';

/**
 * Add the AI Toolbox meta box.
 */
function ai_toolbox_add_meta_box()
{
    add_meta_box('ai_toolbox_meta_box', 'AI ToolBox', 'ai_toolbox_meta_box_callback', ['post', 'page'], 'side', 'default');
}

/**
 * Callback function for AI Toolbox meta box.
 *
 * @param WP_Post $post The post object.
 */
function ai_toolbox_meta_box_callback($post)
{
    // Check if the API key is set
    $chatgpt_api_key = get_option('ai_toolbox_chatgpt_api_key', '');

    // If API key is missing, display a warning and return
    if (empty($chatgpt_api_key)) {
        echo '<div class="alert alert-warning" role="alert">';
        echo 'Warning: API key is missing. Please go to the <a href="' . esc_url(admin_url('admin.php?page=ai_toolbox_settings_menu')) . '">Settings</a> page and enter the API key.';
        echo '</div>';
        return;
    }
    wp_nonce_field('ai_toolbox_call_meta_box_data', 'ai_toolbox_meta_box_nonce');
    echo '<div class="form-group">';
    echo '<label for="ai_toolbox_directive">Content Directive:</label>';
    echo '<textarea id="ai_toolbox_directive" name="ai_toolbox_directive" class="form-control" rows="4" placeholder="Example: Write an article about the importance of daily exercise."></textarea>';
    echo '</div>';


    echo '<div class="form-group">';
    echo '<label for="ai_toolbox_seo_question">Focused SEO Question:</label>';
    echo '<input type="text" id="ai_toolbox_seo_question" name="ai_toolbox_seo_question" class="form-control" placeholder="Example: How can daily exercise improve your health?">';
    echo '</div>';

    echo '<div class="form-group">';
    echo '<label for="ai_toolbox_seo_keywords">Focused SEO Keywords:</label>';
    echo '<input type="text" id="ai_toolbox_seo_keywords" name="ai_toolbox_seo_keywords" class="form-control" placeholder="Example: fitness,exercise,wellness">';
    echo '</div>';

    echo '<div class="form-group">';
    echo '<label for="ai_toolbox_seo_keywords_avoid">SEO Keywords to Avoid:</label>';
    echo '<input type="text" id="ai_toolbox_seo_keywords_avoid" name="ai_toolbox_seo_keywords_avoid" class="form-control" placeholder="Example: junk food, laziness">';
    echo '</div>';

    echo '<div class="form-group">';
    echo '<label for="ai_toolbox_h2_count">Requested H2 Tag Count: <span id="ai_toolbox_h2_count_val">3</span></label>';
    echo '<input type="range" class="form-range" min="0" max="10" step="1" id="ai_toolbox_h2_count" value="3" name="ai_toolbox_h2_count">';
    echo '</div>';

    echo '<div class="form-group">';
    echo '<label for="ai_toolbox_h3_count">Requested H3 Tag Count: <span id="ai_toolbox_h3_count_val">3</span></label>';
    echo '<input type="range" class="form-range" min="0" max="10" step="1" id="ai_toolbox_h3_count" value="3" name="ai_toolbox_h3_count">';
    echo '</div>';

    echo '<button id="ai_toolbox_generateBtn" class="btn btn-primary">Generate</button>';
    echo '<div id="ai_toolbox_result" class="mt-3"></div>';
    echo '<div id="ai_toolbox_progressBar" class="progress mt-3" style="display:none;">
            <div id="ai_toolbox_progress" class="progress-bar" style="width:0%;"></div>
          </div>';
}





/**
 * Parses the content from a raw string format.
 *
 * @param string $raw_content The raw content string.
 * @return array Parsed content elements.
 */
function ai_toolbox_parse_content($raw_content)
{
    $title = $content = $suggestions = '';

    if (preg_match('/\[\[\[title\]\]\](.*?)\[\[\[\/title\]\]\]/s', $raw_content, $title_matches)) {
        $title = $title_matches[1];
    }

    if (preg_match('/\[\[\[content\]\]\](.*?)\[\[\[\/content\]\]\]/s', $raw_content, $content_matches)) {
        $content = $content_matches[1];
    }

    if (preg_match('/\[\[\[suggestions\]\]\](.*?)\[\[\[\/suggestions\]\]\]/s', $raw_content, $suggestions_matches)) {
        $suggestions = $suggestions_matches[1];
    }

    return [
        'title' => $title,
        'content' => $content,
        'suggestions' => $suggestions,
    ];
}

/**
 * AJAX handler for getting task status.
 */
function ai_toolbox_handle_get_task_status_ajax()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'ai_toolbox';
    $task_id = isset($_REQUEST['task_id']) ? intval($_REQUEST['task_id']) : 0;

    // Get the task details from the database
    $task = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $task_id), ARRAY_A);

    if (!$task) {
        // If no task is found, send an error message
        wp_send_json_error("Task not found or invalid task ID: $task_id", 404);
        wp_die();
    }

    $response_content_raw = $task["response"];
    $response_status = $task["response_status"];
    $response_code = $task["response_code"];

    // Decode the 'response' field if it contains JSON
    if (isset($response_content_raw) && is_string($response_content_raw)) {
        $response_content = json_decode($response_content_raw, true);
    }

    // Check if the actual API call was successful
    if ($response_status === 'success' && $response_code >= 200 && $response_code < 300) {
        // Decode the JSON response content.
        // Parse the content.
        $parsed_content = ai_toolbox_parse_content($response_content['choices'][0]['message']['content']);
        if (!empty($parsed_content['content'])) {
            wp_send_json_success([
                'status' => 'success',
                'data' => $parsed_content
            ]);
        } else {
            // If parsed content is not an array or is empty, handle as an error
            wp_send_json_error([
                'status' => 'error',
                'message' => "Parsed content is empty or invalid. Full response of remote service: <i>" . esc_html($response_content_raw) . "</i>"
            ]);
        }
    } elseif ($response_status === 'error') {
        // Attempt to decode the JSON response
        $decoded_error = json_decode($response_content_raw, true);

        // Check if decoding was successful and the expected keys are present
        if (is_array($decoded_error) && isset($decoded_error['error'])) {
            $error_message = "Failed to make API call. Error: ";
            $error_message .= isset($decoded_error['error']['message']) ? $decoded_error['error']['message'] : 'Unknown error';
            if (isset($decoded_error['error']['code'])) {
                $error_message .= " (Code: " . $decoded_error['error']['code'] . ")";
            }
            if (isset($decoded_error['error']['param'])) {
                $error_message .= ", Parameter: " . $decoded_error['error']['param'];
            }
            if (isset($decoded_error['error']['type'])) {
                $error_message .= ", Type: " . $decoded_error['error']['type'];
            }

            wp_send_json_error([
                'status' => 'error',
                'message' => $error_message
            ]);
        } else {
            // If the response is not JSON or doesn't contain the expected keys, fall back to the raw message
            wp_send_json_error([
                'status' => 'error',
                'message' => 'Failed to make API call. Raw response: ' . $response_content_raw
            ]);
        }
    } elseif ($response_status === 'in_progress') {
        wp_send_json_success([
            'status' => 'in_progress',
            'message' => 'Task is in progress.'
        ]);
    } else {
        // Handle any unknown statuses
        wp_send_json_error([
            'status' => 'error',
            'message' => 'Unknown status.'
        ]);
    }

    wp_die();
}


// Register actions
add_action('add_meta_boxes', 'ai_toolbox_add_meta_box');
add_action('wp_ajax_get_task_status', 'ai_toolbox_handle_get_task_status_ajax');
add_action('ai_toolbox_process_request', function ($task_id, $args) {
    ai_toolbox_process_request($task_id, $args);
}, 10, 2);
