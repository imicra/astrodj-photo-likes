<?php

namespace PhotoLikes;

defined('ABSPATH') || exit;

class Install
{
    public static function activate()
    {
        global $wpdb;

        $table = $wpdb->prefix . 'photo_likes';

        $charset = $wpdb->get_charset_collate();

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $sql = "CREATE TABLE {$table} (

            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

            photo_id BIGINT UNSIGNED NOT NULL,

            visitor_hash CHAR(64) NOT NULL,

            created_at DATETIME NOT NULL,

            PRIMARY KEY (id),

            UNIQUE KEY photo_visitor (photo_id, visitor_hash),

            KEY photo (photo_id)

        ) {$charset};";

        dbDelta($sql);
    }
}