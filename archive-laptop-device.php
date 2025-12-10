<?php
/**
 * Template Name: laptop Brands Archive
 * * @package Ezoix_Tech_Blog
 */

get_header(); ?>

<div class="container">
    <div class="content-area">
        <main class="main-content">
            
            <header class="archive-header">
                <?php 
                $term = get_queried_object();
                $term_name = single_term_title('', false);
                $term_description = term_description();
                
                // Check if it's a taxonomy term or post type archive
                if (is_tax()) {
                    $term_name = single_term_title('', false);
                } elseif (is_post_type_archive()) {
                    $term_name = post_type_archive_title('', false);
                }
                ?>
                <h1 class="archive-title">
                    <?php 
                    if (is_tax()) {
                        echo esc_html($term_name) . ' Mobile Devices';
                    } elseif (is_post_type_archive()) {
                        echo 'Mobile Devices';
                    }
                    ?>
                </h1>
                
                <?php if ($term_description) : ?>
                    <div class="archive-description">
                        <?php echo wp_kses_post($term_description); ?>
                    </div>
                <?php endif; ?>
                
                <div class="archive-meta">
                    <span class="device-count">
                        <?php 
                        global $wp_query;
                        echo number_format($wp_query->found_posts) . ' ' . _n('device', 'devices', $wp_query->found_posts, 'ezoix'); 
                        ?>
                    </span>
                </div>
            </header>
            
            <div class="mobile-devices-grid">
                <?php if (have_posts()) : ?>
                    
                    <div class="grid-container">
                        <?php while (have_posts()) : the_post(); 
                            $device_price = get_field('device_price');
                            $device_status = get_field('device_status');
                            $device_rating = get_field('device_rating');
                            $specifications = get_field('specifications');
                            $author_name = get_the_author();
                        ?>
                        <article class="mobile-device-card">
                            <div class="device-image">
                                <a href="<?php the_permalink(); ?>">
                                    <div class="thumbnail-aspect-ratio-box">
                                        <?php if (has_post_thumbnail()) : ?>
                                            <?php the_post_thumbnail('grid-portrait', array('loading' => 'lazy', 'class' => 'category-post-thumbnail')); ?>
                                        <?php else : ?>
                                            <div class="placeholder-content">
                                                <span class="placeholder-icon dashicons dashicons-smartphone"></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </a>
                                
                                <?php if ($device_status) : ?>
                                    <span class="device-status status-<?php echo esc_attr($device_status); ?>">
                                        <?php echo ucfirst($device_status); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="device-content">
                                <h2 class="device-title">
                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                </h2>
                                
                                <div class="item-meta-yt">
                                    <p class="author-name"><?php echo esc_html($author_name); ?></p>

                                    <p class="post-date">
                                    <?php if ($device_price) : ?>
                                        <span class="price-value"><?php echo esc_html($device_price); ?></span>
                                    <?php endif; ?>
                                    <?php if ($device_rating) : 
                                        $rating = floatval($device_rating) / 2;
                                        if ($device_price) echo ' &bull; ';
                                    ?>
                                        <span class="rating-value">⭐ <?php echo number_format($rating, 1); ?>/5</span>
                                    <?php endif; ?>
                                    </p>
                                </div>
                            </div> <!-- Closing div added -->
                        </article>
                        <?php endwhile; ?>
                    </div>
                    
                    <div class="pagination">
                        <?php
                        the_posts_pagination(array(
                            'mid_size' => 2,
                            'prev_text' => __('← Previous', 'ezoix'),
                            'next_text' => __('Next →', 'ezoix'),
                        ));
                        ?>
                    </div>
                    
                <?php else : ?>
                    <div class="no-devices">
                        <p>
                            <?php 
                            if (is_tax()) {
                                echo 'No mobile devices found for ' . esc_html($term_name) . '.';
                            } elseif (is_post_type_archive()) {
                                echo 'No mobile devices found.';
                            }
                            ?>
                        </p>
                        <a href="<?php echo get_post_type_archive_link('mobile_device'); ?>" class="cta-button">
                            ← Browse All Devices
                        </a>
                    </div>
                <?php endif; ?>
            </div>
            
        </main>
    </div>
</div>

<?php get_footer(); ?>