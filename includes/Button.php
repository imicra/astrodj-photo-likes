<?php

namespace PhotoLikes;

defined('ABSPATH') || exit;

class Button
{
    public function __construct()
    {
        add_shortcode('photo_likes', [$this, 'shortcode']);
    }

    public function shortcode($atts)
    {
        $atts = shortcode_atts([
            'id' => get_the_ID()
        ], $atts);

        return self::render((int)$atts['id']);
    }

    /**
     * Used in the following ways: in template echo \PhotoLikes\Button::render(get_the_ID());
     * or in editor [photo_likes] or [photo_likes id="123"]
     */
    public static function render(int $photo_id): string
    {
        $likes = Repository::likes($photo_id);

        $liked = Repository::liked($photo_id);

        ob_start();
        ?>

        <button
            class="photo-like <?php echo $liked ? 'liked' : ''; ?>"
            data-photo="<?php echo esc_attr($photo_id); ?>">

            <span class="heart">❤</span>

            <span class="count <?php echo $likes > 0 ? 'visible' : ''; ?>">
                <?php echo esc_html($likes); ?>
            </span>

        </button>

        <?php
        return ob_get_clean();
    }
}