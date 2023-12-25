<?php
// api-integration.php:
if (!defined('AI_TOOLBOX_INIT')) {
    exit;
}
require_once 'db-integration.php';

function prepare_api_args($api_key, $api_version, $directive, $h2_count, $h3_count, $seo_keywords, $seo_keywords_avoid, $seo_question)
{
    $system_content  = "You are a content writer that lives in a framework.";
    $system_content .= " You are tasked to help users on writing or editing articles. Use the language of the user.";
    $system_content .= " Provide title, html content, and your suggestions in a structured way.";
    $system_content .= " html content will be html codes.";
    $system_content .= " for example [[[title]]]article title[[[/title]]][[[content]]]<h2>Header 1</h2><p>First paragraph</p><h2>Header 2</h2><p>Second paragraph</p><ul><li>Item 1</li><li>Item2</li></ul>[[[/content]]][[[suggestions]]]your suggestions about the article...[[[/suggestions]]]";
    $system_content .= " Allowed structure will be like [[[tag]]]response[[[/tag]]] in your responses.";
    $system_content .= " use [[[title]]] for article title.";
    $system_content .= " use [[[content]]] for article text content.";
    $system_content .= " use [[[suggestions]]] for your suggestions to show to the user also in html format.";
    $system_content .= " important: only give your answers in these tags: [[[title]]] [[[content]]] [[[suggestions]]] for your suggestions to show to the user also in html format.";
    $system_content .= " underline main and helper seo keywords in the content with <u> tag. so the user will know what words will be anchor links.";

    $user_content = $directive . "\n\n";

    if (!empty($seo_keywords)) {
        $user_content .= "- SEO keywords: " . $seo_keywords . "\n";
    }

    if (!empty($seo_keywords_avoid)) {
        $user_content .= "- SEO keywords to avoid: " . $seo_keywords_avoid . "\n";
    }

    if (!empty($seo_question)) {
        $user_content .= "- SEO question: " . $seo_question . "\n";
    }

    $user_content .= "- Requested H2 Count:--\n" . $h2_count . "\n";
    $user_content .= "- Requested H3 Count:--\n" . $h3_count . "\n";

    $messages = [
        ["role" => "system", "content" => $system_content],
        ["role" => "user", "content" => $user_content]
    ];

    return [
        'headers' => [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $api_key
        ],
        'body' => json_encode([
            'model' => $api_version,
            'messages' => $messages
        ]),
        'timeout' => 300
    ];
}




function call_openai_api()
{
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'ai_toolbox_call_meta_box_data')) {
        wp_send_json_error(['error' => 'Nonce verification failed.'], 403);
    }
    global $wpdb;
    $table_name = $wpdb->prefix . 'ai_toolbox';

    $api_key = get_option('ai_toolbox_chatgpt_api_key');
    $api_version = get_option('ai_toolbox_chatgpt_version');

    if (empty($api_key)) {
        echo json_encode([
            'error' => 'API key is missing.',
            'settingsLink' => admin_url('options-general.php?page=ai_toolbox_settings')
        ]);
        die();
    }

    // Debug Record
    if (false && wp_get_environment_type() === "development") {
        wp_send_json_success(['task_id' => 1]);
    }

    // Sanitize and validate the POST data
    $directive = sanitize_text_field($_POST['directive']);
    $h2_count = filter_input(INPUT_POST, 'h2_count', FILTER_SANITIZE_NUMBER_INT);
    $h3_count = filter_input(INPUT_POST, 'h3_count', FILTER_SANITIZE_NUMBER_INT);
    $seo_keywords = sanitize_text_field($_POST['seo_keywords']);
    $seo_keywords_avoid = sanitize_text_field($_POST['seo_keywords_avoid']);
    $seo_question = sanitize_text_field($_POST['seo_question']);

    // API args
    $args = prepare_api_args($api_key, $api_version, $directive, $h2_count, $h3_count, $seo_keywords, $seo_keywords_avoid, $seo_question);

    // Insert the request data and get the task ID
    $task_id = insert_request_data($table_name, $args, $api_version);

    // Schedule a custom action and pass the task ID and args to it
    wp_schedule_single_event(time(), 'ai_toolbox_process_request', [$task_id, $args]);

    // Return the task ID immediately
    wp_send_json_success(['task_id' => $task_id]);
}


// This function will be called by the WordPress cron job
function process_ai_toolbox_request($task_id, $args)
{
    $response = wp_remote_post("https://api.openai.com/v1/chat/completions", $args);
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'ai_toolbox';

    update_response_data($table_name, $response, $task_id);
}

add_action('wp_ajax_call_openai_api', 'call_openai_api');