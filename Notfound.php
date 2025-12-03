<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @package YourThemeName
 */

get_header();
?>

<div class="error-404 not-found">
    <header class="page-header">
        <h1 class="page-title"><?php esc_html_e( 'Oops! That page can&rsquo;t be found.', 'your-theme-textdomain' ); ?></h1>
    </header>

    <div class="page-content">
        <p><?php esc_html_e( 'It looks like nothing was found at this location. Maybe try one of the links below or a search?', 'your-theme-textdomain' ); ?></p>

        <?php get_search_form(); ?>

        <div class="error-404-content">
            <?php
            // Display recent posts
            $recent_posts = wp_get_recent_posts(array(
                'numberposts' => 5,
                'post_status' => 'publish',
            ));
            
            if (!empty($recent_posts)) :
            ?>
                <div class="recent-posts-section">
                    <h2><?php esc_html_e('Recent Posts', 'your-theme-textdomain'); ?></h2>
                    <ul>
                        <?php foreach ($recent_posts as $post) : ?>
                            <li>
                                <a href="<?php echo get_permalink($post['ID']); ?>">
                                    <?php echo esc_html($post['post_title']); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php
            // Display categories
            if (get_categories()) :
            ?>
                <div class="categories-section">
                    <h2><?php esc_html_e('Categories', 'your-theme-textdomain'); ?></h2>
                    <ul>
                        <?php
                        wp_list_categories(array(
                            'orderby'    => 'count',
                            'order'      => 'DESC',
                            'show_count' => 1,
                            'title_li'   => '',
                            'number'     => 10,
                        ));
                        ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php
            // Custom message with link to mobile devices archive (based on your site structure)
            ?>
            <div class="custom-suggestion">
                <h2><?php esc_html_e('Looking for Mobile Devices?', 'your-theme-textdomain'); ?></h2>
                <p>
                    <a href="<?php echo home_url('/mobile-devices/'); ?>">
                        <?php esc_html_e('Browse all mobile devices', 'your-theme-textdomain'); ?>
                    </a>
                </p>
            </div>
        </div>

        <?php
        // Display monthly archives
        the_widget('WP_Widget_Archives', array(
            'count' => 0,
            'dropdown' => 1,
        ), array(
            'before_title' => '<h2 class="widget-title">',
            'after_title' => '</h2>',
        ));
        ?>
    </div>
</div>

<?php
get_footer();