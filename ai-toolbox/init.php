<?php
if (!defined('ABSPATH')) exit;
/**
 * Plugin Name: AI ToolBox
 * Plugin URI: https://www.saledor.com/ai-toolbox
 * Description: AI ToolBox enhances your WordPress experience by leveraging AI to assist in content generation, editing, and product text suggestions.
 * Version: 1.0.3
 * Author: Mustafa Öztürk
 * Author URI: https://www.saledor.com
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: ai-toolbox
 */

define('AI_TOOLBOX_INIT', true);

include 'settings-menu.php';
include 'meta-box/meta-box.php';
include 'create-table.php';
include 'main.php';

// Register the function to create the database table upon plugin activation.
register_activation_hook(__FILE__, 'ai_toolbox_create_ai_toolbox_table');

/**
 * Adds the AI Toolbox menu to the WordPress admin area.
 */
function ai_toolbox_add_menu()
{
    add_menu_page('AI ToolBox', 'AI ToolBox', 'manage_options', 'ai_toolbox', 'ai_toolbox_main_menu_page', 'dashicons-hammer', 2);

    // Add submenu page
    add_submenu_page(
        'ai_toolbox', // Parent slug
        'AI ToolBox Settings', // Page title
        'Settings', // Menu title
        'manage_options', // Capability
        'ai_toolbox_settings_menu', // Menu slug
        'ai_toolbox_settings_menu_page' // Function to display the settings page
    );
}

add_action('admin_menu', 'ai_toolbox_add_menu');

/**
 * Enqueues admin scripts (jQuery).
 */
function ai_toolbox_enqueue_admin_scripts()
{
    wp_enqueue_script('jquery');
}
add_action('admin_enqueue_scripts', 'ai_toolbox_enqueue_admin_scripts');

/**
 * Enqueues Bootstrap assets in the admin area.
 */
function ai_toolbox_enqueue_bootstrap()
{
    // Enqueue Bootstrap CSS
    wp_enqueue_style('bootstrap-css', plugin_dir_url(__FILE__) . 'css/bootstrap.min.css');

    // Enqueue Popper JS
    wp_enqueue_script('popper-js', plugin_dir_url(__FILE__) . 'js/popper.min.js', array(), '', true);

    // Enqueue Bootstrap JS
    wp_enqueue_script('bootstrap-js', plugin_dir_url(__FILE__) . 'js/bootstrap.min.js', array('jquery', 'popper-js'), '', true);
}
add_action('admin_enqueue_scripts', 'ai_toolbox_enqueue_bootstrap');


/**
 * Adds a settings link to the plugin action links.
 *
 * @param array $links Existing action links for the plugin.
 * @return array Modified action links array.
 */
function ai_toolbox_add_settings_link($links) {
    $settings_link = '<a href="' . admin_url('admin.php?page=ai_toolbox_settings_menu') . '">Settings</a>';
    array_unshift($links, $settings_link); // Add to the beginning of the links array
    return $links;
}

// Hook the function into the 'plugin_action_links_' filter
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'ai_toolbox_add_settings_link');
