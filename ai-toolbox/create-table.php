<?php
global $ai_toolbox_db_version;
$ai_toolbox_db_version = '1.1';

function create_ai_toolbox_table() {
    global $wpdb;
    global $ai_toolbox_db_version;

    $table_name = $wpdb->prefix . 'ai_toolbox';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        item_id mediumint(9) NOT NULL,
        item_type varchar(50) NOT NULL,
        request longtext NOT NULL,
        request_version varchar(50) NOT NULL,
        request_time_utc datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        response longtext NOT NULL,
        response_status varchar(50) NOT NULL,
        response_time_utc datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        response_code INT DEFAULT 0,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    add_option('ai_toolbox_db_version', $ai_toolbox_db_version);
}

function ai_toolbox_update_db_check() {
    global $ai_toolbox_db_version;
    $installed_ver = get_option('ai_toolbox_db_version');

    if ($installed_ver != $ai_toolbox_db_version) {
        ai_toolbox_run_migration($installed_ver);
    }
}

function ai_toolbox_run_migration($installed_ver) {
    global $wpdb;
    global $ai_toolbox_db_version;
    
    $table_name = $wpdb->prefix . 'ai_toolbox';
    
    // add response_code
    if ($installed_ver < '1.1') {
        $wpdb->query("ALTER TABLE $table_name ADD `response_code` INT DEFAULT 0 AFTER `response_status`;");
    }
    update_option('ai_toolbox_db_version', $ai_toolbox_db_version);
}

add_action('plugins_loaded', 'ai_toolbox_update_db_check');
register_activation_hook(__FILE__, 'create_ai_toolbox_table');