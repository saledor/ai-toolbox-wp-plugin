<?php
if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * Plugin Name: AI ToolBox
 * Plugin URI: https://www.saledor.com/ai-toolbox
 * Description: AI ToolBox enhances your WordPress experience by leveraging AI to assist in content generation, editing, and product text suggestions.
 * Version: 1.0.1
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
 
     add_submenu_page('ai_toolbox', 'Settings', 'Settings', 'manage_options', 'settings_menu', 'ai_toolbox_settings_menu_page');
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
 