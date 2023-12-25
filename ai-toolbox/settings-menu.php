<?php
if (!defined('AI_TOOLBOX_INIT')) {
    exit; // Exit if accessed directly
}

function settings_menu_page()
{
    // Check for POST request and nonce verification
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Verify the nonce field
        if (!isset($_POST['ai_tool_box_nonce_field']) || !wp_verify_nonce($_POST['ai_tool_box_nonce_field'], 'ai_tool_box_action')) {
            die('Security check failed');
        }
        // Define an array of acceptable version values
        $valid_versions = array('gpt-3.5-turbo-16k', 'gpt-4');
        $sanitized_version = sanitize_text_field($_POST['chatgpt_version']);
        $sanitized_key = sanitize_text_field($_POST['chatgpt_api_key']);

        // Check if the submitted version is in the array of valid versions
        if (in_array($sanitized_version, $valid_versions)) {
            update_option('ai_toolbox_chatgpt_api_key', $sanitized_key);
            update_option('ai_toolbox_chatgpt_version', $sanitized_version);
        } else {
            // Handle the error appropriately, e.g., set an error message, log the attempt, etc.
            // For now, let's just die with an error message
            die('Invalid ChatGPT version submitted');
        }
    }

    $chatgpt_api_key = get_option('ai_toolbox_chatgpt_api_key', '');
    $chatgpt_version = get_option('ai_toolbox_chatgpt_version', '');
?>

    <div class="container">
        <h3 class="my-3">AI ToolBox Settings</h3>
        <!-- Information box for when API key is set -->
        <?php if (!empty($chatgpt_api_key)) : ?>
            <div class="alert alert-info" role="alert">
                Your API key is set. You can now add a new page or post to use the plugin:
                <ul>
                    <li><a href="<?php echo admin_url('post-new.php'); ?>">Add New Post</a></li>
                    <li><a href="<?php echo admin_url('post-new.php?post_type=page'); ?>">Add New Page</a></li>
                </ul>
            </div>
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
                <input type="text" name="chatgpt_api_key" id="chatgpt_api_key" class="form-control" placeholder="sk-..." value="<?php echo $chatgpt_api_key; ?>">
                <small class="form-text text-muted">Get your API key from <a href="https://platform.openai.com/account/api-keys" target="_blank">OpenAI</a></small>
            </div>

            <div class="form-group">
                <label for="chatgpt_version">ChatGPT Version</label>
                <select name="chatgpt_version" id="chatgpt_version" class="form-control">
                    <option value="gpt-3.5-turbo-16k" <?php echo $chatgpt_version === 'gpt-3.5-turbo-16k' ? 'selected' : ''; ?>>v3.5 (gpt-3.5-turbo-16k)</option>
                    <option value="gpt-4" <?php echo $chatgpt_version === 'gpt-4' ? 'selected' : ''; ?>>v4 (gpt-4)</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Save</button>
        </form>
    </div>

<?php
}
