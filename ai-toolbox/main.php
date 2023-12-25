<?php
if ( ! defined( 'ABSPATH' ) ) exit;
if (!defined('AI_TOOLBOX_INIT')) {
    exit; // Exit if accessed directly
}

/**
 * Renders the main menu page for AI ToolBox in the WordPress admin area.
 */
function ai_toolbox_main_menu_page() {
    ?>
    <div class="container">
        <h1>Main Menu - AI ToolBox</h1>
        <p>Welcome to AI ToolBox, your one-stop solution for enhancing your WordPress experience.</p>
        
        <div class="row">
            <div class="col-md-4">
                <h3>Content Generation</h3>
                <p>Generate blog posts, product descriptions, and more using state-of-the-art AI.</p>
            </div>
            <div class="col-md-4">
                <h3>Editing</h3>
                <p>Improve your existing content by running it through our advanced AI algorithms.</p>
            </div>
            <div class="col-md-4">
                <h3>Product Text Suggestions</h3>
                <p>Get tailor-made suggestions for your e-commerce product descriptions.</p>
            </div>
        </div>
        
        <h2>How to Get Started</h2>
        <ol>
            <li>Go to <a href="<?php echo admin_url('admin.php?page=ai_toolbox_settings_menu'); ?>">Settings</a> and insert your ChatGPT API key.</li>
            <li>Select your ChatGPT Version.</li>
            <li>Save and start using AI ToolBox features.</li>
        </ol>
    </div>
    <?php
}
