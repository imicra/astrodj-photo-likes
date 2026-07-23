<?php

namespace PhotoLikes;

defined('ABSPATH') || exit;

class Ajax
{
    private string $table;

    public function __construct()
    {
        global $wpdb;

        $this->table = $wpdb->prefix . 'photo_likes';

        add_action('wp_ajax_photo_like', [$this, 'like']);
        add_action('wp_ajax_nopriv_photo_like', [$this, 'like']);
    }

    public function like()
    {
        check_ajax_referer('photo_likes', 'nonce');

        global $wpdb;

        $photo_id = absint($_POST['photo_id'] ?? 0);

        if (!$photo_id) {
            wp_send_json_error([
                'message' => 'Invalid photo.'
            ]);
        }

        $visitor = Visitor::hash();

        /*
         * UNIQUE(photo_id, visitor_hash)
         * гарантирует, что два одинаковых лайка невозможны.
         */

        $insert = $wpdb->insert(
            $this->table,
            [
                'photo_id'     => $photo_id,
                'visitor_hash' => $visitor,
                'created_at'   => current_time('mysql')
            ],
            [
                '%d',
                '%s',
                '%s'
            ]
        );

        if ($insert === false) {

            wp_send_json_error([
                'message' => 'already'
            ]);

        }

        wp_send_json_success([
            'likes' => self::countLikes($photo_id)
        ]);
    }

    public static function countLikes(int $photo_id): int
    {
        global $wpdb;

        return (int)$wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*)
                 FROM {$wpdb->prefix}photo_likes
                 WHERE photo_id=%d",
                $photo_id
            )
        );
    }

    public static function liked(int $photo_id): bool
    {
        global $wpdb;

        return (bool)$wpdb->get_var(
            $wpdb->prepare(
                "SELECT id
                 FROM {$wpdb->prefix}photo_likes
                 WHERE photo_id=%d
                 AND visitor_hash=%s",
                $photo_id,
                Visitor::hash()
            )
        );
    }
}