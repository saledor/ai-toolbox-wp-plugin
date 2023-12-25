<?php
/**
 * Plugin Name: AI ToolBox
 * Plugin URI: https://www.saledor.com/ai-toolbox
 * Description: AI ToolBox enhances your WordPress experience by leveraging AI to assist in content generation, editing, and product text suggestions.
 * Version: 1.0.0
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

register_activation_hook(__FILE__, 'create_ai_toolbox_table'); 

function ai_toolbox_menu() {
    
    add_menu_page('AI ToolBox', 'AI ToolBox', 'manage_options', 'ai_toolbox', 'ai_toolbox_main_menu_page', 'dashicons-hammer', 2);


    add_submenu_page('ai_toolbox', 'Settings', 'Settings', 'manage_options', 'settings_menu', 'settings_menu_page');
}

add_action('admin_menu', 'ai_toolbox_menu');

function enqueue_admin_scripts() {
    wp_enqueue_script('jquery');
}
add_action('admin_enqueue_scripts', 'enqueue_admin_scripts');

function enqueue_bootstrap() {
    // Add Bootstrap CSS
    wp_enqueue_style('bootstrap-css', 'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css');

    // Add Bootstrap JS and Popper.js (optional)
    wp_enqueue_script('popper-js', 'https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js', array(), '', true);
    wp_enqueue_script('bootstrap-js', 'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js', array('jquery', 'popper-js'), '', true);
}
add_action('admin_enqueue_scripts', 'enqueue_bootstrap');  // Admin Area

