<?php

namespace PhotoLikes;

defined('ABSPATH') || exit;

class Database
{
    /**
     * Имя таблицы
     */
    private static function table(): string
    {
        global $wpdb;

        return $wpdb->prefix . 'photo_likes';
    }

    /**
     * Получить количество лайков
     *
     * @return array<int,int>
     */
    public static function getLikes(array $ids): array
    {
        global $wpdb;

        if (empty($ids)) {
            return [];
        }

        $placeholders = implode( ',', array_fill(0, count($ids), '%d') );

        $sql = $wpdb->prepare(
            "
            SELECT
                photo_id,
                COUNT(*) likes
            FROM " . self::table() . "
            WHERE photo_id IN ($placeholders)
            GROUP BY photo_id
            ",
            ...$ids
        );

        $rows = $wpdb->get_results($sql);

        $result = [];

        foreach ($rows as $row) {

            $result[(int)$row->photo_id] = (int)$row->likes;

        }

        return $result;
    }

    /**
     * Получить лайки пользователя
     *
     * @return array<int,bool>
     */
    public static function getLiked(
        array $ids,
        string $visitor
    ): array {

        global $wpdb;

        if (empty($ids)) {
            return [];
        }

        $placeholders = implode(
            ',',
            array_fill(0, count($ids), '%d')
        );

        $params = $ids;

        $params[] = $visitor;

        $sql = $wpdb->prepare(
            "
            SELECT photo_id
            FROM " . self::table() . "
            WHERE photo_id IN ($placeholders)
            AND visitor_hash=%s
            ",
            ...$params
        );

        $rows = $wpdb->get_col($sql);

        $result = [];

        foreach ($rows as $id) {

            $result[(int)$id] = true;

        }

        return $result;
    }

    /**
     * Добавить лайк
     */
    public static function addLike(
        int $photo,
        string $visitor
    ): bool {

        global $wpdb;

        return (bool)$wpdb->insert(

            self::table(),

            [

                'photo_id'=>$photo,

                'visitor_hash'=>$visitor,

                'created_at' => current_time('mysql', true)

            ],

            [

                '%d',

                '%s',

                '%s'

            ]

        );
    }

    /**
     * Статистика лайков
     *
     * @return array<object>
     */
    public static function getStatistics(
        int $perPage = 20,
        int $offset = 0,
        string $orderby = 'last_like',
        string $order = 'DESC'
    ): array {

        global $wpdb;

        $allowedOrderBy = [
            'title'     => 'p.post_title',
            'likes'     => 'likes',
            'last_like' => 'last_like',
        ];

        $orderby = $allowedOrderBy[$orderby] ?? 'last_like';

        $order = strtoupper($order);

        if (!in_array($order, ['ASC', 'DESC'], true)) {
            $order = 'DESC';
        }

        $postTypes = Config::POST_TYPES;

        $placeholders = implode(
            ',',
            array_fill(0, count($postTypes), '%s')
        );

        $sql = $wpdb->prepare(
            "
            SELECT

                p.ID,
                p.post_title,
                p.post_type,

                COUNT(l.id) AS likes,

                MAX(l.created_at) AS last_like

            FROM " . self::table() . " l

            INNER JOIN {$wpdb->posts} p

                ON p.ID = l.photo_id

            WHERE

                p.post_type IN ($placeholders)

            GROUP BY p.ID

            ORDER BY {$orderby} {$order}

            LIMIT %d OFFSET %d
            ",
            ...array_merge(
                $postTypes,
                [
                    $perPage,
                    $offset
                ]
            )
        );

        return $wpdb->get_results($sql);

    }

    /**
     * Количество фотографий с лайками
     */
    public static function countStatistics(): int
    {
        global $wpdb;

        $postTypes = Config::POST_TYPES;

        $placeholders = implode(
            ',',
            array_fill(0, count($postTypes), '%s')
        );

        $sql = $wpdb->prepare(
            "
            SELECT COUNT(*)

            FROM (

                SELECT p.ID

                FROM " . self::table() . " l

                INNER JOIN {$wpdb->posts} p

                    ON p.ID = l.photo_id

                WHERE

                    p.post_type IN ($placeholders)

                GROUP BY p.ID

            ) stats
            ",
            ...$postTypes
        );

        return (int)$wpdb->get_var($sql);
    }

    /**
     * Общая статистика
     */
    public static function getSummary(): object
    {
        global $wpdb;

        $postTypes = Config::POST_TYPES;

        $placeholders = implode(
            ',',
            array_fill(0, count($postTypes), '%s')
        );

        $sql = $wpdb->prepare(
            "
            SELECT

                COUNT(DISTINCT l.photo_id) AS photos,

                COUNT(l.id) AS likes,

                MAX(l.created_at) AS last_like

            FROM " . self::table() . " l

            INNER JOIN {$wpdb->posts} p

                ON p.ID = l.photo_id

            WHERE p.post_type IN ($placeholders)
            ",
            ...$postTypes
        );

        $summary = $wpdb->get_row($sql);

        if (!$summary) {

            $summary = (object) [
                'photos'    => 0,
                'likes'     => 0,
                'last_like' => null,
            ];

        }

        $summary->average = $summary->photos > 0
            ? round($summary->likes / $summary->photos, 1)
            : 0;

        return $summary;
    }

    /**
     * Самые популярные фотографии
     *
     * @return array<object>
     */
    public static function getTopPhotos(int $limit = 5): array
    {
        global $wpdb;

        $postTypes = Config::POST_TYPES;

        $placeholders = implode(
            ',',
            array_fill(0, count($postTypes), '%s')
        );

        $sql = $wpdb->prepare(
            "
            SELECT

                p.ID,
                p.post_title,
                p.post_type,

                COUNT(l.id) AS likes,

                MAX(l.created_at) AS last_like

            FROM " . self::table() . " l

            INNER JOIN {$wpdb->posts} p

                ON p.ID = l.photo_id

            WHERE p.post_type IN ($placeholders)

            GROUP BY p.ID

            ORDER BY likes DESC, last_like DESC

            LIMIT %d
            ",
            ...array_merge(
                $postTypes,
                [$limit]
            )
        );

        return $wpdb->get_results($sql);
    }

}