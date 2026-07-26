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
        ob_start();
        ?>

        <button
            class="photo-like"
            data-photo="<?php echo esc_attr($photo_id); ?>">
            <span class="heart">
                <svg width="800px" height="800px" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <g transform="translate(0 -1028.4)">
                        <path d="m7 1031.4c-1.5355 0-3.0784 0.5-4.25 1.7-2.3431 2.4-2.2788 6.1 0 8.5l9.25 9.8 9.25-9.8c2.279-2.4 2.343-6.1 0-8.5-2.343-2.3-6.157-2.3-8.5 0l-0.75 0.8-0.75-0.8c-1.172-1.2-2.7145-1.7-4.25-1.7z" />
                    </g>
                </svg>
                <span class="count"></span>
            </span>
        </button>

        <?php
        return ob_get_clean();
    }
}