<?php
if (!defined('ABSPATH')) exit;
if (!defined('AI_TOOLBOX_INIT')) {
    exit; // Exit if accessed directly
}

function ai_toolbox_verify_api_key($api_key)
{
    $test_request = [
        'headers' => [
            'Authorization' => 'Bearer ' . $api_key
        ],
        'timeout' => 15
    ];

    $response = wp_remote_get("https://api.openai.com/v1/models", $test_request);

    if (is_wp_error($response)) {
        return false; // There was an error in the request
    }

    $response_code = wp_remote_retrieve_response_code($response);
    return ($response_code >= 200 && $response_code < 300);
}


function ai_toolbox_settings_menu_page()
{
    $api_key_validity = false;
    $chatgpt_api_key = get_option('ai_toolbox_chatgpt_api_key', '');
    // Check for POST request and nonce verification
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Verify the nonce field
        if (!isset($_POST['ai_tool_box_nonce_field']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['ai_tool_box_nonce_field'])), 'ai_tool_box_action')) {
            die('Security check failed');
        }
        // Define an array of acceptable version values
        $valid_versions = array('gpt-3.5-turbo-16k', 'gpt-4', 'gpt-3.5-turbo-1106');
        $sanitized_version = sanitize_text_field($_POST['chatgpt_version']);
        $sanitized_key = sanitize_text_field($_POST['chatgpt_api_key']);

        $api_key_validity = ai_toolbox_verify_api_key($sanitized_key);

        // Verify the API key
        if ($api_key_validity) {
            update_option('ai_toolbox_chatgpt_api_key', $sanitized_key);
            // Add a success message
            echo '<div class="updated"><p>API Key verified and saved successfully!</p></div>';
        } else {
            // Add an error message
            echo '<div class="error"><p>Invalid API Key. Please check and try again.</p></div>';
            // Optionally, clear the invalid API key from options
            update_option('ai_toolbox_chatgpt_api_key', '');
        }

        // Check if the submitted version is in the array of valid versions
        if (in_array($sanitized_version, $valid_versions)) {
            update_option('ai_toolbox_chatgpt_api_key', $sanitized_key);
            update_option('ai_toolbox_chatgpt_version', $sanitized_version);
        } else {
            // Handle the error appropriately, e.g., set an error message, log the attempt, etc.
            // For now, let's just die with an error message
            die('Invalid ChatGPT version submitted');
        }
        $chatgpt_api_key = get_option('ai_toolbox_chatgpt_api_key', ''); // reload
    } else {
        // Check the API key validity only when the page is initially loaded
        $api_key_validity = !empty($chatgpt_api_key) && ai_toolbox_verify_api_key($chatgpt_api_key);
    }

    
    $chatgpt_version = get_option('ai_toolbox_chatgpt_version', '');
?>

    <div class="container">
        <h3 class="my-3">AI ToolBox Settings</h3>
        <!-- Information box based on API key validity -->
        <?php if (!empty($chatgpt_api_key)) : ?>
            <?php if ($api_key_validity) : ?>
                <div class="alert alert-info" role="alert">
                    Your API key is set. You can now add a new page or post to use the plugin:
                    <ul>
                        <li><a href="<?php echo esc_url(admin_url('post-new.php')); ?>">Add New Post</a></li>
                        <li><a href="<?php echo esc_url(admin_url('post-new.php?post_type=page')); ?>">Add New Page</a></li>
                    </ul>
                </div>
            <?php else : ?>
                <div class="alert alert-warning" role="alert">
                    Warning: Your API key is set but seems to be invalid. Couldn't retrieve models from OpenAI.
                </div>
            <?php endif; ?>
        <?php else : ?>
            <div class="alert alert-warning" role="alert">
                Warning: API key is missing. Please get your API key from <a href="https://platform.openai.com/account/api-keys" target="_blank" rel="noopener noreferrer">OpenAI</a>.<br>
                AI ToolBox needs it to operate.
            </div>
        <?php endif; ?>
        <form method="post">
            <?php wp_nonce_field('ai_tool_box_action', 'ai_tool_box_nonce_field'); ?>
            <div class="form-group">
                <label for="chatgpt_api_key">ChatGPT API Key</label>
                <input type="text" name="chatgpt_api_key" id="chatgpt_api_key" class="form-control" placeholder="sk-..." value="<?php echo esc_attr($chatgpt_api_key); ?>">
                <small class="form-text text-muted">Get your API key from <a href="https://platform.openai.com/account/api-keys" target="_blank">OpenAI</a></small>
            </div>

            <div class="form-group">
                <label for="chatgpt_version">ChatGPT Version</label>
                <select name="chatgpt_version" id="chatgpt_version" class="form-control">
                    <option value="gpt-3.5-turbo-1106" <?php echo $chatgpt_version === 'gpt-3.5-turbo-1106' ? 'selected' : ''; ?>>v3.5 (gpt-3.5-turbo-1106)</option>
                    <option value="gpt-3.5-turbo-16k" <?php echo $chatgpt_version === 'gpt-3.5-turbo-16k' ? 'selected' : ''; ?>>v3.5 (gpt-3.5-turbo-16k)</option>
                    <option value="gpt-4" <?php echo $chatgpt_version === 'gpt-4' ? 'selected' : ''; ?>>v4 (gpt-4)</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Save</button>
        </form>
    </div>

<?php
}
