<?php

namespace PhotoLikes;

defined('ABSPATH') || exit;

class Visitor
{
    public static function id()
    {
        if (!isset($_COOKIE['astrodj_photo_likes_uuid'])) {

            $uuid = wp_generate_uuid4();

            setcookie(
                'astrodj_photo_likes_uuid',
                $uuid,
                time() + YEAR_IN_SECONDS * 5,
                COOKIEPATH,
                COOKIE_DOMAIN
            );

            $_COOKIE['astrodj_photo_likes_uuid'] = $uuid;
        }

        return $_COOKIE['astrodj_photo_likes_uuid'];
    }

    public static function hash()
    {
        return hash('sha256', self::id());
    }
}